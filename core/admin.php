<?php

/**
 * Classifieds Core Admin Class
 */
if ( !class_exists('Classifieds_Core_Admin') ):
class Classifieds_Core_Admin extends Classifieds_Core {

    /** @var string $hook The hook for the current admin page */
    var $hook;
    /** @var string $menu_slug The main menu slug */
    var $menu_slug        = 'classifieds';
    /** @var string $sub_menu_slug Submenu slug @todo better way of hadnling this */
    var $sub_menu_slug    = 'classifieds_credits';

    /**
     * Constructor. Hooks the whole module to the "init" hook.
     *
     * @return void
     **/
    function Classifieds_Core_Admin() {
        /* Attach plugin to the "init" hook */
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'init', array( &$this, 'init_vars' ) );
    }

    /**
     * Initiate the plugin.
     *
     * @return void
     **/
    function init() {
        /* Init if admin only */
        if ( is_admin() ) {
            /* Initiate admin menus and admin head */
            add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
            add_action( 'admin_init', array( &$this, 'admin_head' ) );
            add_action( 'save_post',  array( &$this, 'save_expiration_date' ), 1, 1 );
        }
    }

    /**
     * Add plugin main menu
     *
     * @return void 
     **/
    function admin_menu() {
        add_menu_page( __( 'Classifieds', $this->text_domain ), __( 'Classifieds', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'handle_admin_requests' ) );
        add_submenu_page( $this->menu_slug, __( 'Dashboard', $this->text_domain ), __( 'Dashboard', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'handle_admin_requests' ) );
        add_submenu_page( $this->menu_slug, __( 'Settings', $this->text_domain ), __( 'Settings', $this->text_domain ), 'edit_users', 'classifieds_settings', array( &$this, 'handle_admin_requests' ) );
        add_submenu_page( $this->menu_slug, __( 'Settings', $this->text_domain ), __( 'Credits', $this->text_domain ), 'edit_users', 'classifieds_credits' , array( &$this, 'handle_admin_requests' ) );
    }

    /**
     * Flow of a typical admin page request.
     *
     * @return void
     **/
    function handle_admin_requests() {
        if ( isset( $_GET['page'] ) && $_GET['page'] == $this->menu_slug ) {
            if ( isset( $_POST['confirm'] ) ) {
                /* Change post status */
                if ( $_POST['action'] == 'end' )
                    $this->process_status( $_POST['post_id'], 'private' );
                /* Change post status */
                if ( $_POST['action'] == 'publish' ) {
                    $this->save_expiration_date( $_POST['post_id'] );
                    $this->process_status( $_POST['post_id'], 'publish' );
                }
                /* Delete post */
                if ( $_POST['action'] == 'delete' )
                    wp_delete_post( $_POST['post_id'] );
                /* Render admin template */
                $this->render_admin( 'dashboard' );
            } else {
                /* Render admin template */
                $this->render_admin( 'dashboard' );
            }
        } elseif ( isset( $_GET['page'] ) && $_GET['page'] == 'classifieds_settings' ) {
            if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'payments' ) {
                if ( $_GET['sub'] == 'authorizenet' ) {
                    $this->render_admin( 'payments-authorizenet' );
                } else {
                    /* Save options */
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    /* Render admin template */
                    $this->render_admin( 'payments-paypal' );
                }
            } else {
                if ( $_GET['sub'] == 'checkout' ) {
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    $this->render_admin( 'settings-checkout' );
                } else {
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    $this->render_admin( 'settings-credits' );
                }
            }
        } elseif ( $_GET['page'] == 'classifieds_credits' ) {
            if ( $_GET['tab'] == 'send_credits' ) {
                
            } else {
                if ( isset( $_POST['purchase'] ) ) {
                    $this->js_redirect( get_bloginfo('url') . '/checkout/' );
                } else {
                    $this->render_admin( 'credits-my-credits' );
                }
            }
        }
    }

    /**
     * Hook styles and scripts into plugin admin head
     *
     * @return void 
     **/
    function admin_head() {
        /* Get plugin hook */
        $this->hook = get_plugin_page_hook( $_GET['page'], $this->menu_slug );
        /* Add actions for printing the styles and scripts of the document */
        add_action( 'admin_print_scripts-' . $this->hook, array( &$this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_head-' . $this->hook, array( &$this, 'admin_print_styles' ) );
        add_action( 'admin_head-' . $this->hook, array( &$this, 'admin_print_scripts' ) );
    }
    
    /**
     * Enqueue scripts.
     *
     * @return void 
     **/
    function admin_enqueue_scripts() {
        wp_enqueue_script('jquery');
    }

    /**
     * Print document styles.
     */
    function admin_print_styles() { ?>
        <style type="text/css">
            .wrap table    { text-align: left; }
            .wrap table th { width: 200px; }
            .classifieds_page_classifieds_settings .wrap h2 { border-bottom:1px solid #CCCCCC; padding-bottom:0; }
            .classifieds_page_classifieds_credits .wrap h2 { border-bottom:1px solid #CCCCCC; padding-bottom:0; }
            .form-table #available_credits { color: #333; }
            .purchase_credits .submit { padding: 0; margin: 0; float: left; }
            .purchase_credits #purchase_credits { float: left; }
            .subsubsub h3 { margin: 0; }
        </style> <?php
    }

    /**
     * Print document scripts
     */
    function admin_print_scripts() { ?>
        <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function($) {
            $('form.cf-form').hide();
        });
        var classifieds = {
            toggle_end: function(key) {
                jQuery('#form-'+key).show();
                jQuery('.action-links-'+key).hide();
                jQuery('.separators-'+key).hide();
                jQuery('input[name="action"]').val('end');
            },
            toggle_publish: function(key) {
                jQuery('#form-'+key).show();
                jQuery('#form-'+key+' select').show();
                jQuery('.action-links-'+key).hide();
                jQuery('.separators-'+key).hide();
                jQuery('input[name="action"]').val('publish');
            },
            toggle_delete: function(key) {
                jQuery('#form-'+key).show();
                jQuery('#form-'+key+' select').hide();
                jQuery('.action-links-'+key).hide();
                jQuery('.separators-'+key).hide();
                jQuery('input[name="action"]').val('delete');
            },
            cancel: function(key) {
                jQuery('#form-'+key).hide();
                jQuery('.action-links-'+key).show();
                jQuery('.separators-'+key).show();
            }
        };
        //]]>
        </script> <?php
    }
}
endif;

if ( class_exists('Classifieds_Core_Admin') )
	$__classifieds_core_admin = new Classifieds_Core_Admin();

?>