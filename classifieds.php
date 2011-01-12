<?php

/*
Plugin Name: Classifieds
Plugin URI: http://premium.wpmudev.org/project/classifieds
Description: Add Classifieds to your blog, network or BuddyPress site. Create and manage ads, upload images, send emails, play with the provided set of widgets, enable the credit system and charge your users for placing ads on your network or BuddyPress site.
Version: 1.2.0
Author: Ivan Shaovchev (Incsub), Andrew Billits,
Author URI: http://ivan.sh
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
 * Classifieds LOADER
 * Loads Classifieds plugin
 *
 * @package Classifieds
 * @subpackage Loader
 * @since 1.1.0
 */



/* Define plugin version */
define ( 'CF_VERSION', '1.2.0' );
define ( 'CF_DB_VERSION', '2.0' );

/* define the plugin folder url */
define ( 'CF_PLUGIN_URL', WP_PLUGIN_URL . '/' . str_replace( basename(__FILE__), '', plugin_basename(__FILE__) ));
/* define the plugin folder dir */
define ( 'CF_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . str_replace( basename(__FILE__), '', plugin_basename(__FILE__) ));

/* 
 * Load plugin files from folder
 */
include_once 'classifieds/classifieds.php';
include_once 'core/core.php';
include_once 'submodules/content-types/ct-loader.php';

/* tmp debug func */
function cf_debug( $param ) {
    echo '<pre>';
    print_r( (array) $param );
    echo '</pre>';
}

?>