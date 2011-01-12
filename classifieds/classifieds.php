<?php

/**
 * Classifieds LOADER
 * Includes Classifieds plugin files
 *
 * @package Classifieds
 * @subpackage Loader
 * @since 1.1.0
 */

include_once 'classifieds-core.php';
include_once 'classifieds-shortcode.php';
include_once 'classifieds-widgets.php';
include_once 'classifieds-frontend.php';
include_once 'classifieds-credits.php';
include_once 'classifieds-credits-payment-module-paypal.php';

/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
function classifieds_buddypress() {
    include_once( dirname( __FILE__ ) . '/classifieds-buddypress.php' );
}
add_action( 'bp_init', 'classifieds_buddypress' );

?>
