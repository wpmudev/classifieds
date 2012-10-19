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

	var $tutorial_id = 0;

	var $tutorial_script = '';

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

			//IPN script for Paypal
			add_action( 'wp_ajax_nopriv_classifieds_ipn', array( &$this, 'ajax_classifieds_ipn' ) );
			add_action( 'wp_ajax_classifieds_ipn', array( &$this, 'ajax_classifieds_ipn' ) );

			//Sil;ent Post script for Authorizenet
			add_action( 'wp_ajax_nopriv_classifieds_sp', array( &$this, 'ajax_classifieds_silent_post' ) );
			add_action( 'wp_ajax_classifieds_sp', array( &$this, 'ajax_classifieds_silent_post' ) );

			add_action('admin_init', array($this, 'tutorial_script') );
			add_action('admin_print_footer_scripts', array($this, 'print_tutorial_script') );
		}
	}

	function print_tutorial_script(){
		echo $this->tutorial_script;
	}

	function tutorial_script(){

		if(file_exists($this->plugin_dir . 'tutorial/classifieds-tutorial.js') ){
			$this->tutorial_script = file_get_contents($this->plugin_dir . 'tutorial/classifieds-tutorial.js');

			preg_match('/data-kera-tutorial="(.+)">/', $this->tutorial_script, $matches);

			$this->tutorial_id = $matches[1];

			$this->tutorial_script = strstr($this->tutorial_script, '<script');
		}
	}

	function launch_tutorial(){
		?>
		<h2>Classifieds Tutorial</h2>
		<a href="#" data-kera-tutorial="<?php echo $this->tutorial_id; ?>">Launch Tutorial</a>
		<?php
	}

	/**
	* Add plugin main menu
	*
	* @return void
	**/
	function admin_menu() {

		if ( ! current_user_can('unfiltered_html') ) {
			remove_submenu_page('edit.php?post_type=classifieds', 'post-new.php?post_type=classifieds' );
			add_submenu_page( 'edit.php?post_type=classifieds', __( 'Add New', $this->text_domain ), __( 'Add New', $this->text_domain ), 'create_classifieds', 'classifieds_add', array( &$this, 'redirect_add' ) );
		}

		//add_menu_page( __( 'Classifieds', $this->text_domain ), __( 'Classifieds', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'handle_admin_requests' ) );
		add_submenu_page( 'edit.php?post_type=classifieds', __( 'Dashboard', $this->text_domain ), __( 'Dashboard', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'handle_admin_requests' ) );
		$settings_page = add_submenu_page( 'edit.php?post_type=classifieds', __( 'Classifieds Settings', $this->text_domain ), __( 'Settings', $this->text_domain ), 'edit_users', 'classifieds_settings', array( &$this, 'handle_admin_requests' ) );

		add_action( 'admin_print_styles-' .  $settings_page, array( &$this, 'enqueue_scripts' ) );

		if($this->use_credits){
			$settings_page = add_submenu_page( 'edit.php?post_type=classifieds', __( 'Classifieds Credits', $this->text_domain ), __( 'Credits', $this->text_domain ), 'read', 'classifieds_credits' , array( &$this, 'handle_admin_requests' ) );
			add_action( 'admin_print_styles-' .  $settings_page, array( &$this, 'enqueue_scripts' ) );
		}

		if(file_exists($this->plugin_dir . 'tutorial/classifieds-tutorial.js') ){
			add_submenu_page( 'edit.php?post_type=classifieds', __( 'Tutorial', $this->text_domain ), __( 'Tutorial', $this->text_domain ), 'read', 'classifieds_tutorial', array( &$this, 'launch_tutorial' ) );
		}
	}


	function redirect_add(){
		echo '<script>window.location = "' . get_permalink($this->add_classified_page_id) . '";</script>';
		//wp_redirect(get_permalink($this->my_classifieds_page_id) ); exit;
	}


	function enqueue_scripts(){
		wp_enqueue_style( 'cf-admin-styles', $this->plugin_url . 'ui-admin/css/ui-styles.css');
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
		$valid_tabs = array(
		'general',
		'capabilities',
		'payments',
		'payment-types',
		'affiliate',
		'shortcodes',
		'my-credits',
		'send-credits',
		);

		$page = (empty($_GET['page'])) ? '' : $_GET['page'] ;

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
			$tab = (empty($_GET['tab'])) ? 'general' : $_GET['tab']; //default tab
			if ( in_array( $tab, $valid_tabs)) {
				/* Save options */
				if ( isset( $_POST['add_role'] ) ) {
					check_admin_referer('verify');
					$name = sanitize_file_name($_POST['new_role']);
					$slug = sanitize_key(preg_replace('/\W+/','_',$name) );
					$result = add_role($slug, $name, array('read' => true) );
					if (empty($result) ) $this->message = __('ROLE ALREADY EXISTS' , $this->text_domain);
					else $this->message = sprintf(__('New Role "%s" Added' , $this->text_domain), $name);
				}
				if ( isset( $_POST['remove_role'] ) ) {
					check_admin_referer('verify');
					$name = $_POST['delete_role'];
					remove_role($name);
					$this->message = sprintf(__('Role "%s" Removed' , $this->text_domain), $name);
				}
				if ( isset( $_POST['save'] ) ) {
					check_admin_referer('verify');
					unset($_POST['new_role'],
					$_POST['add_role'],
					$_POST['delete_role'],
					$_POST['save']
					);

					$this->save_options( $_POST );
					$this->message = __( 'Settings Saved.', $this->text_domain );
				}
				/* Render admin template */
				$this->render_admin( "settings-{$tab}" );

			}
		}
		//Send credits to other users.
		elseif ( $page == 'classifieds_credits' ) {
			$tab = (empty($_GET['tab'])) ? 'my-credits' : $_GET['tab']; //default tab
			if ( in_array( $tab, $valid_tabs)) {

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


				} else {
					if ( isset( $_POST['purchase'] ) ) {
						$this->js_redirect( get_permalink($this->checkout_page_id) );
					}
				}
				$this->render_admin( "credits-{$tab}" );
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


	function write_to_log($error, $log = 'error') {

		//create filename for each month
		$filename = $this->plugin_dir . "{$log}_" . date('Y_m') . '.log';

		//add timestamp to error
		$message = gmdate('[Y-m-d H:i:s] ') . $error;

		//write to file
		file_put_contents($filename, $message . "\n", FILE_APPEND);
	}


	/**
	* IPN script for change user role when Paypal Recurring Payment changed status
	*
	* @return void
	*/
	function ajax_classifieds_ipn() {
		// debug mode for IPN script (please open plugin dir (classifieds) for writing)
		$debug_ipn = 0;
		if ( 1 == $debug_ipn ) {
			$this->write_to_log(
			' - 01 -' . " POST\r\n" .
			print_r( $_SERVER, true ) . "\r\n" .
			print_r( $_POST, true ),
			'debug_ipn' );
		}

		$postdata = http_build_query($_POST);
		$postdata .= "&cmd=_notify-validate";

		$options = $this->get_options( 'payment_types' );
		$options = $options['paypal'];

		if ( 'live' == $options['api_url'] )
		$url = "https://www.paypal.com/cgi-bin/webscr";
		else
		$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";

		$args =  array(
		'timeout' => 90,
		'sslverify' => false
		);

		$response = wp_remote_get( $url . "?" . $postdata, $args );

		if( is_wp_error( $response ) ) {
			if ( 1 == $debug_ipn ) {
				$this->write_to_log(
				' - 02 -' . " error with send post\r\n" .
				print_r( "url: " . $url . "\r\n", true ) .
				print_r( $response, true ),
				'debug_ipn' );
			}
			die('error with send post');
		} else {
			$response = $response["body"];
		}


		if ( $response != "VERIFIED" ) {
			if ( 1 == $debug_ipn ) {
				$this->write_to_log(
				' - 03 -' . " not VERIFIED\r\n" .
				print_r( $response, true ),
				'debug_ipn' );
			}
			die( 'not VERIFIED' );
		}

		if ( $_POST['subscr_id'] ) {

			if( is_numeric($_POST['custom']) ) { //old style
				$user_id = $_POST['custom'];
			} else {
				parse_str($_POST['custom'], $custom);
				$user_id = $custom['uid'];
				$blogid = $custom['bid'];
			}

			$transactions = new CF_Transactions($user_id, $blogid);

			if ( "subscr_payment" == $_POST['txn_type'] ) {

				$key = md5( $_POST['mc_currency'] . "classifieds_123" . $_POST['mc_gross'] );

				//checking hash keys
				if ( $key != $transactions->paypal['key']) {
					if ( 1 == $debug_ipn ) {
						$this->write_to_log(
						' - 04 -' . " Conflict Keys:\r\n" .
						print_r( " key from site: " . $transactions->paypal['key'], true ) . "\r\n" .
						print_r( "key from Paypal: " . $key, true ) . "\r\n" .
						print_r($transactions->paypal, true),
						'debug_ipn' );
					}
					die("conflict key");
				}

				//write subscr_id (profile_id) to user meta
				$transactions->paypal = $_POST;

				if ( 1 == $debug_ipn ) {
					$this->write_to_log(
					' - 05 -' . " subscr_payment OK\r\n" .
					print_r($transactions, true) . "\r\n",
					'debug_ipn' );
				}

			} elseif( in_array( $_POST['txn_type'], array("subscr_cancel", "subscr_failed", "subscr_eot") ) ) {

				$transactions->paypal = $_POST;

				if ( 1 == $debug_ipn ) {
					$this->write_to_log(
					' - 05 -' . " subscr_payment OK\r\n" .
					print_r($transactions, true) . "\r\n",
					'debug_ipn' );
				}
			}
		}
		die("ok");
	}

	/**
	* Script for change user role when Authorizenet Recurring Payment changed status
	*
	* @return void
	*/
	function ajax_classifieds_silent_post() {

		// debug mode for Silent Post script (please open plugin dir (classifieds) for writing)
		$debug_sp = 0;
		if ( 1 == $debug_sp ) {
			$this->write_to_log(
			print_r( date( "H:i:s m.d.y" ) . ' - 01 -' . " POST\r\n", true ) .
			print_r( $_POST, true ),
			'debug_sp' );
		}

		//silent doesn't do any handshaking
		if ( ! empty($_POST['x_invoice_num']) ) {
			$blogid = explode('-', $_POST['x_invoice_num']); //Format CLS-4-87sd8si222ldff
			if($blogid[0] == 'CLS' && is_numeric($blogid[1])){
				$blogid = intval($blogid[1]);
				$user_id = $_POST['x_cust_id'];

				$transactions = new CF_Transactions($user_id, $blogid);

				$this->write_to_log(print_r($transactions->transactions, true), 'debug_sp');

				if(! empty($_POST['x_subscription_id'])
				&& $_POST['x_subscription_id'] == $transactions->authorizenet['profile_id'] ){

					$transactions->authorizenet = $_POST;

				} else {

					if ( 1 == $debug_sp ) $this->write_to_log('Subscription ID mismatch Post: ' . $_POST['x_subscription_id'] . ' Key: ' . $transactions->authorizenet['profile_id'] , 'debug_sp');

				}
			} else{
				if ( 1 == $debug_sp ) $this->write_to_log('Bad x-invoice_num Post: ' . $_POST['x_subscription_id'] . ' Key: ' . $transactions->authorizenet['key'], 'debug_sp' );
			}

		}
		die("ok");
	}

}

global $Classifieds_Core;

$Classifieds_Core = new Classifieds_Core_Admin();

endif;

?>