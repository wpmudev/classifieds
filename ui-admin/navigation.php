<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php screen_icon('options-general'); ?>
<h2><?php echo sprintf( __('Classifieds Settings %s', $this->text_domain), CF_VERSION);?></h2>

<h2>

	<?php if($page == 'classifieds_settings'): ?>

	<a class="nav-tab <?php if ( $tab == 'general' || empty($tab))  echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_settings&tab=general');?>" ><?php _e( 'General', $this->text_domain ); ?></a>
	<a class="nav-tab <?php if ( $tab == 'capabilities' || empty($tab))  echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_settings&tab=capabilities');?>" ><?php _e( 'Capabilities', $this->text_domain ); ?></a>
	<a class="nav-tab <?php if ( $tab == 'payments' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_settings&tab=payments'); ?>" ><?php _e( 'Payments', $this->text_domain ); ?></a>
	<a class="nav-tab <?php if ( $tab == 'payment-types' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_settings&tab=payment-types'); ?>"><?php _e( 'Payment Types', $this->text_domain ); ?></a>

	<?php if ( class_exists( 'affiliateadmin' ) ):?>
	<a id="dr-settings_affiliate" class="nav-tab <?php if ( $tab == 'affiliate' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_settings&tab=affiliate');?>"><?php _e( 'Affiliate', $this->text_domain ); ?></a>
	<?php endif; ?>


	<a class="nav-tab <?php if ( $tab == 'shortcodes' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_settings&tab=shortcodes' ); ?>"><?php _e( 'Shortcodes', $this->text_domain ); ?></a>

	<?php endif; ?>

	<?php if( $page == 'classifieds_credits'):?>
	<a class="nav-tab <?php if ( $tab == 'my-credits' || empty( $tab) )  echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_credits&tab=my-credits'); ?>" ><?php _e( 'My Credits', $this->text_domain ); ?></a>
	<?php
	$current_user = wp_get_current_user();
	if(! empty($current_user->ID) && $current_user->has_cap('manage_options')): ?>
	<a class="nav-tab <?php if ( $tab == 'send-credits' || empty( $tab) )  echo 'nav-tab-active'; ?>" href="<?php echo esc_attr('admin.php?page=classifieds_credits&tab=send-credits'); ?>" ><?php _e( 'Send Credits', $this->text_domain ); ?></a>
	<?php endif; ?>

	<?php endif; ?>

</h2>

<div class="clear"></div>