<?php

/**
 * Classifieds Core BuddyPress Class
 */
if ( !class_exists('Classifieds_Core_BuddyPress') ):
class Classifieds_Core_BuddyPress extends Classifieds_Core {

    /** @var boolean $bp_active True if BuddyPress is active. */
    var $bp_active;

    /**
     * Constructor. Hooks the whole module to the 'bp_init" hook.
     *
     * @return void 
     **/
    function Classifieds_Core_BuddyPress() {
        /* Init plugin BuddyPress integration when BP is ready */
        add_action( 'bp_init', array( &$this, 'init' ) );
        /* Add theme support for post thumbnails */
        add_theme_support( 'post-thumbnails' );
    }

    /**
     * Initiate BuddyPress
     *
     * @return void 
     **/
    function init() {
        /* Set BuddyPress active state */
        $this->bp_active = true;
        add_action( 'wp', array( &$this, 'add_navigation' ), 2 );
        add_action( 'admin_menu', array( &$this, 'add_navigation' ), 2 );
        add_action( 'init', array( &$this, 'redirects' ) );
        add_action( 'bp_head', array( &$this, 'print_styles' ) );
        add_action( 'wp_head', array( &$this, 'print_scripts' ) );
        add_action( 'bp_template_content', array( &$this, 'template_content' ) );
        add_filter( 'the_content', array( &$this, 'filter_the_content' ) );
    }

    /**
     * Add BuddyPress navigation.
     *
     * @return void 
     **/
    function add_navigation() {
        /** Init vars @todo move to another function or to core */
        $this->init_vars();
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
        bp_core_new_subnav_item( array(
            'name'            => __( 'Create New Ad', $this->text_domain ),
            'slug'            => 'add-new',
            'parent_url'      => $parent_url,
            'parent_slug'     => $bp->classifieds->slug,
            'screen_function' => array( &$this, 'load_template' ),
            'position'        => 10,
            'user_has_access' => true
        ));
    }

    /**
     *
     * @global <type> $bp 
     */
    function redirects() {
        global $bp;
        if ( $bp->current_component == 'classifieds' ) {
             if ( isset( $_POST['view'] ) ) {
                wp_redirect( $_POST['url'] );
                exit;
             }
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
                $this->render_front('members/single/classifieds/edit-ad', array( 'post_id' => (int) $_POST['post_id'] ));
            } elseif ( isset( $_POST['update'] ) ) {
                $this->update_ad( $_POST, $_FILES );
                $this->render_front('members/single/classifieds/my-classifieds', array( 'action' => 'edit', 'post_title' => $_POST['post_title'] ));
            } elseif ( isset( $_POST['confirm'] ) ) {
                wp_delete_post( $_POST['post_id'] );
                $this->render_front('members/single/classifieds/my-classifieds', array( 'action' => 'delete', 'post_title' => $_POST['post_title'] ));
            } else {
                $this->render_front('members/single/classifieds/my-classifieds');
            }
        } elseif ( $bp->current_component == 'classifieds' && $bp->current_action == 'add-new' ) {
            if ( isset( $_POST['save'] ) ) {
                $this->update_ad( $_POST, $_FILES );
            }
            $this->render_front('members/single/classifieds/add-new');
        }
    }

    /**
     * Filter the_content() function output.
     *
     * @global <type> $post
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
     * Update or insert ad if no ID is passed.
     *
     * @param array $params Array of $_POST data
     * @param array $file   Array of $_FILE data
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
            foreach ( $params['custom_fields'] as $key => $value )
                update_post_meta( $post_id, $key, $value );
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
     * Print styles for BuddyPress pages
     *
     * @global object $bp
     * @return void 
     **/
    function print_styles() {
        global $bp;
        if ( $bp->current_component == 'classifieds' ) { ?>
            <style type="text/css">
                .bp-cf-ad    { border: 1px solid #ddd; padding: 10px; display: block; overflow: hidden; float: left; margin: 0 15px 15px 0;  }
                .bp-cf-ad table { margin: 5px 5px 5px 15px ; width: 280px; border-width: 1px; border-style: solid; border-color: #ddd; border-collapse: collapse; }
                .bp-cf-ad table th { text-align: right; width: 50px; }
                .bp-cf-ad table th, .bp-cf-ad table td { border-width: 1px; border-style: inset; border-color: #ddd; }
                .bp-cf-ad form { padding-left: 56px; overflow: hidden; }
                .bp-cf-ad form.del-form { padding-left: 147px; }
                .bp-cf-image { float: left;  }
                .bp-cf-info  { float: left; }
                .cf-ad-info { float: left; width: 450px; margin-left: 15px; }
                .cf-ad-info th { width: 100px; vertical-align: top; }
                .single-classifieds div.post p { margin: 0; padding: 0 5px 10px 0; }
                #cf-fimage { float: left; border: 1px solid #eee; padding: 15px 15px 0; margin-bottom: 15px; }
                .cf-terms { width: inherit; }
                .cf-terms td { vertical-align: top; }
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
                $('form.del-form').hide();
            });
            var classifieds = {
                toggle_delete: function(key) {
                    jQuery('#del-form-'+key).show();
                    jQuery('#action-form-'+key).hide();
                },
                cancel: function(key) {
                    jQuery('#del-form-'+key).hide();
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