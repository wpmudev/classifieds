<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<div class="profile">
    
    <form class="standard-form base" method="post" action="" enctype="multipart/form-data">
        
        <div class="editfield">
            <label for="title"><?php _e( 'Title', $this->text_domain ); ?></label>
            <input type="text" value="" id="title" name="title">
            <p class="description"><?php _e( 'Enter title here.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield alt">
            <label for="description"><?php _e( 'Description', $this->text_domain ); ?></label>
            <textarea id="description" name="description" cols="40" rows="5"></textarea>
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
                                <option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
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
            <label for="image"><?php _e( 'Select Featured Image', $this->text_domain ); ?></label>
            <p id="featured-image">
                <input type="file" id="image" name="image">
                <input type="hidden" value="featured-image" id="action" name="action">
            </p>
        </div>

        <div class="editfield">
            <div class="radio">
                <span class="label"><?php _e( 'Ad Status' );  ?></span>
                <div id="status-box">
                    <label><input type="radio" value="publish" name="status"><?php _e( 'Published', $this->text_domain ); ?></label>
                    <label><input type="radio" value="draft" name="status"><?php _e( 'Draft', $this->text_domain ); ?></label>
                </div>
            </div>
            <p class="description"><?php _e( 'Check a status for your Ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <?php $this->render_front('buddypress/members/single/classifieds/custom-fields'); ?>
        </div>

        <div class="submit">
            <input type="submit" value="Save Changes " name="save">
        </div>
        
    </form>
    
</div>