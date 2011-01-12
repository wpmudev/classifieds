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
 * Classifieds CREDITS
 * Handles the overall operations of the credits payment module
 *
 * @package Classifieds
 * @subpackage Credits Payment Module
 * @since 1.1.0
 */

//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//

//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//

add_action('classifieds_payment_module_options', 'classifieds_payment_module_paypal_options');
add_action('classifieds_payment_module_process', 'classifieds_payment_module_paypal_process');
add_action('classifieds_payment_module_buy', 'classifieds_payment_module_paypal_buy_output');
add_action('classifieds_payment_module_buy_5', 'classifieds_payment_module_paypal_buy_5');
add_action('classifieds_payment_module_buy_10', 'classifieds_payment_module_paypal_buy_10');
add_action('classifieds_payment_module_buy_25', 'classifieds_payment_module_paypal_buy_25');
add_action('classifieds_payment_module_buy_50', 'classifieds_payment_module_paypal_buy_50');
add_action('classifieds_payment_module_buy_75', 'classifieds_payment_module_paypal_buy_75');
add_action('classifieds_payment_module_buy_100', 'classifieds_payment_module_paypal_buy_100');

//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
function classifieds_payment_module_paypal_process(){
	global $wpdb, $current_site;
	if (get_site_option( "classifieds_paypal_email" ) == '') {
		add_site_option( 'classifieds_paypal_email', $_POST['classifieds_paypal_email'] );
	} else {
		update_site_option( "classifieds_paypal_email", $_POST['classifieds_paypal_email'] );
	}
	if (get_site_option( "classifieds_paypal_site" ) == '') {
		add_site_option( 'classifieds_paypal_site', $_POST['classifieds_paypal_site'] );
	} else {
		update_site_option( "classifieds_paypal_site", $_POST['classifieds_paypal_site'] );
	}
	if (get_site_option( "classifieds_paypal_status" ) == '') {
		add_site_option( 'classifieds_paypal_status', $_POST['classifieds_paypal_status'] );
	} else {
		update_site_option( "classifieds_paypal_status", $_POST['classifieds_paypal_status'] );
	}
}
//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//
function classifieds_payment_module_paypal_options(){
	global $wpdb, $current_site;
	?>
    <fieldset class="options"> 
    <legend>PayPal Options</legend>          
    <table class="optiontable">
        <tr valign="top"> 
        <th scope="row"><?php _e('PayPal Email:') ?></th> 
        <td><input type="text" name="classifieds_paypal_email" value="<?php echo get_site_option( "classifieds_paypal_email" ); ?>" />
        <br />
        <?php //_e('Format: 00.00 - Ex: 1.25') ?></td> 
        </tr>
        <tr valign="top"> 
        <th scope="row"><?php _e('PayPal Site:') ?></th> 
        <td><select name="classifieds_paypal_site">
        <?php
            $tmp_classifieds_paypal_site = get_site_option( "classifieds_paypal_site" );
            $sel_locale = empty($tmp_classifieds_paypal_site) ? 'US' : $tmp_classifieds_paypal_site;
            $locales = array(
                'AU'	=> 'Australia',
                'AT'	=> 'Austria',
                'BE'	=> 'Belgium',
                'CA'	=> 'Canada',
                'CN'	=> 'China',
                'FR'	=> 'France',
                'DE'	=> 'Germany',
                'IT'	=> 'Italy',
                'NL'	=> 'Netherlands',
                'PL'	=> 'Poland',
                'ES'	=> 'Spain',
                'CH'	=> 'Switzerland',
                'GB'	=> 'United Kingdom',
                'US'	=> 'United States',
                'BG'	=> 'Bulgaria'
                );
        
            foreach ($locales as $k => $v) {
                echo '		<option value="' . $k . '"' . ($k == $sel_locale ? ' selected' : '') . '>' . wp_specialchars($v, true) . '</option>' . "\n";
            }
        ?>
        </select>
        <br />
        <?php //_e('Format: 00.00 - Ex: 1.25') ?></td> 
        </tr> 
        <tr valign="top"> 
        <th scope="row"><?php _e('PayPal Mode:') ?></th> 
        <td><select name="classifieds_paypal_status">
        <option value="live" <?php if (get_site_option( "classifieds_paypal_status" ) == 'live') echo 'selected="selected"'; ?>>Live Site</option>
        <option value="test" <?php if (get_site_option( "classifieds_paypal_status" ) == 'test') echo 'selected="selected"'; ?>>Test Mode (Sandbox)</option>
        </select>
        <br />
        <?php //_e('Format: 00.00 - Ex: 1.25') ?></td> 
        </tr>
    </table>
    </fieldset>
    <?php
}

function classifieds_payment_module_paypal_button_output($tmp_credits) {
	global $wpdb, $current_site, $current_user;
	// Live URL:	https://www.paypal.com/cgi-bin/webscr
	// Sandbox URL:	https://www.sandbox.paypal.com/cgi-bin/webscr
	if (get_site_option( "classifieds_paypal_status" ) == 'live'){
		$action = 'https://www.paypal.com/cgi-bin/webscr';
	} else {
		$action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	}
	$blog_url = get_blogaddress_by_id($wpdb->blogid);
	$tmp_amount = get_site_option( "classifieds_cost_per_credit" );
	$tmp_amount = $tmp_amount * $tmp_credits;

    echo '
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="' . get_site_option( "classifieds_paypal_email" ) . '">
        <input type="hidden" name="lc" value="US">
        <input type="hidden" name="item_name" value="' . $current_site->site_name . ' Credits">
        <input type="hidden" name="amount" value="' . $tmp_amount . '">
        <input type="hidden" name="currency_code" value="' . get_site_option( "classifieds_currency" ) . '">
        <input type="hidden" name="button_subtype" value="services">
        <input type="hidden" name="no_note" value="0">
        <input type="hidden" name="tax_rate" value="0.000">
        <input type="hidden" name="shipping" value="0.00">
        <input type="hidden" name="return" value="' . $blog_url . 'wp-admin/admin.php?page=classifieds_credits_management&updated=true&updatedmsg=' . urlencode(__('Transaction Complete!')) . '">
		<input type="hidden" name="cancel_return" value="' . $blog_url . 'wp-admin/admin.php?page=classifieds_credits_management&updated=true&updatedmsg=' . urlencode(__('Transaction Canceled!')) . '">
        <input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHostedGuest">
        <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
        <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
        </form>
        ';
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//
function classifieds_payment_module_paypal_buy_5(){
	classifieds_payment_module_paypal_button_output('5');
}
function classifieds_payment_module_paypal_buy_10(){
	classifieds_payment_module_paypal_button_output('10');
}
function classifieds_payment_module_paypal_buy_25(){
	classifieds_payment_module_paypal_button_output('25');
}
function classifieds_payment_module_paypal_buy_50(){
	classifieds_payment_module_paypal_button_output('50');
}
function classifieds_payment_module_paypal_buy_75(){
	classifieds_payment_module_paypal_button_output('75');
}
function classifieds_payment_module_paypal_buy_100(){
	classifieds_payment_module_paypal_button_output('100');
}
?>
