<?php

/*
Plugin Name: Classifieds
Plugin URI: http://premium.wpmudev.org/project/classifieds
Description: A brief description of the Plugin.
Version: 1.1.0
Author: Andrew Billits, Ivan Shaovchev
Author URI:
License: GNU General Public License (Version 2 - GPLv2)
*/

/*
Copyright 2007-2010 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
 * Classifieds SHORTCODE
 * Handles the shortcode functionality of the plugin
 *
 * @package Classifieds
 * @subpackage Shortcodes
 * @since 1.1.0
 */

function classifieds_page_shortcode() {
    
    $tmp_search_query = $_POST['search'];

    $tmp_search_query = urldecode( $tmp_search_query );
    $tmp_base_url = get_option('siteurl') . '/classifieds/';

    $tmp_current_cat  = $_GET['cat'];
    $tmp_current_ad   = $_GET['ad'];
    $tmp_current_page = $_GET['page'];
    
    $tmp_base_url = get_option('siteurl') . '/classifieds/';

    if ( $tmp_search_query != '' ) {
        //search results
        classifieds_frontend_search_results_paginated( $tmp_search_query,'10', $tmp_current_page, $tmp_base_url );
    } else if ( $tmp_current_ad != '' ) {
        //ad listing
        classifieds_frontend_display_ad_information( $tmp_current_ad, $tmp_base_url );
        classifieds_frontend_display_ad_contact_form( $tmp_current_ad, $tmp_base_url );
    } else if ( $tmp_current_cat != '' ) {
        //category listings
        classifieds_frontend_display_ads_paginated( $tmp_current_cat, '10', $tmp_current_page, $tmp_base_url );
    } else {
        //frontpage listings
        classifieds_frontend_display_ads( '',10,'random',$tmp_base_url );
    }
}
add_shortcode('classifieds_page', 'classifieds_page_shortcode');

?>