<?php

/**
* Load core DB data.
*/
if ( !class_exists('Classifieds_Core_Data') ):
class Classifieds_Core_Data extends Classifieds_Core {

	/**
	* Constructor.
	*
	* @return void
	**/
	function Classifieds_Core_Data() {
		add_action( 'init', array( &$this, 'load_data' ), 0 );
	}

	/**
	* Load initial Content Types data for plugin
	*
	* @return void
	*/
	function load_data() {
		/* Get setting options. If empty return an array */
		$options = ( get_site_option( $this->options_name ) ) ? get_site_option( $this->options_name ) : array();

		// Check whether post types are loaded
		$ct_custom_post_types = get_option( 'ct_custom_post_types' );
		if ( ! isset( $ct_custom_post_types['classifieds'] ) ) {

			$classifieds_default =
			array (
			'capability_type' => 'classified',
			'description' => 'Classifieds post type.',
			'menu_position' => '',
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array ( 'slug' => 'classified', 'with_front' => false),
			'query_var' => true,
			'can_export' => true,

			'labels' => array (
			'name' => 'Classifieds',
			'singular_name' => 'Classified',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Classified',
			'edit_item' => 'Edit Classified',
			'new_item' => 'New Classified',
			'view_item' => 'View Classified',
			'search_items' => 'Search Classifieds',
			'not_found' => 'No Classifieds Found',
			'not_found_in_trash' => 'No Classifieds Found In Trash',
			),

			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'revisions', /*'post-formats'*/ ),

			);

			//Update custom post types
			$ct_custom_post_types['classifieds'] = $classifieds_default;
			update_site_option( 'ct_custom_post_types', $ct_custom_post_types );

			// Update post types and delete tmp options
			update_site_option( 'ct_flush_rewrite_rules', true );
		}

		/* Check whether taxonomies data is loaded */

		$ct_custom_taxonomies = get_option('ct_custom_taxonomies');

		if (empty($ct_custom_taxonomies['classifieds_tags'])){

			$classifieds_tags_default = array();
			$classifieds_tags_default['object_type'] = array ( 'classifieds');
			$classifieds_tags_default['args'] = array (
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array ( 'slug' => 'cf-tags', 'with_front' => false, 'hierarchical' => false ),
			'query_var' => true,
			'capabilities' => array ('assign_terms' => 'assign_terms'),

			'labels' => array (
			'name' => 'Tags',
			'singular_name' => 'Tag',
			),
			);

			$ct_custom_taxonomies['classifieds_tags'] = $classifieds_tags_default;
			update_site_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );

			// Update post types and delete tmp options
			update_site_option( 'ct_flush_rewrite_rules', true );

		}

		if (empty($ct_custom_taxonomies['classifieds_categories'])){
			$classifieds_categories_default = array();
			$classifieds_categories_default['object_type'] = array ('classifieds');
			$classifieds_categories_default['args'] = array (
			'public' => true,
			'hierarchical'  => true,
			'rewrite' => array ('slug' => 'cf-categories', 'with_front' => false, 'hierarchical' => true),
			'query_var' => true,
			'capabilities' => array ( 'assign_terms' => 'assign_terms' ),

			'labels' => array (
			'name' => 'Categories',
			'singular_name' => 'Category',
			),
			);

			$ct_custom_taxonomies['classifieds_categories'] = $classifieds_categories_default;
			update_site_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );

			// Update post types and delete tmp options
			update_site_option( 'ct_flush_rewrite_rules', true );
		}


		/* Check whether custom fields data is loaded */

		$ct_custom_fields = ( get_site_option( 'ct_custom_fields' ) );

		if (empty($ct_custom_fields['selectbox_4cf582bd61fa4'])){

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

			'required' => NULL,
			'field_id' => 'selectbox_4cf582bd61fa4',
			);

			$ct_custom_fields['selectbox_4cf582bd61fa4'] = $selectbox_4cf582bd61fa4_default;
			update_site_option( 'ct_custom_fields', $ct_custom_fields );

		}

		if (empty($ct_custom_fields['text_4cfeb3eac6f1f'])){
			$text_4cfeb3eac6f1f_default =
			array (
			'field_title' => 'Cost',
			'field_type' => 'text',
			'field_sort_order' => 'default',
			'field_default_option' => NULL,
			'field_description' => 'The cost of the item.',
			'object_type' => array ('classifieds'),
			'required' => NULL,
			'field_id' => 'text_4cfeb3eac6f1f',
			);

			$ct_custom_fields['text_4cfeb3eac6f1f'] = $text_4cfeb3eac6f1f_default;
			update_site_option( 'ct_custom_fields', $ct_custom_fields );
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

/* Initiate Class */

$__classifieds_core_data = new Classifieds_Core_Data();

endif;

?>