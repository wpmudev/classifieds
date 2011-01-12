<?php

/**
 * Classifieds Core Class
 */
if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

    var $page;
    var $options;
    var $plugin_url       = CF_PLUGIN_URL;
    var $plugin_dir       = CF_PLUGIN_DIR;
    var $text_domain      = 'classifieds';
    var $post_type        = 'classifieds';
    var $menu_slug        = 'classifieds';

    /**
     * Initiate the custom field keys
     */
    var $custom_fields = array();


    function Classifieds_Core() {
        add_action( 'init', array( &$this, 'init' ) );
    }

    function init() {
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
        add_action( 'admin_init', array( &$this, 'admin_head' ) );
        $this->custom_fields['price']    = '_ct_selectbox_4ce176abe31a3';
        $this->custom_fields['duration'] = '_ct_selectbox_4ce176abe31a3';
    }

    function admin_menu() {
        $this->page = add_menu_page( __( 'Classifieds', $this->text_domain ), __( 'Classifieds', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'admin_screen' ) );
        add_submenu_page( $this->menu_slug, __( 'Dashboard', $this->text_domain ), __( 'Dashboard', $this->text_domain ), 'edit_users', $this->menu_slug, array( &$this, 'admin_screen' ) );
    }

    function admin_head() {
        add_action( 'admin_print_styles-'  . $this->page, array( &$this, 'enqueue_styles' ));
        add_action( 'admin_print_scripts-' . $this->page, array( &$this, 'enqueue_scripts' ));
        add_action( 'admin_head-' . $this->page, array( &$this, 'print_admin_styles' ));
        add_action( 'admin_head-' . $this->page, array( &$this, 'print_admin_scripts' ));
    }

    /**
     * Enqueue styles.
     */
    function enqueue_styles() {
        //wp_enqueue_style('farbtastic');
    }

    /**
     * Enqueue scripts.
     */
    function enqueue_scripts() {
        wp_enqueue_script('jquery');
        //wp_enqueue_script('farbtastic');
    }

    /**
     * Print document styles.
     */
    function print_admin_styles() { ?>
        <style type="text/css">
            .wrap table    { text-align: left; }
            .wrap table th { width: 200px;     }
        </style> <?php
    }

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

    function admin_screen() {
        if ( isset( $_POST['confirm'] ) ) {
            if ( $_POST['action'] == 'end' ) 
                $this->process_status( $_POST['post_id'], 'private' );
            if ( $_POST['action'] == 'publish' )
                $this->process_status( $_POST['post_id'], 'publish' );
            if ( $_POST['action'] == 'delete' )
                wp_delete_post( $_POST['post_id'] );
            $this->render_admin( 'dashboard' );
        } else {
            $this->render_admin( 'dashboard' );
        }
    }

    /**
     * Process post status. 
     *
     * @global object $wpdb
     * @param string $post_id
     * @param string $status
     */
    function process_status( $post_id, $status ) {
        global $wpdb;
        $wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $post_id ), array( '%s' ), array( '%d' ) );
    }

    /**
	 * Renders an admin section of display code
	 *
	 * @param string $name Name of the admin file(without extension)
	 * @param string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
    function render_admin( $name, $vars = array() ) {

		foreach ( $vars as $key => $val )
			$$key = $val;

		if ( file_exists( "{$this->plugin_dir}/admin-ui/{$name}.php" ) )
			include "{$this->plugin_dir}/admin-ui/{$name}.php";
		else
			echo "<p>Rendering of admin template {$this->plugin_dir}/admin-ui/{$name}.php failed</p>";
	}
}
endif;

if ( class_exists('Classifieds_Core') )
	$__classifieds_core = new Classifieds_Core();

?>
