<?php

/**
 * Classifieds BuddyPress
 * Handles the overall frontend output of the plugin
 *
 * @package BuddyPress
 * @subpackage Frontend
 * @since 1.1.1
 */

/*
 * Setup classifieds navigation and subnavigation items
 */
function classifieds_bp_add_settings_nav() {
	global $bp;

	/* Set up classifieds as a sudo-component for identification and nav selection */
	$bp->classifieds->slug = 'classifieds';
    $user_domain = ( !empty( $bp->displayed_user->domain ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
	$classifieds_link = $user_domain . $bp->classifieds->slug . '/';

	/* Add the settings navigation item */
	bp_core_new_nav_item( array( 'name' => __('Classifieds', 'buddypress'), 'slug' => $bp->classifieds->slug, 'position' => 100, 'show_for_displayed_user' => true, 'screen_function' => 'classifieds_bp_page', 'default_subnav_slug' => 'my-classifieds' ) );
	bp_core_new_subnav_item( array( 'name' => __( 'My Classifieds', 'buddypress' ), 'slug' => 'my-classifieds', 'parent_url' => $classifieds_link, 'parent_slug' => $bp->classifieds->slug, 'screen_function' => 'classifieds_bp_page', 'position' => 10, 'user_has_access' => true ) );

}
add_action( 'wp', 'classifieds_bp_add_settings_nav', 2 );
add_action( 'admin_menu', 'classifieds_bp_add_settings_nav', 2 );

/*
 * Load theme template file for page classifieds
 */
function classifieds_bp_page() {
	bp_core_load_template( 'members/single/plugins', true );
}

function classifieds_print_user_classifieds() {
    global $wpdb, $bp, $current_site;

    /* Construct query and fetch ads info  */
    $query = "SELECT ad_ID, ad_title FROM {$wpdb->base_prefix}classifieds_ads 
              WHERE ad_status = 'active' AND ad_user_ID = {$bp->displayed_user->id} 
              ORDER BY ad_ID DESC";
    $result = $wpdb->get_results( $query, ARRAY_A ); ?>

    <ul class="bp-classifieds-ads">
    <?php if ( count( $result ) > 0 ): ?>
        <?php foreach ( $result as $result ): ?>
        <li>
            <a href="<?php if ( is_multisite() ) echo 'http://' . $current_site->domain . $current_site->path . CLASSIFIEDS_PATH; else echo get_bloginfo('url') . '/' . CLASSIFIEDS_PATH; ?>?ad=<?php echo $result['ad_ID']; ?>"><strong><?php echo $result['ad_title']; ?></strong></a><br>
            <a href="<?php if ( is_multisite() ) echo 'http://' . $current_site->domain . $current_site->path . CLASSIFIEDS_PATH; else echo get_bloginfo('url') . '/' . CLASSIFIEDS_PATH; ?>?ad=<?php echo $result['ad_ID']; ?>"><img src="<?php echo get_bloginfo('url') . "/wp-content/classifieds-images/" . $result['ad_ID'] . "-200.png"; ?>" /></a>
        </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li><?php _e('Nothing to display...', 'classifieds'); ?></li>
    <?php endif; ?>
    </ul> <?php
}
add_action('bp_template_content', 'classifieds_print_user_classifieds');

function classifieds_bp_admin_menu_item() {
    if ( bp_is_my_profile() ) { ?>
        <li>
            <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=classifieds_new">Create New Add <span style="color:#888">(wp-admin)</span></a>
        </li>
        <li>
            <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=classifieds">Manage Ads <span style="color:#888">(wp-admin)</span></a>
        </li>
        <?php if ( get_site_option('classifieds_credits_enabled') ): ?>
        <li>
            <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=classifieds_credits_management">My Credits <span style="color:#888">(wp-admin)</span></a>
        </li>
        <?php endif;
    }
}
add_action('bp_member_plugin_options_nav', 'classifieds_bp_admin_menu_item');

/*
 * Show member profile link on ad preview
 */
function classifieds_bp_ad_info_member( $tmp_ad_user_ID ) {
    ?>
    <td style="background-color:#F2F2EA; text-align:left;" width="20%">Profile:</td>
    <td style="background-color:#F2F2EA; text-align:left;" width="80%">
        <a href="<?php echo bp_core_get_user_domain( $tmp_ad_user_ID ) ?>"><?php echo bp_core_get_user_domain( $tmp_ad_user_ID ); ?></a>
    </td>
    <?php
}
add_action( 'classifieds_bp_ad_info', 'classifieds_bp_ad_info_member' );
?>