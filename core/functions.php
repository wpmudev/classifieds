<?php

/**
* Does a Classifieds listing support a given taxonomy
* @return bool
*/
function cf_supports_taxonomy($taxonomy=''){
	global $wp_taxonomies;

	if(empty($taxonomy)) return false;
	return (is_array($wp_taxonomies[$taxonomy]->object_type)) ? in_array('classifieds', $wp_taxonomies[$taxonomy]->object_type) : false;
}

function the_cf_categories_home( $echo = true ){
	
	//get plugin options
	$options  = get_option( CF_OPTIONS_NAME );

	$cat_num                = ( isset( $options['general']['count_cat'] ) && is_numeric( $options['general']['count_cat'] ) && 0 < $options['general']['count_cat'] ) ? $options['general']['count_cat'] : 10;
	$sub_cat_num            = ( isset( $options['general']['count_sub_cat'] ) && is_numeric( $options['general']['count_sub_cat'] ) && 0 < $options['general']['count_sub_cat'] ) ? $options['general']['count_sub_cat'] : 5;
	$hide_empty_sub_cat     = ( isset( $options['general']['hide_empty_sub_cat'] ) && is_numeric( $options['general']['hide_empty_sub_cat'] ) && 0 < $options['general']['hide_empty_sub_cat'] ) ? $options['general']['hide_empty_sub_cat'] : 0;
	
	$taxonomies = array_values(get_taxonomies(array('object_type' => array('classifieds'), 'hierarchical' => 1)));

	$args = array(
	'parent'       => 0,
	'orderby'      => 'name',
	'order'        => 'ASC',
	'hide_empty'   => 0,
	'hierarchical' => 1,
	'number'       => $cat_num,
	'taxonomy'     => $taxonomies,
	'pad_counts'   => 1
	);

	$categories = get_categories( $args );

	$output = '<div id="cf_list_categories" class="cf_list_categories" >' . "\n";
	$output .= "<ul>\n";

	foreach( $categories as $category ){

		$output .= "<li>\n";
		$output .= '<h2><a href="' . get_term_link( $category ) . '" title="' . __( 'View all posts in ', CF_TEXT_DOMAIN ) . $category->name . '" >' . $category->name . "</a> </h2>\n";
		$args = array(
		'show_option_all'    => '',
		'orderby'            => 'name',
		'order'              => 'ASC',
		'style'              => 'none',
		'show_count'         => 1,
		'hide_empty'         => $hide_empty_sub_cat,
		'use_desc_for_title' => 1,
		'child_of'           => $category->term_id,
		'feed'               => '',
		'feed_type'          => '',
		'feed_image'         => '',
		'exclude'            => '',
		'exclude_tree'       => '',
		'include'            => '',
		'hierarchical'       => true,
		'title_li'           => '',
		'show_option_none'   => __('No categories', CF_TEXT_DOMAIN ),
		'number'             => $sub_cat_num,
		'echo'               => 0,
		'depth'              => 1,
		'current_category'   => 0,
		'pad_counts'         => 1,
		'taxonomy'           => $category->taxonomy,
		'walker'             => null
		);
		$output .= 	wp_list_categories($args);

		$output .= "</li>\n";

	}

	$output .= "</ul>\n";
	$output .= "</div><!-- .cf_list_categories -->\n";

	return $output;
}

/**
* the_dir_breadcrumbs
*
* @access public
* @return void
*/
function the_cf_breadcrumbs() {
	global $wp_query;
	
	$output = '';
	$category = get_queried_object();
	$category_parent_ids = get_ancestors( $category->term_id, $category->taxonomy );
	$category_parent_ids = array_reverse( $category_parent_ids );
	
	foreach ( $category_parent_ids as $category_parent_id ) {
		$category_parent = get_term( $category_parent_id, $category->taxonomy );

		$output .= '<a href="' . get_term_link( $category_parent ) . '" title="' . sprintf( __( 'View all posts in %s', CF_TEXT_DOMAIN ), $category_parent->name ) . '" >' . $category_parent->name . '</a> / ';
	}

	$output .= '<a href="' . get_term_link( $category ) . '" title="' . sprintf( __( 'View all posts in %s', CF_TEXT_DOMAIN ), $category->name ) . '" >' . $category->name . '</a>';

	echo $output;
}

//function allow_classifieds_filter($allow = false){
//
//  //Whatever logic to decide whether they should have access.
//  if(false ) $allow = true;
//
//  return $allow;
//}
//add_filter('classifieds_full_access', 'allow_classifieds_filter');

