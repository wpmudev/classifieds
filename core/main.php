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
        if ( !$this->bp_active ) {
            add_action( 'wp_loaded', array( &$this, 'create_main_pages' ) );
            add_action( 'template_redirect', array( &$this, 'page_handle_requests' ) );
            add_action( 'wp_print_scripts', array( &$this, 'enqueue_scripts' ) );
            add_action( 'wp_head', array( &$this, 'print_scripts' ) );
            add_action( 'wp_head', array( &$this, 'print_main_styles' ) );
            add_shortcode( 'classifieds', array( &$this, 'classifieds_shortcode' ) );
            add_shortcode( 'classifieds_create_new', array( &$this, 'classifieds_create_new_shortcode' ) );
            //add_shortcode( 'classifieds_my', array( &$this, 'classifieds_my_shortcode' ) );
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
     *
     */
    function classifieds_create_new_shortcode() {
        if ( isset( $_POST['save'] ) ) {
            $this->validate_fields( $_POST, $_FILES );
            if ( $this->form_valid ) {
                $this->update_ad( $_POST, $_FILES );
                $this->js_redirect( get_bloginfo('url') );
            } else {
                $this->render_front('classifieds/create-new');
            }
        } else {
            $this->render_front('classifieds/create-new');
        }
    }

    /**
     *
     */
    function classifieds_my_shortcode() {
        if ( isset( $_POST['edit'] ) ) {
            if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) )
                $this->render_front('classifieds/edit-ad', array( 'post_id' => (int) $_POST['post_id'] ));
            else
                die( __( 'Security check failed!', $this->text_domain ) );
        } elseif ( isset( $_POST['update'] ) ) {
            $this->update_ad( $_POST, $_FILES );
            $this->save_expiration_date( $_POST['post_id'] );
            $this->render_front('classifieds/my-classifieds', array( 'action' => 'edit', 'post_title' => $_POST['post_title'] ));
        } elseif ( isset( $_POST['confirm'] ) ) {
            if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                if ( $_POST['action'] == 'end' ) {
                    $this->process_status( (int) $_POST['post_id'], 'private' );
                    $this->render_front('classifieds/my-classifieds', array( 'action' => 'end', 'post_title' => $_POST['post_title'] ));
                } elseif ( $_POST['action'] == 'renew' ) {
                    $this->process_status( (int) $_POST['post_id'], 'publish' );
                    $this->save_expiration_date( $_POST['post_id'] );
                    $this->render_front('classifieds/my-classifieds', array( 'action' => 'renew', 'post_title' => $_POST['post_title'] ));
                } elseif ( $_POST['action'] == 'delete' ) {
                    wp_delete_post( $_POST['post_id'] );
                    $this->render_front('classifieds/my-classifieds', array( 'action' => 'delete', 'post_title' => $_POST['post_title'] ));
                }
            }
        } else {
            //$this->render_front('classifieds/my-classifieds');
        }
    }

    /**
     *
     */
    function page_handle_requests() {
        if ( is_page('my-classifieds') ) {
            if ( isset( $_POST['edit'] ) ) {
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    set_query_var( 'cf_post_id', $_POST['post_id'] );
                    set_query_var( 'cf_action', 'edit' );
                }
                else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            } elseif ( isset( $_POST['update'] ) ) {
                $this->update_ad( $_POST, $_FILES );
                $this->save_expiration_date( $_POST['post_id'] );
                set_query_var( 'cf_action', 'my-classifieds' );
            } elseif ( isset( $_POST['confirm'] ) ) {
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    if ( $_POST['action'] == 'end' ) {
                        $this->process_status( (int) $_POST['post_id'], 'private' );
                    } elseif ( $_POST['action'] == 'renew' ) {
                        $this->process_status( (int) $_POST['post_id'], 'publish' );
                        $this->save_expiration_date( $_POST['post_id'] );
                    } elseif ( $_POST['action'] == 'delete' ) {
                        wp_delete_post( $_POST['post_id'] );
                    }
                    set_query_var( 'cf_action', 'my-classifieds' );
                } else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            } else {
                set_query_var( 'cf_action', 'my-classifieds' );
            }
        } elseif ( is_page('create-new') ) {
            if ( isset( $_POST['save'] ) ) {
                $this->validate_fields( $_POST, $_FILES );
                if ( $this->form_valid ) {
                    $this->update_ad( $_POST, $_FILES );
                    wp_redirect( get_bloginfo('url') );
                } else {
                    //$this->render_front('classifieds/create-new');
                }
            } else {
                //$this->render_front('classifieds/create-new');
            }
        } 
    }

    /**
     * Print styles for general WordPress pages
     *
     * @global object $bp
     * @return void
     **/
    function print_main_styles() { ?>
        <style type="text/css">
            .error { background: #FFEBE8; }
            .submit { margin: 10px 0; }
            .invalid-login { margin-top: 10px; }
            .description { color:#888888; font-size:12px; }
            .editfield label, .editfield .label { font-size:14px; color: #333; display: block; font-family:"Helvetica Neue",Arial,Helvetica,"Nimbus Sans L",sans-serif; }
            .entry-content .editfield input { margin: 0; width: 100%; }
            .entry-content .editfield textarea { margin: 0; width: 100%; }
            .entry-content .editfield select, .entry-content .confirm-form select { margin: 0; }
            .entry-content .cf-ad input { margin: 0; }
            .entry-content .cf-checkout input { margin: 0; }
            .entry-content .cf-login input { margin: 0; }
            #content .editfield table { border:0; width:inherit; margin: 0; }
            #content .editfield tr td { border-top:0; padding:0 15px 0 0; vertical-align: top; }
            #content .cf-ad table { margin: 5px 5px 5px 15px ; width: 445px; border-width: 1px; border-style: solid; border-color: #ddd; border-collapse: collapse; }
            .cf-ad { border: 1px solid #ddd; padding: 10px; display: block; overflow: hidden; float: left; margin: 0 0 15px 0;  }
            .cf-ad table th { text-align: right; width: 50px; }
            .cf-ad table th, .bp-cf-ad table td { border-width: 1px; border-style: inset; border-color: #ddd; }
            .cf-ad form { padding-right: 7px; overflow: hidden; float: right; }
            .cf-ad form.del-form { padding-right: 7px; }
            .cf-image { float: left; }
            .cf-info { float: left; }
            ul.button-nav li { float: left; list-style: none; padding-right: 15px; }
            #content .classifieds ul { overflow: hidden; margin: 0; padding-bottom: 15px; }
            .cf-checkout { margin-right: 15px; }
            #content .cf-checkout table { margin:0; }
            #content .cf-checkout table tr td { padding:6px 0 6px 24px; }
            #content .cf-login table { margin:0; }
            .terms { height:100px; overflow-x: hidden; color:#888888; font-size:12px; font-family:"Helvetica Neue",Arial,Helvetica,"Nimbus Sans L",sans-serif; }
            .editfield .wp-post-image { border: 1px solid #ddd; padding: 15px; }
        </style> <?php
    }

    /**
     * Enqueue scripts.
     *
     * @return void
     **/
    function enqueue_scripts() {
        if ( !is_admin() )
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
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('renew');
            },
            toggle_delete: function(key) {
                jQuery('#confirm-form-'+key).show();
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
