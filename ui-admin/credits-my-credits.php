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
                    <select id="purchase_credits" name="purchase_credits">
                        <option value="10"><?php _e('10 Credits', $this->text_domain ); ?></option>
                        <option value="20"><?php _e('20 Credits', $this->text_domain ); ?></option>
                        <option value="30"><?php _e('30 Credits', $this->text_domain ); ?></option>
                        <option value="40"><?php _e('40 Credits', $this->text_domain ); ?></option>
                        <option value="50"><?php _e('50 Credits', $this->text_domain ); ?></option>
                        <option value="60"><?php _e('60 Credits', $this->text_domain ); ?></option>
                        <option value="70"><?php _e('70 Credits', $this->text_domain ); ?></option>
                        <option value="80"><?php _e('80 Credits', $this->text_domain ); ?></option>
                        <option value="90"><?php _e('90 Credits', $this->text_domain ); ?></option>
                        <option value="100"><?php _e('100 Credits', $this->text_domain ); ?></option>
                    </select>
                    <p class="submit">
                        <?php wp_nonce_field('verify'); ?>
                        <input type="hidden" name="key" value="purchase" />
                        <input type="submit" class="button-secondary" name="save" value="<?php _e( 'Purchase', $this->text_domain ); ?>" />
                    </p>
                    <br /><br />
                    <span class="description"><?php _e( 'All of your currently available credits.', $this->text_domain ); ?></span>
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