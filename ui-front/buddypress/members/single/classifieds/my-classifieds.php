<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'publish' ) ); ?>

<?php foreach ( $posts as $post ): ?>
    <?php $terms = wp_get_object_terms( $post->ID, $taxonomies ); ?>

    <div class="bp-cf-ad">
        <div class="bp-cf-image"><?php echo get_the_post_thumbnail( $post->ID, array( 200, 150 ) ); ?></div>
        <div class="bp-cf-info">
            <table>
                <tr>
                    <th><?php _e( 'Title', $this->text_domain ); ?></th>
                    <td><?php echo $post->post_title; ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                    <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?></td>
                <tr>
                <tr>
                    <th><?php _e( 'Ends', $this->text_domain ); ?></th>
                    <td><?php echo get_post_meta( $post->ID, $this->custom_fields['duration'], true ); ?></td>
                </tr>
            </table>
        </div>

        <form action="" method="post">
            <input type="submit" name="bp-view-ad" value="<?php _e('View Ad', $this->text_domain ); ?>" />
            <input type="submit" name="bp-edit-ad" value="<?php _e('Edit Ad', $this->text_domain ); ?>" />
            <input type="submit" name="bp-delete-ad" value="<?php _e('Delete Ad', $this->text_domain ); ?>" />
        </form>

    </div>

<?php endforeach; ?>