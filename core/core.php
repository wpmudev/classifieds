<?php

/**
 * Classifieds Core Class
 */
if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

    var $page;

    var $options;

    var $plugin_url = CF_PLUGIN_URL;
    var $plugin_dir = CF_PLUGIN_DIR;
    var $text_domain = 'classifieds';

    function Classifieds_Core() {
        add_action( 'init', array( &$this, 'init' ) );
    }

    function init() {
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
    }

    function setup() {

    }

    function admin_menu() {
        $this->page = add_menu_page( __('Classifieds', $this->text_domain ), __('Classifieds', $this->text_domain ), 'read', 'classifieds', array( &$this, 'admin_screen' ) );
        add_submenu_page( 'classifieds', 'Create an Ad &lsaquo; Classifieds', 'Create New Ad', 'read', 'classifieds_new', 'classifieds_page_new_output' );
        add_submenu_page( 'classifieds', 'Configuration &lsaquo; Classifieds', 'Categories', 'edit_users', 'classifieds_categories', 'classifieds_page_categories_output');
    }

    function admin_screen() {
        $this->render_admin( 'dashboard' );
    }

    function init_db_options() {

        $options = '';
        //update_site_option('cf_options', $value);
    }

    /**
	 * Renders an admin section of display code
	 *
	 * @param string $name Name of the admin file(without extension)
	 * @param string $array Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
    function render_admin( $name, $vars = array() ) {

		foreach ( $vars as $key => $val ) {
			$$key = $val;
		}

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
