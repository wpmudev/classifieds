<?php global $dp_global; ?>


<?php if ( $dp_global->checkout->paypal_express->current_step == 'payment_method_submit' ): ?>

    <?php if ( $dp_global->checkout->paypal_express->payment_method == 'cc' ): ?>

        <?php locate_template( array( 'checkout/checkout-cc-details.php' ), true ); ?>

    <?php endif; ?>

<?php elseif ( $dp_global->checkout->paypal_express->current_step == 'confitm_payment_submit' ): ?>

    <?php locate_template( array( 'checkout/checkout-success-proceed.php' ), true ); ?>

<?php endif; ?>

<div class="clear"></div>