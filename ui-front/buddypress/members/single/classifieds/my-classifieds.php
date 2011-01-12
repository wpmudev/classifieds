<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
/**
 * The template for displaying BuddyPress Classifieds component - My Classifieds page.
 * You can override this file in your active theme.
 *
 * @package Classifieds
 * @subpackage UI Front BuddyPress
 * @since Classifieds 2.0
 */
?>

<?php
global $bp;
/* Get posts based on post_status */
if ( in_array( 'active', $bp->action_variables ) || empty( $bp->action_variables ) ) {
    $sub = 'active';
    $status = 'publish';
}
elseif ( in_array( 'saved',  $bp->action_variables ) ) {
    $sub = 'saved';
    $status = 'draft';
}
elseif ( in_array( 'ended',  $bp->action_variables ) ) {
    $sub = 'ended';
    $status = 'private';
}
?>

<div class="profile">

    <?php if ( bp_is_my_profile() ): ?>
        <ul class="button-nav">
            <li class="<?php if ( in_array( 'active', $bp->action_variables ) || empty( $bp->action_variables ) ) echo 'current'; ?>"><a href="<?php echo $bp->displayed_user->domain . 'classifieds/my-classifieds/active/'; ?>"><?php _e( 'Active Ads', $this->text_domain ); ?></a></li>
            <li class="<?php if ( in_array( 'saved',  $bp->action_variables ) ) echo 'current'; ?>"><a href="<?php echo $bp->displayed_user->domain . 'classifieds/my-classifieds/saved/'; ?>"><?php _e( 'Saved Ads', $this->text_domain ); ?></a></li>
            <li class="<?php if ( in_array( 'ended',  $bp->action_variables ) ) echo 'current'; ?>"><a href="<?php echo $bp->displayed_user->domain . 'classifieds/my-classifieds/ended/'; ?>"><?php _e( 'Ended Ads', $this->text_domain ); ?></a></li>
        </ul>
    <?php endif; ?>
    <div class="clear"></div>



    <?php $current_user = wp_get_current_user();  ?>
    <?php query_posts( array( 'author' => bp_displayed_user_id(), 'post_type' => array( 'classifieds' ), 'post_status' => $status ) ); ?>

    <?php
    /* Build messages */
    if ( !have_posts() ) {
        $msg   = __( 'There were no ads found.', $this->text_domain );
        $class = 'info';
    } elseif ( $action == 'end' ) {
        $msg = sprintf( __( 'Ad "%s" ended.', $this->text_domain ), $post_title );
        $class = 'updated';
    } elseif ( $action == 'renew' ) {
        $msg = sprintf( __( 'Ad "%s" renewed.', $this->text_domain ), $post_title );
        $class = 'updated';
    } elseif ( $action == 'edit' ) {
        $msg = sprintf( __( 'Ad "%s" updated successfully.', $this->text_domain ), $post_title );
        $class = 'updated';
    } elseif ( $action == 'delete' ) {
        $msg = sprintf( __( 'Ad "%s" deleted successfully.', $this->text_domain ), $post_title );
        $class = 'updated';
    }
    ?>

    <?php if ( $msg ): ?>
        <div class="<?php echo $class; ?>" id="message">
            <p><?php echo $msg; ?></p>
        </div>
    <?php endif; ?>

    <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
    
        <div class="cf-ad">
            
            <div class="cf-image"><?php echo get_the_post_thumbnail( get_the_ID(), array( 200, 150 ) ); ?></div>
            <div class="cf-info">
                <table>
                    <tr>
                        <th><?php _e( 'Title', $this->text_domain ); ?></th>
                        <td>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                        <td>
                           <?php $taxonomies = get_object_taxonomies( 'classifieds', 'names' ); ?>
                           <?php foreach ( $taxonomies as $taxonomy ): ?>
                               <?php echo get_the_term_list( get_the_ID(), $taxonomy, '', ', ', '' ) . ' '; ?>
                           <?php endforeach; ?>
                        </td>
                    <tr>
                    <tr>
                        <th><?php _e( 'Expires', $this->text_domain ); ?></th>
                        <td><?php if ( class_exists('Classifieds_Core') ) echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
                    </tr>
                </table>
            </div>
            
            <form action="" method="post" id="action-form-<?php the_ID(); ?>" class="action-form">
                <?php wp_nonce_field('verify'); ?>
                <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                <input type="hidden" name="url" value="<?php the_permalink(); ?>" />
                <?php if ( bp_is_my_profile() ): ?>
                    <input type="submit" name="edit" value="<?php _e( 'Edit Ad', $this->text_domain ); ?>" />
                    <?php if ( $sub == 'active' ): ?>
                        <input type="submit" name="end" value="<?php _e( 'End Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_end('<?php the_ID(); ?>'); return false;" />
                    <?php elseif ( $sub == 'saved' || $sub == 'ended' ): ?>
                        <input type="submit" name="renew" value="<?php _e( 'Renew Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_renew('<?php the_ID(); ?>'); return false;" />
                    <?php endif; ?>
                    <input type="submit" name="delete" value="<?php _e( 'Delete Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_delete('<?php the_ID(); ?>'); return false;" />
                <?php endif; ?>
            </form>
            
            <?php if ( bp_is_my_profile() ): ?>
            
            <form action="" method="post" id="confirm-form-<?php the_ID(); ?>" class="confirm-form">
                <?php wp_nonce_field('verify'); ?>
                <input type="hidden" name="action" />
                <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                <input type="hidden" name="post_title" value="<?php the_title(); ?>" />
                <?php if ( $sub == 'saved' || $sub == 'ended' ): ?>
                    <select name="duration">
                        <option value="1 Week"><?php _e( '1 Week',  $this->text_domain ); ?></option>
                        <option value="2 Weeks"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                        <option value="3 Weeks"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                        <option value="4 Weeks"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
                    </select>
                <?php endif; ?>
                <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
                <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onClick="classifieds.cancel('<?php the_ID(); ?>'); return false;" />
            </form>

            <?php endif; ?>

        </div>
    
    <?php endwhile; ?>
    <?php wp_reset_query(); ?>
    
</div>