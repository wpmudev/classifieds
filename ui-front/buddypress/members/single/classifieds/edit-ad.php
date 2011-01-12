<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
/**
 * The template for displaying BuddyPress Classifieds component - Edit Ad page.
 * You can override this file in your active theme.
 *
 * @package Classifieds
 * @subpackage UI Front BuddyPress
 * @since Classifieds 2.0
 */
?>

<?php
global $bp;
$post = get_post( $post_id );
$post_terms = wp_get_object_terms( $post->ID, $this->taxonomy_names );
?>

<div class="profile">
    
    <form class="standard-form base" method="post" action="" enctype="multipart/form-data">
        
        <div class="editfield">
            <label for="title"><?php _e( 'Title', $this->text_domain ); ?></label>
            <input type="text" value="<?php echo $post->post_title; ?>" id="title" name="title">
            <p class="description"><?php _e( 'Enter title here.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield alt">
            <label for="description"><?php _e( 'Description', $this->text_domain ); ?></label>
            <textarea id="description" name="description" cols="40" rows="5"><?php echo $post->post_content; ?></textarea>
            <p class="description"><?php _e( 'The main description of your ad.', $this->text_domain ); ?></p>
        </div>
        
        <div class="editfield">
            <label for="terms"><?php _e( 'Terms ( Categories / Tags )', $this->text_domain ); ?></label>
            <table class="cf-terms">
                <tr>
                <?php foreach ( $this->taxonomy_objects as $taxonomy_name => $taxonomy_object ): ?>
                    <?php $terms = get_terms( $taxonomy_name, array( 'hide_empty' => 0 ) ); ?>
                    <td>
                        <select id="terms" name="terms[<?php echo $taxonomy_name; ?>][]" multiple="multiple">
                            <optgroup label="<?php echo $taxonomy_object->labels->name; ?>">
                                <?php foreach ( $terms as $term ): ?>
                                <option value="<?php echo $term->slug; ?>" <?php foreach ( $post_terms as $post_term ) { if ( $post_term->term_id == $term->term_id  ) echo 'selected="selected"'; } ?>><?php echo $term->name; ?></option>
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
            <?php echo get_the_post_thumbnail( $post->ID, array( 200, 150 ) ); ?>
            <label for="image"><?php _e( 'Change Featured Image', $this->text_domain ); ?></label>
            <p id="featured-image">
                <input type="file" id="image" name="image">
                <input type="hidden" value="featured-image" id="action" name="action">
            </p>
        </div>

        <div class="editfield">
            <div class="radio">
                <span class="label"><?php _e( 'Ad Status' );  ?></span>
                <div id="status-box">
                    <label><input type="radio" value="publish" name="status" <?php if ( $post->post_status == 'publish' ) echo 'checked="checked"'; ?>><?php _e( 'Published', $this->text_domain ); ?></label>
                    <label><input type="radio" value="draft" name="status" <?php if ( $post->post_status == 'draft' ) echo 'checked="checked"'; ?>><?php _e( 'Draft', $this->text_domain ); ?></label>
                </div>
            </div>
            <p class="description"><?php _e( 'Check a status for your Ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <?php $this->render_front('members/single/classifieds/custom-fields', array( 'post' => $post )); ?>
        </div>

        <div class="submit">
            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
            <input type="hidden" name="post_title" value="<?php echo $post->post_title; ?>" />
            <input type="hidden" name="url" value="<?php echo $post->guid; ?>" />
            <input type="submit" value="Save Changes " name="update">
        </div>
        
    </form>
    
</div>