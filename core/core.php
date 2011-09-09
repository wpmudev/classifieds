<?php
/**
 * Classifieds Core Class
 **/
if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

    /** @var plugin version */
    var $plugin_version = CF_VERSION;
    /** @var plugin database version */
    var $plugin_db_version = CF_DB_VERSION;
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
    /** @var boolean The current user */
    var $current_user;
    /** @var string Current user credits */
    var $user_credits;
    /** @var boolean flag whether to flush all plugin data on plugin deactivation */
    var $flush_plugin_data = false;

    /**
     * Constructor.
     *
     * @return void
     **/
    function Classifieds_Core() {
        /* Hook the entire class to WordPress init hook */
        add_action( 'init', array( &$this, 'init' ) );
        /* Load plugin translation file */
        add_action( 'init', array( &$this, 'load_plugin_textdomain' ), 0 );
        /* Register activation hook */
        register_activation_hook( $this->plugin_dir . 'loader.php', array( &$this, 'plugin_activate' ) );
        /* Register deactivation hook */
        register_deactivation_hook( $this->plugin_dir . 'loader.php', array( &$this, 'plugin_deactivate' ) );
        /* Add theme support for post thumbnails */
        add_theme_support( 'post-thumbnails' );
    }

    /**
     * Intiate plugin.
     *
     * @return void
     **/
    function init() {
        /* Create neccessary pages */
        add_action( 'wp_loaded', array( &$this, 'create_default_pages' ) );
        /* Setup reles and capabilities */
        add_action( 'wp_loaded', array( &$this, 'roles' ) );
        /* Schedule expiration check */
        add_action( 'wp_loaded', array( &$this, 'scheduly_expiration_check' ) );
        /* Add template filter */
        add_filter( 'single_template', array( &$this, 'get_single_template' ) ) ;
        /* Add template filter */
        add_filter( 'page_template', array( &$this, 'get_page_template' ) ) ;
        /* Add template filter */
        add_filter( 'taxonomy_template', array( &$this, 'get_taxonomy_template' ) ) ;
        /* template for cf-author page */
        add_action( 'template_redirect', array( &$this, 'get_cf_author_template' ) );
        /* Handle login requests */
        add_action( 'template_redirect', array( &$this, 'handle_login_requests' ) );
        /* Handle all requests for checkout */
        add_action( 'template_redirect', array( &$this, 'handle_checkout_requests' ) );
        /* Handle all requests for contact form submission */
        add_action( 'template_redirect', array( &$this, 'handle_contact_form_requests' ) );
        /* Cehck expiration dates */
        add_action( 'check_expiration_dates', array( &$this, 'check_expiration_dates_callback' ) );
        /* Set signup credits for new users */
        add_action( 'user_register', array( &$this, 'set_signup_user_credits' ) );
        /** Map meta capabilities @todo */
        //add_filter( 'map_meta_cap', array( &$this, 'map_meta_cap' ), 10, 4 );
    }

    /**
     * Initiate variables.
     *
     * @return void
     **/
    function init_vars() {
        /* Set Taxonomy objects and names */
        $this->taxonomy_objects = get_object_taxonomies( $this->post_type, 'objects' );
        $this->taxonomy_names   = get_object_taxonomies( $this->post_type, 'names' );
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
        /* Set current user */
        $this->current_user = wp_get_current_user();
        /* Set current user credits */
        $this->user_credits = get_user_meta( $this->current_user->ID, 'cf_credits', true );
    }


    /**
     * Loads "classifieds-[xx_XX].mo" language file from the "languages" directory
     *
     * @return void
     **/
    function load_plugin_textdomain() {
		load_plugin_textdomain( $this->text_domain, null, 'classifieds/languages/' );
    }

    /**
     * Update plugin versions
     *
     * @return void
     **/
    function plugin_activate() {
        /* Update plugin versions */
        $versions = array( 'versions' => array( 'version' => $this->plugin_version, 'db_version' => $this->plugin_db_version ) );
        $options = get_site_option( $this->options_name );
        $options = ( isset( $options['versions'] ) ) ? array_merge( $options, $versions ) : $versions;
        update_site_option( $this->options_name, $options );
    }

    /**
     * Deactivate plugin. If $this->flush_plugin_data is set to "true"
     * all plugin data will be deleted
     *
     * @return void
     */
    function plugin_deactivate() {
        /* if $this->flush_plugin_data is set to true it will delete all plugin data */
        if ( $this->flush_plugin_data ) {
            delete_option( $this->options_name );
            delete_site_option( $this->options_name );
            delete_site_option( 'ct_custom_post_types' );
            delete_site_option( 'ct_custom_taxonomies' );
            delete_site_option( 'ct_custom_fields' );
            delete_site_option( 'ct_flush_rewrite_rules' );
        }
    }

    /**
     * Get page by meta value
     *
     * @return int $page[0] /bool false
     */
    function get_page_by_meta( $value ) {
        $post_statuses = array( 'publish', 'trash', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit' );
        foreach ( $post_statuses as $post_status ) {
            $args = array(
                    'meta_key'      => 'classifieds_type',
                    'meta_value'    => $value,
                    'post_type'     => 'page',
                    'post_status'   => $post_status
                );

            $page = get_posts( $args );
            if ( 0 < $page[0]->ID )
                return $page[0];
        }

        return false;
    }

    /**
     * Create the default Classifieds pages.
     *
     * @return void
     **/
    function create_default_pages() {
        /* Create neccasary pages */

        $classifieds_page = $this->get_page_by_meta( 'classifieds' );

        if ( 0 >= $classifieds_page->ID ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'Classifieds',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            $parent_id = wp_insert_post( $args );
            add_post_meta( $parent_id, "classifieds_type", "classifieds" );
        }

        $classifieds_page = $this->get_page_by_meta( 'my_classifieds' );

        if ( 0 >= $classifieds_page->ID ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'My Classifieds',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'post_parent'    => $parent_id,
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            $page_id = wp_insert_post( $args );
            add_post_meta( $page_id, "classifieds_type", "my_classifieds" );
        }

        $page['checkout'] = get_page_by_title('Checkout');
        if ( !isset( $page['checkout'] ) ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'Checkout',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            $page_id = wp_insert_post( $args );
            add_post_meta( $page_id, "classifieds_type", "checkout" );
        }
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
     * Add custom role for Classifieds members. Add new capabilities for admin.
     *
     * @global $wp_roles
     * @return void
     **/
    function roles() {
        global $wp_roles;
        if ( $wp_roles ) {
            /** @todo remove remove_role */
            $wp_roles->remove_role( $this->user_role );
            $wp_roles->add_role( $this->user_role, 'Classifieds Member', array(
                'publish_classifieds'       => true,
                'edit_classifieds'          => true,
                'edit_others_classifieds'   => false,
                'delete_classifieds'        => false,
                'delete_others_classifieds' => false,
                'read_private_classifieds'  => false,
                'edit_classified'           => true,
                'delete_classified'         => true,
                'read_classified'           => true,
                'upload_files'              => true,
                'assign_terms'              => true,
                'read'                      => true
            ) );
            /* Set administrator roles */
            $wp_roles->add_cap( 'administrator', 'publish_classifieds' );
            $wp_roles->add_cap( 'administrator', 'edit_classifieds' );
            $wp_roles->add_cap( 'administrator', 'edit_others_classifieds' );
            $wp_roles->add_cap( 'administrator', 'delete_classifieds' );
            $wp_roles->add_cap( 'administrator', 'delete_others_classifieds' );
            $wp_roles->add_cap( 'administrator', 'read_private_classifieds' );
            $wp_roles->add_cap( 'administrator', 'edit_classified' );
            $wp_roles->add_cap( 'administrator', 'delete_classified' );
            $wp_roles->add_cap( 'administrator', 'read_classified' );
            $wp_roles->add_cap( 'administrator', 'assign_terms' );
        }
    }

    /**
     * Map meta capabilities
     *
     * Learn more:
     * @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
     * @link http://wordpress.stackexchange.com/questions/1684/what-is-the-use-of-map-meta-cap-filter/2586#2586
     *
     * @param <type> $caps
     * @param <type> $cap
     * @param <type> $user_id
     * @param <type> $args
     * @return array
     **/
    function map_meta_cap( $caps, $cap, $user_id, $args ) {
        /* If editing, deleting, or reading a classified, get the post and post type object. */
        if ( 'edit_classified' == $cap || 'delete_classified' == $cap || 'read_classified' == $cap ) {
            $post = get_post( $args[0] );
            $post_type = get_post_type_object( $post->post_type );

            /* Set an empty array for the caps. */
            $caps = array();
        }
        /* If editing a classified, assign the required capability. */
        if ( 'edit_classified' == $cap ) {
            if ( $user_id == $post->post_author )
                $caps[] = $post_type->cap->edit_posts;
            else
                $caps[] = $post_type->cap->edit_posts;
        }
        /* If deleting a classified, assign the required capability. */
        elseif ( 'delete_classified' == $cap ) {
            if ( $user_id == $post->post_author )
                $caps[] = $post_type->cap->delete_posts;
            else
                $caps[] = $post_type->cap->delete_others_posts;
        }
        /* If reading a private classified, assign the required capability. */
        elseif ( 'read_classified' == $cap ) {
            if ( 'private' != $post->post_status )
                $caps[] = 'read';
            elseif ( $user_id == $post->post_author )
                $caps[] = 'read';
            else
                $caps[] = $post_type->cap->read_private_posts;
        }
        /* Return the capabilities required by the user. */
        return $caps;
    }

    /**
     * Insert/Update User
     *
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param string $billing The billing type for the user
     * @return NULL|void
     **/
    function update_user( $email, $first_name, $last_name, $billing, $credits ) {
        /* Include registration helper functions */
        require_once( ABSPATH . WPINC . '/registration.php' );
        /* Variables */
        $user_login     = sanitize_user( strtolower( $first_name ));
        $user_email     = $email;
        $user_pass      = wp_generate_password();
        if ( username_exists( $user_login ) )
            $user_login .= '-' . sanitize_user( strtolower( $last_name ));
        if ( username_exists( $user_login ) )
            $user_login .= rand(1,9);
        if ( email_exists( $user_email )) {
            $user = get_user_by( 'email', $user_email );
            /* If user exists update it */
            if ( $user ) {
                wp_update_user( array( 'ID' => $user->ID, 'role' => $this->user_role ) );
                update_user_meta( $user->ID, 'cf_billing', $billing );
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
        if ( $user_id ) {
            update_user_meta( $user_id, 'cf_billing', $billing );
            $this->update_user_credits( $credits, $user_id );
            wp_new_user_notification( $user_id, $user_pass );
            $credentials = array( 'remember'=> true, 'user_login' => $user_login, 'user_password' => $user_pass );
            wp_signon( $credentials );
        }
    }

    /**
     * Update or insert ad if no ID is passed.
     *
     * @param array $params Array of $_POST data
     * @param array|NULL $file Array of $_FILES data
     * @return int $post_id
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
            return $post_id;
       }
    }

    /**
     * Handle user login.
     *
     * @return void
     **/
    function handle_login_requests() {
        if ( isset( $_POST['login_submit'] ) )
            $this->login_error = $this->login( $_POST['username'], $_POST['password'] );
    }

    /**
     * Handle all checkout requests.
     *
     * @uses session_start() We need to keep track of some session variables for the checkout
     * @return NULL If the payment gateway options are not configured.
     **/
    function handle_checkout_requests() {
        /* Only handle request if on the proper page */
        if ( is_page('checkout') ) {
            /* Start session */
            if ( !session_id() )
                session_start();
            /* Get site options */
            $options = $this->get_options();
            /* Redirect if user is logged in */
            if ( is_user_logged_in() ) {
                /** @todo Set redirect */
                //wp_redirect( get_bloginfo('url') );
            }
            /* If no PayPal API credentials are set, disable the checkout process */
            if ( empty( $options['paypal'] ) ) {
                /* Set the proper step which will be loaded by "page-checkout.php" */
                set_query_var( 'cf_step', 'disabled' );
                return;
            }
            /* If Terms and Costs step is submitted */
            if ( isset( $_POST['terms_submit'] ) ) {
                /* Validate fields */
                if ( empty( $_POST['tos_agree'] ) || empty( $_POST['billing'] ) ) {
                    if ( empty( $_POST['tos_agree'] ))
                        add_action( 'tos_invalid', create_function('', 'echo "class=\"error\"";') );
                    if ( empty( $_POST['billing'] ))
                        add_action( 'billing_invalid', create_function('', 'echo "class=\"error\"";') );
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'terms' );
                } else {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'payment_method' );
                }
            }
            /* If login attempt is made */
            elseif ( isset( $_POST['login_submit'] ) ) {
                if ( isset( $this->login_error )) {
                    add_action( 'login_invalid', create_function('', 'echo "class=\"error\"";') );
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'terms' );
                    /* Pass error params to "page-checkout.php" */
                    set_query_var( 'cf_error', $this->login_error );
                } else {
                    wp_redirect( get_bloginfo('url') . '/classifieds/my-classifieds/' );
                }
            }
            /* If payment method is selected and submitted */
            elseif ( isset( $_POST['payment_method_submit'] )) {
                if ( $_POST['payment_method'] == 'paypal' ) {
                    /* Initiate paypal class */
                    $checkout = new Classifieds_Core_PayPal();
                    /* Make API call */
                    $result = $checkout->call_shortcut_express_checkout( $_POST['cost'] );
                    /* Handle Success and Error scenarios */
                    if ( $result['status'] == 'error' ) {
                        /* Set the proper step which will be loaded by "page-checkout.php" */
                        set_query_var( 'cf_step', 'api_call_error' );
                        /* Pass error params to "page-checkout.php" */
                        set_query_var( 'cf_error', $result );
                    } else {
                        /* Set billing and credits so we can update the user account later */
                        $_SESSION['billing'] = $_POST['billing'];
                        $_SESSION['credits'] = $_POST['credits'];
                    }
                } elseif ( $_POST['payment_method'] == 'cc' ) {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'cc_details' );
                }
            }
            /* If direct CC payment is submitted */
            elseif ( isset( $_POST['direct_payment_submit'] ) ) {
                /* Initiate paypal class */
                $checkout = new Classifieds_Core_PayPal();
                /* Make API call */
                $result = $checkout->direct_payment( $_POST['total_amount'], $_POST['cc_type'], $_POST['cc_number'], $_POST['exp_date'], $_POST['cvv2'], $_POST['first_name'], $_POST['last_name'], $_POST['street'], $_POST['city'], $_POST['state'], $_POST['zip'], $_POST['country_code'] );
                /* Handle Success and Error scenarios */
                if ( $result['status'] == 'success' ) {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'direct_payment' );
                } else {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'api_call_error' );
                    /* Pass error params to "page-checkout.php" */
                    set_query_var( 'cf_error', $result );
                }
            }
            /* If PayPal has redirected us back with the proper TOKEN */
            elseif ( isset( $_REQUEST['token'] ) && !isset( $_POST['confirm_payment_submit'] ) && !isset( $_POST['redirect_my_classifieds'] ) ) {
                /* Initiate paypal class */
                $checkout = new Classifieds_Core_PayPal();
                /* Make API call */
                $result = $checkout->get_shipping_details();
                /* Handle Success and Error scenarios */
                if ( $result['status'] == 'success' ) {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'confirm_payment' );
                    /* Pass transaction details params to "page-checkout.php" */
                    set_query_var( 'cf_transaction_details', $result );
                } else {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'api_call_error' );
                    /* Pass error params to "page-checkout.php" */
                    set_query_var( 'cf_error', $result );
                }
            }
            /* If payment confirmation is submitted */
            elseif ( isset( $_POST['confirm_payment_submit'] ) ) {
                /* Initiate paypal class */
                $checkout = new Classifieds_Core_PayPal();
                /* Make API call */
                $result = $checkout->confirm_payment( $_POST['total_amount'] );
                /* Handle Success and Error scenarios */
                if ( $result['status'] == 'success' ) {
                    /* Insert/Update User */
                    $this->update_user( $_POST['email'], $_POST['first_name'], $_POST['last_name'], $_POST['billing'], $_POST['credits'] );
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'success' );
                } else {
                    /* Set the proper step which will be loaded by "page-checkout.php" */
                    set_query_var( 'cf_step', 'api_call_error' );
                    /* Pass error params to "page-checkout.php" */
                    set_query_var( 'cf_error', $result );
                }
            }
            /* If transaction processed successfully, redirect to my-classifieds */
            elseif( isset( $_POST['redirect_my_classifieds'] ) ) {
                wp_redirect( get_bloginfo('url') . '/classifieds/my-classifieds/' );
            }
            /* If no requests are made load default step */
            else {
                /* Set the proper step which will be loaded by "page-checkout.php" */
                set_query_var( 'cf_step', 'terms' );
            }
        }
    }

    /**
     * Handles the request for the contact form on the single{}.php template
     **/
    function handle_contact_form_requests() {
        /* Only handle request if on single{}.php template and our post type */
        if ( get_post_type() == $this->post_type && is_single() ) {
            if ( isset( $_POST['contact_form_send'] ) ) {
                global $post;
                /** @todo validate fields */
                $user_info = get_userdata( $post->post_author );
                $to      = $user_info->user_email;
                $subject = $_POST['subject'];
                $message = $_POST['message'];
                $headers = 'From: ' . $_POST['name'] . ' <' . $_POST['email'] . '>' . "\r\n";
                wp_mail( $to, $subject, $message, $headers );
            }
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
            return __( 'No expiration date set.', 'classifieds' );
    }

    /**
     * Calculate the Unix time stam of the modified posts
     *
     * @param int|string $post_id
     * @param string $duration Valid value: "1 Week", "2 Weeks" ... etc
     * @return int Unix timestamp
     **/
    function calculate_expiration_date( $post_id, $duration ) {
        /** @todo Remove ugly hack { Update Content Types so they can have empty default values and required fields }*/
        if ( $duration == '----------' ) {
            $expiration_date = get_post_meta( $post_id, '_expiration_date', true );
            return $expiration_date;
        }
        /* Process normal request */
        $post = get_post( $post_id );
        $expiration_date = get_post_meta( $post_id, '_expiration_date', true );
        if ( empty( $expiration_date ) || $expiration_date < time() )
            $expiration_date = time();
        $date = strtotime( "+{$duration}", $expiration_date );
        return $date;
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
     * Sets initial credits amount.
     *
     * @param int $user_id
     * @return void
     **/
    function set_signup_user_credits( $user_id ) {
        $options = $this->get_options('credits');
        if ( $options['enable_credits'] == true ) {
            if ( !empty( $options['signup_credits'] ) )
                update_user_meta( $user_id, 'cf_credits', $options['signup_credits'] );
        }
    }

    /**
     * Get user credits.
     *
     * @return string User credits.
     **/
    function get_user_credits() {
        $credits = get_user_meta( $this->current_user->ID, 'cf_credits', true );
        $credits_log = get_user_meta( $this->current_user->ID, 'cf_credits_log', true );
        if ( empty( $credits ) )
            return 0;
        else
            return $credits;
    }

    /**
     * Set user credits.
     *
     * @param string $credits Number of credits to add.
     * @param int|string $user_id
     * @return void
     **/
    function update_user_credits( $credits, $user_id = NULL ) {
        if ( isset( $user_id ) ) {
            $available_credits = get_user_meta( $user_id , 'cf_credits', true );
            $total_credits = ( get_user_meta( $user_id , 'cf_credits', true ) ) ? ( $available_credits + $credits ) : $credits;
            update_user_meta( $user_id, 'cf_credits', $total_credits );
            $this->update_user_credits_log( $credits );
        } else {
            $available_credits = get_user_meta( $this->current_user->ID , 'cf_credits', true );
            $total_credits = ( get_user_meta( $this->current_user->ID , 'cf_credits', true ) ) ? ( $available_credits + $credits ) : $credits;
            update_user_meta( $this->current_user->ID, 'cf_credits', $total_credits );
            $this->update_user_credits_log( $credits );
        }
    }

    /**
     * Get the credits log of an user.
     *
     * @return string|array Log of credit events
     **/
    function get_user_credits_log() {
        $credits_log =  get_user_meta( $this->current_user->ID , 'cf_credits_log', true );
        if ( !empty( $credits_log ) )
            return $credits_log;
        else
            return __( 'No History', $this->text_domain );
    }

    /**
     * Log user credits activity.
     *
     * @param string $credits How many credits to log
     **/
    function update_user_credits_log( $credits ) {
        $date = time();
        $credits_log = array( array(
            'credits' => $credits,
            'date' => $date
        ));
        $user_meta = get_user_meta( $this->current_user->ID , 'cf_credits_log', true );
        $user_meta = ( get_user_meta( $this->current_user->ID , 'cf_credits_log', true ) ) ? array_merge( $user_meta, $credits_log ) : $credits_log;
        update_user_meta( $this->current_user->ID, 'cf_credits_log', $user_meta );
    }

    /**
     * Return the number of credits based on the duration selected.
     *
     * @return int|string Number of credits
     **/
    function get_credits_from_duration( $duration ) {
        $options = $this->get_options('credits');
        switch ( $duration ) {
            case '1 Week':
                return 1 * $options['credits_per_week'];
            case '2 Weeks':
                return 2 * $options['credits_per_week'];
            case '3 Weeks':
                return 3 * $options['credits_per_week'];
            case '4 Weeks':
                return 4 * $options['credits_per_week'];
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
     * Format date.
     *
     * @param int $date unix timestamp
     * @return string formatted date
     **/
    function format_date( $date ) {
        return date( get_option('date_format'), $date );
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
     * Filter the template path to single{}.php templates.
     * Load from theme directory primary if it doesn't exist load from plugin dir.
     *
     * Learn more: http://codex.wordpress.org/Template_Hierarchy
     * Learn more: http://codex.wordpress.org/Plugin_API/Filter_Reference#Template_Filters
     *
     * @global <type> $post Post object
     * @param string Templatepath to filter
     * @return string Templatepath
     **/
    function get_single_template( $template ) {
        global $post;
        if ( ! file_exists( get_template_directory() . "/single-{$post->post_type}.php" )
            && file_exists( "{$this->plugin_dir}/ui-front/general/single-{$post->post_type}.php" ) )
            return "{$this->plugin_dir}/ui-front/general/single-{$post->post_type}.php";
        else
            return $template;
    }


    /**
     * Filter the template path to page{}.php templates.
     * Load from theme directory primary if it doesn't exist load from plugin dir.
     *
     * Learn more: http://codex.wordpress.org/Template_Hierarchy
     * Learn more: http://codex.wordpress.org/Plugin_API/Filter_Reference#Template_Filters
     *
     * @global <type> $post Post object
     * @param string Templatepath to filter
     * @return string Templatepath
     **/
    function get_page_template( $template ) {
        global $post;
        if ( ! file_exists( get_template_directory() . "/page-{$post->post_name}.php" )
            && file_exists( "{$this->plugin_dir}/ui-front/general/page-{$post->post_name}.php" ) )
            return "{$this->plugin_dir}/ui-front/general/page-{$post->post_name}.php";
        else
            return $template;
    }

    /**
     *Get Template for classifieds author page
     **/
    function get_cf_author_template() {
        global $wp_query;
        if ( '' != get_query_var( 'cf_author_name' ) || '' != $_REQUEST['cf_author'] )  {
            load_template( "{$this->plugin_dir}/ui-front/general/author.php" );
            exit();
        }
    }

    /**
     * Filter the template path to taxonomy{}.php templates.
     * Load from theme directory primary if it doesn't exist load from plugin dir.
     *
     * Learn more: http://codex.wordpress.org/Template_Hierarchy
     * Learn more: http://codex.wordpress.org/Plugin_API/Filter_Reference#Template_Filters
     *
     * @global <type> $post Post object
     * @param string Templatepath to filter
     * @return string Templatepath
     **/
    function get_taxonomy_template( $template ) {
        $taxonomy = get_query_var('taxonomy');
        $term = get_query_var('term');

        if ( "classifieds_categories" != $taxonomy && "classifieds_tags" != $taxonomy )
            return;

        /* Check whether the files dosn't exist in the active theme directrory,
         * alos check for file to load in our general template directory */
        if ( ! file_exists( get_template_directory() . "/taxonomy-{$taxonomy}-{$term}.php" )
            && file_exists( "{$this->plugin_dir}/ui-front/general/taxonomy-{$taxonomy}-{$term}.php" ) )
            return "{$this->plugin_dir}/ui-front/general/taxonomy-{$taxonomy}-{$term}.php";
        elseif ( ! file_exists( get_template_directory() . "/taxonomy-{$taxonomy}.php" )
                && file_exists( "{$this->plugin_dir}/ui-front/general/taxonomy-{$taxonomy}.php" ) )
            return "{$this->plugin_dir}/ui-front/general/taxonomy-{$taxonomy}.php";
        elseif ( ! file_exists( get_template_directory() . "/taxonomy.php" )
                && file_exists( "{$this->plugin_dir}/ui-front/general/taxonomy.php" ) )
            return "{$this->plugin_dir}/ui-front/general/taxonomy.php";
        else
            return $template;
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