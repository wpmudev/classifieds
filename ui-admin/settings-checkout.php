<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options('checkout'); ?>

<div class="wrap">
    <?php screen_icon('options-general'); ?>

    <?php $this->render_admin( 'navigation', array( 'sub' => 'checkout' ) ); ?>

    <?php $this->render_admin( 'message' ); ?>

    <form action="" method="post">

        <table class="form-table">
            <tr>
                <th>
                    <label for="annual_cost"><?php _e('Annual Payment Option', $this->text_domain ) ?></label>
                </th>
                <td>
                    <input type="text" id="annual_cost" class="small-text" name="annual_cost" value="<?php echo $options['annual_cost']; ?>" />
                    <span class="description"><?php _e( 'Cost of "Annual" service.', $this->text_domain ); ?></span>
                    <br /><br />
                    <input type="text" name="annual_txt" value="<?php echo $options['annual_txt']; ?>" />
                    <span class="description"><?php _e( 'Text of "Annual" service.', $this->text_domain ); ?></span>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="one_time_cost"><?php _e( 'One Time Payment Option', $this->text_domain ) ?></label>
                </th>
                <td>
                    <input type="text" id="one_time_cost" class="small-text" name="one_time_cost" value="<?php echo $options['one_time_cost']; ?>" />
                    <span class="description"><?php _e( 'Cost of "One Time" service.', $this->text_domain ); ?></span>
                    <br /><br />
                    <input type="text" name="one_time_txt" value="<?php echo $options['one_time_txt']; ?>" />
                    <span class="description"><?php _e( 'Text of "One Time" service.', $this->text_domain ); ?></span>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="tos_txt"><?php _e('Terms of Service Text', $this->text_domain ) ?></label>
                </th>
                <td>
                    <textarea name="tos_txt" id="tos_txt" rows="15" cols="50"><?php echo $options['tos_txt']; ?></textarea>
                    <br />
                    <span class="description"><?php _e( 'Text for "Terms of Service"', $this->text_domain ); ?></span>
                </td>
            </tr>
        </table>

        <p class="submit">
            <?php wp_nonce_field('verify'); ?>
            <input type="hidden" name="key" value="checkout" />
            <input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" />
        </p>
        
    </form>

</div>