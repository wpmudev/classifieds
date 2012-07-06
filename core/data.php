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
	function Classifieds_Core_Data() {
		add_action( 'init', array( &$this, 'load_data' ) );
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
			'capability_type' => 'classified',
			'map_meta_cap' => true,
			'description' => 'Classifieds post type.',
			'menu_position' => '',
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array ( 'slug' => 'classified', 'with_front' => false),
			'query_var' => true,
			'can_export' => true,

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
			update_site_option( 'ct_flush_rewrite_rules', true );
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
			'capabilities' => array ('assign_terms' => 'assign_terms'),

			'labels' => array (
			'name'          => __( 'Classified Tags', $this->text_domain ),
			'singular_name' => __( 'Classified Tag', $this->text_domain ),
			'search_items'  => __( 'Search Classified Tags', $this->text_domain ),
			'popular_items' => __( 'Popular Classified Tags', $this->text_domain ),
			'all_items'     => __( 'All Classified Tags', $this->text_domain ),
			'edit_item'     => __( 'Edit Classified Tag', $this->text_domain ),
			'update_item'   => __( 'Update Classified Tag', $this->text_domain ),
			'add_new_item'  => __( 'Add New Classified Tag', $this->text_domain ),
			'new_item_name' => __( 'New Classified Tag Name', $this->text_domain ),
			'separate_items_with_commas' => __( 'Separate Classified tags with commas', $this->text_domain ),
			'add_or_remove_items'        => __( 'Add or remove Classified tags', $this->text_domain ),
			'choose_from_most_used'      => __( 'Choose from the most used Classified tags', $this->text_domain ),
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
			update_site_option( 'ct_flush_rewrite_rules', true );

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
			'capabilities' => array ( 'assign_terms' => 'assign_terms' ),

			'labels' => array (
			'name'          => __( 'Classified Categories', $this->text_domain ),
			'singular_name' => __( 'Classified Category', $this->text_domain ),
			'search_items'  => __( 'Search Classified Categories', $this->text_domain ),
			'popular_items' => __( 'Popular Classified Categories', $this->text_domain ),
			'all_items'     => __( 'All Classified Categories', $this->text_domain ),
			'parent_item'   => __( 'Parent Category', $this->text_domain ),
			'edit_item'     => __( 'Edit Classified Category', $this->text_domain ),
			'update_item'   => __( 'Update Classified Category', $this->text_domain ),
			'add_new_item'  => __( 'Add New Classified Category', $this->text_domain ),
			'new_item_name' => __( 'New Classified Category', $this->text_domain ),
			'parent_item_colon'   => __( 'Parent Category:', $this->text_domain ),
			'add_or_remove_items' => __( 'Add or remove Classified categories', $this->text_domain ),
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

			// Update post types and delete tmp options
			update_site_option( 'ct_flush_rewrite_rules', true );
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
			1 => '----------',
			2 => '1 Week',
			3 => '2 Weeks',
			4 => '3 Weeks',
			5 => '4 Weeks',
			),
			'field_default_option' => '1',
			'field_description' => 'The duration of this ad. ',
			'object_type' => array ('classifieds'),

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

		//add rule for show cf-author page
		global $wp, $wp_rewrite;

		if ( class_exists( 'BP_Core' ) ) {
			$wp->add_query_var( 'cf_author_page' );
			$wp->add_query_var( 'cf_current_component' );
			$result = add_query_arg(  array(
			'cf_author_page'        => '$matches[3]',
			'cf_current_component'  => 'classifieds'
			), 'index.php' );
			//            add_rewrite_rule( 'members/admin/classifieds(/page/(.+?))?/?$', $result, 'top' );
			add_rewrite_rule( 'members/(.+?)/classifieds(/page/(.+?))?/?$', $result, 'top' );
			$rules = get_option( 'rewrite_rules' );
			if ( ! isset( $rules['members/(.+?)/classifieds(/page/(.+?))?/?$'] ) )
			//            if ( ! isset( $rules['members/admin/classifieds(/page/(.+?))?/?$'] ) )
			$wp_rewrite->flush_rules();
		} else {
			$wp->add_query_var( 'cf_author_name' );
			$wp->add_query_var( 'cf_author_page' );
			$result = add_query_arg(  array(
			'cf_author_name' => '$matches[1]',
			'cf_author_page' => '$matches[3]',
			), 'index.php' );
			add_rewrite_rule( 'cf-author/(.+?)(/page/(.+?))?/?$', $result, 'top' );
			$rules = get_option( 'rewrite_rules' );
			if ( ! isset( $rules['cf-author/(.+?)(/page/(.+?))?/?$'] ) )
			$wp_rewrite->flush_rules();
		}
	}
}

endif;

?>