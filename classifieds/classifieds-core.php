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
 * Classifieds CORE
 * Handles the overall operations of the plugin
 *
 * @package Classifieds
 * @subpackage Core
 * @since 1.1.0
 */

/** Define version constants */
define ( 'CLASSIFIEDS_VERSION', '1.1.0' );
define ( 'CLASSIFIEDS_DB_VERSION', 1.1 );

/** Define path constants */
define ( 'CLASSIFIEDS_PATH', 'classifieds/' );
define ( 'CLASSIFIEDS_UPLOAD_PATH', ABSPATH . 'wp-content/classifieds-images/' );
define ( 'CLASSIFIEDS_DEFAULT_IMAGE_PATH', ABSPATH . 'wp-content/mu-plugins/classifieds/classifieds_default.png' );

/** Currencies list */
$currencies = array(
    'AUD' => 'AUD - Australian Dollar',
    'CAD' => 'CAD - Canadian Dollar',
    'CHF' => 'CHF - Swiss Franc',
    'CZK' => 'CZK - Czech Koruna',
    'DKK' => 'DKK - Danish Krone',
    'EUR' => 'EUR - Euro',
    'GBP' => 'GBP - Pound Sterling',
    'HKD' => 'HKD - Hong Kong Dollar',
    'HUF' => 'HUF - Hungarian Forint',
    'JPY' => 'JPY - Japanese Yen',
    'NOK' => 'NOK - Norwegian Krone',
    'NZD' => 'NZD - New Zealand Dollar',
    'PLN' => 'PLN - Polish Zloty',
    'SEK' => 'SEK - Swedish Krona',
    'SGD' => 'SGD - Singapore Dollar',
    'USD' => 'USD - U.S. Dollar'
);

/** Install plugin if not installed or different version */
if ( !get_site_option( 'classifieds_installed' ) ||
      get_site_option( 'classifieds_db_version' ) < CLASSIFIEDS_DB_VERSION )
    classifieds_global_install();

/**
 * Install plugin - create database tables and set option configurations
 *
 * @since 1.1.0
 * 
 */
function classifieds_global_install() {
	global $wpdb, $user_ID;
	
    $classifieds_table1 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "classifieds_ads` (
                          `ad_ID` bigint(20) unsigned NOT NULL auto_increment,
                          `ad_user_ID` int(30),
                          `ad_first_name` varchar(255),
                          `ad_last_name` varchar(255),
                          `ad_email_address` varchar(255),
                          `ad_phone_number` varchar(255),
                          `ad_expire` bigint(30),
                          `ad_status` varchar(255),
                          `ad_title` varchar(255),
                          `ad_description` TEXT,
                          `ad_price` varchar(255),
                          `ad_currency` varchar(255),
                          `ad_primary_category` int(30),
                          `ad_secondary_category` int(30),
                          PRIMARY KEY  (`ad_ID`)
                        ) ENGINE=MyISAM  AUTO_INCREMENT=0;";

    $classifieds_table2 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "classifieds_categories` (
                          `category_ID` bigint(20) unsigned NOT NULL auto_increment,
                          `category_name` varchar(255),
                          `category_description` TEXT,
                          PRIMARY KEY  (`category_ID`)
                        ) ENGINE=MyISAM  AUTO_INCREMENT=0;";

    $classifieds_table3 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "classifieds_credits` (
                          `user_ID` int(11) NOT NULL default '0',
                          `credits` int(11) NOT NULL default '0',
                          PRIMARY KEY  (`user_ID`)
                        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";

    $wpdb->query( $classifieds_table1 );
    $wpdb->query( $classifieds_table2 );
    $wpdb->query( $classifieds_table3 );

    // check whether the page "classifieds" exists
    $page_exists = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->posts . " WHERE post_name = 'classifieds' AND post_type = 'page'" );

    // if no create it 
    if ( !$page_exists )
        $wpdb->query( "INSERT INTO " . $wpdb->posts . " ( post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count )
                       VALUES ( '" . $user_ID . "', '" . current_time( 'mysql' ) . "', '" . current_time( 'mysql' ) . "', '[classifieds_page]', '" . __('Classifieds') . "', '', 'publish', 'closed', 'closed', '', 'classifieds', '', '', '" . current_time( 'mysql' ) . "', '" . current_time( 'mysql' ) . "', '', 0, '', 0, 'page', '', 0 )" );
    
    // initiate configuration options
    update_site_option( 'classifieds_installed', true );
    update_site_option( 'classifieds_version', CLASSIFIEDS_VERSION );
    update_site_option( 'classifieds_db_version', CLASSIFIEDS_DB_VERSION );

    // disable the credits module by default
    update_site_option( 'classifieds_credits_enabled', false );

}

/*
 * Crop script
 */
function classifieds_plug_scripts() {
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/fat.js?ver=1.0-RC1_3660'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/prototype.js?ver=1.5.0-0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/scriptaculous/wp-scriptaculous.js?ver=1.7.0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/scriptaculous/builder.js?ver=1.7.0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/scriptaculous/effects.js?ver=1.7.0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/scriptaculous/dragdrop.js?ver=1.7.0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/scriptaculous/slider.js?ver=1.7.0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/scriptaculous/controls.js?ver=1.7.0'></script>";
	echo "<script type='text/javascript' src='" . get_option('siteurl') . "/wp-includes/js/crop/cropper.js'></script>";
	?>
	<script type="text/javascript">
        function onEndCrop( coords, dimensions ) {
            $( 'x1' ).value = coords.x1;
            $( 'y1' ).value = coords.y1;
            $( 'x2' ).value = coords.x2;
            $( 'y2' ).value = coords.y2;
            $( 'width' ).value = dimensions.width;
            $( 'height' ).value = dimensions.height;
        }

        // with a supplied ratio
        Event.observe(
            window,
            'load',
            function() {
                new Cropper.Img(
                    'upload',
                    {
                        ratioDim: { x: 500, y: 375 },
                        displayOnInit: true,
                        onEndCrop: onEndCrop
                    }
                )
            }
        );
	</script>
	<?php
}
if ( $_GET['page'] == 'classifieds_new' || $_GET['action'] == 'change_image_crop' ) {
	add_action('admin_head', 'classifieds_plug_scripts');
}

/*
 * Add css styling to the admin pages
 */
function classifieds_admin_styles() {
    ?>
    <style type="text/css">
        .classifieds table { text-align: left; }
        .classifieds table th { width: 200px; }
        .classifieds table td input { width: 250px; }
        .classifieds table td select { width: 250px; }
        .classifieds table td textarea { width: 250px; }
    </style>
    <?php
}
add_action( 'admin_head', 'classifieds_admin_styles' );

/*
 * Create admin menu pages
 */
function classifieds_admin_menu_pages() {
	add_menu_page( 'Classifieds', 'Classifieds', 'activate_plugins', 'classifieds', 'classifieds_page_main_output' );
	add_submenu_page( 'classifieds', 'Create an Ad &lsaquo; Classifieds', 'Create New Ad', 'activate_plugins', 'classifieds_new', 'classifieds_page_new_output' );
    add_submenu_page( 'classifieds', 'Configuration &lsaquo; Classifieds', 'Categories', 'edit_users', 'classifieds_categories', 'classifieds_page_categories_output');
}
add_action( 'admin_menu', 'classifieds_admin_menu_pages' );

/**
 * Inserts ad into the DB an returns the ad Id
 * 
 * @return Ad ID
 */
function classifieds_insert_ad( $tmp_title, $tmp_description, $tmp_primary_category, $tmp_secondary_category, $tmp_first_name, $tmp_last_name, $tmp_email_address, $tmp_phone_number, $tmp_currency, $tmp_price ) {
	global $wpdb, $current_user;

	$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "classifieds_ads (ad_user_ID, ad_first_name, ad_last_name, ad_email_address, ad_phone_number, ad_status, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category) VALUES ( '" . $current_user->ID . "', '" . $tmp_first_name . "', '" . $tmp_last_name . "', '" . $tmp_email_address . "', '" . $tmp_phone_number . "', 'saved', '" . $tmp_title . "', '" . $tmp_description . "', '" . $tmp_price . "', '" . $tmp_currency . "', '" . $tmp_primary_category . "', '" . $tmp_secondary_category . "' )" );

	$tmp_ad_id = $wpdb->get_var( "SELECT ad_ID FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_title = '" . $tmp_title . "' AND ad_description = '" . $tmp_description . "' AND ad_user_ID = '" . $current_user->ID . "'" );
    
	return $tmp_ad_id;
}

/*
 * Updates ad information
 */
function classifieds_update_ad( $tmp_ad_id, $tmp_title, $tmp_description, $tmp_primary_category, $tmp_secondary_category, $tmp_first_name, $tmp_last_name, $tmp_email_address, $tmp_phone_number, $tmp_currency, $tmp_price ) {
	global $wpdb;
    
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_first_name         = '" . $tmp_first_name . "'         WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_last_name          = '" . $tmp_last_name . "'          WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_email_address      = '" . $tmp_email_address . "'      WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_phone_number       = '" . $tmp_phone_number . "'       WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_title              = '" . $tmp_title . "'              WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_description        = '" . $tmp_description . "'        WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_price              = '" . $tmp_price . "'              WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_currency           = '" . $tmp_currency . "'           WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_primary_category   = '" . $tmp_primary_category . "'   WHERE ad_ID = '" . $tmp_ad_id . "'" );
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_secondary_category = '" . $tmp_secondary_category . "' WHERE ad_ID = '" . $tmp_ad_id . "'" );
}

/*
 * Updates ad status ( active, saved, ended )
 */
function classifieds_update_ad_status( $tmp_ad_id, $tmp_ad_status ) {
	global $wpdb;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_status = '" . $tmp_ad_status . "' WHERE ad_ID = '" . $tmp_ad_id . "'" );
}

function classifieds_update_ad_expire( $tmp_ad_id, $tmp_weeks ) {
	global $wpdb;
	$tmp_now = time();
	$tmp_amount_of_time = ($tmp_weeks * 7) * (24 * (60 * 60));
	$tmp_expire = $tmp_now + $tmp_amount_of_time;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_expire = '" . $tmp_expire . "' WHERE ad_ID = '" . $tmp_ad_id . "'" );
}

function classifieds_update_ad_expire_now( $tmp_ad_id ) {
	global $wpdb;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_ads SET ad_expire = '" . time() . "' WHERE ad_ID = '" . $tmp_ad_id . "'" );
}

function classifieds_check_expired( $tmp_ads ) {
	global $wpdb;
	foreach ( $tmp_ads as $tmp_ad ) {
		if (time() > $tmp_ad['ad_expire']) {
			//ad expired
			classifieds_update_ad_status( $tmp_ad['ad_ID'], 'ended' );
		}
	}
}

function classifieds_send_email( $tmp_ad_id, $tmp_ad_title, $tmp_to_email, $tmp_from_email, $tmp_from_name, $tmp_content ) {
	global $wpdb;
    
	$subject_content = 'RE: ' . $tmp_ad_title . '(ad: ' . $tmp_ad_id . ')';
	$message_headers = "MIME-Version: 1.0\n" . "From: " . $tmp_from_name .  " <" . $tmp_from_email . ">\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
	wp_mail( $tmp_to_email, $subject_content, $tmp_content, $message_headers );
}

function classifieds_delete_ad( $tmp_ad_id ){
	global $wpdb;
    
	$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_id . "'" );
    
	classifieds_delete_file( CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id . '-500.png' );
	classifieds_delete_file( CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id . '-80.png'  );
	classifieds_delete_file( CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id . '-40.png'  );
	classifieds_delete_file( CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id . '-16.png'  );
}

function classifieds_create_default_images( $tmp_ad_id ) {
	
	$im = ImageCreateFrompng( CLASSIFIEDS_DEFAULT_IMAGE_PATH );

	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (500, 375);
	imagecopyresampled($im_dest, $im, 0, 0, 0, 0, 500, 375, 500, 375);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-500.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (80, 60);
	imagecopyresampled($im_dest, $im, 0, 0, 0, 0, 80, 60, 500, 375);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-80.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (40, 30);
	imagecopyresampled($im_dest, $im, 0, 0, 0, 0, 40, 30, 500, 375);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-40.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (16, 12);
	imagecopyresampled($im_dest, $im, 0, 0, 0, 0, 16, 12, 500, 375);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-16.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
}

function classifieds_create_image( $tmp_ad_id, $tmp_image_type, $tmp_file_name, $tmp_x1, $tmp_y1, $tmp_x2, $tmp_y2 ) {
	
	if ($tmp_image_type == 'jpeg') {
		$im = ImageCreateFromjpeg(CLASSIFIEDS_UPLOAD_PATH . $tmp_file_name);
	}
	if ($tmp_image_type == 'png') {
		$im = ImageCreateFrompng(CLASSIFIEDS_UPLOAD_PATH . $tmp_file_name);
	}
	if ($tmp_image_type == 'gif') {
		$im = ImageCreateFromgif(CLASSIFIEDS_UPLOAD_PATH . $tmp_file_name);
	}
	
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (500, 375);
	$tmp_image_width = $tmp_x2 - $tmp_x1;
	$tmp_image_height = $tmp_y2 - $tmp_y1;
	imagecopyresampled($im_dest, $im, 0, 0, $tmp_x1, $tmp_y1, 500, 375, $tmp_image_width, $tmp_image_height);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-500.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (80, 60);
	$tmp_image_width = $tmp_x2 - $tmp_x1;
	$tmp_image_height = $tmp_y2 - $tmp_y1;
	imagecopyresampled($im_dest, $im, 0, 0, $tmp_x1, $tmp_y1, 80, 60, $tmp_image_width, $tmp_image_height);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-80.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (40, 30);
	$tmp_image_width = $tmp_x2 - $tmp_x1;
	$tmp_image_height = $tmp_y2 - $tmp_y1;
	imagecopyresampled($im_dest, $im, 0, 0, $tmp_x1, $tmp_y1, 40, 30, $tmp_image_width, $tmp_image_height);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-40.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
	$im_dest = imagecreatetruecolor (16, 12);
	imagecopyresampled($im_dest, $im, 0, 0, 0, 0, 16, 12, 500, 375);
	if ($tmp_image_type == 'png'){
		imagesavealpha($im_dest, true);
	}
	imagepng($im_dest, CLASSIFIEDS_UPLOAD_PATH . $tmp_ad_id .'-16.png');
	//----------------------------------------------------------------//
	//----------------------------------------------------------------//
    
	classifieds_delete_file( CLASSIFIEDS_UPLOAD_PATH . $tmp_file_name );

}

/*
 * Add clasifieds overview to main dashboard panel
 */
function classifieds_dashboard_output() {
	global $wpdb, $current_user;

	$tmp_saved_ads_count  = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_user_ID = '" . $current_user->ID . "' AND ad_status = 'saved'" );
	$tmp_active_ads_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_user_ID = '" . $current_user->ID . "' AND ad_status = 'active'" );
	$tmp_ended_ads_count  = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_user_ID = '" . $current_user->ID . "' AND ad_status = 'ended'" );
	?>
    <br />
	<div id='availablecredits'>
		<h3><?php _e("Classifieds <a href='admin.php?page=classifieds' title='Manage Ads'>&raquo;</a>"); ?></h3>
		<p><?php _e('Your Saved Ads:'); ?> <strong><?php echo $tmp_saved_ads_count; ?></strong></p>
		<p><?php _e('Your Active Ads:'); ?> <strong><?php echo $tmp_active_ads_count; ?></strong></p>
		<p><?php _e('Your Ended Ads:'); ?> <strong><?php echo $tmp_ended_ads_count; ?></strong></p>
		<p><?php _e('<a href="admin.php?page=classifieds_new">Click here to place a new ad &raquo;</a>'); ?></p>
	</div>
	<?php
}
add_action('activity_box_end', 'classifieds_dashboard_output');

/*
 * Outputs the admin categories page
 */
function classifieds_page_categories_output() {
	global $wpdb;
	
	if( !current_user_can('edit_users') ) {
		echo "<p>" . __('Nice Try...') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if ( isset($_GET['updated']) ) { ?>
        <div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div>
        <?php
	}

	echo '<div class="wrap classifieds">';
    
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
            $tmp_category_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_categories" ); ?>
        
            <a name='categories_top' id='categories_top'></a>
            <h2><?php _e('Classifieds Categories') ?> (<a href="admin.php?page=classifieds_categories&action=new_category"><?php _e('Create New Category') ?></a>)</h2>
            <?php
			if ($tmp_category_count == 0) {
            ?>
                <p><?php _e('Click ') ?><a href="admin.php?page=classifieds_categories&action=new_category"><?php _e('here') ?></a><?php _e(' to create a category.') ?></p>
            <?php
			} else {
                $query = "SELECT category_ID, category_name, category_description FROM " . $wpdb->base_prefix . "classifieds_categories";

                if( $_GET[ 'sortby' ] == 'name' )
                    $query .= ' ORDER BY category_name DESC';
                else
                    $query .= ' ORDER BY category_ID DESC';

                $tmp_classifieds_categories = $wpdb->get_results( $query, ARRAY_A );
                
                ?>
                <table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
                    <tr class='thead'>
                        <th scope='col'><a href='admin.php?page=classifieds_categories&sortby=id#categories_top'><?php echo __('ID'); ?></a></th>
                        <th scope='col'><a href='admin.php?page=classifieds_categories&sortby=name#categories_top'><?php echo __('Name'); ?></a></th>
                        <th scope='col'>Description</th>
                        <th scope='col'>Actions</th>
                        <th scope='col'></th>
                    </tr>
                <?php
                if ( count( $tmp_classifieds_categories ) > 0 ) {
                    $class = ( 'alternate' == $class ) ? '' : 'alternate';
                    foreach ( $tmp_classifieds_categories as $tmp_classifieds_category ) {
                        echo "<tr class='" . $class . "'>";
                        echo "<td valign='top'><strong>" . $tmp_classifieds_category['category_ID'] . "</strong></td>";
                        echo "<td valign='top'>" . $tmp_classifieds_category['category_name'] . "</td>";
                        echo "<td valign='top'>" . $tmp_classifieds_category['category_description'] . "</td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds_categories&action=edit_category&cid=" . $tmp_classifieds_category['category_ID'] . "' rel='permalink' class='edit'>" . __('Edit') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds_categories&action=delete_category&cid=" . $tmp_classifieds_category['category_ID'] . "' rel='permalink' class='delete'>" . __('Remove') . "</a></td>";
                        echo "</tr>";               
                        $class = ('alternate' == $class) ? '' : 'alternate';
                    }
                } ?>
                </table>
                <?php
			}
            break;    
		//---------------------------------------------------//
		case "new_category":
            ?>
			<h2><?php _e('New Category') ?></h2>
            <?php classifieds_categories_html_template();
            break;
		//---------------------------------------------------//
		case "new_category_process":
			$tmp_name_check = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_name = '" . $_POST['category_name'] . "'");
			if ( $_POST['category_name'] == '' ) {
				?>
				<h2><?php _e('New Category') ?></h2>
				<p><?php _e('You must provide a name for this category!') ?></p>
                <?php classifieds_categories_html_template();
			} else if ( $tmp_name_check > 0 ) {
				?>
				<h2><?php _e('New Category') ?></h2>
				<p><?php _e('There is already a category with that name!') ?></p>
                <?php classifieds_categories_html_template();
			} else {
				$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "classifieds_categories (category_name, category_description) VALUES ( '" . $_POST['category_name'] . "', '" . $_POST['category_description'] . "' )" );          
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds_categories&updated=true&updatedmsg=" . urlencode(__('Category Added!')) . "';
				      </script>";
			}
            break;
		//---------------------------------------------------//
		case "edit_category":
            $tmp_cat_name =        $wpdb->get_var( "SELECT category_name        FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $_GET['cid'] . "'" );
            $tmp_cat_description = $wpdb->get_var( "SELECT category_description FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $_GET['cid'] . "'" );
            ?>
			<h2><?php _e('Edit Category') ?></h2>
            <?php classifieds_categories_edit_html_template();
            break;
		//---------------------------------------------------//
		case "edit_category_process":
			$tmp_name_check = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_name = '" . $_POST['category_name'] . "' AND category_ID != '" . $_GET['cid'] . "'" );
			if ( $_POST['category_name'] == '' ) {
				?>
				<h2><?php _e('Edit Category') ?></h2>
				<p><?php _e('You must provide a name for this category!') ?></p>
                <?php classifieds_categories_edit_html_template();				
			} else if ( $tmp_name_check > 0 ) {
				$tmp_cat_name = $wpdb->get_var("SELECT category_name FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $_GET['cid'] . "'");
				?>
				<h2><?php _e('Edit Category') ?></h2>
				<p><?php _e('There is already a category with that name!') ?></p>
                <?php classifieds_categories_edit_html_template();
			} else {
				$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_categories SET category_name = '" . $_POST['category_name'] . "' WHERE category_ID = '" . $_GET['cid'] . "'" );
				$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_categories SET category_description = '" . $_POST['category_description'] . "' WHERE category_ID = '" . $_GET['cid'] . "'" );            
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds_categories&updated=true&updatedmsg=" . urlencode(__('Category Updated!')) . "';
                      </script>";
			}
            break;
		//---------------------------------------------------//
		case "delete_category":
			//do cat delete
			$wpdb->query( "DELETE FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $_GET['cid'] . "'" );
			
			echo "<script type='text/javascript'>
                      window.location='admin.php?page=classifieds_categories&updated=true&updatedmsg=" . urlencode(__('Category Removed!')) . "';
			      </script>";
            break;
	}
	echo '</div>';
}

/*
 * Lists active, saved and ended ads on the classifieds page
 */
function classifieds_page_main_output() {
	global $wpdb, $current_user, $current_site, $classifieds_credits_singular, $classifieds_credits_plural;
	
	$classifieds_path = 'http://' . $current_site->domain . $current_site->path . CLASSIFIEDS_PATH ;
	
	if ( isset( $_GET['updated'] ) ) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap classifieds">';
    
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_user_ID = '" . $current_user->ID . "' AND ad_status = 'saved'";
			$tmp_saved_ads = $wpdb->get_results( $query, ARRAY_A );

			$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_user_ID = '" . $current_user->ID . "' AND ad_status = 'active'";
			$tmp_active_ads = $wpdb->get_results( $query, ARRAY_A );

			$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_user_ID = '" . $current_user->ID . "' AND ad_status = 'ended'";
			$tmp_ended_ads = $wpdb->get_results( $query, ARRAY_A );


            if ( get_site_option('classifieds_credits_enabled') ) {
                //display information text
                echo '<p>' . get_site_option( "classifieds_description_text" ) . '</p>';
                echo '<p>' . __('Each ad costs ')
                           . get_site_option( "classifieds_credits_per_week" )
                           . __(' ' . ( $tmp_credits_cost = ( get_site_option( "classifieds_credits_per_week" ) == 1 ) ? $classifieds_credits_singular : $classifieds_credits_plural ) . ' per week. You currently have ' )
                           . classifieds_get_user_credits( $current_user->ID )
                           . __(' ' . ( $tmp_credits_current = ( classifieds_get_user_credits( $current_user->ID ) == 1 ) ? $classifieds_credits_singular : $classifieds_credits_plural ) . '.') . '</p>';
            }
            
                        
			if ( count( $tmp_saved_ads ) == 0 && count( $tmp_active_ads ) == 0 && count( $tmp_ended_ads ) == 0 ) { ?>
                <h2><?php _e('Classifieds Ads (<a href="admin.php?page=classifieds_new">Create New Ad</a>)') ?></h2>
                <p><?php //_e('Click <a href="admin.php?page=classifieds_new">here</a> to place an ad!') ?></p> <?php
			} else {
				if ( count( $tmp_saved_ads ) > 0 ) { ?>
					<h2><?php _e('Saved Ads') ?></h2>
                    <table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
                        <tr class='thead'>
                            <th scope='col'>ID</th>
                            <th scope='col'>Title</th>
                            <th scope='col'>Primary Category</th>
                            <th scope='col'>Created</th>
                            <th scope='col'>Image</th>
                            <th scope='col'>Actions</th>
                            <th scope='col'></th>
                            <th scope='col'></th>
                            <th scope='col'></th>
                        </tr> <?php
                    $class = '';
                    foreach ( $tmp_saved_ads as $tmp_saved_ad ) {
                        $tmp_cat_name = $wpdb->get_var("SELECT category_name FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $tmp_saved_ad['ad_primary_category'] . "'");
                        echo "<tr class='" . $class . "'>";
                        echo "<td valign='top'><strong>" . $tmp_saved_ad['ad_ID'] . "</strong></td>";
                        echo "<td valign='top'>" . $tmp_saved_ad['ad_title'] . "</td>";
                        echo "<td valign='top'>" . $tmp_cat_name . "</td>";
                        echo "<td valign='top' style='width: 300px'>" . date("D, F jS Y g:i A",$tmp_saved_ad['ad_expire']) . "</td>";
                        echo "<td valign='top'><img src='" . get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_saved_ad['ad_ID'] . "-16.png?" . md5(time()) . "'></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=place_ad&aid=" . $tmp_saved_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Place Ad') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=edit_ad&aid=" . $tmp_saved_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Edit Ad') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=change_image&aid=" . $tmp_saved_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Change Image') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=delete_ad&aid=" . $tmp_saved_ad['ad_ID'] . "' rel='permalink' class='delete'>" . __('Remove') . "</a></td>";
                        echo "</tr>";
                        $class = ('alternate' == $class) ? '' : 'alternate';
                    } ?>
                    </table><br />
                    <?php
				}
				if ( count( $tmp_active_ads ) > 0 ) {
					?>
					<h2><?php _e('Active Ads (<a href=' . $classifieds_path . '>view all ads</a>)') ?></h2>
                    <table cellpadding='3' cellspacing='3' width='100%' class='widefat'> 
                        <tr class='thead'>
                            <th scope='col'>ID</th>
                            <th scope='col'>Title</th>
                            <th scope='col'>Primary Category</th>
                            <th scope='col'>Ends</th>
                            <th scope='col'>Image</th>
                            <th scope='col'>Actions</th>
                            <th scope='col'></th>
                            <th scope='col'></th>
                        </tr> <?php
                    $class = '';
                    foreach ( $tmp_active_ads as $tmp_active_ad ) {
                        $tmp_cat_name = $wpdb->get_var("SELECT category_name FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $tmp_active_ad['ad_primary_category'] . "'");
                        echo "<tr class='" . $class . "'>";
                        echo "<td valign='top'><strong>" . $tmp_active_ad['ad_ID'] . "</strong></td>";
                        echo "<td valign='top'><a href='" . $classifieds_path . "?ad=" . $tmp_active_ad['ad_ID'] . "'>" . $tmp_active_ad['ad_title'] . "</a></td>";
                        echo "<td valign='top'>" . $tmp_cat_name . "</td>";
                        echo "<td valign='top' style='width: 300px'>" . date("D, F jS Y g:i A",$tmp_active_ad['ad_expire']) . "</td>";
                        echo "<td valign='top'><img src='" . get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_active_ad['ad_ID'] . "-16.png?" . md5(time()) . "'></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=edit_ad&aid=" . $tmp_active_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Edit Ad') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=change_image&aid=" . $tmp_active_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Change Image') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=end_ad&aid=" . $tmp_active_ad['ad_ID'] . "' rel='permalink' class='delete'>" . __('End Early') . "</a></td>";
                        echo "</tr>";
                        $class = ('alternate' == $class) ? '' : 'alternate';
                    } ?>
                    </table><br />
                    <?php
				}
				if ( count( $tmp_ended_ads ) > 0 ) {
					?>
					<h2><?php _e('Ended Ads') ?></h2>
                    <table cellpadding='3' cellspacing='3' width='100%' class='widefat'> 
                        <tr class='thead'>
                            <th scope='col'>ID</th>
                            <th scope='col'>Title</th>
                            <th scope='col'>Primary Category</th>
                            <th scope='col'>Ended</th>
                            <th scope='col'>Image</th>
                            <th scope='col'>Actions</th>
                            <th scope='col'></th>
                            <th scope='col'></th>
                            <th scope='col'></th>
                        </tr>
                    <?php
                    $class = '';
                    foreach ( $tmp_ended_ads as $tmp_ended_ad ) {
                        $tmp_cat_name = $wpdb->get_var("SELECT category_name FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $tmp_ended_ad['ad_primary_category'] . "'");
                        echo "<tr class='" . $class . "'>";
                        echo "<td valign='top'><strong>" . $tmp_ended_ad['ad_ID'] . "</strong></td>";
                        echo "<td valign='top'>" . $tmp_ended_ad['ad_title'] . "</td>";
                        echo "<td valign='top'>" . $tmp_cat_name . "</td>";
                        echo "<td valign='top' style='width: 300px'>" . date("D, F jS Y g:i A",$tmp_ended_ad['ad_expire']) . "</td>";
                        echo "<td valign='top'><img src='" . get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ended_ad['ad_ID'] . "-16.png?" . md5(time()) . "'></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=renew_ad&aid=" . $tmp_ended_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Renew Ad') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=edit_ad&aid=" . $tmp_ended_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Edit Ad') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=change_image&aid=" . $tmp_ended_ad['ad_ID'] . "' rel='permalink' class='edit'>" . __('Change Image') . "</a></td>";
                        echo "<td valign='top'><a href='admin.php?page=classifieds&action=delete_ad&aid=" . $tmp_ended_ad['ad_ID'] . "' rel='permalink' class='delete'>" . __('Remove') . "</a></td>";
                        echo "</tr>";
                        $class = ('alternate' == $class) ? '' : 'alternate';
                    } ?>
                    </table><br />
                    <?php
				}
			}
            if ( current_user_can('edit_users') ): ?>
                <h2><?php _e('Edit Ad by ID') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds&action=edit_ad">
                    <table class="optiontable">
                        <tr valign="top">
                            <th scope="row"><?php _e('Ad ID:') ?></th>
                            <td><input type="text" name="aid" value=""  />
                            <br />
                            <?php _e('Enter the ID of the ad you wish the modify' ); ?>
                            </td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" /></p>
                </form>
            <?php endif;
            break;
		//---------------------------------------------------//
		case "delete_ad":
			classifieds_delete_ad($_GET['aid']);
			echo "
			<script type='text/javascript'>
                window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Removed!')) . "';
			</script>
			";
            break;
		//---------------------------------------------------//
		case "change_image":
            ?>
            <h2><?php _e('Change Image') ?></h2>
            <p><?php _e('Current Image:') ?></p>
            <img src='<?php echo get_option('siteurl'); ?>/wp-content/classifieds-images/<?php echo $_GET['aid']; ?>-500.png?<?php echo md5(time()); ?>"'>
            <form name="step_one" method="POST" action="admin.php?page=classifieds&action=change_image_crop" enctype="multipart/form-data">
                <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />
                <fieldset class="options">
                    <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                        <tr valign="top">
                            <th scope="row"><?php _e('Select New Image:') ?></th>
                            <td>
                                <input name="change_image" id="change_image" size="20" type="file"><br />
                                <?php _e('Upload an image (jpeg, gif, or png) for this ad.') ?><br />
                                <?php _e('Note: GIF animations will not be preserved.') ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Upload &raquo;') ?>" />
                    <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
            </form>
            <?php
            break;
		//---------------------------------------------------//
		case "change_image_crop":

			$tmp_basename = basename($_FILES['change_image']['name']);
			$tmp_basename = str_replace(',','',$tmp_basename);
			$tmp_basename = str_replace(' ','',$tmp_basename);
			$tmp_basename = str_replace('&','',$tmp_basename);

			if ( isset($_POST['Cancel']) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds';
                      </script>";
			}
			if (move_uploaded_file($_FILES['change_image']['tmp_name'], CLASSIFIEDS_UPLOAD_PATH . $tmp_basename)){
				list($tmp_image_width, $tmp_image_height, $tmp_image_type, $tmp_image_attr) = getimagesize(get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_basename);
				if ($_FILES['change_image']['type'] == "image/gif"){
					$tmp_image_type = 'gif';
				}
				if ($_FILES['change_image']['type'] == "image/jpeg"){
					$tmp_image_type = 'jpeg';
				}
				if ($_FILES['change_image']['type'] == "image/pjpeg"){
					$tmp_image_type = 'jpeg';
				}
				if ($_FILES['change_image']['type'] == "image/jpg"){
					$tmp_image_type = 'jpeg';
				}
				if ($_FILES['change_image']['type'] == "image/png"){
					$tmp_image_type = 'png';
				}
				if ($_FILES['change_image']['type'] == "image/x-png"){
					$tmp_image_type = 'png';
				}
				?>
				<h2><?php _e('Crop Image') ?></h2>
                <p>Choose the part of the image you want to use for your ad.</p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds&action=change_image_process">
                    <input type="hidden" name="path" id="path" value="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_basename; ?>" />
                    <input type="hidden" name="aid" value="<?php echo $_POST['aid']; ?>" />
                    <input type="hidden" name="fname" id="fname" value="<?php echo $tmp_basename; ?>" />
                    <input type="hidden" name="image_type" id="image_type" value="<?php echo $tmp_image_type; ?>" />
                    <input type="hidden" name="x1" id="x1" />
                    <input type="hidden" name="y1" id="y1" />
                    <input type="hidden" name="x2" id="x2" />
                    <input type="hidden" name="y2" id="y2" />
                    <input type="hidden" name="width" id="width" />
                    <input type="hidden" name="height" id="height" />
                    <input type="hidden" name="attachment_id" id="attachment_id" value="11" />
                    <input type="hidden" name="oitar" id="oitar" value="1" />
                    <div id="crop_wrap">
                        <img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_basename; ?>" id="upload" width="<?php echo $tmp_image_width; ?>" height="<?php echo $tmp_image_height; ?>" />
                    </div>
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Crop &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
              	<?php
			} else {
				?>
				<h2><?php _e('Change Image') ?></h2>
				<p><?php _e('There was an error uploading the image, please try again!') ?></p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds&action=change_image_crop" enctype="multipart/form-data">
                    <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />
                    <fieldset class="options">
                        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                            <tr valign="top">
                                <th scope="row"><?php _e('Select New Image:') ?></th>
                                <td>
                                    <input name="change_image" id="change_image" size="20" type="file"><br />
                                    <?php _e('Upload an image (jpeg, gif, or png) for this ad.') ?><br />
                                    <?php _e('Note: GIF animations will not be preserved.') ?>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Upload &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
              	<?php
			}
            break;
		//---------------------------------------------------//
		case "change_image_process":
			if ( isset($_POST['Cancel']) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds';
				      </script>";
			} else {
				if ( $_POST['fname'] != '' ) {
					classifieds_create_image($_POST['aid'],$_POST['image_type'],$_POST['fname'],$_POST['x1'],$_POST['y1'],$_POST['x2'],$_POST['y2']);
				} else {
					classifieds_create_default_images($_POST['aid']);
				}
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Image Changed!')) . "';
                      </script>";
			}
            break;
		//---------------------------------------------------//
		case "edit_ad":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds';
                      </script>";
            }
            if ( isset( $_POST['aid'] ) )
                $tmp_ad_ID = $_POST['aid'];
            else
                $tmp_ad_ID = $_GET['aid'];

            $tmp_ad_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'" );

            if ( $tmp_ad_count > 0 ) {
                $tmp_ad_title              = $wpdb->get_var("SELECT ad_title              FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_description        = $wpdb->get_var("SELECT ad_description        FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_price              = $wpdb->get_var("SELECT ad_price              FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_currency           = $wpdb->get_var("SELECT ad_currency           FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_primary_category   = $wpdb->get_var("SELECT ad_primary_category   FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_secondary_category = $wpdb->get_var("SELECT ad_secondary_category FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_first_name         = $wpdb->get_var("SELECT ad_first_name         FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_last_name          = $wpdb->get_var("SELECT ad_last_name          FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_email_address      = $wpdb->get_var("SELECT ad_email_address      FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                $tmp_ad_phone_number       = $wpdb->get_var("SELECT ad_phone_number       FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_ad_ID . "'");
                ?>
                <h2><?php _e('Edit Ad Information') ?></h2>

                <?php
                classifieds_ad_html_template( $tmp_ad_ID, $tmp_ad_title, $tmp_ad_description, $tmp_ad_price, $tmp_ad_currency,
                                              $tmp_ad_primary_category ,$tmp_ad_secondary_category, $tmp_ad_first_name,
                                              $tmp_ad_last_name, $tmp_ad_email_address, $tmp_ad_phone_number );

            } else {
				?>
				<h2><?php _e('Edit Ad') ?></h2>
				<p><?php _e('Invalid Ad ID. Please try again.') ?></p>
				<form name="form1" method="POST" action="admin.php?page=classifieds&action=edit_ad">
                    <table class="optiontable">
                        <tr valign="top">
                            <th scope="row"><?php _e('Ad ID:') ?></th>
                            <td>
                                <input type="text" name="aid" value=""  />
                                <br />
                                <?php _e('Enter the ID of the ad you wish to modify' ); ?>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
				</form>
				<?php
			}
            break;
		//---------------------------------------------------//
		case "edit_ad_process":
			if ( isset($_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds';
                      </script>";
			} else if ( $_POST['ad_title'] == '' || $_POST['ad_description'] == '' || $_POST['ad_price'] == '' || $_POST['ad_first_name'] == '' || $_POST['ad_email_address'] == '' ) {
				?>
                <h2><?php _e('Edit Ad Information') ?></h2>
				<p><?php _e('Please fill in all required fields!') ?></p>

                <?php
                classifieds_ad_html_template( $_POST['aid'], $_POST['ad_title'], $_POST['ad_description'], $_POST['ad_price'], $_POST['ad_currency'], $_POST['ad_primary_category'], $_POST['ad_secondary_category'], $_POST['ad_first_name'], $_POST['ad_last_name'], $_POST['ad_email_address'], $_POST['ad_phone_number'] );

			} else {
				classifieds_update_ad( $_POST['aid'], $_POST['ad_title'], $_POST['ad_description'], $_POST['ad_primary_category'], $_POST['ad_secondary_category'], $_POST['ad_first_name'], $_POST['ad_last_name'], $_POST['ad_email_address'], $_POST['ad_phone_number'], $_POST['ad_currency'], $_POST['ad_price'] );
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Information Updated!')) . "';
                      </script>";
			}
            break;
		//---------------------------------------------------//
		case "place_ad":
            if ( get_site_option('classifieds_credits_enabled') ) {
                ?>
                <h2><?php _e('Select Period') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds&action=place_ad_process">
                <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />
                <fieldset class="options">
                    <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                    <tr valign="top">
                    <th scope="row"><?php _e('Number of weeks:') ?></th>
                    <td><select name="number_of_weeks">
                    <?php
                        $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                        $tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
                        $tmp_currency = get_site_option( "classifieds_currency" );
                        $tmp_counter = 0;
                        for ( $counter = 1; $counter <= 12; $counter += 1) {
                            $tmp_counter = $tmp_counter + 1;
                            $tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_counter) * $tmp_cost_per_credit;
                            $tmp_credits = ($tmp_classifieds_credits_per_week * $tmp_counter);
                            if ($tmp_counter == 1){
                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Week - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                            } else {
                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Weeks - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                            }
                        }
                    ?>
                    </select>
                    <br />
                    <?php _e('How many weeks would you like your ad to be displayed?') ?></td>
                    </tr>
                    </table>
                </fieldset>
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
                    <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
                </form>
                <?php
            } else {
                // credits are not enabled
                ?>
                <h2><?php _e('Select Period') ?></h2>
                <form name="confirm" method="POST" action="admin.php?page=classifieds&action=place_ad_confirm_process">
                    <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />
                    <fieldset class="options">
                        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                            <tr valign="top">
                                <th scope="row"><?php _e('Number of weeks:') ?></th>
                                <td>
                                    <select name="number_of_weeks">
                                    <?php
                                        for ( $i = 1; $i <= 12; $i ++ ) {
                                            if ( $i == 1 )
                                                echo '<option value="' . $i . '">' . $i . ' Week</option>' . "\n";
                                            else
                                                echo '<option value="' . $i . '">' . $i . ' Weeks</option>' . "\n";
                                        }
                                    ?>
                                    </select><br />
                                    <?php _e('How many weeks would you like your ad to be displayed?') ?>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
                <?php
            }
            break;
		//---------------------------------------------------//
		case "place_ad_process":
			if ( isset($_POST['Cancel']) ) {
				echo "
				<script type='text/javascript'>
                    window.location='admin.php?page=classifieds';
				</script>
				";
			} else {
				$tmp_user_credits = classifieds_get_user_credits( $current_user->ID );
				$tmp_number_of_weeks = $_POST['number_of_weeks'];
				$tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
				$tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
				$tmp_needed_credits = ( $tmp_classifieds_credits_per_week * $tmp_number_of_weeks );
				$tmp_credit_check = $tmp_user_credits - $tmp_needed_credits;

				if ( $tmp_user_credits == $tmp_needed_credits || $tmp_credit_check > 0 ) {
					//user has enough credits
					$tmp_currency = get_site_option( "classifieds_currency" );
					$tmp_needed_credits = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks);
					$tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks) * $tmp_cost_per_credit;
					?>
					<h2><?php _e('Confirm') ?></h2>
					<p><?php _e('You are about to place this ad for ') ?><?php echo $tmp_needed_credits . ' Credits ( ' . $tmp_cost . ' ' . $tmp_currency . ' ) . '; ?></p>
					<form name="confirm" method="POST" action="admin.php?page=classifieds&action=place_ad_confirm_process">
                        <input type="hidden" name="aid" value="<?php echo $_POST['aid']; ?>" />
                        <input type="hidden" name="number_of_weeks" value="<?php echo $_POST['number_of_weeks']; ?>" />

                        <p class="submit">
                            <input type="submit" name="Submit" value="<?php _e('Place Ad &raquo;') ?>" />
                            <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                        </p>
					</form>
					<?php
				} else {
					//user doesn't have enough credits
					?>
					<h2><?php _e('Error: Not Enough Credits') ?></h2>
                    <?php _e('You currently do not have enough credits to place this ad for ') ?><?php echo $tmp_number_of_weeks; ?><?php _e(' weeks. Click <a href="admin.php?page=classifieds_credits_management">here</a> to purchase more credits or use the form below to select a different time period for your ad. You have ') ?><?php echo $tmp_user_credits; ?><?php _e(' credit(s).') ?>
					<form name="form1" method="POST" action="admin.php?page=classifieds&action=place_ad_process">
					<input type="hidden" name="aid" value="<?php echo $_POST['aid']; ?>" />
					<fieldset class="options">
						<table width="100%" cellspacing="2" cellpadding="5" class="editform">
                            <tr valign="top">
                                <th scope="row"><?php _e('Number of weeks:') ?></th>
                                <td>
                                    <select name="number_of_weeks">
                                    <?php
                                        $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                                        $tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
                                        $tmp_currency = get_site_option( "classifieds_currency" );
                                        $tmp_counter = 0;
                                        for ( $counter = 1; $counter <= 12; $counter += 1) {
                                            $tmp_counter = $tmp_counter + 1;
                                            $tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_counter) * $tmp_cost_per_credit;
                                            $tmp_credits = ($tmp_classifieds_credits_per_week * $tmp_counter);
                                            if ($tmp_counter == 1){
                                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Week - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                                            } else {
                                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Weeks - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                                            }
                                        }
                                    ?>
                                    </select>
                            <br />
                            <?php _e( 'How many weeks would you like your ad to be displayed?' ) ?></td>
                            </tr>
						</table>
					</fieldset>
					<p class="submit"> 
						<input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
						<input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
					</p> 
					</form>
					<?php
				}
			}
            break;
		//---------------------------------------------------//
		case "place_ad_confirm_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds';
                      </script>";
			} else {
                if ( get_site_option('classifieds_credits_enabled') ) {
                    $tmp_user_credits = classifieds_get_user_credits( $current_user->ID );
                    $tmp_number_of_weeks = $_POST['number_of_weeks'];
                    $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                    $tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
                    $tmp_currency = get_site_option( "classifieds_currency" );
                    $tmp_needed_credits = ( $tmp_classifieds_credits_per_week * $tmp_number_of_weeks );
                    $tmp_cost = ( $tmp_classifieds_credits_per_week * $tmp_number_of_weeks ) * $tmp_cost_per_credit;
                    $tmp_new_user_credits = $tmp_user_credits - $tmp_needed_credits;
                    //deduct credits
                    classifieds_update_user_credits( $current_user->ID, $tmp_new_user_credits );
                    //change ad status
                    classifieds_update_ad_status( $_POST['aid'],'active' );
                    //change ad expire
                    classifieds_update_ad_expire( $_POST['aid'],$tmp_number_of_weeks );
                    //redirect!!!
                    echo "<script type='text/javascript'>
                              window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Placed!')) . "';
                          </script>";
                } else {
                    $tmp_number_of_weeks = $_POST['number_of_weeks'];
                    //change ad status
                    classifieds_update_ad_status( $_POST['aid'],'active' );
                    //change ad expire
                    classifieds_update_ad_expire( $_POST['aid'], $tmp_number_of_weeks );
                    //redirect!!!
                    echo "<script type='text/javascript'>
                              window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Placed!')) . "';
                          </script>";
                }
			}
            break;
		//---------------------------------------------------//
		case "end_ad":
			?>
			<h2><?php _e('End Ad Early') ?></h2>
			<p><?php _e('Are you sure you want to end this ad early?') ?></p>
			<form name="confirm" method="POST" action="admin.php?page=classifieds&action=end_ad_process">
                <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />

                <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('End Ad Early &raquo;') ?>" />
                <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
			</form>
			<?php
            break;
		//---------------------------------------------------//
		case "end_ad_process":
			if ( isset($_POST['Cancel']) ) {
				echo "
				<script type='text/javascript'>
				window.location='admin.php?page=classifieds';
				</script>
				";
			} else {
				//change ad status
				classifieds_update_ad_status($_POST['aid'],'ended');
				//change ad expire
				classifieds_update_ad_expire_now($_POST['aid']);
				//redirect!!!
				echo "
				<script type='text/javascript'>
                    window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Ended Early!')) . "';
				</script>
				";
			}
            break;
		//---------------------------------------------------//
		case "renew_ad":
            if ( get_site_option('classifieds_credits_enabled') ) {
                ?>
                <h2><?php _e('Select Period') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds&action=renew_ad_process">
                <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />
                <fieldset class="options">
                    <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                    <tr valign="top">
                    <th scope="row"><?php _e('Number of weeks:') ?></th>
                    <td><select name="number_of_weeks">
                    <?php
                        $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                        $tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
                        $tmp_currency = get_site_option( "classifieds_currency" );
                        $tmp_counter = 0;
                        for ( $counter = 1; $counter <= 12; $counter += 1) {
                            $tmp_counter = $tmp_counter + 1;
                            $tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_counter) * $tmp_cost_per_credit;
                            $tmp_credits = ($tmp_classifieds_credits_per_week * $tmp_counter);
                            if ($tmp_counter == 1){
                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Week - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                            } else {
                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Weeks - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                            }
                        }
                    ?>
                    </select>
                    <br />
                            <?php _e('How many weeks would you like to renew your ad for?') ?></td>
                    </tr>
                    </table>
                </fieldset>
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
                    <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
                </form>
                <?php
            } else {
                ?>
                <h2><?php _e('Select Period') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds&action=renew_ad_confirm_process">
                <input type="hidden" name="aid" value="<?php echo $_GET['aid']; ?>" />
                <fieldset class="options">
                    <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                    <tr valign="top">
                    <th scope="row"><?php _e('Number of weeks:') ?></th>
                    <td><select name="number_of_weeks">
                    <?php
                        for ( $i = 1; $i <= 12; $i ++ ) {
                            if ( $i == 1 )
                                echo '<option value="' . $i . '">' . $i . ' Week</option>' . "\n";
                            else
                                echo '<option value="' . $i . '">' . $i . ' Weeks</option>' . "\n";
                        }
                    ?>
                    </select>
                    <br />
                            <?php _e('How many weeks would you like to renew your ad for?') ?></td>
                    </tr>
                    </table>
                </fieldset>
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
                    <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
                </form>
                <?php
            }
            break;
		//---------------------------------------------------//
		case "renew_ad_process":
			if ( isset($_POST['Cancel']) ) {
				echo "
				<script type='text/javascript'>
                    window.location='admin.php?page=classifieds';
				</script>
				";
			} else {
				$tmp_user_credits = classifieds_get_user_credits($current_user->ID);
				$tmp_number_of_weeks = $_POST['number_of_weeks'];
				$tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
				$tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
				$tmp_needed_credits = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks);
				$tmp_credit_check = $tmp_user_credits - $tmp_needed_credits;
                
				if ( $tmp_user_credits == $tmp_needed_credits || $tmp_credit_check > 0 ) {
					//user has enough credits
					$tmp_currency = get_site_option( "classifieds_currency" );
					$tmp_needed_credits = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks);
					$tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks) * $tmp_cost_per_credit;
					?>
					<h2><?php _e('Confirm') ?></h2>
					<p><?php _e('You are about to renew this ad for ') ?><?php echo $tmp_needed_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ').'; ?></p>
					<form name="confirm" method="POST" action="admin.php?page=classifieds&action=renew_ad_confirm_process">
                        <input type="hidden" name="aid" value="<?php echo $_POST['aid']; ?>" />
                        <input type="hidden" name="number_of_weeks" value="<?php echo $_POST['number_of_weeks']; ?>" />
                        <p class="submit">
                            <input type="submit" name="Submit" value="<?php _e('Renew Ad &raquo;') ?>" />
                            <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                        </p>
					</form>
					<?php
				} else {
					//user doesn't have enough credits
					?>
					<h2><?php _e('Error: Not Enough Credits') ?></h2>
                    <?php _e('You currently do not have enough credits to place this ad for ') ?><?php echo $tmp_number_of_weeks; ?><?php _e(' weeks. Click <a href="admin.php?page=classifieds_credits_management">here</a> to purchase more credits or use the form below to select a different time period for your ad. You have ') ?><?php echo $tmp_user_credits; ?><?php _e(' credit(s).') ?>
					<form name="form1" method="POST" action="admin.php?page=classifieds&action=renew_ad_process">
                        <input type="hidden" name="aid" value="<?php echo $_POST['aid']; ?>" />
                        <fieldset class="options">
                            <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                                <tr valign="top">
                                    <th scope="row"><?php _e('Number of weeks:') ?></th>
                                    <td>
                                        <select name="number_of_weeks">
                                        <?php
                                            $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                                            $tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
                                            $tmp_currency = get_site_option( "classifieds_currency" );
                                            $tmp_counter = 0;
                                            for ( $counter = 1; $counter <= 12; $counter += 1) {
                                                $tmp_counter = $tmp_counter + 1;
                                                $tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_counter) * $tmp_cost_per_credit;
                                                $tmp_credits = ($tmp_classifieds_credits_per_week * $tmp_counter);
                                                if ($tmp_counter == 1){
                                                    echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Week - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                                                } else {
                                                    echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . ' Weeks - ' . $tmp_credits . ' Credits (' . $tmp_cost . ' ' . $tmp_currency . ')</option>' . "\n";
                                                }
                                            }
                                        ?>
                                        </select><br />
                                        <?php _e('How many weeks would you like to renew your ad for?') ?>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                        <p class="submit">
                            <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
                            <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                        </p>
					</form>
					<?php
				}
			}
            break;
		//---------------------------------------------------//
		case "renew_ad_confirm_process":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds';
				      </script>";
			} else {
                if ( get_site_option('classifieds_credits_enabled') ) {
                    $tmp_user_credits = classifieds_get_user_credits( $current_user->ID );
                    $tmp_number_of_weeks = $_POST['number_of_weeks'];
                    $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                    $tmp_cost_per_credit = get_site_option( "classifieds_cost_per_credit" );
                    $tmp_currency = get_site_option( "classifieds_currency" );
                    $tmp_needed_credits = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks);
                    $tmp_cost = ($tmp_classifieds_credits_per_week * $tmp_number_of_weeks) * $tmp_cost_per_credit;
                    $tmp_new_user_credits = $tmp_user_credits - $tmp_needed_credits;
                    //deduct credits
                    classifieds_update_user_credits( $current_user->ID, $tmp_new_user_credits );
                    //change ad status
                    classifieds_update_ad_status( $_POST['aid'], 'active' );
                    //change ad expire
                    classifieds_update_ad_expire( $_POST['aid'], $tmp_number_of_weeks );
                    //redirect!!!
                    echo "<script type='text/javascript'>
                              window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Renewed!')) . "';
                          </script>";
                } else {
                    $tmp_number_of_weeks = $_POST['number_of_weeks'];
                    //change ad status
                    classifieds_update_ad_status($_POST['aid'],'active');
                    //change ad expire
                    classifieds_update_ad_expire($_POST['aid'],$tmp_number_of_weeks);
                    //redirect!!!
                    echo "<script type='text/javascript'>
                              window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Renewed!')) . "';
                          </script>";
                }
			}
            break;
	}
	echo '</div>';
}

function classifieds_page_new_output() {
	global $wpdb, $currencies;
	
    $tmp_upload_dir_exists = true;

    if ( !is_dir(CLASSIFIEDS_UPLOAD_PATH) )
        $tmp_upload_dir_exists = false;
    
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap classifieds">';

	switch( $_GET[ 'action' ] ) {
		default: ?>
			<h2><?php _e('Step One: Ad Information') ?></h2>
            <?php classifieds_place_ad_step_one_html_template(); 
		break;
		//---------------------------------------------------//
		case "step2":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds_new';
				      </script>";
			}
			if ( $_POST['ad_title'] == '' ) { ?>
				<h2><?php _e('Step One: Ad Information') ?></h2>
				<p><?php _e('You must provide a title!') ?></p>
                <?php classifieds_place_ad_step_one_html_template();
			} else if ( $_POST['ad_description'] == '' ) { ?>
				<h2><?php _e('Step One: Ad Information') ?></h2>
				<p><?php _e('You must provide a description!') ?></p>
                <?php classifieds_place_ad_step_one_html_template();
			} else if ( $_POST['ad_price'] == '' ) { ?>
				<h2><?php _e('Step One: Ad Information') ?></h2>
				<p><?php _e('You must provide a price!') ?></p>
                <?php classifieds_place_ad_step_one_html_template();
            } else if ( $_POST['ad_primary_category'] == '' ) { ?>
				<h2><?php _e('Step One: Ad Information') ?></h2>
				<p><?php _e('You must provide a primary category! Either select one from the list or ( <a href="admin.php?page=classifieds_categories&action=new_category">Add New Category</a> )') ?></p>
                <?php classifieds_place_ad_step_one_html_template();
			} else {
				$tmp_current_user = wp_get_current_user();
				?>
				<h2><?php _e('Step Two: Contact Information') ?></h2>
                <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step3">
                <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
                <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
                <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
                <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
                <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
                <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />
                <fieldset class="options">
                    <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                    <tr valign="top">
                    <th scope="row"><?php _e('First Name:') ?></th>
                    <td><input name="ad_first_name" type="text" id="ad_first_name" style="width: 95%" value="<?php echo $tmp_current_user->first_name; ?>" size="45" />
                    <br />
                    <?php _e('Required <span style="color: red">*</span>') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Last Name:') ?></th>
                    <td><input name="ad_last_name" type="text" id="ad_last_name" style="width: 95%" value="<?php echo $tmp_current_user->last_name; ?>" size="45" />
                    <br />
                    <?php _e('Optional') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Email Address:') ?></th>
                    <td><input name="ad_email_address" type="text" id="ad_email_address" style="width: 95%" value="<?php echo $tmp_current_user->user_email; ?>" size="45" />
                    <br />
                    <?php _e('Required <span style="color: red">*</span> - will not be displayed') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Phone Number:') ?></th>
                    <td><input name="ad_phone_number" type="text" id="ad_phone_number" style="width: 95%" value="<?php echo $_POST['ad_phone_number']; ?>" size="45" />
                    <br />
                    <?php _e('Optional') ?></td>
                    </tr>
                    </table>
                </fieldset>
                <p class="submit"> 
                <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" /> 
                <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" /> 
                </p>
                </form>
              	<?php
			}
            break;
		//---------------------------------------------------//
		case "step3":
			if ( isset($_POST['Cancel']) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds_new';
                      </script>";
			}
			if ($_POST['ad_first_name'] == ''){
				?>
				<h2><?php _e('Step Two: Contact Information') ?></h2>
				<p><?php _e('You must provide your first name!') ?></p>
                <?php classifieds_place_ad_step_two_html_template();
			} else if ($_POST['ad_email_address'] == ''){
				?>
				<h2><?php _e('Step Two: Contact Information') ?></h2>
				<p><?php _e('You must provide an email address!') ?></p>
              	<?php classifieds_place_ad_step_two_html_template();
			} else {
				?>
				<h2><?php _e('Step Three: Upload Image') ?></h2>
                
                <?php if ( !$tmp_upload_dir_exists ): ?>
				<p><?php _e('It appears that the folder "<span style="color: red; font-weight: bold;">wp-content/classifieds-images/</span>" is missing.') ?></p>
   				<p><?php _e('You will have to manualy create the directory "<span style="color: red; font-weight: bold;">classifieds-images</span>" inside the wp-content folder and set the folder permission to <span style="color: red; font-weight: bold;">777</span> in order to upload image and finish the ad publishing process.') ?></p>
                <?php endif; ?>

                <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step4" enctype="multipart/form-data">
                    <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
                    <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
                    <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
                    <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
                    <input type="hidden" name="ad_first_name" value="<?php echo $_POST['ad_first_name']; ?>" />
                    <input type="hidden" name="ad_last_name" value="<?php echo $_POST['ad_last_name']; ?>" />
                    <input type="hidden" name="ad_email_address" value="<?php echo $_POST['ad_email_address']; ?>" />
                    <input type="hidden" name="ad_phone_number" value="<?php echo $_POST['ad_phone_number']; ?>" />
                    <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
                    <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />
                    <?php if ( $tmp_upload_dir_exists ): ?>
                    <fieldset class="options">
                        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                            <tr valign="top">
                                <th scope="row"><?php _e('Select Image:') ?></th>
                                <td>
                                    <input name="ad_image" id="ad_image" size="20" type="file"><br />
                                    <?php _e('Upload an image (jpeg, gif, or png) for this ad.') ?><br />
                                    <?php _e('Note: GIF animations will not be preserved.') ?>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <?php endif; ?>
                    <p class="submit">
                        <?php if ( $tmp_upload_dir_exists ): ?>
                        <input type="submit" name="Submit" value="<?php _e('Upload &raquo;') ?>" />
                        <input type="submit" name="Skip" value="<?php _e('Skip &raquo;') ?>" />
                        <?php endif; ?>
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
              	<?php
			}
            break;
		//---------------------------------------------------//
		case "step4":

			$tmp_basename = basename($_FILES['ad_image']['name']);
			$tmp_basename = str_replace(',','',$tmp_basename);
			$tmp_basename = str_replace(' ','',$tmp_basename);
			$tmp_basename = str_replace('&','',$tmp_basename);

			if ( isset( $_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
				          window.location='admin.php?page=classifieds';
				      </script>";
			}
			if ( isset( $_POST['Skip'] ) ) {
				?>
				<h2><?php _e('Finished') ?></h2>
				<p><?php _e('What would you like to do now?') ?></p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step6">
                    <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
                    <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
                    <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
                    <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
                    <input type="hidden" name="ad_first_name" value="<?php echo $_POST['ad_first_name']; ?>" />
                    <input type="hidden" name="ad_last_name" value="<?php echo $_POST['ad_last_name']; ?>" />
                    <input type="hidden" name="ad_email_address" value="<?php echo $_POST['ad_email_address']; ?>" />
                    <input type="hidden" name="ad_phone_number" value="<?php echo $_POST['ad_phone_number']; ?>" />
                    <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
                    <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Place Ad Now &raquo;') ?>" />
                        <input type="submit" name="Save" value="<?php _e('Save Ad &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
              	<?php
			} else if ( move_uploaded_file( $_FILES['ad_image']['tmp_name'], CLASSIFIEDS_UPLOAD_PATH . $tmp_basename ) ) {

				list( $tmp_image_width, $tmp_image_height, $tmp_image_type, $tmp_image_attr ) = getimagesize( get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_basename );
				if ( $_FILES['ad_image']['type'] == "image/gif" ) {
					$tmp_image_type = 'gif';
				}
				if ( $_FILES['ad_image']['type'] == "image/jpeg" ) {
					$tmp_image_type = 'jpeg';
				}
				if ( $_FILES['ad_image']['type'] == "image/pjpeg" ) {
					$tmp_image_type = 'jpeg';
				}
				if ( $_FILES['ad_image']['type'] == "image/jpg" ) {
					$tmp_image_type = 'jpeg';
				}
				if ( $_FILES['ad_image']['type'] == "image/png" ) {
					$tmp_image_type = 'png';
				}
				if ( $_FILES['ad_image']['type'] == "image/x-png" ) {
					$tmp_image_type = 'png';
				}
				?>
				<h2><?php _e('Step Four: Crop Image') ?></h2>
                <p>Choose the part of the image you want to use for your ad.</p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step5">
                    <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
                    <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
                    <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
                    <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
                    <input type="hidden" name="ad_first_name" value="<?php echo $_POST['ad_first_name']; ?>" />
                    <input type="hidden" name="ad_last_name" value="<?php echo $_POST['ad_last_name']; ?>" />
                    <input type="hidden" name="ad_email_address" value="<?php echo $_POST['ad_email_address']; ?>" />
                    <input type="hidden" name="ad_phone_number" value="<?php echo $_POST['ad_phone_number']; ?>" />
                    <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
                    <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />
                    <input type="hidden" name="path" id="path" value="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_basename; ?>" />
                    <input type="hidden" name="fname" id="fname" value="<?php echo $tmp_basename; ?>" />
                    <input type="hidden" name="image_type" id="image_type" value="<?php echo $tmp_image_type; ?>" />
                    <input type="hidden" name="x1" id="x1" />
                    <input type="hidden" name="y1" id="y1" />
                    <input type="hidden" name="x2" id="x2" />
                    <input type="hidden" name="y2" id="y2" />
                    <input type="hidden" name="width" id="width" />
                    <input type="hidden" name="height" id="height" />
                    <input type="hidden" name="attachment_id" id="attachment_id" value="11" />
                    <input type="hidden" name="oitar" id="oitar" value="1" />
                    <div id="crop_wrap">
                        <img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_basename; ?>" id="upload" width="<?php echo $tmp_image_width; ?>" height="<?php echo $tmp_image_height; ?>" />
                    </div>
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Crop &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
              	<?php
			} else {
				?>
				<h2><?php _e('Step Three: Upload Image') ?></h2>
				<p><?php _e('There was an error uploading the image, please try again!') ?></p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step4" enctype="multipart/form-data">
                    <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
                    <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
                    <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
                    <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
                    <input type="hidden" name="ad_first_name" value="<?php echo $_POST['ad_first_name']; ?>" />
                    <input type="hidden" name="ad_last_name" value="<?php echo $_POST['ad_last_name']; ?>" />
                    <input type="hidden" name="ad_email_address" value="<?php echo $_POST['ad_email_address']; ?>" />
                    <input type="hidden" name="ad_phone_number" value="<?php echo $_POST['ad_phone_number']; ?>" />
                    <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
                    <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />

                    <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
                    <fieldset class="options">
                        <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                            <tr valign="top">
                                <th scope="row"><?php _e('Select Image:') ?></th>
                                <td>
                                    <input name="ad_image" id="ad_image" size="20" type="file"><br />
                                    <?php _e('Upload an image (jpeg, gif, or png) for this ad.') ?><br />
                                    <?php _e('Note: GIF animations will not be preserved.') ?>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Upload &raquo;') ?>" />
                        <input type="submit" name="Skip" value="<?php _e('Skip &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
              	<?php
			}
            break;
		//---------------------------------------------------//
		case "step5":
			if ( isset( $_POST['Cancel'] ) ) {
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds_new';
				      </script>";
			}
			?>
			<h2><?php _e('Finished') ?></h2>
			<p><?php _e('What would you like to do now?') ?></p>
			<form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step6">
                <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
                <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
                <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
                <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
                <input type="hidden" name="ad_first_name" value="<?php echo $_POST['ad_first_name']; ?>" />
                <input type="hidden" name="ad_last_name" value="<?php echo $_POST['ad_last_name']; ?>" />
                <input type="hidden" name="ad_email_address" value="<?php echo $_POST['ad_email_address']; ?>" />
                <input type="hidden" name="ad_phone_number" value="<?php echo $_POST['ad_phone_number']; ?>" />
                <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
                <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />
                <input type="hidden" name="path" id="path" value="<?php echo $_POST['path']; ?>" />
                <input type="hidden" name="fname" id="fname" value="<?php echo $_POST['fname']; ?>" />
                <input type="hidden" name="image_type" id="image_type" value="<?php echo $_POST['image_type']; ?>" />
                <input type="hidden" name="x1" id="x1" value="<?php echo $_POST['x1']; ?>" />
                <input type="hidden" name="y1" id="y1" value="<?php echo $_POST['y1']; ?>" />
                <input type="hidden" name="x2" id="x2" value="<?php echo $_POST['x2']; ?>" />
                <input type="hidden" name="y2" id="y2" value="<?php echo $_POST['y2']; ?>" />
                <input type="hidden" name="width" id="width" value="<?php echo $_POST['width']; ?>" />
                <input type="hidden" name="height" id="height" value="<?php echo $_POST['height']; ?>" />
                <input type="hidden" name="attachment_id" id="attachment_id" value="<?php echo $_POST['attachment_id']; ?>" />
                <input type="hidden" name="oitar" id="oitar" value="<?php echo $_POST['oitar']; ?>" />
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Place Ad Now &raquo;') ?>" />
                    <input type="submit" name="Save" value="<?php _e('Save Ad &raquo;') ?>" />
                    <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
			</form>
			<?php
            break;
		//---------------------------------------------------//
		case "step6":
			if ( isset($_POST['Cancel']) ) {
				echo "<script type='text/javascript'>
                         window.location='admin.php?page=classifieds_new';
				      </script>";
			} else if ( isset( $_POST['Save'] ) ) {
				//insert ad
				$tmp_ad_id = classifieds_insert_ad( $_POST['ad_title'], $_POST['ad_description'], $_POST['ad_primary_category'], $_POST['ad_secondary_category'], $_POST['ad_first_name'], $_POST['ad_last_name'], $_POST['ad_email_address'], $_POST['ad_phone_number'], $_POST['ad_currency'], $_POST['ad_price'] );
				classifieds_update_ad_expire_now($tmp_ad_id);
				if ( $_POST['fname'] != '' ){
					classifieds_create_image( $tmp_ad_id, $_POST['image_type'], $_POST['fname'], $_POST['x1'], $_POST['y1'], $_POST['x2'], $_POST['y2'] );
				} else {
					classifieds_create_default_images( $tmp_ad_id );
				}
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Saved!')) . "';
				      </script>";
			} else {
				//place ad now - save and redirect to place ad page, insert ad
				$tmp_ad_id = classifieds_insert_ad( $_POST['ad_title'], $_POST['ad_description'], $_POST['ad_primary_category'], $_POST['ad_secondary_category'], $_POST['ad_first_name'], $_POST['ad_last_name'], $_POST['ad_email_address'], $_POST['ad_phone_number'], $_POST['ad_currency'], $_POST['ad_price'] );
				classifieds_update_ad_expire_now( $tmp_ad_id );
				if ( $_POST['fname'] != '' ){
					classifieds_create_image( $tmp_ad_id,$_POST['image_type'], $_POST['fname'], $_POST['x1'], $_POST['y1'], $_POST['x2'], $_POST['y2'] );
				} else {
					classifieds_create_default_images($tmp_ad_id);
				}
				echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds&action=place_ad&aid=" . $tmp_ad_id . "';
				      </script>";
			}
            break;
	}
	echo '</div>';
}

function classifieds_place_ad_step_one_html_template() {
    global $wpdb, $currencies;
    ?>
    <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step2">
        <fieldset class="options">
            <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                <tr valign="top" align="left">
                    <th scope="row"><?php _e('Title:') ?></th>
                    <td>
                        <input name="ad_title" type="text" id="ad_title" style="width: 95%" value="<?php echo $_POST['ad_title']; ?>" size="45" /><br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top" align="left">
                    <th scope="row"><?php _e('Description:') ?></th>
                    <td>
                        <textarea name="ad_description" id="ad_description" style="width: 95%" rows="5"><?php echo $_POST['ad_description']; ?></textarea><br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top" align="left">
                    <th scope="row"><?php _e('Price:') ?></th>
                    <td>
                        <input type="text" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" /><br />
                        <?php _e('Required <span style="color: red">*</span> - Format: 00.00 - Ex: 1.25') ?></td>
                </tr>
                <tr valign="top" align="left">
                    <th scope="row"><?php _e('Currency:') ?></th>
                    <td>
                        <select name="ad_currency">
                        <?php
                        $tmp_ad_currency = $_POST['ad_currency'];
                        $sel_currency = empty( $tmp_ad_currency ) ? 'USD' : $tmp_ad_currency;

                        foreach ( $currencies as $k => $v ) {
                            echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . wp_specialchars($v, true) . '</option>' . "\n";
                        }
                        ?>
                        </select><br />
                    </td>
                </tr>
                <tr valign="top" align="left">
                    <th scope="row"><?php _e('Primary Category:') ?></th>
                    <td>
                        <select name="ad_primary_category">
                        <?php
                        $query = "SELECT category_ID, category_name, category_description FROM " . $wpdb->base_prefix . "classifieds_categories ORDER BY category_name";
                        $tmp_classifieds_categories = $wpdb->get_results( $query, ARRAY_A );

                        if ( count( $tmp_classifieds_categories ) > 0 ) {
                            foreach ( $tmp_classifieds_categories as $tmp_classifieds_category ) {
                                echo '<option value="' . $tmp_classifieds_category['category_ID'] . '"' . ($tmp_classifieds_category['category_ID'] == $_POST['ad_primary_category'] ? ' selected' : '') . '>' . $tmp_classifieds_category['category_name'] . '</option>' . "\n";
                            }
                        }
                        ?>
                        </select><br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top" align="left">
                    <th scope="row"><?php _e('Secondary Category:') ?></th>
                    <td>
                        <select name="ad_secondary_category">
                        <?php
                        echo '<option value="0"' . ( '0' == $_POST['ad_secondary_category'] ? ' selected' : '' ) . '>None</option>' . "\n";
                        $query = "SELECT category_ID, category_name, category_description FROM " . $wpdb->base_prefix . "classifieds_categories ORDER BY category_name";
                        $tmp_classifieds_categories = $wpdb->get_results( $query, ARRAY_A );

                        if ( count( $tmp_classifieds_categories ) > 0 ) {
                            foreach ( $tmp_classifieds_categories as $tmp_classifieds_category ) {
                                echo '<option value="' . $tmp_classifieds_category['category_ID'] . '"' . ($tmp_classifieds_category['category_ID'] == $_POST['ad_secondary_category'] ? ' selected' : '') . '>' . $tmp_classifieds_category['category_name'] . '</option>' . "\n";
                            }
                        }
                        ?>
                        </select<br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
        </p>
    </form>
    <?php
}

function classifieds_place_ad_step_two_html_template() { ?>

    <form name="step_one" method="POST" action="admin.php?page=classifieds_new&action=step3">
        <input type="hidden" name="ad_title" value="<?php echo $_POST['ad_title']; ?>" />
        <input type="hidden" name="ad_description" value="<?php echo $_POST['ad_description']; ?>" />
        <input type="hidden" name="ad_primary_category" value="<?php echo $_POST['ad_primary_category']; ?>" />
        <input type="hidden" name="ad_secondary_category" value="<?php echo $_POST['ad_secondary_category']; ?>" />
        <input type="hidden" name="ad_price" value="<?php echo $_POST['ad_price']; ?>" />
        <input type="hidden" name="ad_currency" value="<?php echo $_POST['ad_currency']; ?>" />
        <fieldset class="options">
            <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                <tr valign="top">
                    <th scope="row"><?php _e('First Name:') ?></th>
                    <td>
                        <input name="ad_first_name" type="text" id="ad_first_name" style="width: 95%" value="<?php echo $_POST['ad_first_name']; ?>" size="45" /><br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Last Name:') ?></th>
                    <td>
                        <input name="ad_last_name" type="text" id="ad_last_name" style="width: 95%" value="<?php echo $_POST['ad_last_name']; ?>" size="45" /><br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Email Address:') ?></th>
                    <td>
                        <input name="ad_email_address" type="text" id="ad_email_address" style="width: 95%" value="<?php echo $_POST['ad_email_address']; ?>" size="45" /><br />
                        <?php _e('Required <span style="color: red">*</span> - will not be displayed') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Phone Number:') ?></th>
                    <td>
                        <input name="ad_phone_number" type="text" id="ad_phone_number" style="width: 95%" value="<?php echo $_POST['ad_phone_number']; ?>" size="45" /><br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
            <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
        </p>
    </form>
    <?php
}

function classifieds_ad_html_template( $tmp_ad_ID, $tmp_ad_title,  $tmp_ad_description, $tmp_ad_price,     $tmp_ad_currency,      $tmp_ad_primary_category,
                                       $tmp_ad_secondary_category, $tmp_ad_first_name,  $tmp_ad_last_name, $tmp_ad_email_address, $tmp_ad_phone_number ) {
    global $wpdb, $currencies; ?>

    <form name="step_one" method="post" action="admin.php?page=classifieds&action=edit_ad_process">
        <input type="hidden" name="aid" value="<?php echo $tmp_ad_ID; ?>" />
        <fieldset class="options">
            <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                <tr valign="top">
                    <th scope="row"><?php _e('Title:') ?></th>
                    <td>
                        <input name="ad_title" type="text" id="ad_title" style="width: 95%" value="<?php echo $tmp_ad_title; ?>" size="45" />
                        <br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Description:') ?></th>
                    <td>
                        <textarea name="ad_description" id="ad_description" style="width: 95%" rows="5"><?php echo $tmp_ad_description; ?></textarea>
                        <br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Price:') ?></th>
                    <td>
                        <input type="text" name="ad_price" value="<?php echo $tmp_ad_price; ?>" />
                        <br />
                        <?php _e('Required <span style="color: red">*</span> - Format: 00.00 - Ex: 1.25') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Currency:') ?></th>
                    <td>
                        <select name="ad_currency">
                        <?php
                        $tmp_ad_currency = $_POST['ad_currency'];
                        $sel_currency = empty( $tmp_ad_currency ) ? 'USD' : $tmp_ad_currency;

                        foreach ( $currencies as $k => $v ) {
                            echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . wp_specialchars($v, true) . '</option>' . "\n";
                        }
                        ?>
                        </select>
                        <br />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Primary Category:') ?></th>
                    <td>
                        <select name="ad_primary_category">
                        <?php
                        $query = "SELECT category_ID, category_name, category_description FROM " . $wpdb->base_prefix . "classifieds_categories ORDER BY category_name";
                        $tmp_classifieds_categories = $wpdb->get_results( $query, ARRAY_A );

                        if ( count( $tmp_classifieds_categories ) > 0 ) {
                            foreach ($tmp_classifieds_categories as $tmp_classifieds_category) {
                                echo '<option value="' . $tmp_classifieds_category['category_ID'] . '"' . ($tmp_classifieds_category['category_ID'] == $tmp_ad_primary_category ? ' selected' : '') . '>' . $tmp_classifieds_category['category_name'] . '</option>' . "\n";
                            }
                        }
                        ?>
                        </select>
                        <br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Secondary Category:') ?></th>
                    <td>
                        <select name="ad_secondary_category">
                        <?php
                        echo '<option value="0"' . ('0' == $_POST['ad_secondary_category'] ? ' selected' : '') . '>None</option>' . "\n";

                        $query = "SELECT category_ID, category_name, category_description FROM " . $wpdb->base_prefix . "classifieds_categories ORDER BY category_name";
                        $tmp_classifieds_categories = $wpdb->get_results( $query, ARRAY_A );

                        if ( count( $tmp_classifieds_categories ) > 0 ) {
                            foreach ($tmp_classifieds_categories as $tmp_classifieds_category) {
                                echo '<option value="' . $tmp_classifieds_category['category_ID'] . '"' . ( $tmp_classifieds_category['category_ID'] == $tmp_ad_secondary_category ? ' selected' : '' ) . '>' . $tmp_classifieds_category['category_name'] . '</option>' . "\n";
                            }
                        }
                        ?>
                        </select>
                        <br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('First Name:') ?></th>
                    <td>
                        <input name="ad_first_name" type="text" id="ad_first_name" style="width: 95%" value="<?php echo $tmp_ad_first_name; ?>" size="45" />
                        <br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Last Name:') ?></th>
                    <td>
                        <input name="ad_last_name" type="text" id="ad_last_name" style="width: 95%" value="<?php echo $tmp_ad_last_name; ?>" size="45" />
                        <br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Email Address:') ?></th>
                    <td>
                        <input name="ad_email_address" type="text" id="ad_email_address" style="width: 95%" value="<?php echo $tmp_ad_email_address; ?>" size="45" />
                        <br />
                        <?php _e('Required <span style="color: red">*</span> - will not be displayed') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Phone Number:') ?></th>
                    <td>
                        <input name="ad_phone_number" type="text" id="ad_phone_number" style="width: 95%" value="<?php echo $tmp_ad_phone_number; ?>" size="45" />
                        <br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" />
            <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
        </p>
    </form>
    <?php
}

function classifieds_categories_html_template() {
    ?>
    <form name="form1" method="POST" action="admin.php?page=classifieds_categories&action=new_category_process">
        <fieldset class="options">
            <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                <tr valign="top">
                    <th scope="row"><?php _e('Name:') ?></th>
                    <td>
                        <input name="category_name" type="text" id="package_name" style="width: 95%" value="<?php echo $_POST['category_name']; ?>" size="45" /><br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Description:') ?></th>
                    <td>
                        <textarea name="category_description" id="category_description" style="width: 95%" rows="5"><?php echo $_POST['category_description']; ?></textarea><br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save &raquo;') ?>" />
        </p>
    </form>
    <?php
}

function classifieds_categories_edit_html_template() {
    ?>
    <form name="form1" method="POST" action="admin.php?page=classifieds_categories&action=edit_category_process&cid=<?php echo $_GET['cid']; ?>">
        <fieldset class="options">
            <table width="100%" cellspacing="2" cellpadding="5" class="editform">
                <tr valign="top">
                    <th scope="row"><?php _e('Name:') ?></th>
                    <td>
                        <input name="category_name" type="text" id="package_name" style="width: 95%" value="<?php echo $tmp_cat_name; ?>" size="45" /><br />
                        <?php _e('Required <span style="color: red">*</span>') ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Description:') ?></th>
                    <td>
                        <textarea name="category_description" id="category_description" style="width: 95%" rows="5"><?php echo $tmp_cat_description; ?></textarea><br />
                        <?php _e('Optional') ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save &raquo;') ?>" />
        </p>
    </form>
    <?php
}

//------------------------------------------------------------------------//
//---Support Functions----------------------------------------------------//
//------------------------------------------------------------------------//

function classifieds_delete_file($file){
	chmod( $file, 0777 );
	if ( unlink( $file ) )
		return true;
	else
		return false;
}

function classifieds_get_user_credits( $tmp_user_id ) {
	global $wpdb;
	if ( function_exists('classifieds_user_credit_check') )
        classifieds_user_credit_check();
    
    $tmp_user_credits = $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $tmp_user_id . "'");
	return $tmp_user_credits;
}

function classifieds_update_user_credits( $tmp_user_id, $tmp_new_total ) {
	global $wpdb;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_credits SET credits = '" . $tmp_new_total . "' WHERE user_ID = '" . $tmp_user_id . "'");
}

function classifieds_roundup( $value, $dp ) {
    return ceil($value*pow(10, $dp))/pow(10, $dp);
}

function classifieds_get_user_login( $tmp_user_id ) {
	global $wpdb;
	$tmp_user_login = $wpdb->get_var("SELECT user_login FROM $wpdb->users WHERE ID = '" . $tmp_user_id . "'");
	return $tmp_user_login;
}
?>