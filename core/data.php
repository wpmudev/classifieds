<?php

/**
* Load core DB data. Only loaded during Activation
*/
if ( !class_exists('Classifieds_Core_Data') ):
class Classifieds_Core_Data {

	/**
	* Constructor.
	*
	* @return void
	**/

	function __construct() {
		add_action( 'init', array( &$this, 'load_data' ) );
		add_action( 'init', array( &$this, 'load_payment_data' ) );
		add_action( 'init', array( &$this, 'load_mu_plugins' ) );
		add_action( 'init', array( &$this, 'rewrite_rules' ) );
	}

	/**
	* Load initial Content Types data for plugin
	*
	* @return void
	*/
	function load_data() {
		/* Get setting options. If empty return an array */
		$options = ( get_site_option( CF_OPTIONS_NAME ) ) ? get_site_option( CF_OPTIONS_NAME ) : array();

		// Check whether post types are loaded

		if ( ! post_type_exists('classifieds') ) {

			$classifieds_default =
			array (
			'can_export' => true,
			'capability_type' => 'classified',
			'description' => 'Classifieds post type.',
			'has_archive' => 'classifieds',
			'hierarchical' => false,
			'map_meta_cap' => true,
			'menu_position' => '',
			'public' => true,
			'query_var' => true,
			'rewrite' => array ( 'slug' => 'classified', 'with_front' => false, 'pages' => true),

			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'revisions', /*'post-formats'*/ ),

			'labels' => array (
			'name'          => __('Classifieds', CF_TEXT_DOMAIN),
			'singular_name' => __('Classified', CF_TEXT_DOMAIN),
			'add_new'       => __('Add New', CF_TEXT_DOMAIN),
			'add_new_item'  => __('Add New Classified', CF_TEXT_DOMAIN),
			'edit_item'     => __('Edit Classified', CF_TEXT_DOMAIN),
			'new_item'      => __('New Classified', CF_TEXT_DOMAIN),
			'view_item'     => __('View Classified', CF_TEXT_DOMAIN),
			'search_items'  => __('Search Classifieds', CF_TEXT_DOMAIN),
			'not_found'     => __('No Classifieds Found', CF_TEXT_DOMAIN),
			'not_found_in_trash' => __('No Classifieds Found In Trash', CF_TEXT_DOMAIN),
			),
			);

			//Update custom post types
			if(is_network_admin()){
				$ct_custom_post_types = get_site_option( 'ct_custom_post_types' );
				$ct_custom_post_types['classifieds'] = $classifieds_default;
				update_site_option( 'ct_custom_post_types', $ct_custom_post_types );
			} else {
				$ct_custom_post_types = get_option( 'ct_custom_post_types' );
				$ct_custom_post_types['classifieds'] = $classifieds_default;
				update_option( 'ct_custom_post_types', $ct_custom_post_types );
			}

			// Update post types and delete tmp options
			flush_network_rewrite_rules();
		}

		/* Check whether taxonomies data is loaded */


		if ( ! taxonomy_exists('classifieds_tags') ){

			$classifieds_tags_default = array();
			$classifieds_tags_default['object_type'] = array ( 'classifieds');
			$classifieds_tags_default['args'] = array (
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array ( 'slug' => 'cf-tags', 'with_front' => false, 'hierarchical' => false ),
			'query_var' => true,
			'capabilities' => array ('assign_terms' => 'edit_classifieds'),

			'labels' => array (
			'name'          => __( 'Classified Tags', CF_TEXT_DOMAIN ),
			'singular_name' => __( 'Classified Tag', CF_TEXT_DOMAIN ),
			'search_items'  => __( 'Search Classified Tags', CF_TEXT_DOMAIN ),
			'popular_items' => __( 'Popular Classified Tags', CF_TEXT_DOMAIN ),
			'all_items'     => __( 'All Classified Tags', CF_TEXT_DOMAIN ),
			'edit_item'     => __( 'Edit Classified Tag', CF_TEXT_DOMAIN ),
			'update_item'   => __( 'Update Classified Tag', CF_TEXT_DOMAIN ),
			'add_new_item'  => __( 'Add New Classified Tag', CF_TEXT_DOMAIN ),
			'new_item_name' => __( 'New Classified Tag Name', CF_TEXT_DOMAIN ),
			'add_or_remove_items' => __( 'Add or remove Classified tags', CF_TEXT_DOMAIN ),
			'choose_from_most_used' => __( 'Choose from the most used Classified tags', CF_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Classified tags with commas', CF_TEXT_DOMAIN ),
			),
			);

			if(is_network_admin()){
				$ct_custom_taxonomies = get_site_option('ct_custom_taxonomies');
				$ct_custom_taxonomies['classifieds_tags'] = $classifieds_tags_default;
				update_site_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );
			} else {
				$ct_custom_taxonomies = get_option('ct_custom_taxonomies');
				$ct_custom_taxonomies['classifieds_tags'] = $classifieds_tags_default;
				update_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );
			}

			// Update post types and delete tmp options
			flush_network_rewrite_rules();

		}

		if ( ! taxonomy_exists('classifieds_categories') ){

			if(is_multisite()){
				$ct = get_option( 'ct_custom_taxonomies' ); // get the blog types
				if(isset($ct['classifieds_categories'])) unset($ct['classifieds_categories']);
				update_option( 'ct_custom_taxonomies', $ct ); //Remove from site specific and move to network options.
			}

			$classifieds_categories_default = array();
			$classifieds_categories_default['object_type'] = array ('classifieds');
			$classifieds_categories_default['args'] = array (
			'public' => true,
			'hierarchical'  => true,
			'rewrite' => array ('slug' => 'cf-categories', 'with_front' => false, 'hierarchical' => true),
			'query_var' => true,
			'capabilities' => array ( 'assign_terms' => 'edit_classifieds' ),

			'labels' => array (
			'name'          => __( 'Classified Categories', CF_TEXT_DOMAIN ),
			'singular_name' => __( 'Classified Category', CF_TEXT_DOMAIN ),
			'search_items'  => __( 'Search Classified Categories', CF_TEXT_DOMAIN ),
			'popular_items' => __( 'Popular Classified Categories', CF_TEXT_DOMAIN ),
			'all_items'     => __( 'All Classified Categories', CF_TEXT_DOMAIN ),
			'parent_item'   => __( 'Parent Category', CF_TEXT_DOMAIN ),
			'edit_item'     => __( 'Edit Classified Category', CF_TEXT_DOMAIN ),
			'update_item'   => __( 'Update Classified Category', CF_TEXT_DOMAIN ),
			'add_new_item'  => __( 'Add New Classified Category', CF_TEXT_DOMAIN ),
			'new_item_name' => __( 'New Classified Category', CF_TEXT_DOMAIN ),
			'parent_item_colon'   => __( 'Parent Category:', CF_TEXT_DOMAIN ),
			'add_or_remove_items' => __( 'Add or remove Classified categories', CF_TEXT_DOMAIN ),
			),
			);

			if(is_network_admin()){
				$ct_custom_taxonomies = get_site_option('ct_custom_taxonomies');
				$ct_custom_taxonomies['classifieds_categories'] = $classifieds_categories_default;
				update_site_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );
			} else {
				$ct_custom_taxonomies = get_option('ct_custom_taxonomies');
				$ct_custom_taxonomies['classifieds_categories'] = $classifieds_categories_default;
				update_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );
			}

			flush_network_rewrite_rules();
		}


		/* Check whether custom fields data is loaded */

		$ct_custom_fields = ( get_option( 'ct_custom_fields' ) );
		$ct_network_custom_fields = ( get_site_option( 'ct_custom_fields' ) );

		if ( empty($ct_custom_fields['selectbox_4cf582bd61fa4']) && empty($ct_network_custom_fields['selectbox_4cf582bd61fa4'])){

			$selectbox_4cf582bd61fa4_default =
			array (
			'field_title' => 'Duration',
			'field_type' => 'selectbox',
			'field_sort_order' => 'default',
			'field_options' =>
			array (
			1 => '',
			2 => '1 Week',
			3 => '2 Weeks',
			4 => '3 Weeks',
			5 => '4 Weeks',
			),
			'field_default_option' => '1',
			'field_description' => 'Extend the duration of this ad. ',
			'object_type' => array ('classifieds'),
			'hide_type' => array (),

			'field_required' => NULL,
			'field_id' => 'selectbox_4cf582bd61fa4',
			);

			if( is_network_admin() ){
				$ct_network_custom_fields['selectbox_4cf582bd61fa4'] = $selectbox_4cf582bd61fa4_default;
				update_site_option( 'ct_custom_fields', $ct_network_custom_fields );
			} else {
				$ct_custom_fields['selectbox_4cf582bd61fa4'] = $selectbox_4cf582bd61fa4_default;
				update_option( 'ct_custom_fields', $ct_custom_fields );
			}
		}

		if ( empty($ct_custom_fields['text_4cfeb3eac6f1f']) && empty($ct_network_custom_fields['text_4cfeb3eac6f1f'])){

			$text_4cfeb3eac6f1f_default =
			array (
			'object_type' => array ('classifieds'),
			'hide_type' => array (),
			'field_title' => 'Cost',
			'field_type' => 'text',
			'field_sort_order' => 'default',
			'field_default_option' => NULL,
			'field_description' => 'The cost of the item.',
			'field_required' => NULL,
			'field_id' => 'text_4cfeb3eac6f1f',
			);

			if( is_network_admin() ){
				$ct_network_custom_fields['text_4cfeb3eac6f1f'] = $text_4cfeb3eac6f1f_default;
				update_site_option( 'ct_custom_fields', $ct_network_custom_fields );
			} else {
				$ct_custom_fields['text_4cfeb3eac6f1f'] = $text_4cfeb3eac6f1f_default;
				update_option( 'ct_custom_fields', $ct_custom_fields );
			}
		}

		//Custompress specfic
		if(is_multisite()){
			update_site_option( 'allow_per_site_content_types', true );
			update_site_option( 'display_network_content_types', true );

		}

		flush_network_rewrite_rules();

	}

	function load_payment_data() {

		$options = ( get_option( CF_OPTIONS_NAME ) ) ? get_option( CF_OPTIONS_NAME ) : array();
		$options = ( is_array($options) ) ? $options : array();

		//General default
		if(empty($options['general']) ){
			$options['general'] = array(
			'member_role'             => 'subscriber',
			'moderation'              => array('publish' => 1, 'pending' => 1, 'draft' => 1 ),
			'custom_fields_structure' => 'table',
			'welcome_redirect'        => 'true',
			'key'                     => 'general'
			);
		}

		//Update from older version
		if (! empty($options['general_settings']) ) {
			$options['general'] = array_replace($options['general_settings']);
			unset($options['general_settings']);
		}

		//Default Payments settings
		if ( empty( $options['payments'] ) ) {
			$options['payments'] = array(
			'enable_recurring'    => '1',
			'recurring_cost'      => '9.99',
			'recurring_name'      => 'Subscription',
			'billing_period'      => 'Month',
			'billing_frequency'   => '1',
			'billing_agreement'   => 'Customer will be billed at &ldquo;9.99 per month for 2 years&rdquo;',
			'enable_one_time'     => '1',
			'one_time_cost'       => '99.99',
			'one_time_name'       => 'One Time Only',
			'enable_credits'      => '1',
			'cost_credit'         => '.99',
			'credits_per_week'    => 1,
			'signup_credits'      => 0,
			'credits_description' => '',
			'tos_txt'             => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at sem libero. Pellentesque accumsan consequat porttitor. Curabitur ut lorem sed ipsum laoreet tempus at vel erat. In sed tempus arcu. Quisque ut luctus leo. Nulla facilisi. Sed sodales lectus ut tellus venenatis ac convallis metus suscipit. Vestibulum nec orci ut erat ultrices ullamcorper nec in lorem. Vivamus mauris velit, vulputate eget adipiscing elementum, mollis ac sem. Aliquam faucibus scelerisque orci, ut venenatis massa lacinia nec. Phasellus hendrerit lorem ornare orci congue elementum. Nam faucibus urna a purus hendrerit sit amet pulvinar sapien suscipit. Phasellus adipiscing molestie imperdiet. Mauris sit amet justo massa, in pellentesque nibh. Sed congue, dolor eleifend egestas egestas, erat ligula malesuada nulla, sit amet venenatis massa libero ac lacus. Vestibulum interdum vehicula leo et iaculis.',
			'key'                 => 'payments'
			);
		}

		if (! empty($options['payment_settings']) ) {
			$options['payments'] = array_replace($options['payment_settings']);
			unset($options['payment_settings']);
		}

		if(empty($options['payment_types']) ) {
			$options['payment_types'] = array(
			'use_free'         => 1,
			'use_paypal'       => 0,
			'use_authorizenet' => 0,
			'paypal'           => array('api_url' => 'sandbox', 'api_username' => '', 'api_password' => '', 'api_signature' => '', 'currency' => 'USD'),
			'authorizenet'     => array('mode' => 'sandbox', 'delim_char' => ',', 'encap_char' => '', 'email_customer' => 'yes', 'header_email_receipt' => 'Thanks for your payment!', 'delim_data' => 'yes'),
			);
		}

		if ( ! empty($options['paypal']) ){
			$options['payment_types']['paypal'] = array_replace($options['paypal']);
			unset($options['paypal']);
		}

		update_option( CF_OPTIONS_NAME, $options );
	}

	function load_mu_plugins(){

		if(!is_dir(WPMU_PLUGIN_DIR . '/logs')):
		mkdir(WPMU_PLUGIN_DIR . '/logs', 0755, true);
		endif;

		copy(	CF_PLUGIN_DIR . 'mu-plugins/gateway-relay.php', WPMU_PLUGIN_DIR .'/gateway-relay.php');
		copy(	CF_PLUGIN_DIR . 'mu-plugins/wpmu-assist.php', WPMU_PLUGIN_DIR .'/wpmu-assist.php');

	}

	function rewrite_rules() {

		add_rewrite_rule("classifieds/author/([^/]+)/page/?([2-9][0-9]*)",
		"index.php?post_type=classifieds&author_name=\$matches[1]&paged=\$matches[2]", 'top');

		add_rewrite_rule("classifieds/author/([^/]+)",
		"index.php?post_type=classifieds&author_name=\$matches[1]", 'top');

		flush_network_rewrite_rules();
	}

}

endif;
