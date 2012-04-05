<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<h2>

	<?php if($page == 'classifieds_settings'): ?>

	<a class="nav-tab <?php if ( $tab == 'general' || empty($tab))  echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_settings&tab=general"><?php _e( 'General', $this->text_domain ); ?></a>
	<a class="nav-tab <?php if ( $tab == 'payments' ) echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_settings&tab=payments"><?php _e( 'Payments', $this->text_domain ); ?></a>
	<a class="nav-tab <?php if ( $tab == 'payment-types' ) echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_settings&tab=payment-types"><?php _e( 'Payment Types', $this->text_domain ); ?></a>
	<?php endif; ?>

	<?php if( $page == 'classifieds_credits'):?>
	<a class="nav-tab <?php if ( $tab == 'my-credits' || empty( $tab) )  echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_credits&tab=my-credits"><?php _e( 'My Credits', $this->text_domain ); ?></a>
	<?php endif; ?>
	
</h2>

<div class="clear"></div>