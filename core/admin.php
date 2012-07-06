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
	/** @var string $sub_menu_slug Submenu slug @todo better way of handling this */
	var $sub_menu_slug    = 'classifieds_credits';

	/** @var string $message Return message after save settings operation */
	var $message  = '';

	/**
	* Constructor. Hooks the whole module to the "init" hook.
	*
	* @return void
	**/
	function Classifieds_Core_Admin() { __construct(); }

	function __construct(){

		parent::__construct();

	}

	/**
	* Initiate the plugin.
	*
	* @return void
	**/
	function init() {

		parent::init();

		/* Init if admin only */
		if ( is_admin() ) {
			/* Initiate admin menus and admin head */
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_init', array( &$this, 'admin_head' ) );
			add_action( 'save_post',  array( &$this, 'save_expiration_date' ), 1, 1 );

			add_action( 'wp_ajax_cf_get_caps', array( &$this, 'ajax_get_caps' ) );
			add_action( 'wp_ajax_cf_save', array( &$this, 'ajax_save' ) );

		}
	}

	/**
	* Add plugin main menu
	*
	* @return void
	**/
	function admin_menu() {
		//add_menu_page( __( 'Classifieds', $this->text_domain ), __( 'Classifieds', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'handle_admin_requests' ) );
		add_submenu_page( 'edit.php?post_type=classifieds', __( 'Dashboard', $this->text_domain ), __( 'Dashboard', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'handle_admin_requests' ) );
		$settings_page = add_submenu_page( 'edit.php?post_type=classifieds', __( 'Settings', $this->text_domain ), __( 'Settings', $this->text_domain ), 'edit_users', 'classifieds_settings', array( &$this, 'handle_admin_requests' ) );

		add_action( 'admin_print_scripts-' .  $settings_page, array( &$this, 'enqueue_scripts' ) );

		if($this->use_credits){
			add_submenu_page( 'edit.php?post_type=classifieds', __( 'Credits', $this->text_domain ), __( 'Credits', $this->text_domain ), 'read', 'classifieds_credits' , array( &$this, 'handle_admin_requests' ) );
		}
	}

	function enqueue_scripts(){
		wp_enqueue_script( 'cf-admin-scripts', $this->plugin_url . 'ui-admin/js/ui-scripts.js', array( 'jquery' ) );
	}

	/**
	* Renders an admin section of display code.
	*
	* @param  string $name Name of the admin file(without extension)
	* @param  string $vars Array of variable name=>value that is available to the display code(optional)
	* @return void
	**/
	function render_admin( $name, $vars = array() ) {
		foreach ( $vars as $key => $val )
		$$key = $val;
		if ( file_exists( "{$this->plugin_dir}ui-admin/{$name}.php" ) )
		include "{$this->plugin_dir}ui-admin/{$name}.php";
		else
		echo "<p>Rendering of admin template {$this->plugin_dir}ui-admin/{$name}.php failed</p>";
	}


	/**
	* Flow of a typical admin page request.
	*
	* @return void
	**/
	function handle_admin_requests() {
		$valid_tabs = array('general', 'capabilities', 'payments', 'payment-types','shortcodes');

		$page = (empty($_GET['page'])) ? '' : $_GET['page'] ;
		$tab = (empty($_GET['tab'])) ? 'general' : $_GET['tab']; //default tab

		if ( $page == $this->menu_slug ) {
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
		}
		elseif ( $page == 'classifieds_settings' ) {
			if ( in_array( $tab, $valid_tabs)) {

				/* Save options */
				if ( isset( $_POST['add_role'] ) ) {
					check_admin_referer('verify');
					$name = $_POST['new_role'];
					$slug = strtolower(str_replace(' ','_',$name) );
					$result = add_role($slug, $name, array('read' => true) );
					if (empty($result) ) $this->message = __('ROLE ALREADY EXISTS' , $this->text_domain);
					else $this->message = __('New Role Added' , $this->text_domain);
				}
				if ( isset( $_POST['remove_role'] ) ) {
					check_admin_referer('verify');
					$name = $_POST['delete_role'];
					remove_role($name);
					$this->message = __('Role Removed' , $this->text_domain);
				}
				if ( isset( $_POST['save'] ) ) {
					check_admin_referer('verify');
					unset($_POST['new_role']);
					unset($_POST['delete_role']);
					unset($_POST['save']);

					$this->save_options( $_POST );
					$this->message = __( 'Settings Saved.', $this->text_domain );
				}
				/* Render admin template */
				$this->render_admin( "settings-{$tab}" );

			}
		}
		//Send credits to other users.
		elseif ( $page == 'classifieds_credits' ) {

			if ( $tab == 'send-credits' ) {
				if(!empty($_POST)) check_admin_referer('verify');
				$send_to = ( empty($_POST['manage_credits'])) ? '' : $_POST['manage_credits'];
				$send_to_user = ( empty($_POST['manage_credits_user'])) ? '' : $_POST['manage_credits_user'];
				$send_to_count = ( empty($_POST['manage_credits_count'])) ? '' : $_POST['manage_credits_count'];

				$credits = (is_numeric($send_to_count)) ? abs(intval($send_to_count)) : 0;

				if ($send_to == 'send_single'){
					$user = get_user_by('login', $send_to_user);
					if($user){
						$this->update_user_credits($credits, $user->ID);
					} else {
						$this->message = sprintf(__('User "%s" not found or not a Classifieds member',$this-text_domain), $send_to_user);
					}
				}

				if ($send_to == 'send_all'){
					$search = array();
					if(is_multisite()) $search['blog_id'] = get_current_blog_id();
					$users = get_users($search);
					foreach($users as $user){
						$this->update_user_credits($credits, $user->ID);
					}
					$this->message = sprintf(__('All users have had "%s" credits added to their accounts.',$this-text_domain), $credits);

				}

				$this->render_admin( "settings-{$tab}" );

			} else {
				if ( isset( $_POST['purchase'] ) ) {
					$this->js_redirect( get_permalink($this->checkout_page_id) );
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
		$this->hook = '';
		if ( isset( $_GET['page'] ) )
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
		wp_enqueue_style( 'cf-admin-styles', $this->plugin_url . 'ui-admin/css/ui-styles.css');
	}

	/**
	* Print document styles.
	*/
	function admin_print_styles() {
		?>
		<?php
	}

	/**
	* Print document scripts
	*/
	function admin_print_scripts() {
		?>
		<script type="text/javascript">//<![CDATA[
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
		</script>
		<?php
	}

	/**
	* Ajax callback which gets the post types associated with each page.
	*
	* @return JSON Encoded string
	*/
	function ajax_get_caps() {
		if ( !current_user_can( 'manage_options' ) ) die(-1);
		if(empty($_POST['role'])) die(-1);

		global $wp_roles;

		$role = $_POST['role'];

		if ( !$wp_roles->is_role( $role ) )
		die(-1);

		$role_obj = $wp_roles->get_role( $role );

		$response = array_intersect( array_keys( $role_obj->capabilities ), array_keys( $this->capability_map ) );
		$response = array_flip( $response );

		// response output
		header( "Content-Type: application/json" );
		echo json_encode( $response );
		die();
	}

	/**
	* Save admin options.
	*
	* @return void die() if _wpnonce is not verified
	*/
	function ajax_save() {

		check_admin_referer( 'verify' );

		if ( !current_user_can( 'manage_options' ) )
		die(-1);

		// add/remove capabilities
		global $wp_roles;

		$role = $_POST['roles'];

		$all_caps = array_keys( $this->capability_map );
		$to_add = array_keys( $_POST['capabilities'] );
		$to_remove = array_diff( $all_caps, $to_add );

		foreach ( $to_remove as $capability ) {
			$wp_roles->remove_cap( $role, $capability );
		}

		foreach ( $to_add as $capability ) {
			$wp_roles->add_cap( $role, $capability );
		}

		die(1);
	}

}

global $Classifieds_Core;

$Classifieds_Core = new Classifieds_Core_Admin();

endif;

?>