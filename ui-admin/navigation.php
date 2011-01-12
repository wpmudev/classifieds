<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<h2>
    <?php if ( $_GET['tab'] == 'general' || $_GET['tab'] == 'payments' || ( $_GET['page'] == 'classifieds_settings' && empty( $_GET['tab'] ) ) ): ?>
        <a class="nav-tab <?php if ( $_GET['tab'] == 'general' || empty( $_GET['tab'] ) )  echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_settings&tab=general&sub=credits"><?php _e( 'General', $this->text_domain ); ?></a>
        <a class="nav-tab <?php if ( $_GET['tab'] == 'payments' ) echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_settings&tab=payments&sub=paypal"><?php _e( 'Payments', $this->text_domain ); ?></a>
    <?php elseif (  $_GET['tab'] == 'my_credits' || $_GET['tab'] == 'send_credits' || ( $_GET['page'] == 'classifieds_credits' && empty( $_GET['tab'] ) ) ): ?>
        <a class="nav-tab <?php if ( $_GET['tab'] == 'my_credits' || empty( $_GET['tab'] ) )  echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_credits&tab=my_credits"><?php _e( 'My Credits', $this->text_domain ); ?></a>
        <?php /* <a class="nav-tab <?php if ( $_GET['tab'] == 'send_credits' ) echo 'nav-tab-active'; ?>" href="admin.php?page=classifieds_credits&tab=send_credits"><?php _e( 'Send Credits', $this->text_domain ); ?></a> */ ?>
    <?php endif; ?>
</h2>

<?php if ( $sub == 'credits' || $sub == 'checkout' ): ?>
<ul>
    <li class="subsubsub"><h3><a class="<?php if ( $_GET['sub'] == 'credits' || empty( $_GET['tab'] ) )   echo 'current'; ?>" href="admin.php?page=classifieds_settings&tab=general&sub=credits"><?php _e( 'Credits', $this->text_domain ); ?></a> | </h3></li>
    <li class="subsubsub"><h3><a class="<?php if ( $_GET['sub'] == 'checkout' ) echo 'current'; ?>" href="admin.php?page=classifieds_settings&tab=general&sub=checkout"><?php _e( 'Checkout', $this->text_domain ); ?></a></h3></li>
</ul>
<?php endif; ?>

<?php if ( $sub == 'paypal' || $sub == 'authorizenet'  ): ?>
<ul>
    <li class="subsubsub"><h3><a class="<?php if ( $_GET['sub'] == 'paypal' )       echo 'current'; ?>" href="admin.php?page=classifieds_settings&tab=payments&sub=paypal"><?php _e( 'PayPal Express', $this->text_domain ); ?></a> | </h3></li>
    <li class="subsubsub"><h3><a class="<?php if ( $_GET['sub'] == 'authorizenet' ) echo 'current'; ?>" href="admin.php?page=classifieds_settings&tab=payments&sub=authorizenet"><?php _e( 'Authorize.net', $this->text_domain ); ?></a></h3></li>
</ul>
<?php endif; ?>

<div class="clear"></div>