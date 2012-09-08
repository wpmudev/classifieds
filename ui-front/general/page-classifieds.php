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

query_posts($query_args);

load_template( CF_PLUGIN_DIR . 'ui-front/general/loop-taxonomy.php' );

wp_reset_query();