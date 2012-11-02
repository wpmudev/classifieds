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
	
	$taxonomies = array_values(get_taxonomies(array('object_type' => array('directory_listing'), 'hierarchical' => 1)));

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
	$output .= "</div>\n";

	return $output;
}