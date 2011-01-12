<?php

/**
 * Classifieds Core Main Class
 **/
if ( !class_exists('Classifieds_Core_Main') ):
class Classifieds_Core_Main extends Classifieds_Core {

    /**
     * Constructor.
     *
     * @return void
     **/
    function Classifieds_Core_Main() {
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'init', array( &$this, 'init_vars' ) );
        /* Start session */
        if ( !session_id() )
            add_action( 'init', 'session_start' );
    }

    /**
     * Initiate Main.
     *
     * @return void
     **/
    function init() {
        $this->create_main_pages();
        add_action( 'wp_head', array( &$this, 'print_main_styles' ) );
        add_shortcode( 'classifieds', array( &$this, 'classifieds_shortcode' ) );
        add_shortcode( 'classifieds_checkout', array( &$this, 'classifieds_checkout_shortcode' ) );
        add_shortcode( 'classifieds_create_new', array( &$this, 'classifieds_create_new_shortcode' ) );
        add_shortcode( 'classifieds_my', array( &$this, 'classifieds_my_shortcode' ) );
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
                'post_content'   => '[classifieds]',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            $parent_id = wp_insert_post( $args );
        }
        $page['my_classifieds'] = get_page_by_title('My Classifieds');
        if ( !isset( $page['my_classifieds'] ) ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'My Classifieds',
                'post_content'   => '[classifieds_my]',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'post_parent'    => $parent_id,
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            wp_insert_post( $args );
        }
        $page['create_new'] = get_page_by_title('Create New');
        if ( !isset( $page['create_new'] ) ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'Create New',
                'post_content'   => '[classifieds_create_new]',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'post_parent'    => $parent_id,
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            wp_insert_post( $args );
        }
        $page['checkout'] = get_page_by_title('Checkout');
        if ( !isset( $page['checkout'] ) ) {
            $current_user = wp_get_current_user();
            /* Construct args for the new post */
            $args = array(
                'post_title'     => 'Checkout',
                'post_content'   => '[classifieds_checkout]',
                'post_status'    => 'publish',
                'post_author'    => $current_user->ID,
                'post_type'      => 'page',
                'ping_status'    => 'closed',
                'comment_status' => 'closed'
            );
            wp_insert_post( $args );
        }
    }

    /**
     * Ads shortcode.
     *
     **/
    function classifieds_shortcode() {
        // 
    }

    /**
     * Checkout shortcode.
     *
     * @return <type>
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
            $error = $this->login( $_POST['username'], $_POST['password'] );
            if ( isset( $error )) {
                add_action( 'login_invalid', create_function('', 'echo "class=\"error\"";') );
                $this->render_front( 'includes/checkout', array( 'step' => 'terms', 'error' => $error ) );
            } else {
                /** @todo Login User */
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

    function classifieds_create_new_shortcode() {
        $this->render_front('classifieds/create-new');
    }

    function classifieds_my_shortcode() {
        $this->render_front('classifieds/my-classifieds');
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

}
endif;

/* Initiate Class */
if ( class_exists('Classifieds_Core_Main') )
	$__classifieds_core_main = new Classifieds_Core_Main();
?>
