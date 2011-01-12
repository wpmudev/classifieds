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
        /* Hook the entire class to WordPress init hook */
        add_action( 'init', array( &$this, 'init' ) );
        /* Initiate class variables from core class */
        add_action( 'init', array( &$this, 'init_vars' ) );
        /* Hook to bp_init so we can determine whether BuddyPress is active */
        add_action( 'bp_init', array( &$this, 'buddypress_active' ) );
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
        /* Load general WordPress front if BuddyPress is disabled */
        if ( !$this->bp_active && !is_admin() ) {
            /* Create neccessary pages */
            add_action( 'wp_loaded', array( &$this, 'create_main_pages' ) );
            /* Handle requests for plugin pages */
            add_action( 'template_redirect', array( &$this, 'page_handle_requests' ) );
            /* Enqueue styles */
            add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );
            /* Enqueue scripts */
            add_action( 'wp_print_scripts', array( &$this, 'enqueue_scripts' ) );
            /* Print scripts */
            add_action( 'wp_head', array( &$this, 'print_scripts' ) );
        }
    }

    /**
     * Determine whether BuddyPress is active and based on that disable functions
     * that may interfere with the BuddyPress install
     *
     * @return void
     **/
    function buddypress_active() {
        $this->bp_active = true;
    }

    /**
     * Create the main Classifieds page.
     *
     * @return void
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
     * Handle $_REQUEST for main pages.
     *
     * @uses set_query_var() For passing variables to pages
     * @return void|die() if "_wpnonce" is not verified
     **/
    function page_handle_requests() {
        /* Handles request for my-classifieds page */
        if ( is_page('my-classifieds') ) {
            /* If edit button is pressed */
            if ( isset( $_POST['edit'] ) ) {
                /* Verify _wpnonce field */
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    /* Set the post ID which will be used by "page-my-classifieds.php" */
                    set_query_var( 'cf_post_id', $_POST['post_id'] );
                    /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                    set_query_var( 'cf_action', 'edit' );
                } else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            }
            /* If update button is pressed */
            elseif ( isset( $_POST['update'] ) ) {
                $this->update_ad( $_POST, $_FILES );
                $this->save_expiration_date( $_POST['post_id'] );
                /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                set_query_var( 'cf_action', 'my-classifieds' );
            }
            /* If confirm button is pressed */
            elseif ( isset( $_POST['confirm'] ) ) {
                /* Verify _wpnonce field */
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    /* Process posts based on the action variables */
                    if ( $_POST['action'] == 'end' ) {
                        $this->process_status( (int) $_POST['post_id'], 'private' );
                    } elseif ( $_POST['action'] == 'renew' ) {
                        $this->process_status( (int) $_POST['post_id'], 'publish' );
                        $this->save_expiration_date( $_POST['post_id'] );
                    } elseif ( $_POST['action'] == 'delete' ) {
                        wp_delete_post( $_POST['post_id'] );
                    }
                    /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                    set_query_var( 'cf_action', 'my-classifieds' );
                } else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            }
            /* If create new button is pressed */
            elseif ( isset( $_POST['create_new'] ) ) {
                /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                set_query_var( 'cf_action', 'create-new' );
            }
            /* If save new button is pressed */
            elseif ( isset( $_POST['save_new'] ) ) {
                /* Validate form fields */
                $this->validate_fields( $_POST, $_FILES );
                if ( $this->form_valid ) {
                    $post_id = $this->update_ad( $_POST, $_FILES );
                    $this->save_expiration_date( $post_id );
                    wp_redirect( get_bloginfo('url') . '/classifieds/my-classifieds/' );
                }
            } else {
                /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                set_query_var( 'cf_action', 'my-classifieds' );
            }
        }
        /* Handles request for classifieds page */
        elseif ( is_page('classifieds') ) {
            /* Set the proper step which will be loaded by "page-my-classifieds.php" */
            set_query_var( 'cf_action', 'my-classifieds' );
        }
    }
    
    /**
     * Enqueue styles.
     *
     * @return void
     **/
    function enqueue_styles() {
        if ( file_exists( get_template_directory() . '/style-classifieds.css' ) )
            wp_enqueue_style( 'style-classifieds', get_template_directory() . '/style-classifieds.css' );
        elseif ( file_exists( $this->plugin_dir . 'ui-front/general/style-classifieds.css' ) )
            wp_enqueue_style( 'style-classifieds', $this->plugin_url . 'ui-front/general/style-classifieds.css' );
    }

    /**
     * Enqueue scripts.
     *
     * @return void
     **/
    function enqueue_scripts() {
            wp_enqueue_script('jquery');
    }

    /**
     * Print scripts for BuddyPress pages
     *
     * @global object $bp
     * @return void
     **/
    function print_scripts() { ?>
        <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function($) {
            $('form.confirm-form').hide();
        });
        var classifieds = {
            toggle_end: function(key) {
                jQuery('#confirm-form-'+key).show();
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('end');
            },
            toggle_renew: function(key) {
                jQuery('#confirm-form-'+key).show();
                jQuery('#confirm-form-'+key+' select[name="duration"]' ).show();
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('renew');
            },
            toggle_delete: function(key) {
                jQuery('#confirm-form-'+key).show();
                jQuery('#confirm-form-'+key+' select[name="duration"]' ).hide();
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('delete');
            },
            cancel: function(key) {
                jQuery('#confirm-form-'+key).hide();
                jQuery('#action-form-'+key).show();
            }
        };
        //]]>
        </script> <?php
    }

}
endif;

/* Initiate Class */
if ( class_exists('Classifieds_Core_Main') ) {
	$__classifieds_core_main = new Classifieds_Core_Main();
}
?>