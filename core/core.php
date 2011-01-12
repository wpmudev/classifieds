<?php
/**
 * Classifieds Core Class
 **/
if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

    /** @var string $plugin_url Plugin URL */
    var $plugin_url    = CF_PLUGIN_URL;
    /** @var string $plugin_dir Path to plugin directory */
    var $plugin_dir    = CF_PLUGIN_DIR;
    /** @var string $plugin_prefix Plugin prefix */
    var $plugin_prefix = 'cf_';
    /** @var string $text_domain The text domain for strings localization */
    var $text_domain   = 'classifieds';
    /** @var string $post_type Plugin post type */
    var $post_type     = 'classifieds';
    /** @var array $taxonomies Post taxonomies */
    var $taxonomy_objects;
    /** @var array $taxonomies Post taxonomies */
    var $taxonomy_names;
    /** @var array $custom_fields The custom fields associated with this post type */
    var $custom_fields = array();
    /** @var string $custom_fields_prefix The custom fields DB prefix */
    var $custom_fields_prefix = '_ct_';
    /** @var string $options_name The name of the plugin options entry in DB */
    var $options_name  = 'classifieds_options';
    /** @var string User role */
    var $user_role = 'cf_member';
    /** @var boolean True if submitted form is valid. */
    var $form_valid = true;
    /** @var boolean True if BuddyPress is active. */
    var $bp_active;
    /** @var boolean Login error flag */
    var $login_error;

    /**
     * Constructor.
     *
     * @return void
     **/
    function Classifieds_Core() {
        add_action( 'init', array( &$this, 'init' ) );
    }

    /**
     * Intiate plugin.
     *
     * @return void
     **/
    function init() {
        add_action( 'wp_loaded', array( &$this, 'handle_login' ) );
        add_action( 'wp_loaded', array( &$this, 'scheduly_expiration_check' ) );
        add_action( 'check_expiration_dates', array( &$this, 'check_expiration_dates_callback' ) );
        add_shortcode( 'classifieds_checkout', array( &$this, 'classifieds_checkout_shortcode' ) );
    }

    /**
     * Initiate variables.
     *
     * @return void
     **/
    function init_vars() {
        $this->taxonomy_objects = get_taxonomies( array( 'object_type' => array( $this->post_type ), '_builtin' => false ), 'objects' );
        $this->taxonomy_names   = get_taxonomies( array( 'object_type' => array( $this->post_type ), '_builtin' => false ), 'names' );
        /* Get all custom fields values with their ID's as keys */
        $custom_fields = get_site_option('ct_custom_fields');
        if ( is_array( $custom_fields ) ) {
            foreach ( $custom_fields as $key => $value ) {
                if ( in_array( $this->post_type, $value['object_type'] ) );
                    $this->custom_fields[$key] = $value;
            }
        }
        /* Assign key 'duration' to predifined Custom Field ID */
        $this->custom_fields['duration'] = '_ct_selectbox_4cf582bd61fa4';
    }

    /**
     * Process login request.
     *
     * @param string $username 
     * @param string $password
     * @return object $result->errors 
     **/
    function login( $username, $password ) {
        /* Check whether the required information is submitted */
        if ( empty( $username ) || empty( $password ) )
            return __( 'Please fill in the required fields.', $this->text_domain );
        /* Build the login credentials */
        $credentials = array( 'remember' => true, 'user_login' => $username, 'user_password' => $password );
        /* Sign the user in and get the result */
        $result = wp_signon( $credentials );
        if ( isset( $result->errors )) {
            if ( isset( $result->errors['invalid_username'] ))
                return $result->errors['invalid_username'][0];
            elseif ( isset( $result->errors['incorrect_password'] ))
                return $result->errors['incorrect_password'][0];
        }
    }

    /**
     * Handle user login.
     *
     * @return void
     **/
    function handle_login() {
        if ( isset( $_POST['login_submit'] ) )
            $this->login_error = $this->login( $_POST['username'], $_POST['password'] );
    }

    /**
     * Insert User
     *
     * @param <type> $email
     * @param <type> $first_name
     * @param <type> $last_name
     * @return <type>
     **/
    function insert_user( $email, $first_name, $last_name, $billing ) {
        require_once( ABSPATH . WPINC . '/registration.php' );
        // variables
        $user_login     = sanitize_user( strtolower( $first_name ));
        $user_email     = $email;
        $user_pass      = wp_generate_password();
        if ( username_exists( $user_login ) )
            $user_login .= '-' . sanitize_user( strtolower( $last_name ));
        if ( username_exists( $user_login ) )
            $user_login .= rand(1,9);
        if ( email_exists( $user_email )) {
            $user = get_user_by( 'email', $user_email );

            if ( $user ) {
                wp_update_user( array( 'ID' => $user->ID, 'role' => $this->user_role ) );
                update_user_meta( $user->ID, 'dp_billing', $billing );
                $credentials = array( 'remember'=>true, 'user_login' => $user->user_login, 'user_password' => $user->user_pass );
                wp_signon( $credentials );
                return;
            }
        }
        $user_id = wp_insert_user( array(
            'user_login'   => $user_login,
            'user_pass'    => $user_pass,
            'user_email'   => $email,
            'display_name' => $first_name . ' ' . $last_name,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'role'         => $this->user_role
        ) ) ;
        update_user_meta( $user_id, 'dp_billing', $billing );
        wp_new_user_notification( $user_id, $user_pass );
        $credentials = array( 'remember'=>true, 'user_login' => $user_login, 'user_password' => $user_pass );
        wp_signon( $credentials );
    }

    /**
     * Update or insert ad if no ID is passed.
     *
     * @param array $params Array of $_POST data
     * @param array|NULL $file Array of $_FILES data
     * @return void
     **/
    function update_ad( $params, $file = NULL ) {
        $current_user = wp_get_current_user();
        /* Construct args for the new post */
        $args = array(
            /* If empty ID insert Ad insetad of updating it */
            'ID'             => $params['post_id'],
            'post_title'     => $params['title'],
            'post_content'   => $params['description'],
            'post_status'    => $params['status'],
            'post_author'    => $current_user->ID,
            'post_type'      => $this->post_type,
            'ping_status'    => 'closed',
            'comment_status' => 'closed'
        );
        /* Insert page and get the ID */
        $post_id = wp_insert_post( $args );
        if ( $post_id ) {
            /* Set object terms */
            foreach ( $params['terms'] as $taxonomy => $terms  )
                wp_set_object_terms( $post_id, $terms, $taxonomy );
            /* Set custom fields data */
            if ( is_array( $params['custom_fields'] ) ) {
                foreach ( $params['custom_fields'] as $key => $value )
                    update_post_meta( $post_id, $key, $value );
            }
            /* Require WordPress utility functions for handling media uploads */
            require_once( ABSPATH . '/wp-admin/includes/media.php' );
            require_once( ABSPATH . '/wp-admin/includes/image.php' );
            require_once( ABSPATH . '/wp-admin/includes/file.php' );
            /* Upload the image ( handles creation of thumbnails etc. ), set featured image  */
            if ( empty( $file['image']['error'] )) {
                $thumbnail_id = media_handle_upload( 'image', $post_id );
                update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );
            }
       }
    }

    /**
     * Validate firelds
     * 
     * @param array $params $_POST data
     * @param array|NULL $file $_FILES data
     * @return void
     **/
    function validate_fields( $params, $file = NULL ) {
        if ( empty( $params['title'] ) || empty( $params['description'] ) || empty( $params['terms'] ) || empty( $params['status'] )) {
            $this->form_valid = false;
        }
        if ( $file['image']['error'] !== 0 ) {
            $this->form_valid = false;
        }
    }

    /**
     * Save custom fields data
     *
     * @param int $post_id The post id of the post being edited
     * @return NULL If there is autosave attempt
     **/
    function save_expiration_date( $post_id ) {
        /* prevent autosave from deleting the custom fields */
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
            return;
        /* Update  */
        if ( isset( $_POST[$this->custom_fields['duration']] ) ) {
            $date = $this->calculate_expiration_date( $post_id, $_POST[$this->custom_fields['duration']] );
            update_post_meta( $post_id, '_expiration_date', $date );
        } if ( isset( $_POST['custom_fields'][$this->custom_fields['duration']] ) ) {
            $date = $this->calculate_expiration_date( $post_id, $_POST['custom_fields'][$this->custom_fields['duration']] );
            update_post_meta( $post_id, '_expiration_date', $date );
        } elseif ( isset( $_POST['duration'] ) ) {
            $date = $this->calculate_expiration_date( $post_id, $_POST['duration'] );
            update_post_meta( $post_id, '_expiration_date', $date );
        }
    }

    /**
     * Checkout shortcode.
     *
     * @return NULL If the payment gateway options are not configured.
     **/
    function classifieds_checkout_shortcode() {
        /* Get site options */
        $options = $this->get_options();
        if ( is_user_logged_in() ) {
            /** @todo Set redirect */
            //$this->js_redirect( get_bloginfo('url') );
        }
        if ( empty( $options['paypal'] ) ) {
            $this->render_front( 'classifieds/checkout', array( 'step' => 'disabled' ) );
            return;
        }
        if ( isset( $_POST['terms_submit'] ) ) {
            if ( empty( $_POST['tos_agree'] ) || empty( $_POST['billing'] ) ) {
                if ( empty( $_POST['tos_agree'] ))
                    add_action( 'tos_invalid', create_function('', 'echo "class=\"error\"";') );
                if ( empty( $_POST['billing'] ))
                    add_action( 'billing_invalid', create_function('', 'echo "class=\"error\"";') );
                $this->render_front( 'includes/checkout', array( 'step' => 'terms' ) );
            } else {
                $this->render_front('includes/checkout', array( 'step' => 'payment_method' ) );
            }
        } elseif ( isset( $_POST['login_submit'] ) ) {
            if ( isset( $this->login_error )) {
                add_action( 'login_invalid', create_function('', 'echo "class=\"error\"";') );
                $this->render_front( 'includes/checkout', array( 'step' => 'terms', 'error' => $this->login_error ) );
            } else {
                $this->js_redirect( get_bloginfo('url') );
            }
        } elseif ( isset( $_POST['payment_method_submit'] )) {
            if ( $_POST['payment_method'] == 'paypal' ) {
                $checkout = new Classifieds_Core_PayPal();
                $checkout->call_shortcut_express_checkout( $_POST['cost'] );
            } elseif ( $_POST['payment_method'] == 'cc' ) {
                $this->render_front( 'includes/checkout', array( 'step' => 'cc_details' ) );
            }
        } elseif ( isset( $_POST['direct_payment_submit'] ) ) {
            $checkout = new Classifieds_Core_PayPal();
            $result = $checkout->direct_payment( $_POST['total_amount'], $_POST['cc_type'], $_POST['cc_number'], $_POST['exp_date'], $_POST['cvv2'], $_POST['first_name'], $_POST['last_name'], $_POST['street'], $_POST['city'], $_POST['state'], $_POST['zip'], $_POST['country_code'] );
        } elseif ( isset( $_REQUEST['token'] ) && !isset( $_POST['confirm_payment_submit'] ) ) {
            $checkout = new Classifieds_Core_PayPal();
            $result = $checkout->get_shipping_details();
            $this->render_front( 'includes/checkout', array( 'step' => 'confirm_payment', 'transaction_details' => $result ) );
        } elseif ( isset( $_POST['confirm_payment_submit'] ) ) {
            $checkout = new Classifieds_Core_PayPal();
            $result = $checkout->confirm_payment( $_POST['total_amount'] );
            if ( strtoupper( $result['ACK'] ) == 'SUCCESS' || strtoupper( $result['ACK'] ) == 'SUCCESSWITHWARNING' ) {
                /** @todo Insert User */
                // $this->insert_user( $_POST['email'], $_POST['first_name'], $_POST['last_name'], $_POST['billing'] );
                $this->render_front( 'includes/checkout', array( 'step' => 'success' ) );
            }
        } else {
            $this->render_front( 'includes/checkout', array( 'step' => 'terms' ) );
        }
    }

    /**
     * Calculate the Unix time stam of the modified posts
     *
     * @param int|string $post_id
     * @param string $duration Valid value: "1 Week", "2 Weeks" ... etc
     * @return int Unix timestamp
     **/
    function calculate_expiration_date( $post_id, $duration ) {
        $post = get_post( $post_id );
        $expiration_date = get_post_meta( $post_id, '_expiration_date', true );
        if ( empty( $expiration_date ) )
            $expiration_date = time();
        elseif ( $expiration_date < time() )
            $expiration_date = time();
        $date = strtotime( "+{$duration}", $expiration_date );
        return $date;
    }

    /**
     * Get formated expiration date.
     *
     * @param int|string $post_id
     * @return string Date/Time formated string
     **/
    function get_expiration_date( $post_id ) {
        $date = get_post_meta( $post_id, '_expiration_date', true );
        if ( !empty( $date ) )
            return date( get_option('date_format'), $date );
        else
            return __( 'No expiration date set.', $this->text_domain );
    }

    /**
     * Schedule expiration check for twice daily.
     *
     * @return void
     **/
    function scheduly_expiration_check() {
        if ( !wp_next_scheduled( 'check_expiration_dates' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'check_expiration_dates' );
        }
    }

    /**
     * Check each post from the used post type and compare the expiration date/time
     * with the current date/time. If the post is expired update it's status.
     *
     * @return void
     **/
    function check_expiration_dates_callback() {
        $posts = get_posts( array( 'post_type' => $this->post_type, 'numberposts' => 0 ) );
        foreach ( $posts as $post ) {
            $expiration_date = get_post_meta( $post->ID, '_expiration_date', true );
            if ( empty( $expiration_date ) )
                $this->process_status( $post->ID, 'draft' );
            elseif ( $expiration_date < time() )
                $this->process_status( $post->ID, 'private' );
        }
    }


    /**
     * Save plugin options.
     *
     * @param  array $params The $_POST array 
     * @return die() if _wpnonce is not verified
     **/
    function save_options( $params ) {
        if ( wp_verify_nonce( $params['_wpnonce'], 'verify' ) ) {
            /* Remove unwanted parameters */
            unset( $params['_wpnonce'], $params['_wp_http_referer'], $params['save'] );
            /* Update options by merging the old ones */
            $options = $this->get_options();
            $options = array_merge( $options, array( $params['key'] => $params ) );
            update_option( $this->options_name, $options );
        } else {
            die( __( 'Security check failed!', $this->text_domain ) );
        }           
    }

    /**
     * Get plugin options.
     *
     * @param  string|NULL $key The key for that plugin option.
     * @return array $options Plugin options or empty array if no options are found
     **/
    function get_options( $key = NULL ) {
        $options = get_option( $this->options_name );
        $options = is_array( $options ) ? $options : array();
        /* Check if specific plugin option is requested and return it */
        if ( isset( $key ) && array_key_exists( $key, $options ) ) 
            return $options[$key];
        else 
            return $options;
    }

    /**
     * Process post status. 
     *
     * @global object $wpdb
     * @param  string $post_id
     * @param  string $status
     * @return void
     **/
    function process_status( $post_id, $status ) {
        global $wpdb;
        $wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $post_id ), array( '%s' ), array( '%d' ) );
    }

    /**
	 * Renders an admin section of display code.
	 *
	 * @param  string $name Name of the admin file(without extension)
	 * @param  string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
    function render_admin( $name, $vars = array() ) {
		foreach ( $vars as $key => $val )
			$$key = $val;
		if ( file_exists( "{$this->plugin_dir}/ui-admin/{$name}.php" ) )
			include "{$this->plugin_dir}/ui-admin/{$name}.php";
		else
			echo "<p>Rendering of admin template {$this->plugin_dir}/ui-admin/{$name}.php failed</p>";
	}

    /**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param  string $name Name of the admin file(without extension)
	 * @param  string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
	function render_front( $name, $vars = array() ) {
        /* Construct extra arguments */
		foreach ( $vars as $key => $val )
			$$key = $val;
        /* Include templates */
        if ( file_exists( get_template_directory() . "/{$name}.php" ) )
            include get_template_directory() . "/{$name}.php";
        elseif ( file_exists( "{$this->plugin_dir}/ui-front/buddypress/{$name}.php" ) && $this->bp_active )
            include "{$this->plugin_dir}/ui-front/buddypress/{$name}.php";
        elseif ( file_exists( "{$this->plugin_dir}/ui-front/general/{$name}.php" ) )
            include "{$this->plugin_dir}/ui-front/general/{$name}.php";
        else
            echo "<p>Rendering of template {$name}.php failed</p>";
	}

    /**
     * Redirect using JavaScript. Usful if headers are already sent.
     *
     * @param string $url The URL to which the function should redirect
     **/
    function js_redirect( $url ) { ?>
        <p><?php _e( 'You are being redirected. Please wait.', $this->text_domain );  ?></p>
        <img src="<?php echo $this->plugin_url .'/ui-front/general/images/loader.gif'; ?>" alt="<?php _e( 'You are being redirected. Please wait.', $this->text_domain );  ?>" />
        <script type="text/javascript">
            //<![CDATA[
            window.location = "<?php echo $url; ?>"
            //]]>
        </script> <?php
    }
}
endif;

/* Initiate Class */
if ( class_exists('Classifieds_Core') )
	$__classifieds_core = new Classifieds_Core();
?>