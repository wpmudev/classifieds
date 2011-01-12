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

    /**
     * Constructor.
     *
     * @return void
     **/
    function Classifieds_Core() {
        add_action( 'init', array( &$this, 'init' ) );
        /* Start session */
        if ( !session_id() )
            add_action( 'init', 'session_start' );
    }

    /**
     * Init plugin.
     *
     * @return void
     **/
    function init() {
        $this->create_main_pages();
        add_shortcode( 'classifieds_ads', array( &$this, 'ads_shortcode' ) );
        add_shortcode( 'classifieds_checkout', array( &$this, 'checkout_shortcode' ) );
        add_action( 'wp_head', array( &$this, 'print_main_styles' ) );
    }

    /**
     * Initiate variables.
     *
     * @return void
     **/
    function init_vars() {
        $this->taxonomy_objects = get_taxonomies( array( 'object_type' => array( $this->post_type ), '_builtin' => false ), 'objects' );
        $this->taxonomy_names   = get_taxonomies( array( 'object_type' => array( $this->post_type ), '_builtin' => false ), 'names' );
        $custom_fields = get_site_option('ct_custom_fields');
        foreach ( $custom_fields as $key => $value ) {
            if ( in_array( $this->post_type, $value['object_type'] ) );
                $this->custom_fields[$key] = $value;
        }
    }

    /**
     * Template redirect.
     *
     * @global <type> $wp_query 
     **/
    function template_redirect() {
        global $wp_query;
        cf_debug( $wp_query );
    }

    /**
     * Create the main Classifieds page.
     *
     * @return
     **/
    function create_main_pages() {
        $page['classifieds'] = get_page_by_title('Classifieds');
        if ( !isset( $page['classifieds'] ) ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'Classifieds',
                'post_content'   => '[classifieds_ads]',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            /* Insert page and get the ID */
            wp_insert_post( $args );
        }
        $page['checkout'] = get_page_by_title('Classifieds Checkout');
        if ( !isset( $page['checkout'] ) ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'Classifieds Checkout',
                'post_content'   => '[classifieds_checkout]',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            /* Insert page and get the ID */
            wp_insert_post( $args );
        }
    }

    /**
     * Print styles for BuddyPress pages
     *
     * @global object $bp
     * @return void
     **/
    function print_main_styles() { ?>
        <style type="text/css">
            .error { background: #FFEBE8; }
            .submit { margin: 10px 0; }
            .invalid-login { margin-top: 10px; }
        </style> <?php
    }

    /**
     * Ads shortcode.
     *
     **/
    function ads_shortcode() {
        /*
        $tmp_search_query = $_POST['search'];

        $tmp_search_query = urldecode( $tmp_search_query );
        $tmp_base_url = get_option('siteurl') . '/classifieds/';

        $tmp_current_cat  = $_GET['cat'];
        $tmp_current_ad   = $_GET['ad'];
        $tmp_current_page = $_GET['page'];

        $tmp_base_url = get_option('siteurl') . '/classifieds/';

        if ( $tmp_search_query != '' ) {
            //search results
            classifieds_frontend_search_results_paginated( $tmp_search_query,'10', $tmp_current_page, $tmp_base_url );
        } else if ( $tmp_current_ad != '' ) {
            //ad listing
            classifieds_frontend_display_ad_information( $tmp_current_ad, $tmp_base_url );
            classifieds_frontend_display_ad_contact_form( $tmp_current_ad, $tmp_base_url );
        } else if ( $tmp_current_cat != '' ) {
            //category listings
            classifieds_frontend_display_ads_paginated( $tmp_current_cat, '10', $tmp_current_page, $tmp_base_url );
        } else {
            //frontpage listings
            classifieds_frontend_display_ads( '', 20,'random',$tmp_base_url );
        } */
    }

    /**
     * Checkout shortcode.
     *
     * @return <type>
     **/
    function checkout_shortcode() {
        /* Get site options */
        $options = $this->get_options();
        if ( is_user_logged_in() ) {
            //$this->js_redirect( get_bloginfo('url') );
        }
        if ( empty( $options['paypal'] ) ) {
            $this->render_front( 'general/checkout/checkout', array( 'step' => 'disabled' ) );
            return;
        }
        if ( isset( $_POST['terms_submit'] ) ) {
            if ( empty( $_POST['tos_agree'] ) || empty( $_POST['billing'] ) ) {
                if ( empty( $_POST['tos_agree'] ))
                    add_action( 'tos_invalid', create_function('', 'echo "class=\"error\"";') );
                if ( empty( $_POST['billing'] ))
                    add_action( 'billing_invalid', create_function('', 'echo "class=\"error\"";') );
                $this->render_front( 'general/checkout/checkout', array( 'step' => 'terms' ) );
            } else {
                $this->render_front('general/checkout/checkout', array( 'step' => 'payment_method' ) );
            }
        } elseif ( isset( $_POST['login_submit'] ) ) {
            $error = $this->login( $_POST['username'], $_POST['password'] );
            if ( isset( $error )) {
                add_action( 'login_invalid', create_function('', 'echo "class=\"error\"";') );
                $this->render_front( 'general/checkout/checkout', array( 'step' => 'terms', 'error' => $error ) );
            } else {
                echo 'nada';
            }
        } elseif ( isset( $_POST['payment_method_submit'] )) {
            if ( $_POST['payment_method'] == 'paypal' ) {
                $checkout = new Classifieds_Core_PayPal();
                $checkout->call_shortcut_express_checkout( $_POST['cost'] );
            } elseif ( $_POST['payment_method'] == 'cc' ) {
                $this->render_front( 'general/checkout/checkout', array( 'step' => 'cc_details' ) );
            }
        } elseif ( isset( $_POST['direct_payment_submit'] ) ) {
            $checkout = new Classifieds_Core_PayPal();
            $result = $checkout->direct_payment( $_POST['total_amount'], $_POST['cc_type'], $_POST['cc_number'], $_POST['exp_date'], $_POST['cvv2'], $_POST['first_name'], $_POST['last_name'], $_POST['street'], $_POST['city'], $_POST['state'], $_POST['zip'], $_POST['country_code'] );
        } elseif ( isset( $_REQUEST['token'] ) && !isset( $_POST['confirm_payment_submit'] ) ) {
            $checkout = new Classifieds_Core_PayPal();
            $result = $checkout->get_shipping_details();
            $this->render_front( 'general/checkout/checkout', array( 'step' => 'confirm_payment', 'transaction_details' => $result ) );
        } elseif ( isset( $_POST['confirm_payment_submit'] ) ) {
            $checkout = new Classifieds_Core_PayPal();
            $result = $checkout->confirm_payment( $_POST['total_amount'] );
            if ( strtoupper( $result['ACK'] ) == 'SUCCESS' || strtoupper( $result['ACK'] ) == 'SUCCESSWITHWARNING' ) {
                //$this->insert_user( $_POST['email'], $_POST['first_name'], $_POST['last_name'], $_POST['billing'] );
                $this->render_front( 'general/checkout/checkout', array( 'step' => 'success' ) );
            }
        } else {
            $this->render_front( 'general/checkout/checkout', array( 'step' => 'terms' ) );
        }
    }

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
     * Insert User
     *
     * @param <type> $email
     * @param <type> $first_name
     * @param <type> $last_name
     * @return <type>
     */
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

        $user_id = wp_insert_user( array( 'user_login'   => $user_login,
                                          'user_pass'    => $user_pass,
                                          'user_email'   => $email,
                                          'display_name' => $first_name . ' ' . $last_name,
                                          'first_name'   => $first_name,
                                          'last_name'    => $last_name,
                                          'role'         => $this->user_role
                                        )) ;

        update_user_meta( $user_id, 'dp_billing', $billing );
        wp_new_user_notification( $user_id, $user_pass );
        $credentials = array( 'remember'=>true, 'user_login' => $user_login, 'user_password' => $user_pass );
        wp_signon( $credentials );
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
		if ( file_exists( get_template_directory() . "/$name.php" ) )
			include get_template_directory() . "/$name.php";
        elseif ( file_exists( get_template_directory() . "/members/single/$name.php" ) && $this->bp_active == true )
			include get_template_directory() . "/members/single/$name.php";
		elseif ( file_exists( "{$this->plugin_dir}/ui-front/$name.php" ) )
			include "{$this->plugin_dir}/ui-front/$name.php";
		else
			echo "<p>Rendering of template $name.php failed</p>";
	}

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