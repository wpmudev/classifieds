<?php

/**
 * Classifieds CREDITS
 * Handles the overall operations of the credits module
 *
 * @package Classifieds
 * @subpackage Credits
 * @since 1.1.0
 */

$classifieds_credits_singular = __('Credit');
$classifieds_credits_plural = __('Credits');



function classifieds_admin_menu_credits_pages() {
	global $wpdb;
	
    if ( current_user_can('edit_users' ) )
        add_submenu_page( 'classifieds', 'Credit Options', 'Credit Options', 'edit_users', 'classifieds_credits', 'classifieds_page_config_output');

    if ( get_site_option('classifieds_credits_enabled') )
        add_submenu_page('classifieds', 'My Credits', 'My Credits', 'read', 'classifieds_credits_management', 'classifieds_page_credits_output' );
} 
add_action('admin_menu', 'classifieds_admin_menu_credits_pages');

function classifieds_credits_admin_styles() {
    ?>
    <style type="text/css">
        .credits table { text-align: left; }
        .credits table th { width: 200px; }
        .credits table td input { width: 250px; }
        .credits table td select { width: 250px; }
        .credits table td textarea { width: 250px; }
    </style>
    <?php
}
add_action( 'admin_head', 'classifieds_credits_admin_styles' );

function classifieds_user_credit_check($tmp_user_id = 'na'){
	global $wpdb, $wp_roles, $current_user;
	if ($tmp_user_id == '' || $tmp_user_id == 'na'){
		$tmp_credits_check = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'");
		if ($tmp_credits_check == 0){
			$tmp_classifieds_signup_credits = get_site_option( "classifieds_signup_credits" );
			if ($tmp_classifieds_signup_credits == ''){
				$tmp_classifieds_signup_credits = '0';
			}
			$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "classifieds_credits (user_ID, credits) VALUES ( '" . $current_user->ID . "', '" . $tmp_classifieds_signup_credits . "' )" );
		}
	} else {
		$tmp_credits_check = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $tmp_user_id . "'");
		if ($tmp_credits_check == 0){
			$tmp_classifieds_signup_credits = get_site_option( "classifieds_signup_credits" );
			if ($tmp_classifieds_signup_credits == ''){
				$tmp_classifieds_signup_credits = '0';
			}
			$wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "classifieds_credits (user_ID, credits) VALUES ( '" . $tmp_user_id . "', '" . $tmp_classifieds_signup_credits . "' )" );
		}
	}
}

function classifieds_user_credits_update( $new_total ) {
	global $wpdb, $wp_roles, $current_user;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_credits SET credits = '" . $new_total . "' WHERE user_ID = '" . $current_user->ID . "'");
}

function classifieds_user_credits_available( $tmp_user_id ) {
	global $wpdb;
	classifieds_user_credit_check();
	$tmp_user_credits = $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $tmp_user_id . "'");
	return $tmp_user_credits;
}

function classifieds_user_credits_add( $tmp_credits, $tmp_user_id ) {
	global $wpdb, $wp_roles, $current_user;
	$tmp_old_total = $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE user_ID = '" . $tmp_user_id . "'");
	$tmp_new_total = $tmp_old_total + $tmp_credits;
	$wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_credits SET credits = '" . $tmp_new_total . "' WHERE user_ID = '" . $tmp_user_id . "'");
}


function classifieds_gift_notification( $sending_user, $recieving_user, $credits ) {
		global $wpdb, $wp_roles, $current_user, $current_site;

		$notification_email = stripslashes( __( "Hello, SENDING_USER has sent you NUM_CREDITS credits.
                                                 You can use these credits to purchase advanced features for your blog!
                                                 Thanks!
                                               --The SITE_NAME Team" ) );

	$url = get_blogaddress_by_id( $blog_id );
	$user = new WP_User( $user_id );

	$notification_email = str_replace( "SITE_NAME", $current_site->site_name, $notification_email );
	$notification_email = str_replace( "SENDING_USER", $sending_user, $notification_email );
	$notification_email = str_replace( "RECIEVING_USER", $$recieving_user, $notification_email );
	$notification_email = str_replace( "NUM_CREDITS", $credits, $notification_email );


	$admin_email = get_site_option( "admin_email" );
	if( $admin_email == '' )
		$admin_email = 'support@' . $_SERVER[ 'SERVER_NAME' ];
	$message_headers = "MIME-Version: 1.0\n" . "From: " . get_site_option( "site_name" ) .  " <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
	$message = $notification_email;
	if( empty( $current_site->site_name ) )
		$current_site->site_name = "Not Set";
	$subject = ucfirst($recieving_user) . ": You have been sent credits";
	$tmp_recieving_user_email = $wpdb->get_var("SELECT user_email FROM " . $wpdb->base_prefix . "users WHERE user_login = '" . $recieving_user . "'");
	wp_mail($tmp_recieving_user_email, $subject, $message, $message_headers);
}

/*
 * Add credits overview to the main dashboard panel
 */
function classifieds_dashboard_credits() {
	global $wpdb, $wp_roles, $current_user, $classifieds_credits_singular, $classifieds_credits_plural;
    
	$tmp_available_credits = classifieds_user_credits_available( $current_user->ID );

	if ( $tmp_available_credits == '' )
		 $tmp_available_credits = '0';
	?>
	<div id='availablecredits'>
		<h3><?php _e("Credits <a href='admin.php?page=classifieds_credits_management' title='Manage Credits...'>&raquo;</a>"); ?></h3>
        <?php if ( $tmp_available_credits == '0' ): ?>
		<p><?php _e('Available Credits:'); ?> <strong><?php _e('None - <a href="admin.php?page=classifieds_credits_management">Click here</a> to purchase credits'); ?></strong></p>
        <?php else: ?>
		<p><?php _e( 'Available Credits:' ); ?> <strong><?php echo classifieds_user_credits_available( $current_user->ID ); ?></strong></p>
		<p><?php _e( '<a href="admin.php?page=classifieds_credits_management">Click here to purchase more credits &raquo;</a>'); ?></p>
        <?php endif; ?>
	</div>
	<?php
}
if ( get_site_option('classifieds_credits_enabled') )
    add_action('activity_box_end', 'classifieds_dashboard_credits');


function classifieds_buy_output(){
	$tmp_amount = get_site_option( "classifieds_cost_per_credit" );
	echo '<p>';
	echo '' . __('Purchase') . ' <strong>5</strong> ' . __('credits') . ' ( ' . __('for') . ' <strong>' . ($tmp_amount * 5) . '</strong> ' . get_site_option( "classifieds_currency" ) . ' ):<br />';
	do_action('classifieds_payment_module_buy_5');
	echo '</p>';
	echo '<p>';
	echo '' . __('Purchase') . ' <strong>10</strong> ' . __('credits') . ' ( ' . __('for') . ' <strong>' . ($tmp_amount * 10) . '</strong> ' . get_site_option( "classifieds_currency" ) . ' ):<br />';
	do_action('classifieds_payment_module_buy_10');
	echo '</p>';
	echo '<p>';
	echo '' . __('Purchase') . ' <strong>25</strong> ' . __('credits') . ' ( ' . __('for') . ' <strong>' . ($tmp_amount * 25) . '</strong> ' . get_site_option( "classifieds_currency" ) . ' ):<br />';
	do_action('classifieds_payment_module_buy_25');
	echo '</p>';
	echo '<p>';
	echo '' . __('Purchase') . ' <strong>50</strong> ' . __('credits') . ' ( ' . __('for') . ' <strong>' . ($tmp_amount * 50) . '</strong> ' . get_site_option( "classifieds_currency" ) . ' ):<br />';
	do_action('classifieds_payment_module_buy_50');
	echo '</p>';
	echo '<p>';
	echo '' . __('Purchase') . ' <strong>75</strong> ' . __('credits') . ' ( ' . __('for') . ' <strong>' . ($tmp_amount * 75) . '</strong> ' . get_site_option( "classifieds_currency" ) . ' ):<br />';
	do_action('classifieds_payment_module_buy_75');
	echo '</p>';
	echo '<p>';
	echo '' . __('Purchase') . ' <strong>100</strong> ' . __('credits') . ' ( ' . __('for') . ' <strong>' . ($tmp_amount * 100) . '</strong> ' . get_site_option( "classifieds_currency" ) . ' ):<br />';
	do_action('classifieds_payment_module_buy_100');
	echo '</p>';
	echo '<br />';
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function classifieds_page_config_output() {
	global $wpdb, $wp_roles, $current_user, $classifieds_credits_singular, $classifieds_credits_plural, $currencies;
	
	if( !current_user_can('edit_users') ) {
		echo "<p>" . __('Nice Try...') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if ( isset( $_GET['updated'] ) ) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap credits">';

    if ( get_site_option('classifieds_credits_enabled') ) {
        switch( $_GET[ 'action' ] ) {
            //---------------------------------------------------//
            default:
                ?>
                <h2><?php _e($classifieds_credits_singular . ' Options') ?></h2>

                <form name="form1" method="POST" action="admin.php?page=classifieds_credits&action=process"><p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></p>
                <table class="optiontable">
                    <tr valign="top">
                    <th scope="row"><?php _e('Cost per credit:') ?></th>
                    <td><input type="text" name="classifieds_cost_per_credit" value="<?php echo get_site_option( "classifieds_cost_per_credit" ); ?>" />
                    <br />
                    <?php _e('Format: 00.00 - Ex: 1.25') ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Currency:') ?></th>
                    <td><select name="classifieds_currency">
                    <?php
                        $tmp_classifieds_currency = get_site_option( "classifieds_currency" );
                        $sel_currency = empty($tmp_classifieds_currency) ? 'USD' : $tmp_classifieds_currency;
                        foreach ( $currencies as $k => $v ) {
                            echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . wp_specialchars($v, true) . '</option>' . "\n";
                        }
                    ?>
                    </select>
                    <br />
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Credits Per Week:') ?></th>
                        <td>
                            <select name="classifieds_credits_per_week">
                            <?php
                            $tmp_classifieds_credits_per_week = get_site_option( "classifieds_credits_per_week" );
                            $tmp_counter = 0;
                            for ( $counter = 1; $counter <= 100; $counter += 1) {
                                $tmp_counter = $tmp_counter + 1;
                                echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_credits_per_week ? ' selected' : '') . '>' . $tmp_counter . '</option>' . "\n";
                            }
                            ?>
                            </select>
                            <br />
                            <?php _e('1 credit = ' ) ?><?php echo get_site_option( "classifieds_cost_per_credit" ); ?> <?php echo get_site_option( "classifieds_currency" ); ?>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Description:') ?></th>
                        <td>
                            <textarea name="classifieds_description_text" rows="5"><?php echo get_site_option( "classifieds_description_text" ); ?></textarea>
                            <br />
                            <?php _e('Display on the classifieds admin page' ); ?>
                        </td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Signup Credits:') ?></th>
                    <td><select name="classifieds_signup_credits">
                    <?php
                        $tmp_classifieds_signup_credits = get_site_option( "classifieds_signup_credits" );
                        $tmp_counter = 0;
                        for ( $counter = 1; $counter <= 101; $counter += 1) {
                            echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_classifieds_signup_credits ? ' selected' : '') . '>' . $tmp_counter . '</option>' . "\n";
                            $tmp_counter = $tmp_counter + 1;
                        }
                    ?>
                    </select>
                    <br />
                    <?php _e('How many credits each user receives when they signup.'); ?></td>
                    </tr>
                </table>
                <?php
                do_action('classifieds_payment_module_options');
                ?>
                <p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></p>
                </form>

                <h2><?php _e('Send Credits') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds_credits&action=manage_credits">
                    <table class="optiontable">
                        <tr valign="top">
                        <th scope="row"><?php _e('Action:') ?></th>
                        <td><input type="radio" name="manage_credits_action" value="send_all_users" />Send Credits to all users<br />
                        <input type="radio" name="manage_credits_action" value="send_single_user" checked="checked" />Send Credits to one user
                        <br />
                        </td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="Submit" value="<?php _e('Next &raquo;') ?>" /></p>
                </form>

                <h2><?php _e('Disable Credits') ?></h2>
                <p><?php _e('By disabeling credits you will not be able to charge your users a preconfigured amount of credits for each ad they place.') ?></p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds_credits&action=disable_credits">
                    <p class="submit">
                        <input type="submit" name="Submit" value="<?php _e('Disable Credits &raquo;') ?>" />
                        <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                    </p>
                </form>
                <?php
            break;
            //---------------------------------------------------//
            case "process":
                $tmp_errors = 0;
                if ( $_POST['classifieds_cost_per_credit'] == '' ) {
                    echo '<p>' . __('You must decide how much a credit will cost!') . '</p>';
                    $tmp_errors ++;
                }
                if ( $tmp_errors == 0 ) {
                    if ( get_site_option( "classifieds_cost_per_credit" ) == '') {
                         add_site_option( 'classifieds_cost_per_credit', $_POST['classifieds_cost_per_credit'] );
                    } else {
                         update_site_option( "classifieds_cost_per_credit", $_POST['classifieds_cost_per_credit'] );
                    }
                    if ( get_site_option( "classifieds_currency" ) == '' ) {
                         add_site_option( 'classifieds_currency', $_POST['classifieds_currency'] );
                    } else {
                        update_site_option( "classifieds_currency", $_POST['classifieds_currency'] );
                    }
                    if ( get_site_option( "classifieds_signup_credits" ) == '') {
                         add_site_option( 'classifieds_signup_credits', $_POST['classifieds_signup_credits'] );
                    } else {
                         update_site_option( "classifieds_signup_credits", $_POST['classifieds_signup_credits'] );
                    }
                    if ( get_site_option( "classifieds_credits_per_week" ) == '' ) {
                         add_site_option( 'classifieds_credits_per_week', $_POST['classifieds_credits_per_week'] );
                    } else {
                        update_site_option( "classifieds_credits_per_week", $_POST['classifieds_credits_per_week'] );
                    }
                    if ( get_site_option( "classifieds_description_text" ) == '' ) {
                         add_site_option( 'classifieds_description_text', $_POST['classifieds_description_text'] );
                    } else {
                         update_site_option( "classifieds_description_text", $_POST['classifieds_description_text'] );
                    }
                }
                do_action('classifieds_payment_module_process');
                echo "
                <script type='text/javascript'>
                    window.location='admin.php?page=classifieds_credits&updated=true&updatedmsg=" . urlencode(__('Options Updated!')) . "';
                </script>
                ";
                break;
            //---------------------------------------------------//
            case "manage_credits":

                if ($_POST['manage_credits_action'] == 'send_all_users'){
                ?>
                <h2><?php _e('Send Credits To All Users') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds_credits&action=manage_credits_process">
                <input type="hidden" name="manage_credits_action" value="send_all_users" />
                <table class="optiontable">
                    <tr valign="top">
                    <th scope="row"><?php _e('Credits:') ?></th>
                    <td><select name="manage_credits_number">
                    <?php
                        $tmp_default_credits = '20';
                        $tmp_counter = 0;
                        for ( $counter = 1; $counter <= 100; $counter += 1) {
                            $tmp_counter = $tmp_counter + 1;
                            echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_default_credits ? ' selected' : '') . '>' . $tmp_counter . '</option>' . "\n";
                        }
                    ?>
                    </select>
                    <br />
                    <?php //_e(''); ?></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="Submit" value="<?php _e('Send &raquo;') ?>" /></p>
                </form>
                <?php
                }
                ?>
                <?php
                if ($_POST['manage_credits_action'] == 'send_single_user'){
                ?>
                <h2><?php _e('Send Credits To One User') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds_credits&action=manage_credits_process">
                <input type="hidden" name="manage_credits_action" value="send_single_user" />
                <table class="optiontable">
                    <tr valign="top">
                    <th scope="row"><?php _e('User (name, not ID):') ?></th>
                    <td><input name="manage_credits_user" value="" />
                    <br />
                    <?php _e('Case Sensitive'); ?></td>
                    </tr>
                    <tr valign="top">
                    <th scope="row"><?php _e('Credits:') ?></th>
                    <td><select name="manage_credits_number">
                    <?php
                        $tmp_default_credits = '20';
                        $tmp_counter = 0;
                        for ( $counter = 1; $counter <= 100; $counter += 1) {
                            $tmp_counter = $tmp_counter + 1;
                            echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_default_credits ? ' selected' : '') . '>' . $tmp_counter . '</option>' . "\n";
                        }
                    ?>
                    </select>
                    <br />
                    <?php //_e(''); ?></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="Submit" value="<?php _e('Send &raquo;') ?>" /></p>
                </form>
                <?php
                }
                ?>
                <?php
            break;
            //---------------------------------------------------//
            case "manage_credits_process":
                if ($_POST['manage_credits_action'] == 'send_all_users'){
                    $query = "SELECT ID, user_login FROM " . $wpdb->base_prefix . "users";
                    $tmp_manage_credits_users = $wpdb->get_results( $query, ARRAY_A );
                    if ( count( $tmp_manage_credits_users ) > 0 ) {
                        foreach ( $tmp_manage_credits_users as $tmp_manage_credits_user ) {
                            classifieds_user_credit_check( $tmp_manage_credits_user['ID'] );
                            classifieds_user_credits_add( $_POST['manage_credits_number'], $tmp_manage_credits_user['ID'] );
                        }
                    }
                    echo "
                    <script type='text/javascript'>
                        window.location='admin.php?page=classifieds_credits&updated=true&updatedmsg=" . urlencode(__('Credits Sent!')) . "';
                    </script>
                    ";
                }
                if ($_POST['manage_credits_action'] == 'send_single_user'){
                    $tmp_user_check = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "users WHERE user_login = '" . $_POST['manage_credits_user'] . "'");
                    if ($tmp_user_check == 0){
                    ?>
                        <h2><?php _e('Send Credits To One User') ?></h2>
                        <p><?php _e('User not found! Please check the spelling.'); ?></p>
                        <form name="form1" method="POST" action="admin.php?page=classifieds_credits&action=manage_credits_process">
                        <input type="hidden" name="manage_credits_action" value="send_single_user" />
                        <table class="optiontable">
                            <tr valign="top">
                            <th scope="row"><?php _e('User (name, not ID):') ?></th>
                            <td><input name="manage_credits_user" value="" />
                            <br />
                            <?php _e('Case Sensitive'); ?></td>
                            </tr>
                            <tr valign="top">
                            <th scope="row"><?php _e('Credits:') ?></th>
                            <td><select name="manage_credits_number">
                            <?php
                                $tmp_default_credits = '20';
                                $tmp_counter = 0;
                                for ( $counter = 1; $counter <= 100; $counter += 1) {
                                    $tmp_counter = $tmp_counter + 1;
                                    echo '<option value="' . $tmp_counter . '"' . ($tmp_counter == $tmp_default_credits ? ' selected' : '') . '>' . $tmp_counter . '</option>' . "\n";
                                }
                            ?>
                            </select>
                            <br />
                            <?php //_e(''); ?></td>
                            </tr>
                        </table>
                        <p class="submit"><input type="submit" name="Submit" value="<?php _e('Send &raquo;') ?>" /></p>
                        </form>
                    <?php
                    } else {
                        $tmp_user_ID = $wpdb->get_var("SELECT ID FROM " . $wpdb->base_prefix . "users WHERE  user_login = '" . $_POST['manage_credits_user'] . "'");
                        classifieds_user_credit_check($tmp_user_ID);
                        classifieds_user_credits_add($_POST['manage_credits_number'], $tmp_user_ID);
                        echo "
                        <script type='text/javascript'>
                            window.location='admin.php?page=classifieds_credits&updated=true&updatedmsg=" . urlencode(__('Credits Sent To ' . $_POST['manage_credits_user'] . '!')) . "';
                        </script>
                        ";
                    }
                }
                break;
            //---------------------------------------------------//
            case 'disable_credits':
                update_site_option('classifieds_credits_enabled', false );
                echo "
                <script type='text/javascript'>
                    window.location='admin.php?page=classifieds_credits&updated=true&updatedmsg=" . urlencode( __('Credits successfully disabled!') ) . "';
                </script>";
                break;
            //---------------------------------------------------//
            case "3":
                break;
            //---------------------------------------------------//
        }
    } else {
        switch ( $_GET['action'] ) {
            //--default output--------------------------------------------//
            default: ?>
                <h2><?php _e('Enable Credits') ?></h2>
                <p><?php _e('By enabeling credits you will be able to charge your users a preconfigured amount of credits for each ad they place.') ?></p>
                <form name="step_one" method="POST" action="admin.php?page=classifieds_credits&action=enable_credits">
                <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e('Enable Credits &raquo;') ?>" />
                    <input type="submit" name="Cancel" value="<?php _e('Cancel &raquo;') ?>" />
                </p>
                </form>
                <?php break;
            //--enable credits--------------------------------------------//
            case 'enable_credits':
                update_site_option('classifieds_credits_enabled', true );
                echo "
                <script type='text/javascript'>
                    window.location='admin.php?page=classifieds_credits&updated=true&updatedmsg=" . urlencode( __('Credits successfully enabled!') ) . "';
                </script>
                ";
                break;
        }
    }
	echo '</div>';
}

function classifieds_page_credits_output() {
    
	global $wpdb, $wp_roles, $current_user, $classifieds_credits_singular, $classifieds_credits_plural;
	if( !current_user_can('read') ) {
		echo "<p>Nice Try...</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
		classifieds_user_credit_check();
		echo '<h2>' . __('Manage your credits') . '</h2>';
		echo '<p>' . __('You currently have ') . $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") . __(' credits for use with this blog') . '</p>';
		echo '<h3>' . __('Add credits') . '</h3>';
		$tmp_amount = get_site_option( "classifieds_cost_per_credit" );
		$tmp_amount = $tmp_amount . ' ' . get_site_option( "classifieds_currency" );
		echo '<p>' . __('Credits are ') . $tmp_amount . __(' each') . '</p>';

		classifieds_buy_output();
        
		echo '<br />';
		if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE user_ID = '" . $current_user->ID . "'") == 0){
			//do nothing
		} else {
			echo '<h2>' . __('Give credits as a gift') . '</h2>';
			?>
				<form action="admin.php?page=classifieds_credits_management&action=gift" method="post">
				<p><?php _e('Number of credits to send:') ?><label for="number_of_credits_to_send"> 
					<select name="num_credits" id="num_credits">
					<?php
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 5){
						echo'<option value="5">5</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 10){
						echo'<option value="10">10</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 15){
						echo'<option value="15">15</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 20){
						echo'<option value="20">20</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 25){
						echo'<option value="25">25</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 30){
						echo'<option value="30">30</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 35){
						echo'<option value="35">35</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 40){
						echo'<option value="40">40</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 45){
						echo'<option value="45">45</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 50){
						echo'<option value="50">50</option>';
					}
					if ($wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") > 100){
						echo'<option value="100">100</option>';
					}
					echo'<option value="' . $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") . '">' . $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'") . '</option>';

					?>
					</select>
                    </label>
				 </p>
                <p>
                    <label><?php _e('Send to ( Username* ):') ?>
	                    <input name="username" value="" type="text">
                    </label>
                    <br />
                    <?php _e('*Note: This should be the username the person uses to login with and not the name of their blog.') ?>
                </p>

				<p class="submit">
				  <input name="Submit" value="Send Credits &raquo;" type="submit">
				</p>
				</form>
			<?php
		}
		break;
		//---------------------------------------------------//
		case "confirm_gift":
            $check_credits = $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $current_user->ID . "'");
            $check_username = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "users WHERE  user_login = '" . $_GET['username'] . "'");
            $username_id = $wpdb->get_var("SELECT ID FROM " . $wpdb->base_prefix . "users WHERE  user_login = '" . $_GET['username'] . "'");
            $send_credits = $_GET['num_credits'];

            if ($_GET['username'] == ''){
                echo '<h2>' . __('Error') . '</h2>';
                echo '<p>' . __('You must enter a username!') . '</p>';
            } else if ($check_username == 0){
                echo '<h2>' . __('Error') . '</h2>';
                echo '<p>' . __('Invalid Username!') . '</p>';
            } else if ($send_credits > $check_credits){
                echo '<h2>' . __('Error') . '</h2>';
                echo '<p>' . __('You do not have enough credits!') . '</p>';
            } else {
                //--------------------------------//
                    $tmp_new_credits = $check_credits - $send_credits;
                    $wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_credits SET credits = '" . $tmp_new_credits . "' WHERE user_ID = '" . $current_user->ID . "'");
                    //----//
                    //make sure the other user has classifieds installed
                        $tmp2_credits_check = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_credits WHERE  user_ID = '" . $username_id . "'");
                        if ($tmp2_credits_check == 0){
                            $wpdb->query( "INSERT INTO " . $wpdb->base_prefix . "classifieds_credits (user_ID, credits) VALUES ( '" . $username_id . "', '0' )" );
                        }
                    //----//
                    $tmp_username_credits = $wpdb->get_var("SELECT credits FROM " . $wpdb->base_prefix . "classifieds_credits WHERE user_ID = '" . $username_id . "'");
                    $tmp_new_credits_receive = $tmp_username_credits + $send_credits;
                    $wpdb->query( "UPDATE " . $wpdb->base_prefix . "classifieds_credits SET credits = '" . $tmp_new_credits_receive . "' WHERE user_ID = '" . $username_id . "'");

                    $get_current_username = $wpdb->get_var("SELECT user_login FROM " . $wpdb->base_prefix . "users WHERE ID = '" . $current_user->ID . "'");
                    //----//
                    //Send email
                    classifieds_gift_notification( $get_current_username, $_GET['username'], $send_credits );
                    //----//
                //--------------------------------//
                echo '<p>' . __('Gift Sent!') . '</p>';
                echo "<script type='text/javascript'>
                          window.location='admin.php?page=classifieds_credits_management&updated=true&updatedmsg=" . urlencode('Gift Sent!') . "';
                      </script>";
            }
            break;
	}
	echo '</div>';
}
?>
