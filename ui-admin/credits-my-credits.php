<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options('checkout'); ?>

<div class="wrap">
    <?php screen_icon('options-general'); ?>

    <?php $this->render_admin( 'navigation', array( 'sub' => 'my_credits' ) ); ?>

    <?php $this->render_admin( 'message' ); ?>

    <form action="" method="post">

        <h3><?php _e( 'Available Credits', $this->text_domain ); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <label for="available_credits"><?php _e('Available Credits', $this->text_domain ) ?></label>
                </th>
                <td>
                    <input type="text" id="available_credits" class="small-text" name="available_credits" value="<?php echo $this->get_user_credits(); ?>" disabled="disabled" />
                    <span class="description"><?php _e( 'All of your currently available credits.', $this->text_domain ); ?></span>
                </td>
            </tr>
        </table>
        
    </form>

    <form action="" method="post" class="purchase_credits" >
        <h3><?php _e( 'Purchase Additional Credits', $this->text_domain ); ?></h3>
        <table class="form-table">
            <tr>
                <th>
                    <label for="purchase_credits"><?php _e('Purchase Additional Credits', $this->text_domain ) ?></label>
                </th>
                <td>
                    <p class="submit">
                        <?php wp_nonce_field('verify'); ?>
                        <input type="submit" class="button-secondary" name="purchase" value="<?php _e( 'Purchase', $this->text_domain ); ?>" />
                    </p>
                </td>
            </tr>
        </table>

    </form>

        <?php $credits_log = $this->get_user_credits_log(); ?>
        <h3><?php _e( 'Purchase History', $this->text_domain ); ?></h3>
        <table class="form-table">
            <?php if ( is_array( $credits_log ) ): ?>
                <?php foreach ( $credits_log as $log ): ?>
                    <tr>
                        <th>
                            <label for="available_credits"><?php _e('Purchase Date:', $this->text_domain ) ?> <?php echo $this->format_date( $log['date'] ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="available_credits" class="small-text" name="available_credits" value="<?php echo $log['credits']; ?>" disabled="disabled" />
                            <span class="description"><?php _e( 'Credits purchased.', $this->text_domain ); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                    <?php echo $credits_log; ?>
            <?php endif; ?>
        </table>
            
</div>