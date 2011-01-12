<?php

/**
 * Classifieds Core Admin Class
 */
if ( !class_exists('Classifieds_Core_Admin') ):
class Classifieds_Core_Admin extends Classifieds_Core {

    var $hook;
    var $menu_slug        = 'classifieds';
    var $sub_menu_slug    = 'classifieds_credits';


    /**
     * Constructor. Hooks the whole module to the "init" hook.
     */
    function Classifieds_Core_Admin() {
        /* Init plugin */
        add_action( 'init', array( &$this, 'init' ) );
    }

    /**
     * 
     */
    function init() {
        /* Initiate admin menus and admin head */
        if ( is_admin() ) {
            add_action( 'admin_menu',  array( &$this, 'admin_menu' ) );
            add_action( 'admin_init',  array( &$this, 'admin_head' ) );
        }
    }

    /**
     * Add plugin main menu
     */
    function admin_menu() {
        add_menu_page( __( 'Classifieds', $this->text_domain ), __( 'Classifieds', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'admin_flow' ) );
        add_submenu_page( $this->menu_slug, __( 'Dashboard', $this->text_domain ), __( 'Dashboard', $this->text_domain ), 'edit_users', $this->menu_slug, array( &$this, 'admin_flow' ) );
        add_submenu_page( $this->menu_slug, __( 'Settings', $this->text_domain ), __( 'Settings', $this->text_domain ), 'edit_users', $this->sub_menu_slug, array( &$this, 'admin_flow' ) );
    }

    /**
     * Flow of a typical admin page request.
     */
    function admin_flow() {
        if ( $_GET['page'] == $this->menu_slug ) {
            if ( isset( $_POST['confirm'] ) ) {
                /* Change post status */
                if ( $_POST['action'] == 'end' )
                    $this->process_status( $_POST['post_id'], 'private' );
                /* Change post status */
                if ( $_POST['action'] == 'publish' )
                    $this->process_status( $_POST['post_id'], 'publish' );
                /* Delete post */
                if ( $_POST['action'] == 'delete' )
                    wp_delete_post( $_POST['post_id'] );
                /* Render admin template */
                $this->render_admin( 'dashboard' );
            } else {
                /* Render admin template */
                $this->render_admin( 'dashboard' );
            }
        } elseif ( $_GET['page'] == $this->sub_menu_slug ) {
            if ( $_GET['tab'] == 'payments' ) {
                if ( $_GET['sub'] == 'authorizenet' ) {
                    $this->render_admin( 'authorizenet' );
                } else {
                    /* Save options */
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    /* Render admin template */
                    $this->render_admin( 'paypal' );
                }
            } else {
                if ( $_GET['sub'] == 'something' ) {

                } else {
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    $this->render_admin( 'credits' );
                }
            }
        }
    }

    /**
     * Hook styles and scripts into plugin admin head
     */
    function admin_head() {
        /* Get plugin hook */
        $this->hook = get_plugin_page_hook( $_GET['page'], $this->menu_slug );
        /* Add actions for printing the styles and scripts of the document */
        add_action( 'admin_print_scripts-' . $this->hook, array( &$this, 'enqueue_scripts' ) );
        add_action( 'admin_head-' . $this->hook, array( &$this, 'print_admin_styles' ) );
        add_action( 'admin_head-' . $this->hook, array( &$this, 'print_admin_scripts' ) );
    }
    
    /**
     * Enqueue scripts.
     */
    function enqueue_scripts() {
        wp_enqueue_script('jquery');
    }

    /**
     * Print document styles.
     */
    function print_admin_styles() { ?>
        <style type="text/css">
            .wrap table    { text-align: left; }
            .wrap table th { width: 200px; }
            .classifieds_page_classifieds_credits .wrap h2 { border-bottom:1px solid #CCCCCC; padding-bottom:0; }
        </style> <?php
    }

    /**
     * Print document scripts
     */
    function print_admin_scripts() { ?>
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
