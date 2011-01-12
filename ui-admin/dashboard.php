<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $taxonomies = get_object_taxonomies( $this->post_type ); ?>

<div class="wrap">
    <?php screen_icon('index'); ?>
    <h2>
        <?php _e( 'Classifieds Dashboard', $this->text_domain ); ?>
        <a class="button add-new-h2" href="post-new.php?post_type=<?php echo $this->post_type; ?>"><?php _e( 'Create New Ad', $this->text_domain ); ?></a>
    </h2>
    
    <h3><?php _e( 'Active Ads', $this->text_domain ); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'ID', $this->text_domain ); ?></th>
                <th><?php _e( 'Title', $this->text_domain ); ?></th>
                <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                <th><?php _e( 'Ends', $this->text_domain ); ?></th>
                <th><?php _e( 'Image', $this->text_domain ); ?></th>
                <th><?php _e( 'Actions', $this->text_domain ); ?></th>
            </tr>
        </thead>
        <?php $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'publish' ) ); ?>
        <?php foreach ( $posts as $post ): ?>
        <?php $terms = wp_get_object_terms( $post->ID, $taxonomies ); ?>
        <tbody>
            <tr>
                <td><?php echo $post->ID; ?></td>
                <td><?php echo $post->post_title; ?></td>
                <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?> </td>
                <td><?php echo get_post_meta( $post->ID, $this->custom_fields['duration'], true ); ?></td>
                <td><?php echo get_the_post_thumbnail( $post->ID, array( 16, 16 ) ); ?></td>
                <td>
                    <a href="post.php?post=<?php echo $post->ID; ?>&action=edit" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Edit Ad', $this->text_domain ); ?></a> <span class="separators-<?php echo $post->ID; ?>"> | </span>
                    <a href="javascript:classifieds.toggle_end('<?php echo $post->ID; ?>');" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'End Ad', $this->text_domain ); ?></a> <span class="separators-<?php echo $post->ID; ?>"> | </span>
                    <a href="javascript:classifieds.toggle_delete('<?php echo $post->ID; ?>');" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Delete Ad', $this->text_domain ); ?></a>
                    <form action="" method="post" id="form-<?php echo $post->ID; ?>" class="cf-form">
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="javascript:classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
                    </form>
                </td>
            </tr>
        </tbody>
        <?php endforeach; ?>
    </table>

    <h3><?php _e( 'Saved Ads', $this->text_domain ); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'ID', $this->text_domain ); ?></th>
                <th><?php _e( 'Title', $this->text_domain ); ?></th>
                <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                <th><?php _e( 'Ends', $this->text_domain ); ?></th>
                <th><?php _e( 'Image', $this->text_domain ); ?></th>
                <th><?php _e( 'Actions', $this->text_domain ); ?></th>
            </tr>
        </thead>
        <?php $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'draft' ) ); ?>
        <?php foreach ( $posts as $post ): ?>
        <?php $terms = wp_get_object_terms( $post->ID, $taxonomies ); ?>
        <tbody>
            <tr>
                <td><?php echo $post->ID; ?></td>
                <td><?php echo $post->post_title; ?></td>
                <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?> </td>
                <td><?php echo get_post_meta( $post->ID, $this->custom_fields['duration'], true ); ?></td>
                <td><?php echo get_the_post_thumbnail( $post->ID, array( 16, 16 ) ); ?></td>
                <td>
                    <a href="post.php?post=<?php echo $post->ID; ?>&action=edit" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Edit Ad', $this->text_domain ); ?></a> <span class="separators-<?php echo $post->ID; ?>"> | </span>
                    <a href="javascript:classifieds.toggle_publish('<?php echo $post->ID; ?>');" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Publish Ad', $this->text_domain ); ?></a> <span class="separators-<?php echo $post->ID; ?>"> | </span>
                    <a href="javascript:classifieds.toggle_delete('<?php echo $post->ID; ?>');" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Delete Ad', $this->text_domain ); ?></a>
                    <form action="" method="post" id="form-<?php echo $post->ID; ?>" class="cf-form">
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
                        <select name="publish_duration">
                            <option value="1"><?php _e( '1 Week',  $this->text_domain ); ?></option>
                            <option value="2"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                            <option value="3"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                            <option value="4"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
                        </select>
                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="javascript:classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
                    </form>
                </td>
            </tr>
        </tbody>
        <?php endforeach; ?>
    </table>

    <h3><?php _e( 'Ended Ads', $this->text_domain ); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'ID', $this->text_domain ); ?></th>
                <th><?php _e( 'Title', $this->text_domain ); ?></th>
                <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                <th><?php _e( 'Ends', $this->text_domain ); ?></th>
                <th><?php _e( 'Image', $this->text_domain ); ?></th>
                <th><?php _e( 'Actions', $this->text_domain ); ?></th>
            </tr>
        </thead>
        <?php $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'private' ) ); ?>
        <?php foreach ( $posts as $post ): ?>
        <?php $terms = wp_get_object_terms( $post->ID, $taxonomies ); ?>
        <tbody>
            <tr>
                <td><?php echo $post->ID; ?></td>
                <td><?php echo $post->post_title; ?></td>
                <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?> </td>
                <td><?php echo get_post_meta( $post->ID, $this->custom_fields['duration'], true ); ?></td>
                <td><?php echo get_the_post_thumbnail( $post->ID, array( 16, 16 ) ); ?></td>
                <td>
                    <a href="post.php?post=<?php echo $post->ID; ?>&action=edit" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Edit Ad', $this->text_domain ); ?></a> <span class="separators-<?php echo $post->ID; ?>"> | </span>
                    <a href="javascript:classifieds.toggle_publish('<?php echo $post->ID; ?>');" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Renew Ad', $this->text_domain ); ?></a> <span class="separators-<?php echo $post->ID; ?>"> | </span>
                    <a href="javascript:classifieds.toggle_delete('<?php echo $post->ID; ?>');" class="action-links-<?php echo $post->ID; ?>"><?php _e( 'Delete Ad', $this->text_domain ); ?></a>
                    <form action="" method="post" id="form-<?php echo $post->ID; ?>" class="cf-form">
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
                        <select name="publish_duration">
                            <option value="1"><?php _e( '1 Week',  $this->text_domain ); ?></option>
                            <option value="2"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                            <option value="3"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                            <option value="4"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
                        </select>
                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="javascript:classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
                    </form>
                </td>
            </tr>
        </tbody>
        <?php endforeach; ?>
    </table>
</div>