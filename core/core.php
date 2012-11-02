<?php
/**
* Classifieds Core Class
**/

global $Classifieds_Core;


if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

	/** @public plugin version */
	public $plugin_version    = CF_VERSION;
	/** @public plugin database version */
	public $plugin_db_version = CF_DB_VERSION;
	/** @public string $plugin_url Plugin URL */
	public $plugin_url        = CF_PLUGIN_URL;
	/** @public string $plugin_dir Path to plugin directory */
	public $plugin_dir        = CF_PLUGIN_DIR;
	/** @public string $text_domain The text domain for strings localization */
	public $text_domain       = CF_TEXT_DOMAIN;
	/** @public string $options_name The name of the plugin options entry in DB */
	public $options_name      = CF_OPTIONS_NAME;

	/** @public string $plugin_prefix Plugin prefix */
	public $plugin_prefix = 'cf_';
	/** @public string $post_type Plugin post type */
	public $post_type     = 'classifieds';
	/** @public array $taxonomies Post taxonomies */
	public $taxonomy_objects;
	/** @public array $taxonomies Post taxonomies */
	public $taxonomy_names;
	/** @public array $custom_fields The custom fields associated with this post type */
	public $custom_fields = array();
	/** @public string $custom_fields_prefix The custom fields DB prefix */
	public $custom_fields_prefix = '_ct_';
	/** @public string User role */
	public $user_role = 'subscriber';
	/** @public boolean True if submitted form is valid. */
	public $form_valid = true;
	/** @public boolean True if BuddyPress is active. */
	public $bp_active;
	/** @public boolean Login error flag */
	public $login_error;
	/** @public boolean The current user */
	public $current_user;
	/** @public string Current user credits */
	public $user_credits = 0;
	/** @public boolean flag whether to flush all plugin data on plugin deactivation */
	public $flush_plugin_data = false;

	/** @public string/int Current maximum range of page links to show in pagination pagination (used in query)*/
	public $pagination_range = 4;
	/** @public string/bool Whether to display pagination at the top of the page*/
	public $pagination_top;
	/** @public string/bool Whether to display pagination at the bottom of the page*/
	public $pagination_bottom;

	/** @public int classifieds_page_id the Classifieds default page ID number. Track by ID so the page permalink and slug may be internationalized */
	public $classifieds_page_id = 0;
	/** @public string classifieds_page_slug the Classifieds page slug. Track by ID so the page permalink and slug may be internationalized */
	public $classifieds_page_slug = '';
	/** @public string classifieds_page_name the Classifieds default page name for templates. Track by ID so the page permalink and slug may be internationalized */
	public $classifieds_page_name = 'classifieds';

	/** @public int the My Classifieds default page ID number. Track by ID so the page permalink and slug may be internationalized */
	public $my_classifieds_page_id = 0;
	/** @public string the My Classifieds page slug. Track by ID so the page permalink and slug may be internationalized */
	public $my_classifieds_page_slug = '';
	/** @public string classifieds_page_name the Classifieds default page name for templates. Track by ID so the page permalink and slug may be internationalized */
	public $my_classifieds_page_name = 'my-classifieds';

	/** @public int the Checkout default page ID number. Track by ID so the page permalink and slug may be internationalized */
	public $checkout_page_id = 0;
	/** @public string the My Classifieds page slug. Track by ID so the page permalink and slug may be internationalized */
	public $checkout_page_slug = '';
	/** @public string classifieds_page_name the Classifieds default page name for templates. Track by ID so the page permalink and slug may be internationalized */
	public $checkout_page_name = 'checkout';

	public $use_credits = false;
	public $use_paypal = false;
	public $use_authorizenet = false;

	public $use_free = false;
	public $use_recurring = false;
	public $use_one_time = false;

	public $transactions = null;

	/**
	* Constructor. Old style
	*
	* @return void
	**/
	function Classifieds_Core() { __construct(); }

	/**
	* Constructor.
	*
	* @return void
	**/
	function __construct(){

		//Default capability map for Classifieds
		$this->capability_map = array(
		'read_classifieds'             => __( 'View classifieds.', $this->text_domain ),
		'read_private_classifieds'     => __( 'View private classifieds.', $this->text_domain ),

		'publish_classifieds'          => __( 'Add classifieds.', $this->text_domain ),

		'edit_classifieds'             => __( 'Edit classifieds.', $this->text_domain ),
		'edit_published_classifieds'   => __( 'Edit published classifieds.', $this->text_domain ),
		'edit_private_classifieds'     => __( 'Edit private classifieds.', $this->text_domain ),

		'delete_classifieds'           => __( 'Delete classifieds', $this->text_domain ),
		'delete_published_classifieds' => __( 'Delete published classifieds.', $this->text_domain ),
		'delete_private_classifieds'   => __( 'Delete private classifieds.', $this->text_domain ),

		'edit_others_classifieds'      => __( 'Edit others\' classifieds.', $this->text_domain ),
		'delete_others_classifieds'    => __( 'Delete others\' classifieds.', $this->text_domain ),

		'upload_files'                 => __( 'Upload files.', $this->text_domain ),
		);


		/* Register activation hook */
		register_activation_hook( $this->plugin_dir . 'loader.php', array( &$this, 'on_activate' ) );
		/* Register deactivation hook */
		register_deactivation_hook( $this->plugin_dir . 'loader.php', array( &$this, 'on_deactivate' ) );

		/* Initiate class variables from core class */
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'plugins_loaded', array( &$this, 'on_plugins_loaded' ), 8);

		add_action( 'wp_print_scripts', array( &$this, 'on_print_scripts' ), 8);


		/* Add theme support for post thumbnails */
		add_theme_support( 'post-thumbnails' );

		/* Create neccessary pages */
		add_action( 'wp_loaded', array( &$this, 'create_default_pages' ) );
		/* Setup roles and capabilities */
		//add_action( 'wp_loaded', array( &$this, 'roles' ) );
		/* Schedule expiration check */
		add_action( 'wp_loaded', array( &$this, 'schedule_expiration_check' ) );
		/* Add template filter */
		//add_filter( 'single_template', array( &$this, 'get_single_template' ) ) ;
		/* Add template filter */
		//add_filter( 'page_template', array( &$this, 'get_page_template' ) ) ;
		/* Add template filter */
		add_filter( 'taxonomy_template', array( &$this, 'get_taxonomy_template' ) ) ;

		add_filter( 'parse_query', array( &$this, 'on_parse_query' ) ) ;

		add_filter( 'wp_page_menu_args', array( &$this, 'hide_menu_pages' ), 99 );

		/* template for cf-author page */
		add_action( 'template_redirect', array( &$this, 'get_cf_author_template' ) );
		/* Handle login requests */
		add_action( 'template_redirect', array( &$this, 'handle_login_requests' ) );
		/* Handle all requests for checkout */
		//add_action( 'template_redirect', array( &$this, 'handle_checkout_requests' ) );
		/* Handle all requests for contact form submission */
		add_action( 'template_redirect', array( &$this, 'handle_contact_form_requests' ) );

		add_action('wp_enqueue_scripts', array(&$this, 'on_enqueue_scripts'));


		/* Check expiration dates */
		add_action( 'check_expiration_dates', array( &$this, 'check_expiration_dates_callback' ) );

		add_action('pre_get_posts', array(&$this, 'on_pre_get_posts') );

		/** Map meta capabilities */
		add_filter( 'map_meta_cap', array( &$this, 'map_meta_cap' ), 11, 4 );
		/** Show only user's classifieds on classifieds posttype page*/
		add_filter( 'parse_query',  array( &$this, 'show_only_c_user_classifieds' ) );

		// filter for $wp_query on classifieds page - it is necessary that the other plug-ins have not changed it in these pages
		add_filter( 'pre_get_posts', array( &$this, 'pre_get_posts_for_classifieds' ), 101 );

		add_filter( 'user_contactmethods', array( &$this, 'contact_fields' ), 10, 2 );


		//Shortcodes
		add_shortcode( 'cf_list_categories', array( &$this, 'classifieds_categories_sc' ) );
		add_shortcode( 'cf_classifieds_btn', array( &$this, 'classifieds_btn_sc' ) );
		add_shortcode( 'cf_add_classified_btn', array( &$this, 'add_classified_btn_sc' ) );
		add_shortcode( 'cf_edit_classified_btn', array( &$this, 'edit_classified_btn_sc' ) );
		add_shortcode( 'cf_checkout_btn', array( &$this, 'checkout_btn_sc' ) );
		add_shortcode( 'cf_my_credits_btn', array( &$this, 'my_credits_btn_sc' ) );
		add_shortcode( 'cf_my_classifieds_btn', array( &$this, 'my_classifieds_btn_sc' ) );
		add_shortcode( 'cf_profile_btn', array( &$this, 'profile_btn_sc' ) );
		add_shortcode( 'cf_logout_btn', array( &$this, 'logout_btn_sc' ) );
		add_shortcode( 'cf_signin_btn', array( &$this, 'signin_btn_sc' ) );
		add_shortcode( 'cf_custom_fields', array( &$this, 'custom_fields_sc' ) );
	}


	function on_enqueue_scripts(){
		wp_enqueue_style( 'jquery-taginput', $this->plugin_url . 'ui-front/css/jquery.tagsinput.css' );
	}

	/**
	* Initiate variables.
	*
	* @return void
	**/
	function init() {

		// post_status "virtual" for pages not to be displayed in the menus but that users should not be editing.
		register_post_status( 'virtual', array(
		'label' => __( 'Virtual', $this->text_domain ),
		'public' => (! is_admin()), //This trick prevents the virtual pages from appearing in the All Pages list but can be display on the front end.
		'exclude_from_search' => false,
		'show_in_admin_all_list' => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Virtual <span class="count">(%s)</span>', 'Virtual <span class="count">(%s)</span>' ),
		) );

		/* Set Taxonomy objects and names */
		$this->taxonomy_objects = get_object_taxonomies( $this->post_type, 'objects' );
		$this->taxonomy_names   = get_object_taxonomies( $this->post_type, 'names' );
		/* Get all custom fields values with their ID's as keys */

		$custom_fields = get_site_option('ct_custom_fields');
		if ( is_array( $custom_fields ) ) {
			foreach ( $custom_fields as $key => $value ) {
				if ( in_array( $this->post_type, $value['object_type'] ) );
				$this->custom_fields[$key] = $value;
			}
		}

		/* Assign key 'duration' to predifined Custom Field ID */
		$this->custom_fields['duration'] = '_ct_selectbox_4cf582bd61fa4';

		/* Set current user */
		$this->current_user = wp_get_current_user();

		$this->transactions = new CF_Transactions;

		/* Set current user credits */
		$this->user_credits = $this->transactions->credits;

		// Get pagination settings
		$options = $this->get_options('general');

		$this->pagination_range = (isset($options['pagination_range'])) ? intval($options['pagination_range']) : 4;
		$this->pagination_top = ( ! empty($options['pagination_top']));
		$this->pagination_bottom = ( ! empty($options['pagination_bottom']));

		/* Set the member role for classifieds */
		$this->user_role = $options['member_role'];

		//How do we sell stuff
		$options = $this->get_options('payment_types');

		$this->use_free = ( ! empty($options['use_free']));
		if (! $this->use_free) { //Can't use gateways if it's free.

			$this->use_paypal = ( ! empty($options['use_paypal']));
			if ($this->use_paypal){ //make sure the api fields have something in them
				$this->use_paypal = (! empty($options['paypal']['api_username'])) && (! empty($options['paypal']['api_password'])) && (! empty($options['paypal']['api_signature']));
			}

			$this->use_authorizenet = (! empty($options['use_authorizenet']));
			if ($this->use_authorizenet){ //make sure the api fields have something in them
				$this->use_authorizenet = (! empty($options['authorizenet']['api_user']) ) && (! empty($options['authorizenet']['api_key']));
			}

			$options = $this->get_options('payments');

			$this->use_credits = ( ! empty($options['enable_credits']));
			$this->use_recurring = ( ! empty($options['enable_recurring']));
			$this->use_one_time = ( ! empty($options['enable_one_time']));
		}

		//Set a user capability based on users purchases
		if(! is_multisite() || is_user_member_of_blog(get_current_user_id(), $blog_id) ) {
			if( $this->use_free
			|| ($this->use_credits && $this->user_credits >= $options['credits_per_week'])
			|| $this->is_full_access() ){
				$this->current_user->add_cap('create_classifieds');
			} else {
				$this->current_user->remove_cap('create_classifieds');
			}
		}

	}

	/**
	* Contact fields to add to the User profile
	* @param array $contact_fields
	* @param object $user_id
	* @return array
	*/
	function contact_fields($contact_fields = array(), $user = null){

		$cc_contact = array(
		'cc_email'        => __('CC Email', $this->text_domain),
		'cc_firstname'    => __('CC First Name', $this->text_domain),
		'cc_lastname'     => __('CC Last Name', $this->text_domain),
		'cc_street'       => __('CC Street', $this->text_domain),
		'cc_city'         => __('CC City', $this->text_domain),
		'cc_state'        => __('CC State', $this->text_domain),
		'cc_zip'          => __('CC Zip', $this->text_domain),
		'cc_country_code' => __('CC Country Code', $this->text_domain),
		);

		return array_merge($cc_contact, $contact_fields );

	}

	/**
	* Create the default Directory member roles and capabilities.
	*
	* @return void
	*/
	function create_default_classifieds_roles() {

		//set capability for admin
		$admin = get_role('administrator');
		foreach ( array_keys( $this->capability_map ) as $capability )
		$admin->add_cap($capability );
	}

	/**
	* Update plugin versions
	*
	* @return void
	**/
	function on_activate() {
		$this->create_default_classifieds_roles();

		/* Update plugin versions */
		$versions = array( 'versions' => array( 'version' => $this->plugin_version, 'db_version' => $this->plugin_db_version ) );
		$options = get_site_option( $this->options_name );
		$options = ( isset( $options['versions'] ) ) ? array_merge( $options, $versions ) : $versions;
		update_site_option( $this->options_name, $options );
	}

	/**
	* Deactivate plugin. If $this->flush_plugin_data is set to "true"
	* all plugin data will be deleted
	*
	* @return void
	*/
	function on_deactivate() {
		//Remove virtual Pages
		$post_statuses = get_post_stati();
		foreach ( $post_statuses as $post_status ) {
			$args = array(
			'hierarchical'  => 0,
			'meta_key'      => 'classifieds_type',
			'meta_value'    => $value,
			'post_type'     => 'page',
			'post_status'   => $post_status
			);

			$pages = get_pages( $args );

			foreach($pages as $page){
				if ( isset( $page ) && 0 < $page->ID )
				wp_delete_post($page->ID, true);
			}
		}

		return false;
	}

	function on_plugins_loaded(){

		//Loads "classifieds-[xx_XX].mo" language file from the "languages" classifieds
		load_plugin_textdomain( $this->text_domain, false, plugin_basename($this->plugin_dir . 'languages') );

		//If the activate flag is set then try to initalize the defaults
		if( get_site_option('cf_activate', false))	{
			include_once($this->plugin_dir . 'core/data.php');
			new Classifieds_Core_Data();
			delete_site_option('cf_activate');
		}
	}

	function on_parse_query($query){
		global $wp_query;

		if ( is_object( $wp_query ) ) {
			//Handle any security redirects

			if (! is_user_logged_in()) {
				if( @is_page($this->add_classified_page_id)
				|| @is_page($this->edit_classified_page_id)
				|| @is_page($this->my_classifieds_page_id)
				|| @is_page($this->my_credits_page_id)
				|| @is_page($this->checkout_page_id)
				){

					$args = array('redirect_to' => urlencode(get_permalink($query->queried_object_id)));
					if(!empty($_REQUEST['register'])) $args['register'] = $_REQUEST['register'];
					if(!empty($_REQUEST['reset'])) $args['reset'] = $_REQUEST['reset'];

					wp_redirect( add_query_arg($args, get_permalink($this->signin_page_id) )  );
					exit;
				}
			}

			//Are are we managing credits?
			if(! $this->use_credits){
				if(@is_page($this->my_credits_page_id)){
					wp_redirect( get_permalink($this->my_classifieds_page_id) );
					exit;
				}
			}

			//Are we adding a classified?
			if(! (current_user_can('create_classifieds') && current_user_can('publish_classifieds') ) ) {
				if (@is_page($this->add_classified_page_id)) {
					wp_redirect( get_permalink($this->my_classifieds_page_id) );
					exit;
				}
			}

			//Or are we editing a classified?
			//Can the user edit classifieds?
			if ( ! empty($_REQUEST['post_id']) && ! current_user_can('edit_classified', $_REQUEST['post_id'] ) ) {
				if(@is_page($this->edit_classified_page_id)){
					wp_redirect( get_permalink($this->my_classifieds_page_id) );
					exit;
				}
			}
		}
		return $query;
	}

	/**
	* Restrict Media library to current user's files
	*
	*/
	function on_pre_get_posts($wp_query_obj) {

		global $current_user, $pagenow;

		if( !is_a( $current_user, 'WP_User') ) return;

		if( 'media-upload.php' != $pagenow ) return;

		if( !current_user_can('administrator') &&  !current_user_can('edit_others_classifieds') ) $wp_query_obj->set('author', $current_user->id );

		return;
	}




	//filters the titles for our custom pages
	function delete_post_title( $title, $id = false ) {
		global $wp_query;
		if ( $title == $wp_query->post->post_title ) {
			return '';
		}
		return $title;
	}

	/**
	* Hide some menu pages
	*/
	function hide_menu_pages( $args ) {

		$pages = (empty($args['exclude'])) ? '' : $args['exclude'];

		//If free no need for checkout
		if ( $this->use_free ) {
			$pages .= ',' . $this->checkout_page_id;
		}

		//If nothing saleable no checkout
		if ( ! $this->use_credits && ! $this->use_one_time && ! $this->use_recurring ) {
			$pages .= ',' . $this->checkout_page_id;
		}

		$args['exclude'] = trim($pages,',');

		return $args;
	}

	/**
	* filter for $wp_query on classifieds page - it is necessary that the other plug-ins have not changed it in these pages
	*
	* @return void
	**/
	function pre_get_posts_for_classifieds() {
		global $wp_query;

		if ( isset( $wp_query->query_vars['post_type'][0] ) && 'classifieds' == $wp_query->query_vars['post_type'][0] ) {
			$wp_query->query_vars['cat']            = '';
			$wp_query->query_vars['category__in']   = array();
			$wp_query->query_vars['showposts']      = '';
		}
	}


	/**
	* Get page by meta value
	*
	* @return int $page[0] /bool false
	*/
	function get_page_by_meta( $value ) {
		$post_statuses = get_post_stati();
		foreach ( $post_statuses as $post_status ) {
			$args = array(
			'hierarchical'  => 0,
			'meta_key'      => 'classifieds_type',
			'meta_value'    => $value,
			'post_type'     => 'page',
			'post_status'   => $post_status
			);

			$page = get_pages( $args );

			if ( isset( $page[0] ) && 0 < $page[0]->ID )
			return $page[0];
		}

		return false;
	}

	/**
	* Create the default Classifieds pages.
	*
	* @return void
	**/
	function create_default_pages() {
		/* Create neccessary pages */

		$post_content = __('Virtual page. Editing this page won\'t change anything.', $this->text_domain);

		//Classifieds list
		$classifieds_page = $this->get_page_by_meta( 'classifieds' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			$current_user = wp_get_current_user();
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'Classifieds',
			'post_status'    => 'publish',
			'post_author'    => $current_user->ID,
			'post_type'      => 'page',
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type", 'classifieds');
		}

		$this->classifieds_page_id = $page_id; //Remember the number
		$this->classifieds_page_slug = $classifieds_page->post_name; //Remember the slug

		//My Classifieds
		$classifieds_page = $this->get_page_by_meta( 'my_classifieds' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			$current_user = wp_get_current_user();
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'My Classifieds',
			'post_status'    => 'publish',
			'post_author'    => $current_user->ID,
			'post_type'      => 'page',
			'post_parent'    => $this->classifieds_page_id,
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type",  'my_classifieds' );
		}

		$this->my_classifieds_page_id = $page_id; // Remember the number
		$this->my_classifieds_page_slug = $classifieds_page->post_name; //Remember the slug

		//Classifieds Checkout
		$classifieds_page = $this->get_page_by_meta( 'checkout_classified' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			$current_user = wp_get_current_user();
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'Classifieds Checkout',
			'post_name'     => 'checkout',
			'post_status'    => 'publish',
			'post_author'    => $current_user->ID,
			'post_type'      => 'page',
			'post_parent'    => $this->classifieds_page_id,
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			'menu_order'     => 1
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type", 'checkout_classified' );
		}

		$this->checkout_page_id = $page_id; // Remember the number
		$this->checkout_page_slug = $classifieds_page->post_name; //Remember the slug

		$classifieds_page = $this->get_page_by_meta( 'add_classified_page' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			$current_user = wp_get_current_user();
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'Add Classified',
			'post_status'    => 'virtual',
			'post_author'    => $current_user->ID,
			'post_parent'    => $this->classifieds_page_id,
			'post_type'      => 'page',
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type",  'add_classified_page' );
		}

		$this->add_classified_page_id = $page_id; // Remember the number
		$this->add_classified_page_page_slug = $classifieds_page->post_name; //Remember the slug

		$classifieds_page = $this->get_page_by_meta( 'edit_classified' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			$current_user = wp_get_current_user();
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'Edit Classified',
			'post_status'    => 'virtual',
			'post_author'    => $current_user->ID,
			'post_parent'    => $this->classifieds_page_id,
			'post_type'      => 'page',
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type",  'edit_classified' );
		}

		$this->edit_classified_page_id = $page_id; // Remember the number
		$this->edit_classified_page_slug = $classifieds_page->post_name; //Remember the slug

		$classifieds_page = $this->get_page_by_meta( 'my_classifeds_credits' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			$current_user = wp_get_current_user();
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'My Classifieds Credits',
			'post_name'     => 'my-credits',
			'post_status'    => 'virtual',
			'post_author'    => $current_user->ID,
			'post_parent'    => $this->classifieds_page_id,
			'post_type'      => 'page',
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed',
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type", 'my_classifeds_credits' );
		}

		$this->my_credits_page_id = $page_id; // Remember the number
		$this->my_credits_page_slug = $classifieds_page->post_name; //Remember the slug
		/*
		$classifieds_page = $this->get_page_by_meta( 'classifieds_signup' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
		// Construct args for the new post
		$args = array(
		'post_title'     => 'Classifieds Signup',
		'post_status'    => 'virtual',
		'post_author'    => $current_user->ID,
		'post_parent'    => $this->classifieds_page_id,
		'post_type'      => 'page',
		'post_content'   => $post_content,
		'ping_status'    => 'closed',
		'comment_status' => 'closed'
		);
		$page_id = wp_insert_post( $args );
		$classifieds_page = get_post($page_id);
		add_post_meta( $page_id, "classifieds_type", "classifieds_signup" );
		}

		$this->signup_page_id = $page_id; //Remember the number
		$this->signup_page_slug = $classifieds_page->post_name; //Remember the slug
		*/

		$classifieds_page = $this->get_page_by_meta( 'classifieds_signin' );
		$page_id = ($classifieds_page && $classifieds_page->ID > 0) ? $classifieds_page->ID : 0;

		if ( empty($page_id) ) {
			/* Construct args for the new post */
			$args = array(
			'post_title'     => 'Classifieds Signin',
			'post_name'      => 'signin',
			'post_status'    => 'virtual',
			'post_author'    => $current_user->ID,
			'post_parent'    => $this->classifieds_page_id,
			'post_type'      => 'page',
			'post_content'   => $post_content,
			'ping_status'    => 'closed',
			'comment_status' => 'closed'
			);
			$page_id = wp_insert_post( $args );
			$classifieds_page = get_post($page_id);
			add_post_meta( $page_id, "classifieds_type", "classifieds_signin" );
		}

		$this->signin_page_id = $page_id; //Remember the number
		$this->signin_page_slug = $classifieds_page->post_name; //Remember the slug

	}

	/**
	* Print a list of javascript vars specific to Directory for use in javascript routines
	*
	*/
	function on_print_scripts(){
		echo '<script type="text/javascript">';
		echo "\nvar\n";
		echo "cf_classifieds = '" . esc_attr(get_permalink($this->classifieds_page_id) ) . "';\n";
		echo "cf_add = '" . esc_attr(get_permalink($this->add_classified_page_id) ) . "';\n";
		echo "cf_edit = '" . esc_attr(get_permalink($this->edit_classified_page_id) ) . "';\n";
		echo "cf_my = '" . esc_attr(get_permalink($this->my_classifieds_page_id) ) . "';\n";
		echo "cf_credits = '" . esc_attr(get_permalink($this->my_credits_page_id) ) . "';\n";
		echo "cf_checkout = '" . esc_attr(get_permalink($this->checkout_page_id) ) . "';\n";
		echo "cf_signin = '" . esc_attr(get_permalink($this->signin_page_id) ) . "';\n";
		echo "</script>\n";
	}


	/**
	* Process login request.
	*
	* @param string $username
	* @param string $password
	* @return object $result->errors
	**/
	function login( $username, $password ) {
		/* Check whether the required information is submitted */
		if ( empty( $username ) || empty( $password ) )
		return __( 'Please fill in the required fields.', $this->text_domain );
		/* Build the login credentials */
		$credentials = array( 'remember' => true, 'user_login' => $username, 'user_password' => $password );
		/* Sign the user in and get the result */
		$result = wp_signon( $credentials );
		if ( isset( $result->errors )) {
			if ( isset( $result->errors['invalid_username'] ))
			return $result->errors['invalid_username'][0];
			elseif ( isset( $result->errors['incorrect_password'] ))
			return $result->errors['incorrect_password'][0];
		}
	}

	/**
	* Add custom role for Classifieds members. Add new capabilities for admin.
	*
	* @global $wp_roles
	* @return void
	**/

	/*
	function roles() {
	global $wp_roles;

	// @todo remove remove_role
	if ( $wp_roles ) {
	$wp_roles->remove_role( $this->user_role );

	$wp_roles->add_role( $this->user_role, 'Classifieds Member', array(
	'publish_classifieds'       => true,
	'edit_classified'          => true,
	'edit_others_classifieds'   => false,
	'delete_classifieds'        => true,
	'delete_others_classifieds' => false,
	'read_private_classifieds'  => false,
	'edit_classified'           => true,
	'delete_classified'         => true,
	'read_classified'           => true,
	'upload_files'              => true,
	'assign_terms'              => true,
	'read'                      => true
	) );

	// Set administrator roles
	$wp_roles->add_cap( 'administrator', 'publish_classifieds' );
	$wp_roles->add_cap( 'administrator', 'edit_classified' );
	$wp_roles->add_cap( 'administrator', 'edit_others_classifieds' );
	$wp_roles->add_cap( 'administrator', 'delete_classifieds' );
	$wp_roles->add_cap( 'administrator', 'delete_others_classifieds' );
	$wp_roles->add_cap( 'administrator', 'read_private_classifieds' );
	$wp_roles->add_cap( 'administrator', 'edit_classified' );
	$wp_roles->add_cap( 'administrator', 'delete_classified' );
	$wp_roles->add_cap( 'administrator', 'read_classified' );
	$wp_roles->add_cap( 'administrator', 'assign_terms' );
	}
	}
	*/

	/**
	* Show only current user classifieds on page of classifieds posttype page.
	*
	* @return void
	**/
	function show_only_c_user_classifieds ( $wp_query ) {
		if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/edit.php' ) !== false )
		if ( isset( $_GET['post_type'] ) && 'classifieds' == $_GET['post_type'] &&  !current_user_can( 'level_10' ) )
		$wp_query->set( 'author', get_current_user_id() );
	}

	/**
	* Map meta capabilities
	*
	* Learn more:
	* @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
	* @link http://wordpress.stackexchange.com/questions/1684/what-is-the-use-of-map-meta-cap-filter/2586#2586
	*
	* @param <type> $caps
	* @param <type> $cap
	* @param <type> $user_id
	* @param <type> $args
	* @return array
	**/
	function map_meta_cap( $caps, $cap, $user_id, $args ) {

		/* If editing, deleting, or reading a classified, get the post and post type object. */
		if ( 'edit_classified' == $cap || 'delete_classified' == $cap || 'read_classified' == $cap ) {
			$post = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );

			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing a classified, assign the required capability. */
		if ( 'edit_classified' == $cap ) {
			if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
			else
			$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting a classified, assign the required capability. */
		elseif ( 'delete_classified' == $cap ) {
			if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
			else
			$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private classified, assign the required capability. */
		elseif ( 'read_classified' == $cap ) {

			if ( 'private' != $post->post_status )
			$caps[] = 'read';
			elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
			else
			$caps[] = $post_type->cap->read_private_posts;
		}

		/* Return capabilities required by the user. */
		return $caps;
	}

	/**
	* Update or insert ad if no ID is passed.
	*
	* @param array $params Array of $_POST data
	* @return int $post_id
	**/
	function update_ad( $params ) {

		$current_user = wp_get_current_user();
		/* Construct args for the new post */
		$args = array(
		/* If empty ID insert Ad insetad of updating it */
		'ID'             => ( isset( $params['classified_data']['ID'] ) ) ?  $params['classified_data']['ID'] : '',
		'post_title'     => wp_strip_all_tags($params['classified_data']['post_title']),
		'post_name'      => '',
		'post_content'   => $params['classified_data']['post_content'],
		'post_excerpt'   => (empty($params['classified_data']['post_excerpt'])) ? '' : $params['classified_data']['post_excerpt'],
		'post_status'    => $params['classified_data']['post_status'],
		'post_author'    => get_current_user_id(),
		'post_type'      => $this->post_type,
		'ping_status'    => 'closed',
		//'comment_status' => 'open'
		);

		/* Insert page and get the ID */
		if(empty($args['ID']) )
		$post_id = wp_insert_post( $args );
		else
		$post_id = wp_update_post( $args );

		if ( ! empty($post_id) ) {
			//Save custom tags
			if(is_array($params['tag_input'])){
				foreach($params['tag_input'] as $key => $tags){
					wp_set_post_terms($post_id, $params['tag_input'][$key], $key);
				}
			}

			//Save categories
			if(is_array($params['post_category'])){
				wp_set_post_terms($post_id, $params['post_category'], 'category');
			}

			//Save custom terms
			if(is_array($params['tax_input'])){
				foreach($params['tax_input'] as $key => $term_ids){
					if ( is_array( $params['tax_input'][$key] ) ) {
						wp_set_post_terms($post_id, $params['tax_input'][$key], $key);
					}
				}
			}

			if ( class_exists( 'CustomPress_Core' ) ) {
				global $CustomPress_Core;
				$CustomPress_Core->save_custom_fields( $post_id );
			}

			return $post_id;
		}
	}

	/**
	* Checking that current user has full access to add ads without credits.
	*
	* @return boolean
	**/
	function is_full_access() {
		global $blog_id;

		$result = false;
		
		//for admin
		if ( current_user_can('manage_options') )	$result = true;

		//for paid users
		if ( $this->transactions->billing_type ) {
			if ( 'one_time' == $this->transactions->billing_type && 'success' == $this->transactions->status ) {
				$result = true;
			}elseif ( 'recurring' == $this->transactions->billing_type && 'success' == $this->transactions->status) {
				if(time() < $this->transactions->expires ) {
					$result = true;
				} else {
					$this->transactions->status = 'expired';
				}
			}
		}
		return apply_filters('classifieds_full_access', $result);
	}

	/**
	* Return the number of credits based on the duration selected.
	*
	* @return int|string Number of credits
	**/
	function get_credits_from_duration( $duration ) {
		$options = $this->get_options('payments');

		if ( !isset( $options['credits_per_week'] ) || $this->use_free)
		$options['credits_per_week'] = 0;

		$weeks = $duration + 0; //convert to leading number. Allows use of things like 1.5 weeks.

		return round($weeks * $options['credits_per_week']);
	}


	/**
	* Handle user login.
	*
	* @return void
	**/
	function handle_login_requests() {

		if ( isset( $_POST['login_submit'] ) )
		$this->login_error = $this->login( $_POST['username'], $_POST['password'] );
	}

	/**
	* Handles the request for the contact form on the single{}.php template
	**/
	function handle_contact_form_requests() {

		/* Only handle request if on single{}.php template and our post type */
		if ( get_post_type() == $this->post_type && is_single($_SESSION['cf_random_value']) ) {
			
			if(! session_id() ) session_start();
			
			if (isset( $_POST['contact_form_send'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'send_message' ) ){
				if ( isset( $_POST['name'] ) && '' != $_POST['name'] 
				&& isset( $_POST['email'] ) && '' != $_POST['email'] 
				&& isset( $_POST['subject'] ) && '' != $_POST['subject'] 
				&& isset( $_POST['message'] ) && '' != $_POST['message'] 
				&& isset( $_POST['cf_random_value'] ) && (md5(strtoupper($_POST['cf_random_value']) )  == $_SESSION['cf_random_value'] ) 
				
				) {

					global $post;

					$user_info  = get_userdata( $post->post_author );

					$body       = 'Hi %s, you have received message from:<br />
					<br />
					Name: %s <br />
					Email: %s <br />
					Subject: %s <br />
					Message: <br />
					%s
					<br />
					<br />
					<br />
					Classifieds link: %s
					';

					$tm_subject =  'Contact Request: %s [ %s ]';

					$to         = $user_info->user_email;
					$subject    = sprintf( __( $tm_subject, $this->text_domain ), $_POST['subject'], $post->post_title );
					$message    = sprintf( __( $body, $this->text_domain ), $user_info->user_nicename, $_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'], get_permalink( $post->ID ) );
					$headers    = "MIME-Version: 1.0\n" . "From: " . $_POST['name'] .  " <{$_POST['email']}>\n" . "Content-Type: text/html; charset=\"" . get_option( 'blog_charset' ) . "\"\n";

					$sent = (wp_mail( $to, $subject, $message, $headers ) ) ? '1' : '0';
					wp_redirect( get_permalink( $post->ID ) . '?sent=' . $sent );
					exit;
				}
			}
		}
	}

	/**
	* Save custom fields data
	*
	* @param int $post_id The post id of the post being edited
	* @return NULL If there is autosave attempt
	**/
	function save_expiration_date( $post_id ) {
		/* prevent autosave from deleting the custom fields */
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return;
		/* Update  */
		if ( isset( $_POST[$this->custom_fields['duration']] ) ) {
			$date = $this->calculate_expiration_date( $post_id, $_POST[$this->custom_fields['duration']] );
			update_post_meta( $post_id, '_expiration_date', $date );
		} if ( isset( $_POST['custom_fields'][$this->custom_fields['duration']] ) ) {
			$date = $this->calculate_expiration_date( $post_id, $_POST['custom_fields'][$this->custom_fields['duration']] );
			update_post_meta( $post_id, '_expiration_date', $date );
		} elseif ( isset( $_POST['duration'] ) ) {
			$date = $this->calculate_expiration_date( $post_id, $_POST['duration'] );
			update_post_meta( $post_id, '_expiration_date', $date );
		}
	}

	/**
	* Get formated expiration date.
	*
	* @param int|string $post_id
	* @return string Date/Time formated string
	**/
	function get_expiration_date( $post_id ) {
		$date = get_post_meta( $post_id, '_expiration_date', true );
		if ( !empty( $date ) )
		return date( get_option('date_format'), $date );
		else
		return __( 'No expiration date set.', $this->text_domain );
	}

	/**
	* Calculate the Unix time stamp of the modified posts
	*
	* @param int|string $post_id
	* @param string $duration Valid value: "1 Week", "2 Weeks" ... etc
	* @return int Unix timestamp
	**/
	function calculate_expiration_date( $post_id, $duration ) {

		$duration = trim(str_replace('-','',$duration)); //Remove old place holder

		if ( empty($duration) ) {
			$expiration_date = get_post_meta( $post_id, '_expiration_date', true );
			return $expiration_date;
		}
		/* Process normal request */
		$post = get_post( $post_id );
		$expiration_date = get_post_meta( $post_id, '_expiration_date', true );
		if ( empty( $expiration_date ) || $expiration_date < time() )
		$expiration_date = time();
		$date = strtotime( "+{$duration}", $expiration_date );
		return $date;
	}

	/**
	* Schedule expiration check for twice daily.
	*
	* @return void
	**/
	function schedule_expiration_check() {
		if ( !wp_next_scheduled( 'check_expiration_dates' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'check_expiration_dates' );
		}
	}

	/**
	* Check each post from the used post type and compare the expiration date/time
	* with the current date/time. If the post is expired update it's status.
	*
	* @return void
	**/
	function check_expiration_dates_callback() {
		$posts = get_posts( array( 'post_type' => $this->post_type, 'numberposts' => 0 ) );
		foreach ( $posts as $post ) {
			$expiration_date = get_post_meta( $post->ID, '_expiration_date', true );
			if ( empty( $expiration_date ) )
			$this->process_status( $post->ID, 'draft' );
			elseif ( $expiration_date < time() )
			$this->process_status( $post->ID, 'private' );
		}
	}

	/**
	* Save plugin options.
	*
	* @param  array $params The $_POST array
	* @return die() if _wpnonce is not verified
	**/
	function save_options( $params ) {
		if ( wp_verify_nonce( $params['_wpnonce'], 'verify' ) ) {
			/* Remove unwanted parameters */
			unset( $params['_wpnonce'], $params['_wp_http_referer'], $params['save'] );
			/* Update options by merging the old ones */
			$options = $this->get_options();
			$options = array_merge( $options, array( $params['key'] => $params ) );
			update_option( $this->options_name, $options );
		} else {
			die( __( 'Security check failed!', $this->text_domain ) );
		}
	}

	/**
	* Get plugin options.
	*
	* @param  string|NULL $key The key for that plugin option.
	* @return array $options Plugin options or empty array if no options are found
	**/
	function get_options( $key = NULL ) {
		$options = get_option( $this->options_name );
		$options = is_array( $options ) ? $options : array();
		/* Check if specific plugin option is requested and return it */
		if ( isset( $key ) && array_key_exists( $key, $options ) )
		return $options[$key];
		else
		return $options;
	}

	/**
	* Process post status.
	*
	* @global object $wpdb
	* @param  string $post_id
	* @param  string $status
	* @return void
	**/
	function process_status( $post_id, $status ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $post_id ), array( '%s' ), array( '%d' ) );
	}


	function comments_closed_text( $text ) {
		return '';
	}

	//filters the edit post button
	function delete_edit_post_link( $text ) {
		return '';
	}

	//filters the titles for our custom pages
	function page_title_output( $title, $id = false ) {
		global $wp_query, $post;

		//filter out nav titles
		if ($post->ID != $id )
		return $title;

		//taxonomy pages
		$tax_key = (empty($wp_query->query_vars['taxonomy']) ) ? '' : $wp_query->query_vars['taxonomy'];

		$taxonomies = get_object_taxonomies('classifieds', 'objects');
		if ( array_key_exists($tax_key, $taxonomies ) ){
			$term = get_term_by( 'slug', get_query_var( $tax_key ), $tax_key );
			return $taxonomies[$tax_key]->labels->singular_name . ': ' . $term->name;
		}

		//title for listings page
		if ( is_post_type_archive('classifieds' ) ){
			return post_type_archive_title();
		}
		return $title;
	}

	/**
	* Format date.
	*
	* @param int $date unix timestamp
	* @return string formatted date
	**/
	function format_date( $date ) {
		return date( get_option('date_format'), $date );
	}

	/**
	* Validate fields
	*
	* @param array $params $_POST data
	* @param array|NULL $file $_FILES data
	* @return void
	**/
	function validate_fields( $params, $file = NULL ) {
		if ( empty( $params['title'] ) || empty( $params['description'] ) || empty( $params['terms'] ) || empty( $params['status'] )) {
			$this->form_valid = false;
		}

		$options = $this->get_options( 'general' );

		//do image field not required
		if ( !isset( $options['field_image_req'] ) || '1' != $options['field_image_req'] )
		if ( $file['image']['error'] !== 0 ) {
			$this->form_valid = false;
		}

	}

	/**
	* Filter the template path to single{}.php templates.
	* Load from theme classifieds primary if it doesn't exist load from plugin dir.
	*
	* Learn more: http://codex.wordpress.org/Template_Hierarchy
	* Learn more: http://codex.wordpress.org/Plugin_API/Filter_Reference#Template_Filters
	*
	* @global <type> $post Post object
	* @param string Templatepath to filter
	* @return string Templatepath
	**/
	function get_single_template( $template ) {
		global $post;
		$tpldir = get_template_directory();
		$template = (file_exists("{$tpldir}/single-{$template}.php"))
		? "{$tpldir}/single-{$template}.php"
		: (file_exists("{$this->plugin_dir}ui-front/general/single-{$template}.php") ) ? "{$this->plugin_dir}ui-front/general/single-{$template}.php" : $template;
		return $template;
		/*
		if ( ! file_exists( get_template_directory() . "/single-{$post->post_type}.php" )
		&& file_exists( "{$this->plugin_dir}ui-front/general/single-{$post->post_type}.php" ) )
		return "{$this->plugin_dir}ui-front/general/single-{$post->post_type}.php";
		else
		return $template;
		*/
	}

	function template_file($template){
		$tpldir = get_template_directory();
		$template = (file_exists("{$tpldir}/page-{$template}.php"))
		? "{$tpldir}/page-{$template}.php"
		: (file_exists("{$this->plugin_dir}ui-front/general/page-{$template}.php") ) ? "{$this->plugin_dir}ui-front/general/page-{$template}.php" : $template;
		return $template;
	}


	/**
	* Filter the template path to page{}.php templates.
	* Load from theme directory primary if it doesn't exist load from plugin dir.
	*
	* Learn more: http://codex.wordpress.org/Template_Hierarchy
	* Learn more: http://codex.wordpress.org/Plugin_API/Filter_Reference#Template_Filters
	*
	* @global <type> $post Post object
	* @param string Templatepath to filter
	* @return string Templatepath
	**/
	function get_page_template( $template ) {
		global $post, $paged;
		/*
		//get page number for pagination
		if ( get_query_var('paged') ) {
		$paged = get_query_var('paged'); //Usually paged
		} elseif ( get_query_var('page') ) { //But if front page it's page
		$paged = get_query_var('page');
		} else {
		$paged = 1;
		}

		$this->cf_page = $paged;
		*/

		//Translate back to standard names.
		$name = $post->post_name;
		if($post->ID == $this->classifieds_page_id) $name = $this->classifieds_page_name;
		if($post->ID == $this->my_classifieds_page_id) $name = $this->my_classifieds_page_name;
		if($post->ID == $this->checkout_page_id) $name = $this->checkout_page_name;

		if ( ! file_exists( get_template_directory() . "/page-{$name}.php" ) && file_exists( "{$this->plugin_dir}ui-front/general/page-{$name}.php" ) ){
			return "{$this->plugin_dir}ui-front/general/page-{$name}.php";
		} else {
			return $template;
		}
	}

	/**
	*Get Template for classifieds author page
	**/
	function get_cf_author_template() {
		global $wp_query;

		if ( '' != get_query_var( 'cf_author_name' ) || isset( $_REQUEST['cf_author'] ) && '' != $_REQUEST['cf_author'] )  {
			if ( file_exists(get_template_directory() . '/author.php')){
				load_template( get_template_directory() . '/author.php' );
			} else {
				load_template( "{$this->plugin_dir}ui-front/general/author.php" );
			}
			exit();
		}
	}

	/**
	* Filter the template path to taxonomy{}.php templates.
	* Load from theme directory primary if it doesn't exist load from plugin dir.
	*
	* Learn more: http://codex.wordpress.org/Template_Hierarchy
	* Learn more: http://codex.wordpress.org/Plugin_API/Filter_Reference#Template_Filters
	*
	* @global <type> $post Post object
	* @param string Templatepath to filter
	* @return string Templatepath
	**/
	function get_taxonomy_template( $template ) {

		$taxonomy = get_query_var('taxonomy');
		$term = get_query_var('term');

		if ( "classifieds_categories" != $taxonomy && "classifieds_tags" != $taxonomy )
		return;


		/* Check whether the files doesn't exist in the active theme directory,
		* also check for file to load in our general template directory */
		if ( ! file_exists( get_template_directory() . "/taxonomy-{$taxonomy}-{$term}.php" )
		&& file_exists( "{$this->plugin_dir}ui-front/general/taxonomy-{$taxonomy}-{$term}.php" ) )
		return "{$this->plugin_dir}ui-front/general/taxonomy-{$taxonomy}-{$term}.php";
		elseif ( ! file_exists( get_template_directory() . "/taxonomy-{$taxonomy}.php" )
		&& file_exists( "{$this->plugin_dir}ui-front/general/taxonomy-{$taxonomy}.php" ) )
		return "{$this->plugin_dir}ui-front/general/taxonomy-{$taxonomy}.php";
		elseif ( ! file_exists( get_template_directory() . "/taxonomy.php" )
		&& file_exists( "{$this->plugin_dir}ui-front/general/taxonomy.php" ) )
		return "{$this->plugin_dir}ui-front/general/taxonomy.php";
		else
		return $template;
	}

	/**
	* Renders a section of user display code.  The code is first checked for in the current theme display directory
	* before defaulting to the plugin
	*
	* @param  string $name Name of the admin file(without extension)
	* @param  string $vars Array of variable name=>value that is available to the display code(optional)
	* @return void
	**/
	function render_front( $name, $vars = array() ) {
		/* Construct extra arguments */
		foreach ( $vars as $key => $val )
		$$key = $val;
		/* Include templates */

		$result = get_template_directory() . "/{$name}.php";
		if ( file_exists( $result ) ){
			include($result);
			return;
		}

		$result = "{$this->plugin_dir}ui-front/buddypress/members/single/classifieds/{$name}.php";
		if ( file_exists( $result ) && $this->bp_active ){
			include($result);
			return;
		}

		$result = "{$this->plugin_dir}ui-front/general/{$name}.php";

		if ( file_exists( $result ) ) {
			include($result);
			return;
		}

		echo "<p>Rendering of template $result {$name}.php failed</p>";
	}

	/**
	* Redirect using JavaScript. Useful if headers are already sent.
	*
	* @param string $url The URL to which the function should redirect
	**/
	function js_redirect( $url, $silent = false ) {
		if(! $silent ):
		?>
		<p><?php _e( 'You are being redirected. Please wait.', $this->text_domain );  ?></p>
		<img src="<?php echo $this->plugin_url .'/ui-front/general/images/loader.gif'; ?>" alt="<?php _e( 'You are being redirected. Please wait.', $this->text_domain );  ?>" />
		<?php endif; ?>
		<script type="text/javascript">//<![CDATA[
			window.location = '<?php echo $url; ?>';	//]]>
		</script>
		<?php
	}


	/**
	* return fancy pagination links.
	* @uses $wp_query
	*
	*/
	function pagination($show = true ){
		global $wp_query;

		if(! $show) return '';

		ob_start();

		include($this->plugin_dir . 'ui-front/general/pagination.php');

		$result = apply_filters( 'cf_pagination', ob_get_contents());
		ob_end_clean();

		return $result;
	}


	function get_post_image_link($post_id = 0){

		if ( ! ( post_type_supports( 'classifieds', 'thumbnail' )
		&& current_theme_supports( 'post-thumbnails', 'classifieds' ) ) )
		return '';

		ob_start();
		?>
		<div id="postimagediv">
			<div class="inside">
				<?php

				$tb_url = esc_attr(admin_url("/media-upload.php?post_id={$post_id}&type=image&TB_iframe=1&width=640&height=510") );
				$html = '<p>';

				$options = $this->get_options( 'general' );
				if( empty($options['field_image_req']) ){
					$html .= '<input type="text" style="visibility: hidden;" value="" class="required" /><br />';
				}
				$html .= '<a class="thickbox" href="' . $tb_url . '" id="set-post-thumbnail" >' . esc_html__( 'Set Featured Image') . '</a></p>';

				if(has_post_thumbnail($post_id)){
					$html = get_the_post_thumbnail($post_id, array(200,150));
					$ajax_nonce = wp_create_nonce( "set_post_thumbnail-{$post_id}" );
					$html .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail" onclick="WPRemoveThumbnail(\'' . $ajax_nonce . '\');return false;">' . esc_html__( 'Remove featured image' ) . '</a></p>';
				}
				echo $html;

				?>

			</div>
		</div>
		<?php
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	/**
	* Shortcode definitions
	*/

	function classifieds_categories_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'style' => '', //list, grid
		), $atts ) );
		$result = '<div id="cf_list_categories">' . PHP_EOL;
		if($style == 'grid') $result .= '<ul class="cf_list_grid">' .PHP_EOL;
		elseif($style == 'list') $result .= '<ul class="cf_list">' .PHP_EOL;
		else $result .= "<ul>\n";

		$result .= the_cf_categories_home( false, $atts );

		$result .= "</ul><!--.cf_list-->\n";
		$result .= "</div><!--.cf_list_categories-->\n";
		return $result;
	}

	function classifieds_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Classifieds', $this->text_domain),
		'view' => 'both', //loggedin, loggedout, both
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button classifieds_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->classifieds_page_id); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function add_classified_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Add Classified', $this->text_domain),
		'view' => 'both', //loggedin, loggedout, both
		), $atts ) );

		if( ! current_user_can('create_classifieds') ) return '';

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button create-new-btn add_classified_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->add_classified_page_id); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function edit_classified_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Edit Classified', $this->text_domain),
		'view' => 'both', //loggedin, loggedout, both
		'post' => '0',
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button add_classified_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->edit_classified_page_id) . "?post_id=$post"; ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function checkout_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Classifieds Checkout', $this->text_domain),
		'view' => 'both', //loggedin, loggedout, both
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button checkout_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->checkout_page_id); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function my_credits_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('My Classifieds Credits', $this->text_domain),
		'view' => 'both', //loggedin, loggedout, both
		), $atts ) );

		if(! $this->use_credits) return '';

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button credits_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->my_credits_page_id); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function my_classifieds_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('My Classifieds', $this->text_domain),
		'view' => 'loggedin', //loggedin, loggedout, both
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button my_classified_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->my_classifieds_page_id); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function profile_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Go to Profile', $this->text_domain),
		'view' => 'both', //loggedin, loggedout, both
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button profile_btn" type="button" onclick="window.location.href='<?php echo admin_url() . 'profile.php'; ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function signin_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Signin', $this->text_domain),
		'redirect' => '',
		'view' => 'loggedout', //loggedin, loggedout, both
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$options = $this->get_options( 'general' );
		if(empty($redirect)) $redirect = ( empty($options['signin_url'])) ? home_url() : $options['signin_url'];

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button signin_btn" type="button" onclick="window.location.href='<?php echo get_permalink($this->signin_page_id) . '?redirect_to=' . urlencode($redirect); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function logout_btn_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Logout', $this->text_domain),
		'redirect' => '',
		'view' => 'loggedin', //loggedin, loggedout, both
		), $atts ) );

		$view = strtolower($view);
		if(is_user_logged_in())	{if($view == 'loggedout') return '';}
		else if($view == 'loggedin') return '';

		$options = $this->get_options( 'general' );
		if(empty($redirect)) $redirect = ( empty($options['logout_url'])) ? home_url() : $options['logout_url'];

		$content = (empty($content)) ? $text : $content;
		ob_start();
		?>
		<button class="cf_button logout_btn" type="button" onclick="window.location.href='<?php echo wp_logout_url($redirect); ?>';" ><?php echo $content; ?></button>
		<?php
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function custom_fields_sc( $atts, $content = null ) {
		extract( shortcode_atts( array(
		'text' => __('Logout', $this->text_domain),
		'redirect' => '',
		'view' => 'loggedin', //loggedin, loggedout, both
		), $atts ) );

		$options = get_option( $this->options_name );
		$content = (empty($content)) ? $text : $content;
		ob_start();
		$this->display_custom_fields_values();
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	//close comments
	function close_comments( $open, $id = 0 ) {
		return false;
	}

	/**
	* Custom Template.
	*
	* @return void
	**/
	function custom_classifieds_template( $template ) {
		return $this->classifieds_template;
	}

	/**
	* Classifieds content.
	*
	* @return void
	**/
	function classifieds_content($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		remove_filter( 'the_title', array( &$this, 'page_title_output' ), 10 , 2 );
		remove_filter('the_content', array(&$this, 'classifieds_content'));
		require($this->template_file('classifieds'));
		wp_reset_query();

		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* Update Classifieds.
	*
	* @return void
	**/
	function update_classified_content($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('update-classified'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* My Classifieds.
	*
	* @return void
	**/
	function my_classifieds_content($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('my-classifieds'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* My Classifieds.
	*
	* @return void
	**/
	function checkout_content($content = null){
		if(! in_the_loop()) return $content;
		remove_filter('the_content', array(&$this, 'checkout_content'));
		ob_start();
		require($this->template_file('checkout'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* Signin.
	*
	* @return void
	**/
	function signin_content($content = null){
		if(! in_the_loop()) return $content;
		remove_filter( 'the_title', array( &$this, 'delete_post_title' ) ); //after wpautop
		remove_filter('the_content', array(&$this, 'signin_content'));
		ob_start();
		require($this->template_file('signin'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* My Classifieds Credits.
	*
	* @return void
	**/
	function my_credits_content($content = null){
		if(! in_the_loop()) return $content;

		remove_filter('the_content', array(&$this, 'my_credits_content'));
		ob_start();
		require($this->plugin_dir . 'ui-front/general/page-my-credits.php');
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* Single Classifieds.
	*
	* @return void
	**/
	function single_content($content = null){
		if(! in_the_loop()) return $content;
		remove_filter('the_content', array(&$this, 'single_content'));

		ob_start();
		require($this->plugin_dir . 'ui-front/general/single-classifieds.php');
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	function no_title($content = ''){
		if(! in_the_loop()) return $content;
		return '';
	}

	function update_classified($params){
		/* Construct args for the new post */
		$args = array(
		/* If empty ID insert Listing instead of updating it */
		'ID'             => ( isset( $params['classified_data']['ID'] ) ) ? $params['classified_data']['ID'] : '',
		'post_title'     => wp_strip_all_tags($params['classified_data']['post_title']),
		'post_content'   => $params['classified_data']['post_content'],
		'post_excerpt'   => (isset($params['classified_data']['post_excerpt'])) ? $params['classified_data']['post_excerpt'] : '',
		'post_status'    => $params['classified_data']['post_status'],
		'post_author'    => get_current_user_id(),
		'post_type'      => 'classifieds',
		'ping_status'    => 'closed',
		//'comment_status' => 'closed'
		);

		/* Insert page and get the ID */
		if (empty($args['ID']))
		$post_id = wp_insert_post( $args );
		else
		$post_id = wp_update_post( $args );

		if (! empty( $post_id ) ) {

			//Save custom tags
			if(is_array($params['tag_input'])){
				foreach($params['tag_input'] as $key => $tags){
					wp_set_post_terms($post_id, $params['tag_input'][$key], $key);
				}
			}

			//Save categories
			if(is_array($params['post_category'])){
				wp_set_post_terms($post_id, $params['post_category'], 'category');
			}

			//Save custom terms
			if(is_array($params['tax_input'])){
				foreach($params['tax_input'] as $key => $term_ids){
					if ( is_array( $params['tax_input'][$key] ) ) {
						wp_set_post_terms($post_id, $params['tax_input'][$key], $key);
					}
				}
			}

			if ( class_exists( 'CustomPress_Core' ) ) {
				global $CustomPress_Core;
				$CustomPress_Core->save_custom_fields( $post_id );
			}

			return $post_id;
		}
	}

	function hide_duration($content=''){
		return str_replace('_ct_selectbox_4cf582bd61fa4','',$content);
	}

}

include_once CF_PLUGIN_DIR . 'core/class-cf-transactions.php';

endif;

// Set flag on activation to trigger initial data
add_action('activated_plugin', 'cf_flag_activation', 1);
function cf_flag_activation($plugin=''){
	//Flag we're activating
	if($plugin == 'classifieds/loader.php') add_site_option('cf_activate', true);
}

//Decide whether to load Admin, Buddypress or Standard version
add_action('plugins_loaded', 'cf_on_plugins_loaded');
function cf_on_plugins_loaded(){
	if(is_admin()){ 	//Are we admin
		include_once CF_PLUGIN_DIR . 'core/admin.php';
	}
	elseif(defined('BP_VERSION')){ //Are we BuddyPress
		include_once CF_PLUGIN_DIR . 'core/buddypress.php';
	}
	else {
		include_once CF_PLUGIN_DIR . 'core/main.php';
	}
}

