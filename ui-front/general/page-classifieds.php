<?php
/**
* The template for displaying Classifieds Archive page.
* You can override this file in your active theme.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/

global $bp, $post, $wp_query, $paged;

$options = $this->get_options( 'general' );

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

remove_filter( 'the_title', array( &$this, 'page_title_output' ), 10 , 2 );
remove_filter('the_content', array(&$this, 'classifieds_content'));

$query_args = array(
'paged' => $paged,
'post_status' => 'publish',
'post_type' => 'classifieds',
//'author' => get_query_var('author'),
);

//setup taxonomy if applicable
$tax_key = (empty($wp_query->query_vars['taxonomy'])) ? '' : $wp_query->query_vars['taxonomy'];
$taxonomies = array_values(get_object_taxonomies($query_args['post_type'], 'names') );

if ( in_array($tax_key, $taxonomies) ) {
	$query_args['tax_query'] = array(
	array(
	'taxonomy' => $tax_key,
	'field' => 'slug',
	'terms' => get_query_var( $tax_key),
	)
	);
}

query_posts($query_args);


load_template( $this->custom_classifieds_template( 'loop-taxonomy' ) );

if(is_object($wp_query)) $wp_query->post_count = 0; 
