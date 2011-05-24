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
    }

    /**
     * Initiate BuddyPress
     *
     * @return void
     **/
    function init() {
        if ( !is_admin() ) {
            /* Set BuddyPress active state */
            $this->bp_active = true;
            /* Add navigation */
            add_action( 'wp', array( &$this, 'add_navigation' ), 2 );
            /* Add navigation */
            add_action( 'admin_menu', array( &$this, 'add_navigation' ), 2 );
            /* Enqueue styles */
            add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );
            add_action( 'wp_head', array( &$this, 'print_scripts' ) );
            add_action( 'bp_template_content', array( &$this, 'handle_template_requests' ) );
        }
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
        $__classifieds_core = new Classifieds_Core();
        $classifieds_page = $__classifieds_core->get_page_by_meta( 'classifieds' );

        if ( 0 < $classifieds_page->ID )
            $nav_title = $classifieds_page->post_title;
        else
            $nav_title = 'Classifieds';

        bp_core_new_nav_item( array(
            'name'                    => __( $nav_title, $this->text_domain ),
            'slug'                    => $bp->classifieds->slug,
            'position'                => 100,
            'show_for_displayed_user' => true,
            'screen_function'         => array( &$this, 'load_template' ),
            'default_subnav_slug'     => 'my-classifieds'
        ));

        $classifieds_page = $__classifieds_core->get_page_by_meta( 'my_classifieds' );

        if ( 0 < $classifieds_page->ID )
            $nav_title = $classifieds_page->post_title;
        else
            $nav_title = 'My Classifieds';

        bp_core_new_subnav_item( array(
            'name'            => __( $nav_title, $this->text_domain ),
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
     * Load the content for the specific classifieds component and handle requests
     *
     * @global object $bp
     * @return void
     **/
    function handle_template_requests() {
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
                    $post_id = $this->update_ad( $_POST, $_FILES );
                    $this->save_expiration_date( $post_id );

                    if ( "" != $bp->loggedin_user->userdata->user_url )
                        $this->js_redirect( $bp->loggedin_user->userdata->user_url . '/classifieds/my-classifieds/' );
                    else
                        $this->js_redirect( $bp->loggedin_user->domain . '/classifieds/my-classifieds/' );
                } else {
                    $this->render_front('members/single/classifieds/create-new');
                }
            } else {
                $this->render_front('members/single/classifieds/create-new');
            }
        }
    }

    /**
     * Enqueue styles.
     *
     * @return void
     **/
    function enqueue_styles() {
        if ( file_exists( get_template_directory() . '/style-bp-classifieds.css' ) )
            wp_enqueue_style( 'style-classifieds', get_template_directory() . '/style-bp-classifieds.css' );
        elseif ( file_exists( $this->plugin_dir . 'ui-front/buddypress/style-bp-classifieds.css' ) )
            wp_enqueue_style( 'style-classifieds', $this->plugin_url . 'ui-front/buddypress/style-bp-classifieds.css' );
    }

    /**
     * Print scripts for BuddyPress pages
     *
     * @global object $bp
     * @return void
     **/
    function print_scripts() {
        global $bp;
        if ( $bp->current_component == 'classifieds' || is_single() ) { ?>
            <script type="text/javascript">
            //<![CDATA[
            jQuery(document).ready(function($) {
                $('form.confirm-form').hide();
                $('form.cf-contact-form').hide();
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
                toggle_contact_form: function() {
                    jQuery('.cf-ad-info').hide();
                    jQuery('#action-form').hide();
                    jQuery('#confirm-form').show();
                },
                cancel_contact_form: function() {
                    jQuery('#confirm-form').hide();
                    jQuery('.cf-ad-info').show();
                    jQuery('#action-form').show();
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