<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
/**
 * The template for displaying BuddyPress Classifieds component - Create New page.
 * You can override this file in your active theme.
 *
 * @package Classifieds
 * @subpackage UI Front BuddyPress
 * @since Classifieds 2.0
 */
?>

<?php
/* Build messages */
if ( !$this->form_valid ) {
    $msg = __( 'Please make sure you fill in all required fields before saving.', $this->text_domain );
    $class = 'error';
}
?>

<div class="profile">
    
    <?php if ( $msg ): ?>
    <div class="<?php echo $class; ?>" id="message">
		<p><?php echo $msg; ?></p>
	</div>
    <?php endif; ?>

    <form class="standard-form base" method="post" action="" enctype="multipart/form-data">
        
        <div class="editfield">
            <label for="title"><?php _e( 'Title', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
            <input type="text" value="<?php echo $_POST['title']; ?>" id="title" name="title">
            <p class="description"><?php _e( 'Enter title here.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield alt">
            <label for="description"><?php _e( 'Description', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
            <textarea id="description" name="description" cols="40" rows="5"><?php echo $_POST['description']; ?></textarea>
            <p class="description"><?php _e( 'The main description of your ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <label for="terms"><?php _e( 'Terms ( Categories / Tags )', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
            <table class="cf-terms">
                <tr>
                <?php foreach ( $this->taxonomy_objects as $taxonomy_name => $taxonomy_object ): ?>
                    <?php $terms = get_terms( $taxonomy_name, array( 'hide_empty' => 0 ) ); ?>
                    <td>
                        <select id="terms" name="terms[<?php echo $taxonomy_name; ?>][]" multiple="multiple">
                            <optgroup label="<?php echo $taxonomy_object->labels->name; ?>">
                                <?php foreach ( $terms as $term ): ?>
                                <option value="<?php echo $term->slug; ?>" <?php if ( is_array( $_POST['terms'][$taxonomy_name] ) && in_array( $term->slug, $_POST['terms'][$taxonomy_name] ) ) echo 'selected="selected"' ?>><?php echo $term->name; ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </td>
                    <?php endforeach; ?>
                    </tr>
            </table>
            <p class="description"><?php _e( 'Select category for your ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <label for="image"><?php _e( 'Select Featured Image', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
            <p id="featured-image">
                <input type="file" id="image" name="image">
                <input type="hidden" value="featured-image" id="action" name="action">
            </p>
        </div>

        <div class="editfield">
            <div class="radio">
                <span class="label"><?php _e( 'Ad Status' );  ?> (<?php _e( 'required', $this->text_domain ); ?>)</span>
                <div id="status-box">
                    <label><input type="radio" value="publish" name="status" <?php if ( $_POST['status'] == 'publish' ) echo 'checked="checked"'; ?>><?php _e( 'Published', $this->text_domain ); ?></label>
                    <label><input type="radio" value="draft" name="status" <?php if ( $_POST['status'] == 'draft' ) echo 'checked="checked"'; ?>><?php _e( 'Draft', $this->text_domain ); ?></label>
                </div>
            </div>
            <p class="description"><?php _e( 'Check a status for your Ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <?php $this->render_front('members/single/classifieds/custom-fields'); ?>
        </div>

        <div class="submit">
            <input type="submit" value="Save Changes " name="save">
        </div>
        
    </form>
    
</div>