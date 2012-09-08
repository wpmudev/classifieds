<?php
/**
* Uninstall Classifieds plugin
* @package Classifieds
* @version 1.0.0
* @copyright Incsub 2007-2011 {@link http://incsub.com}
* @author Arnold Bailey (Incsub)
* @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
*/

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
exit();

//Remove Classifieds custom post types and fields

$ct_custom_post_types = get_site_option( 'ct_custom_post_types' );
unset($ct_custom_post_types['classifieds']);
update_site_option( 'ct_custom_post_types', $ct_custom_post_types );

$ct_custom_post_types = get_option( 'ct_custom_post_types' );
unset($ct_custom_post_types['classifieds']);
update_option( 'ct_custom_post_types', $ct_custom_post_types );

$ct_custom_taxonomies = get_site_option('ct_custom_taxonomies');
unset($ct_custom_taxonomies['classifieds_tags']);
update_site_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );

$ct_custom_taxonomies = get_option('ct_custom_taxonomies');
unset($ct_custom_taxonomies['classifieds_tags']);
update_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );

$ct_custom_taxonomies = get_site_option('ct_custom_taxonomies');
unset($ct_custom_taxonomies['classifieds_categories']);
update_site_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );

$ct_custom_taxonomies = get_option('ct_custom_taxonomies');
unset($ct_custom_taxonomies['classifieds_categories']);
update_option( 'ct_custom_taxonomies', $ct_custom_taxonomies );

$ct_network_custom_fields = ( get_site_option( 'ct_custom_fields' ) );
unset($ct_network_custom_fields['selectbox_4cf582bd61fa4'], $ct_network_custom_fields['text_4cfeb3eac6f1f']);
update_site_option( 'ct_custom_fields', $ct_network_custom_fields );

$ct_custom_fields = ( get_option( 'ct_custom_fields' ) );
unset($ct_custom_fields['selectbox_4cf582bd61fa4'], $ct_custom_fields['text_4cfeb3eac6f1f']);
update_option( 'ct_custom_fields', $ct_custom_fields );

//Remove Virtual pages @todo

flush_rewrite_rules();
