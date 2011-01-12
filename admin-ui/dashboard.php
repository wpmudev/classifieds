<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<div class="wrap classifieds">
<?php
global $wpdb, $current_user, $current_site, $classifieds_credits_singular, $classifieds_credits_plural;

if ( is_multisite() )
    $classifieds_path = 'http://' . $current_site->domain . $current_site->path . CLASSIFIEDS_PATH ;
else
    $classifieds_path = get_bloginfo('url') . '/' . CLASSIFIEDS_PATH;

if ( isset( $_GET['updated'] ) ) {
    ?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
}

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
            <div class="cf-edit-ad" style="float:left; margin-right: 50px;">
                <h2><?php _e('Edit Ad by ID') ?></h2>
                <form name="form1" method="POST" action="admin.php?page=classifieds&action=edit_ad">
                    <table class="optiontable">
                        <tr valign="top">
                            <th scope="row"><?php _e('Ad ID:') ?></th>
                            <td><input type="text" name="aid" value=""  />
                            <br />
                            <?php _e('Enter the ID of the ad you wish to modify.' ); ?>
                            </td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" /></p>
                </form>
            </div>
            <div class="cf-delete-ad" style="float:left">
                <h2><?php _e('Delete Ad by ID') ?></h2>
                <form name="form-del" method="POST" action="admin.php?page=classifieds&action=delete_ad">
                    <table class="optiontable">
                        <tr valign="top">
                            <th scope="row"><?php _e('Ad ID:') ?></th>
                            <td><input type="text" name="aid" value=""  />
                            <br />
                            <?php _e('Enter the ID of the ad you wish to delete. ( Warning cannot be undone! )' ); ?>
                            </td>
                        </tr>
                    </table>
                    <p class="submit"><input type="submit" name="Submit" value="<?php _e('Continue &raquo;') ?>" /></p>
                </form>
            </div>
        <?php endif;
        break;
    //---------------------------------------------------//
    case "delete_ad":
        if (current_user_can('edit_users')) {
            classifieds_delete_ad($_GET['aid']);
            classifieds_delete_ad($_POST['aid']);
            echo "
            <script type='text/javascript'>
                window.location='admin.php?page=classifieds&updated=true&updatedmsg=" . urlencode(__('Ad Removed!')) . "';
            </script>
            ";
        }
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
?>
</div>