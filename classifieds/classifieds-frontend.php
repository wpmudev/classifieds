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
 * Classifieds FRONTEND
 * Handles the overall frontend output of the plugin
 *
 * @package Classifieds
 * @subpackage Frontend
 * @since 1.1.0
 */

function classifieds_frontend_display_ad_information($tmp_current_ad,$tmp_base_url){
	global $wpdb;

	$tmp_ad_user_ID            = $wpdb->get_var("SELECT ad_user_ID            FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_description        = $wpdb->get_var("SELECT ad_description        FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_first_name         = $wpdb->get_var("SELECT ad_first_name         FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_last_name          = $wpdb->get_var("SELECT ad_last_name          FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_primary_category   = $wpdb->get_var("SELECT ad_primary_category   FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_secondary_category = $wpdb->get_var("SELECT ad_secondary_category FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_price              = $wpdb->get_var("SELECT ad_price              FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_currency           = $wpdb->get_var("SELECT ad_currency           FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_phone_number       = $wpdb->get_var("SELECT ad_phone_number       FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
    
	$tmp_ad_phone_number = trim($tmp_ad_phone_number);

	//get primary blog
	$tmp_blog_id     = $wpdb->get_var("SELECT meta_value FROM " . $wpdb->base_prefix . "usermeta WHERE meta_key = 'primary_blog' AND user_id = '" . $tmp_ad_user_ID . "'");
	$tmp_blog_domain = $wpdb->get_var("SELECT domain     FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = '" . $tmp_blog_id . "'");
	$tmp_blog_path   = $wpdb->get_var("SELECT path       FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = '" . $tmp_blog_id . "'");

	$tmp_blog_display = '<a href="http://' . $tmp_blog_domain . $tmp_blog_path . '">' . $tmp_blog_domain . '</a>';

	if ($tmp_ad_last_name != '') {
		$tmp_name = $tmp_ad_first_name . ' ' . $tmp_ad_last_name;
	} else {
		$tmp_name = $tmp_ad_first_name;
	}
?>
    <center>
        <img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_current_ad . "-500.png"; ?>" width="500px" height="375px" alt="Classifieds Ad" />
    </center>
    <br />
    
    <div style="float:left; width: 100%">
        <table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
            <tr>
                <td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="20%"><center><strong>Information</strong></center></td>
                <td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="80%"></td>
            </tr>
            <?php if ($tmp_ad_phone_number != ''): ?>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">Posted By:</td>
                <td style="background-color:#FFFFFF; text-align:left;" width="80%"><?php echo $tmp_name; ?></td>
            </tr>
            <tr>
                <td style="background-color:#F2F2EA; text-align:left;" width="20%">Blog:</td>
                <td style="background-color:#F2F2EA; text-align:left;" width="80%"><?php echo $tmp_blog_display; ?></td>
            </tr>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">Phone:</td>
                <td style="background-color:#FFFFFF; text-align:left" width="80%"><?php echo $tmp_ad_phone_number; ?></td>
            </tr>
            <tr>
                <td style="background-color:#F2F2EA; text-align:left;" width="20%">Price:</td>
                <td style="background-color:#F2F2EA; text-align:left" width="80%"><?php echo $tmp_ad_price . ' ' . $tmp_ad_currency; ?></td>
            </tr>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">Description:</td>
                <td style="background-color:#FFFFFF; text-align:left" width="80%"><?php echo $tmp_ad_description; ?></td>
            </tr>
            <?php else: ?>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">Posted By:</td>
                <td style="background-color:#FFFFFF; text-align:left;" width="80%"><?php echo $tmp_name; ?></td>
            </tr>
            <tr>
                <td style="background-color:#F2F2EA; text-align:left;" width="20%">Blog:</td>
                <td style="background-color:#F2F2EA; text-align:left;" width="80%"><?php echo $tmp_blog_display; ?></td>
            </tr>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">Price:</td>
                <td style="background-color:#FFFFFF; text-align:left" width="80%"><?php echo $tmp_ad_price . ' ' . $tmp_ad_currency; ?></td>
            </tr>
            <tr>
                <td style="background-color:#F2F2EA; text-align:left;" width="20%">Description:</td>
                <td style="background-color:#F2F2EA; text-align:left" width="80%"><?php echo $tmp_ad_description; ?></td>
            </tr>
            <?php endif; ?>
        </table>
    <br />
    </div>
<?php
}

function classifieds_frontend_display_ad_contact_form( $tmp_current_ad,$tmp_base_url ) {
	global $wpdb;

	$tmp_ad_email_address = $wpdb->get_var("SELECT ad_email_address FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_title         = $wpdb->get_var("SELECT ad_title FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");

	if ($_GET['action'] == 'process') {
		if ($_POST['email_name'] == '' || $_POST['email_email'] == '' || $_POST['email_spam_check'] == '' || $_POST['email_content'] == '') {
			?>
			<div style="float:left;  width:100%">
            <h2><center>You must complete all fields!</center></h2>
                <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url ) ?>
			</div>
			<?php
		} else if ($_POST['email_spam_check'] != '13') {
			?>
			<div style="float:left;  width:100%">
            <h3><center>Please correctly answer the spam question!</center></h3>
                <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url ) ?>
			</div>
			<?php
		} else {
			classifieds_send_email($tmp_current_ad, $tmp_ad_title, $tmp_ad_email_address, $_POST['email_email'], $_POST['email_name'], $_POST['email_content']);
			?>
			<div style="float:left; width:100%">
            <h2><center>Your email has been sent.</center></h2>
                <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url ) ?>
			</div>
			<?php
		}
	} else {
        ?>
		<div style="float:left; width: 100%">
            <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url ) ?>
		</div>
        <?php
	}
}

function classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url ) {
?>
    <form action="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_current_ad; ?>&action=process" method="post">
        <table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
            <tr>
                <td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="20%"><center><strong>Send Email</strong></center></td>
                <td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="80%"><center><small>Please complete all fields.</small></center></td>
            </tr>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">Your Name:</td>
                <td style="background-color:#FFFFFF; text-align:left;" width="80%"><input name="email_name" id="email_name" style="width:100%;" maxlength="50" value="" type="text"></td>
            </tr>
            <tr>
                <td style="background-color:#F2F2EA; text-align:left;" width="20%">Your Email:</td>
                <td style="background-color:#F2F2EA; text-align:left" width="80%"><input name="email_email" id="email_email" style="width:100%;" maxlength="50" value="" type="text"></td>
            </tr>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%">2 + 11 = </td>
                <td style="background-color:#FFFFFF; text-align:left" width="80%"><input name="email_spam_check" id="email_spam_check" style="width:100%;" maxlength="50" value="" type="text"></td>
            </tr>
            <tr>
                <td style="background-color:#F2F2EA; text-align:left;" width="20%" valign="top">Message:</td>
                <td style="background-color:#F2F2EA; text-align:left" width="80%"><textarea name="email_content" id="email_content" rows="10" style="width:100%;"></textarea></td>
            </tr>
            <tr>
                <td style="background-color:#FFFFFF; text-align:left;" width="20%"></td>
                <td style="background-color:#FFFFFF; text-align:right" width="80%"><input name="Submit" value="Submit" id="submit" type="submit"></td>
            </tr>
        </table>
    </form>
<?php
}

function classifieds_frontend_display_search_navigation( $tmp_search_query, $tmp_clean_search_query, $tmp_per_page, $tmp_current_page, $tmp_base_url ) {
	global $wpdb;
	//get ad count
	$tmp_ad_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_title LIKE '%" . $tmp_clean_search_query . "%' OR ad_description LIKE '%" . $tmp_clean_search_query . "%') AND ad_status = 'active'");
	//generate page 
	$tmp_total_pages = classifieds_roundup( $tmp_ad_count / $tmp_per_page, 0 );
    ?>
	<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
        <tr>
            <?php
            $tmp_showing_low = ( $tmp_current_page * $tmp_per_page ) - ( $tmp_per_page - 1 );
            if ( $tmp_total_pages == $tmp_current_page ) {
                //last page...
                $tmp_showing_high = $tmp_ad_count - (( $tmp_total_pages - 1 ) * $tmp_per_page );
            } else {
                $tmp_showing_high = $tmp_current_page * $tmp_per_page;
            }
            ?>
            <td style="font-size:12px; color: #686868; text-align:left;" width="50%">Showing <?php echo $tmp_showing_low; ?> > <?php echo $tmp_showing_high; ?> of <?php echo $tmp_ad_count; ?> Ads</td>
            <td style="font-size:12px; color: #686868; text-align:right;" width="50%">
            <?php
            if ( $tmp_ad_count > $tmp_per_page ) {
                if ( $tmp_current_page == '' || $tmp_current_page == '1' ) { 
                    _e('Previous | ');
                } else {
                    $tmp_previus_page = $tmp_current_page - 1;
                    ?>
                    <a href="<?php echo $tmp_base_url; ?>?search=<?php echo urlencode($tmp_search_query); ?>&page=<?php echo $tmp_previus_page ?>"><?php _e('Previous') ?></a> |
                    <?php
                }
                $counter + 0;
                while ( $counter < $tmp_total_pages ) {
                    $counter = $counter + 1;
                    if ( $counter == $tmp_current_page ) {
                        echo $counter . ' | ';
                    } else {
                    ?>
                    <a href="<?php echo $tmp_base_url; ?>?search=<?php echo urlencode($tmp_search_query); ?>&page=<?php echo $counter; ?>"><?php echo $counter; ?></a> |
                    <?php
                    }
                }
                if ( $tmp_current_page == $tmp_total_pages ) {
                    _e('Next');
                } else {
                    if ( $tmp_total_pages == 1 ) {
                        _e('Next');
                    } else {
                        $tmp_next_page = $tmp_current_page + 1;
                        ?>
                        <a href="<?php echo $tmp_base_url; ?>?search=<?php echo urlencode($tmp_search_query); ?>&page=<?php echo $tmp_next_page; ?>"><?php _e('Next') ?></a>
                        <?php
                    }
                }
            }
            ?>
            </td>
        </tr>
    </table>
	<?php
}

function classifieds_frontend_display_navigation($tmp_cat,$tmp_per_page,$tmp_current_page,$tmp_base_url){
	global $wpdb;
	//get ad count
	$tmp_ad_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $tmp_cat . "' OR ad_secondary_category = '" . $tmp_cat . "') AND ad_status = 'active'");
	//generate page div
	//============================================================================//
	$tmp_total_pages = classifieds_roundup($tmp_ad_count / $tmp_per_page, 0);
	?>
	<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
	<tr>
	<?php
	$tmp_showing_low = ($tmp_current_page * $tmp_per_page) - ($tmp_per_page - 1);
	if ($tmp_total_pages == $tmp_current_page){
		//last page...
		$tmp_showing_high = $tmp_ad_count - (($tmp_total_pages - 1) * $tmp_per_page);
	} else {
		$tmp_showing_high = $tmp_current_page * $tmp_per_page;
	}

	?>
    <td style="font-size:12px; color: #686868; text-align:left;" width="50%">Showing <?php echo $tmp_showing_low; ?> > <?php echo $tmp_showing_high; ?> of <?php echo $tmp_ad_count; ?> Ads</td>
    <td style="font-size:12px; color: #686868; text-align:right;" width="50%">
	<?php
	if ($tmp_ad_count > $tmp_per_page){
	//============================================================================//
		if ($tmp_current_page == '' || $tmp_current_page == '1'){
		?>
		<?php _e('Previous') ?> |
		<?php
		} else {
		$tmp_previus_page = $tmp_current_page - 1;
		?>
		<a href="<?php echo $tmp_base_url; ?>?cat=<?php echo $tmp_cat; ?>&page=<?php echo $tmp_previus_page ?>"><?php _e('Previous') ?></a> |
		<?php
		}
		?>
		<?php
		$counter + 0;
		while ( $counter < $tmp_total_pages ) {
			$counter = $counter + 1;
			if ($counter == $tmp_current_page){
			?>
		<?php echo $counter; ?> |
			<?php
			} else {
			?>
		<a href="<?php echo $tmp_base_url; ?>?cat=<?php echo $tmp_cat; ?>&page=<?php echo $counter; ?>"><?php echo $counter; ?></a> |
			<?php
			}
		}
		?>
		<?php
		if ($tmp_current_page == $tmp_total_pages){
		?>
		<?php _e('Next') ?>
		<?php
		} else {
			if ($tmp_total_pages == 1){
			?>
		<?php _e('Next') ?>
			<?php
			} else {
			$tmp_next_page = $tmp_current_page + 1;
			?>
		<a href="<?php echo $tmp_base_url; ?>?cat=<?php echo $tmp_cat; ?>&page=<?php echo $tmp_next_page; ?>"><?php _e('Next') ?></a>
			<?php
			}
		}
	//============================================================================//
	}
	?>
    </td>
	</tr>
    </table>
	<?php
}

function classifieds_frontend_search_results_paginated($tmp_search_query,$tmp_per_page,$tmp_current_page,$tmp_base_url){
	global $wpdb;
	//cleanup search query
	$tmp_clean_search_query = $tmp_search_query;
	$tmp_clean_search_query = strtolower($tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('and','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('the','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('to','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('but','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('or','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('+','',$tmp_clean_search_query);

	$tmp_clean_search_query = str_replace(' ','%',$tmp_clean_search_query);

	if ($tmp_current_page == ''){
		$tmp_current_page = 1;
	}
	if ($tmp_current_page == 1){
		$tmp_start = 0;
	} else {
		$tmp_math = $tmp_current_page - 1;
		$tmp_math = $tmp_per_page * $tmp_math;
		//$tmp_math = $tmp_math - 1;
		$tmp_start = $tmp_math;
	}

	$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_title LIKE '%" . $tmp_clean_search_query . "%' OR ad_description LIKE '%" . $tmp_clean_search_query . "%') AND ad_status = 'active'";
	$query .= " ORDER BY ad_ID DESC";
	$query .= " LIMIT " . intval( $tmp_start ) . ", " . intval( $tmp_per_page );
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

	if ( count($tmp_ads) > 0 ) {
		classifieds_check_expired( $tmp_ads );
		$tmp_ads = $wpdb->get_results( $query, ARRAY_A );
		classifieds_frontend_display_search_navigation($tmp_search_query,$tmp_clean_search_query,$tmp_per_page,$tmp_current_page,$tmp_base_url);
		?>

		<div style="float:left; width:100%">
		<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
		<tr>
			<td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="20%"><center><strong>Image</strong></center></td>
			<td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="80%"><center><strong>Title</strong></center></td>
		</tr>
		<?php
		$tic_toc = 'toc';
		foreach ($tmp_ads as $tmp_ad){
			//tic/toc
			if ($tic_toc == 'toc'){
				$tic_toc = 'tic';
			} else {
				$tic_toc = 'toc';
			}
			if ($tic_toc == 'tic'){
				$tmp_color = '#FFFFFF';
			} else {
				$tmp_color = '#F2F2EA';
			}
			?>
			<tr>
				<td style="background-color:<?php echo $tmp_color; ?>;" width="20%"><center><a style="text-decoration:none; color:#1793AD;" href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-80.png"; ?>" width="80px" height="60px" /></a></center></td>
				<td style="background-color:<?php echo $tmp_color; ?>;" width="80%"><center><a style="text-decoration:none; color:#1793AD;" href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><strong><?php echo $tmp_ad['ad_title']; ?></strong></a></center></td>
			</tr>
			<?php
		}
		?>
		</table>
		</div>
		<?php
	} else {
		?>
        <p><?php _e('Your search - '); ?><?php echo $tmp_search_query; ?><?php _e(' - did not match any classified ads. '); ?></p>
		<?php
	}
}

function classifieds_frontend_display_ads_paginated($tmp_cat,$tmp_per_page,$tmp_current_page,$tmp_base_url){
	global $wpdb;
	if ($tmp_current_page == ''){
		$tmp_current_page = 1;
	}
	if ($tmp_current_page == 1){
		$tmp_start = 0;
	} else {
		$tmp_math = $tmp_current_page - 1;
		$tmp_math = $tmp_per_page * $tmp_math;
		//$tmp_math = $tmp_math - 1;
		$tmp_start = $tmp_math;
	}
	$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $tmp_cat . "' OR ad_secondary_category = '" . $tmp_cat . "') AND ad_status = 'active'";
	$query .= " ORDER BY ad_ID DESC";
	$query .= " LIMIT " . intval( $tmp_start ) . ", " . intval( $tmp_per_page );
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

	if (count($tmp_ads) > 0){
		classifieds_check_expired($tmp_ads);
		$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

		classifieds_frontend_display_navigation($tmp_cat,$tmp_per_page,$tmp_current_page,$tmp_base_url);
		?>

		<div style="float:left; width:100%">
		<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
		<tr>
			<td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="20%"><center><strong>Image</strong></center></td>
			<td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="80%"><center><strong>Title</strong></center></td>
		</tr>
		<?php
		$tic_toc = 'toc';
		foreach ($tmp_ads as $tmp_ad){
			//tic/toc
			if ($tic_toc == 'toc'){
				$tic_toc = 'tic';
			} else {
				$tic_toc = 'toc';
			}
			if ($tic_toc == 'tic'){
				$tmp_color = '#FFFFFF';
			} else {
				$tmp_color = '#F2F2EA';
			}
			?>
			<tr>
				<td style="background-color:<?php echo $tmp_color; ?>;" width="20%"><center><a style="text-decoration:none; color:#1793AD;" href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-80.png"; ?>" width="80px" height="60px" /></a></center></td>
				<td style="background-color:<?php echo $tmp_color; ?>;" width="80%"><center><a style="text-decoration:none; color:#1793AD;" href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><strong><?php echo $tmp_ad['ad_title']; ?></strong></a></center></td>
			</tr>
			<?php
		}
		?>
		</table>
		</div>
		<?php
	} else {
		?>
        <p><?php _e('Nothing to display...'); ?></p>
		<?php
	}
}

function classifieds_frontend_display_ads( $tmp_cat,$tmp_limit,$tmp_sort,$tmp_base_url ) {
	global $wpdb;
	if ($tmp_cat == ''){
		$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_status = 'active'";
		if ($tmp_sort == 'random'){
		$query .= " ORDER BY RAND()";
		}
		if ($tmp_sort == 'newest'){
		$query .= " ORDER BY ad_ID DESC";
		}
		$query .= " LIMIT " . $tmp_limit;
	} else {
		$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $tmp_cat . "' OR ad_secondary_category = '" . $tmp_cat . "') AND ad_status = 'active'";
		if ($tmp_sort == 'random'){
		$query .= " ORDER BY RAND()";
		}
		if ($tmp_sort == 'newest'){
		$query .= " ORDER BY ad_ID DESC";
		}
		$query .= " LIMIT " . $tmp_limit;
	}
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );
	if (count($tmp_ads) > 0){
		classifieds_check_expired($tmp_ads);
	}
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );
	if (count($tmp_ads) > 0){
		?>
		<div style="float:left; width:100%">
		<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
		<tr>
			<td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="20%"><center><strong>Image</strong></center></td>
			<td style="background-color:#F2F2EA; border-bottom-style:solid; border-bottom-color:#CFD0CB; border-bottom-width:1px; font-size:12px;" width="80%"><center><strong>Title</strong></center></td>
		</tr>
		<?php
		$tic_toc = 'toc';
		foreach ($tmp_ads as $tmp_ad){
			//tic/toc
			if ($tic_toc == 'toc'){
				$tic_toc = 'tic';
			} else {
				$tic_toc = 'toc';
			}
			if ($tic_toc == 'tic'){
				$tmp_color = '#FFFFFF';
			} else {
				$tmp_color = '#F2F2EA';
			}
			?>
			<tr>
				<td style="background-color:<?php echo $tmp_color; ?>;" width="20%"><center><a style="text-decoration:none; color:#1793AD;" href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-80.png"; ?>" width="80px" height="60px" /></a></center></td>
				<td style="background-color:<?php echo $tmp_color; ?>;" width="80%"><center><a style="text-decoration:none; color:#1793AD;" href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><strong><?php echo $tmp_ad['ad_title']; ?></strong></a></center></td>
			</tr>
			<?php
		}
		?>
		</table>
		</div>
		<?php
	} else {
	?>
    <p><center>Nothing to Display</center></p>
    <?php
	}
}

function classifieds_frontend_search_form( $tmp_base_url ) {
	?>
    <form method="post" action="<?php echo $tmp_base_url; ?>">
        <input name="search" size="17" maxlength="50" value="" type="text">
        <input value="Go &raquo;" type="submit">
    </form>
    <?php
}

function classifieds_frontend_list_categories( $tmp_current_cat,$tmp_base_url ) {
	global $wpdb;
	$query = "SELECT category_ID, category_name, category_description FROM " . $wpdb->base_prefix . "classifieds_categories ORDER BY category_name ASC";
	$tmp_classifieds_categories = $wpdb->get_results( $query, ARRAY_A );
	?>
    <ul>
        <?php
        if ( count( $tmp_classifieds_categories ) > 0) {
            foreach ($tmp_classifieds_categories as $tmp_classifieds_category){
            //=========================================================//
                $tmp_ad_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $tmp_classifieds_category['category_ID'] . "' OR ad_secondary_category = '" . $tmp_classifieds_category['category_ID'] . "') AND ad_status = 'active'");
                if ($tmp_ad_count > 0){
                    ?>
                    <li><a href="<?php echo $tmp_base_url;?>?cat=<?php echo $tmp_classifieds_category['category_ID']; ?>" title="<?php echo $tmp_classifieds_category['category_description']; ?>"><?php echo $tmp_classifieds_category['category_name']; ?> (<?php echo $tmp_ad_count; ?>)</a></li>
                    <?php
                }
            //=========================================================//
            }
        } else {
        ?>
        <p>Nothing to Display</p>
        <?php
        }
        ?>
    </ul>
<?php
}

function classifieds_frontend_display_ad_title( $the_title ) {
    if ( in_the_loop() ) {
        global $wpdb;
        
        $tmp_current_ad = $_GET['ad'];
        $tmp_current_cat  = $_GET['cat'];
        $tmp_search_query = $_POST['search'];
        $tmp_search_query = urldecode( $tmp_search_query );

        if ( $tmp_search_query != '' ) {
            return $the_title . ' &raquo; Search Results &raquo; ' . $tmp_search_query;
        } else if ( $tmp_current_ad != '' ) {
            $tmp_current_ad_title = $wpdb->get_var("SELECT ad_title FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "'");
            return $the_title . ' &raquo; ' . $tmp_current_ad_title;
        } else if ( $tmp_current_cat != '') {
            $tmp_current_cat_name = $wpdb->get_var("SELECT category_name FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $tmp_current_cat . "'");
            return $the_title . ' &raquo; Category &raquo; ' . $tmp_current_cat_name;
        } else {
            return $the_title;
        }
    } else {
        return $the_title;
    }
}
add_filter( 'the_title', 'classifieds_frontend_display_ad_title' );