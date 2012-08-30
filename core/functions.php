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
