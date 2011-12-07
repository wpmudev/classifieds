<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $options = $this->get_options( 'general' ); ?>

<div class="wrap">

    <?php screen_icon('options-general'); ?>

    <?php $this->render_admin( 'navigation', array( 'sub' => 'general' ) ); ?>

    <?php $this->render_admin( 'message' ); ?>

    <form action="" method="post">
        <h3><?php _e( 'Form Fields', $this->text_domain ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="field_image_req"><?php _e( 'Image field:', $this->text_domain ); ?></label></th>
                <td>
                    <input type="checkbox" id="field_image_req" name="field_image_req" value="1" <?php echo ( isset( $options['field_image_req'] ) && 1 == $options['field_image_req'] ) ? 'checked' : ''; ?> />
                    <span class="description"><?php _e( 'not required', $this->text_domain ); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="field_image_def"><?php _e( 'Use default image (URL):', $this->text_domain ); ?></label></th>
                <td>
                    <input type="text" id="field_image_def" name="field_image_def" size="70" value="<?php echo ( isset( $options['field_image_def'] ) && '' != $options['field_image_def'] ) ? $options['field_image_def'] : ''; ?>" />
                    <br />
                    <span class="description"><?php _e( 'this image will be show for all ads without images', $this->text_domain ); ?></span>
                </td>
            </tr>
        </table>

        <p class="submit">
            <?php wp_nonce_field( 'verify' ); ?>
            <input type="hidden" name="key" value="general" />
            <input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" />
        </p>

    </form>

</div>