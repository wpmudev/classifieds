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
                <th><?php _e( 'Expiration Date', $this->text_domain ); ?></th>
                <th><?php _e( 'Image', $this->text_domain ); ?></th>
                <th><?php _e( 'Actions', $this->text_domain ); ?></th>
            </tr>
        </thead>
        <tbody>

        <?php $current_user = wp_get_current_user(); ?>
        <?php query_posts( array( 'author' => $current_user->ID, 'post_type' => array( $this->post_type ), 'post_status' => 'publish' ) ); ?>
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
            
            <?php $terms = wp_get_object_terms( get_the_ID(), $taxonomies ); ?>
            <tr>
                <td><?php the_ID(); ?></td>
                <td><?php the_title(); ?></td>
                <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?> </td>
                <td><?php echo $this->get_expiration_date( get_the_ID() ); ?></td>
                <td><?php echo get_the_post_thumbnail( get_the_ID(), array( 16, 16 ) ); ?></td>
                <td>
                    <a href="post.php?post=<?php the_ID(); ?>&action=edit" class="action-links-<?php the_ID(); ?>"><?php _e( 'Edit Ad', $this->text_domain ); ?></a> <span class="separators-<?php the_ID(); ?>"> | </span>
                    <a href="javascript:classifieds.toggle_end('<?php the_ID(); ?>');" class="action-links-<?php the_ID(); ?>"><?php _e( 'End Ad', $this->text_domain ); ?></a> <span class="separators-<?php the_ID(); ?>"> | </span>
                    <a href="javascript:classifieds.toggle_delete('<?php the_ID(); ?>');" class="action-links-<?php the_ID(); ?>"><?php _e( 'Delete Ad', $this->text_domain ); ?></a>
                    <form action="" method="post" id="form-<?php the_ID(); ?>" class="cf-form">
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onClick="classifieds.cancel('<?php the_ID(); ?>'); return false;" />
                    </form>
                </td>
            </tr>
            
        <?php endwhile; ?>
        <?php wp_reset_query(); ?>
            
        </tbody>
    </table>

    <h3><?php _e( 'Saved Ads', $this->text_domain ); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'ID', $this->text_domain ); ?></th>
                <th><?php _e( 'Title', $this->text_domain ); ?></th>
                <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                <th><?php _e( 'Expiration Date', $this->text_domain ); ?></th>
                <th><?php _e( 'Image', $this->text_domain ); ?></th>
                <th><?php _e( 'Actions', $this->text_domain ); ?></th>
            </tr>
        </thead>
        <tbody>

        <?php $current_user = wp_get_current_user(); ?>
        <?php query_posts( array( 'author' => $current_user->ID, 'post_type' => array( $this->post_type ), 'post_status' => 'draft' ) ); ?>
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

        <?php $terms = wp_get_object_terms( get_the_ID(), $taxonomies ); ?>
            <tr>
                <td><?php the_ID(); ?></td>
                <td><?php the_title(); ?></td>
                <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?> </td>
                <td><?php echo $this->get_expiration_date( get_the_ID() ); ?></td>
                <td><?php echo get_the_post_thumbnail( get_the_ID(), array( 16, 16 ) ); ?></td>
                <td>
                    <a href="post.php?post=<?php the_ID(); ?>&action=edit" class="action-links-<?php the_ID(); ?>"><?php _e( 'Edit Ad', $this->text_domain ); ?></a> <span class="separators-<?php the_ID(); ?>"> | </span>
                    <a href="javascript:classifieds.toggle_publish('<?php the_ID(); ?>');" class="action-links-<?php the_ID(); ?>"><?php _e( 'Publish Ad', $this->text_domain ); ?></a> <span class="separators-<?php the_ID(); ?>"> | </span>
                    <a href="javascript:classifieds.toggle_delete('<?php the_ID(); ?>');" class="action-links-<?php the_ID(); ?>"><?php _e( 'Delete Ad', $this->text_domain ); ?></a>
                    <form action="" method="post" id="form-<?php the_ID(); ?>" class="cf-form">
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                        <select name="duration">
                            <option value="1 Week"><?php _e( '1 Week',  $this->text_domain ); ?></option>
                            <option value="2 Weeks"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                            <option value="3 Weeks"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                            <option value="4 Weeks"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
                        </select>
                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="javascript:classifieds.cancel('<?php the_ID(); ?>'); return false;" />
                    </form>
                </td>
            </tr>

        <?php endwhile; ?>
        <?php wp_reset_query(); ?>
            
        </tbody>
    </table>

    <h3><?php _e( 'Ended Ads', $this->text_domain ); ?></h3>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e( 'ID', $this->text_domain ); ?></th>
                <th><?php _e( 'Title', $this->text_domain ); ?></th>
                <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                <th><?php _e( 'Expiration Date', $this->text_domain ); ?></th>
                <th><?php _e( 'Image', $this->text_domain ); ?></th>
                <th><?php _e( 'Actions', $this->text_domain ); ?></th>
            </tr>
        </thead>
        <tbody>

        <?php $current_user = wp_get_current_user(); ?>
        <?php query_posts( array( 'author' => $current_user->ID, 'post_type' => array( $this->post_type ), 'post_status' => 'private' ) ); ?>
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
            
        <?php $terms = wp_get_object_terms( get_the_ID(), $taxonomies ); ?>
            <tr>
                <td><?php the_ID(); ?></td>
                <td><?php the_title(); ?></td>
                <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?> </td>
                <td><?php echo $this->get_expiration_date( get_the_ID() ); ?></td>
                <td><?php echo get_the_post_thumbnail( get_the_ID(), array( 16, 16 ) ); ?></td>
                <td>
                    <a href="post.php?post=<?php the_ID(); ?>&action=edit" class="action-links-<?php the_ID(); ?>"><?php _e( 'Edit Ad', $this->text_domain ); ?></a> <span class="separators-<?php the_ID(); ?>"> | </span>
                    <a href="javascript:classifieds.toggle_publish('<?php the_ID(); ?>');" class="action-links-<?php the_ID(); ?>"><?php _e( 'Renew Ad', $this->text_domain ); ?></a> <span class="separators-<?php the_ID(); ?>"> | </span>
                    <a href="javascript:classifieds.toggle_delete('<?php the_ID(); ?>');" class="action-links-<?php the_ID(); ?>"><?php _e( 'Delete Ad', $this->text_domain ); ?></a>
                    <form action="" method="post" id="form-<?php the_ID(); ?>" class="cf-form">
                        <input type="hidden" name="action" value="" />
                        <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                        <select name="duration">
                            <option value="1 Week"><?php _e( '1 Week',  $this->text_domain ); ?></option>
                            <option value="2 Weeks"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                            <option value="3 Weeks"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                            <option value="4 Weeks"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
                        </select>
                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="javascript:classifieds.cancel('<?php the_ID(); ?>'); return false;" />
                    </form>
                </td>
            </tr>

        <?php endwhile; ?>
        <?php wp_reset_query(); ?>
            
        </tbody>
    </table>
</div>