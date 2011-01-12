<?php

/**
 * Classifieds Core BuddyPress Class
 **/
if ( !class_exists('Classifieds_Core_BuddyPress') ):
class Classifieds_Core_BuddyPress extends Classifieds_Core {

    /**
     * Constructor. Hooks the whole module to the 'bp_init" hook.
     *
     * @return void 
     **/
    function Classifieds_Core_BuddyPress() {
        /* Init plugin BuddyPress integration when BP is ready */
        add_action( 'bp_init', array( &$this, 'init' ) );
        /* Initiate plugin variables */
        add_action( 'init', array( &$this, 'init_vars' ) );
        /* Add theme support for post thumbnails */
        add_theme_support( 'post-thumbnails' );
    }

    /**
     * Initiate BuddyPress
     *
     * @return void 
     **/
    function init() {
        add_action( 'wp', array( &$this, 'add_navigation' ), 2 );
        add_action( 'admin_menu', array( &$this, 'add_navigation' ), 2 );
        add_action( 'bp_head', array( &$this, 'print_styles' ) );
        add_action( 'wp_head', array( &$this, 'print_scripts' ) );
        add_action( 'bp_template_content', array( &$this, 'template_content' ) );
        add_filter( 'the_content', array( &$this, 'filter_the_content' ) );
        /* Set BuddyPress active state */
        $this->bp_active = true;
    }

    /**
     * Add BuddyPress navigation.
     *
     * @return void 
     **/
    function add_navigation() {
        global $bp;
        /* Set up classifieds as a sudo-component for identification and nav selection */
        $bp->classifieds->slug = 'classifieds';
        /* Construct URL to the BuddyPress profile URL */
        $user_domain = ( !empty( $bp->displayed_user->domain ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
        $parent_url = $user_domain . $bp->classifieds->slug . '/';
        /* Add the settings navigation item */
        bp_core_new_nav_item( array( 
            'name'                    => __('Classifieds', $this->text_domain ),
            'slug'                    => $bp->classifieds->slug,
            'position'                => 100,
            'show_for_displayed_user' => true,
            'screen_function'         => array( &$this, 'load_template' ),
            'default_subnav_slug'     => 'my-classifieds'
        ));
        bp_core_new_subnav_item( array( 
            'name'            => __( 'My Classifieds', $this->text_domain ),
            'slug'            => 'my-classifieds',
            'parent_url'      => $parent_url,
            'parent_slug'     => $bp->classifieds->slug,
            'screen_function' => array( &$this, 'load_template' ),
            'position'        => 10,
            'user_has_access' => true
        ));
        if ( bp_is_my_profile() ) {
            bp_core_new_subnav_item( array(
                'name'            => __( 'Create New Ad', $this->text_domain ),
                'slug'            => 'create-new',
                'parent_url'      => $parent_url,
                'parent_slug'     => $bp->classifieds->slug,
                'screen_function' => array( &$this, 'load_template' ),
                'position'        => 10,
                'user_has_access' => true
            ));
        }
    }

    /**
     * Load BuddyPress theme template file for plugin specific page.
     *
     * @return void 
     **/
    function load_template() {
        /* This is generic BuddyPress plugins file. All other functions hook
         * themselves into the plugins template hooks. Each BuddyPress component
         * "members", "groups", etc. offers different plugin file and different hooks */
        bp_core_load_template( 'members/single/plugins', true );
    }

    /**
     * Load the content for the specific classifieds component
     *
     * @global object $bp
     * @return void
     **/
    function template_content() {
        global $bp;
        if ( $bp->current_component == 'classifieds' && $bp->current_action == 'my-classifieds' ) {
            if ( isset( $_POST['edit'] ) ) {
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) )
                    $this->render_front('members/single/classifieds/edit-ad', array( 'post_id' => (int) $_POST['post_id'] ));
                else
                    die( __( 'Security check failed!', $this->text_domain ) );
            } elseif ( isset( $_POST['update'] ) ) {
                $this->update_ad( $_POST, $_FILES );
                $this->save_expiration_date( $_POST['post_id'] );
                $this->render_front('members/single/classifieds/my-classifieds', array( 'action' => 'edit', 'post_title' => $_POST['post_title'] ));
            } elseif ( isset( $_POST['confirm'] ) ) {
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    if ( $_POST['action'] == 'end' ) {
                        $this->process_status( (int) $_POST['post_id'], 'private' );
                        $this->render_front('members/single/classifieds/my-classifieds', array( 'action' => 'end', 'post_title' => $_POST['post_title'] ));
                    } elseif ( $_POST['action'] == 'renew' ) {
                        $this->process_status( (int) $_POST['post_id'], 'publish' );
                        $this->save_expiration_date( $_POST['post_id'] );
                        $this->render_front('members/single/classifieds/my-classifieds', array( 'action' => 'renew', 'post_title' => $_POST['post_title'] ));
                    } elseif ( $_POST['action'] == 'delete' ) {
                        wp_delete_post( $_POST['post_id'] );
                        $this->render_front('members/single/classifieds/my-classifieds', array( 'action' => 'delete', 'post_title' => $_POST['post_title'] ));
                    }
                } else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            } else {
                $this->render_front('members/single/classifieds/my-classifieds');
            }
        } elseif ( $bp->current_component == 'classifieds' && $bp->current_action == 'create-new' ) {
            if ( isset( $_POST['save'] ) ) {
                $this->validate_fields( $_POST, $_FILES );
                if ( $this->form_valid ) {
                    global $bp;
                    $this->update_ad( $_POST, $_FILES );
                    $this->js_redirect( $bp->loggedin_user->userdata->user_url . 'classifieds/' );
                } else {
                    $this->render_front('members/single/classifieds/create-new');
                }
            } else {
                $this->render_front('members/single/classifieds/create-new');
            }
        }
    }

    /**
     * Filter the_content() function output.
     *
     * @global object $post
     * @param  string $content
     * @return string $content The HTML ready content of the ad
     **/
    function filter_the_content( $content ) {
        global $post;
        if ( is_single() && $post->post_type == $this->post_type ) {
            $this->render_front( 'members/single/classifieds/content-ad', array( 'post' => $post, 'content' => $content ) );
        } else {
            return $content;
        }
    }
    
    /**
     * Print styles for BuddyPress pages
     *
     * @global object $bp
     * @return void 
     **/
    function print_styles() {
        global $bp;
        if ( $bp->current_component == 'classifieds' ) { ?>
            <style type="text/css">
                .cf-ad    { border: 1px solid #ddd; padding: 10px; display: block; overflow: hidden; float: left; margin: 0 15px 15px 0; width: 450px;  }
                .cf-ad table { margin: 5px 5px 5px 15px ; width: 280px; border-width: 1px; border-style: solid; border-color: #ddd; border-collapse: collapse; }
                .cf-ad table th { text-align: right; width: 50px; }
                .cf-ad table th, .bp-cf-ad table td { border-width: 1px; border-style: inset; border-color: #ddd; }
                .cf-ad form {  float: right; padding-right: 5px; overflow: hidden; }
                .cf-ad form.confirm-form { float: right; padding-right: 5px; }
                .cf-image { float: left;  }
                .cf-info  { float: left; }
                .cf-ad-info { float: left; width: 450px; margin-left: 15px; }
                .cf-ad-info th { width: 100px; vertical-align: top; }
                .single-classifieds div.post p { margin: 0; padding: 0 5px 10px 0; }
                #cf-fimage { float: left; border: 1px solid #eee; padding: 15px 15px 0; margin-bottom: 15px; }
                .cf-terms { width: inherit; }
                .cf-terms td { vertical-align: top; }
            </style> <?php
        } elseif ( isset( $bp ) ) { ?>
            <style type="text/css">
                .cf-checkout { width: 48%; float: left; margin-right: 15px; }
                .cf-checkout img { margin-bottom: 0 !important; }
                .cf-login { width: 48%; float: left; }
            </style> <?php
        }
    }

    /**
     * Print scripts for BuddyPress pages
     *
     * @global object $bp
     * @return void
     **/
    function print_scripts() {
        global $bp;
        if ( $bp->current_component == 'classifieds' ) { ?>
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
}
endif;

/* Initiate Class */
if ( class_exists('Classifieds_Core_BuddyPress') )
	$__classifieds_core_buddypress = new Classifieds_Core_BuddyPress();
?>