<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
global $bp;
/* Get posts based on post_status */
if ( in_array( 'active', $bp->action_variables ) || empty( $bp->action_variables ) )
    $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'publish', 'numberposts' => 0 ) );
elseif ( in_array( 'saved',  $bp->action_variables ) )
    $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'draft', 'numberposts' => 0 ) );
elseif ( in_array( 'ended',  $bp->action_variables ) )
    $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'private', 'numberposts' => 0 ) );
/* Build messages */
if ( empty( $posts ) ) {
    $msg   = __( 'There were no ads found.', $this->text_domain );
    $class = 'info';
} elseif ( $action == 'edit' ) {
    $msg = sprintf( __( 'Ad "%s" updated successfully.', $this->text_domain ), $post_title );
    $class = 'updated';
} elseif ( $action == 'delete' ) {
    $msg = sprintf( __( 'Ad "%s" deleted successfully.', $this->text_domain ), $post_title );
    $class = 'updated';
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

    <?php if ( $msg ): ?>
    <div class="<?php echo $class; ?>" id="message">
		<p><?php echo $msg; ?></p>
	</div>
    <?php endif; ?>

<?php foreach ( $posts as $post ): ?>

    <div class="bp-cf-ad">
        <div class="bp-cf-image"><?php echo get_the_post_thumbnail( $post->ID, array( 200, 150 ) ); ?></div>
        <div class="bp-cf-info">
            <table>
                <tr>
                    <th><?php _e( 'Title', $this->text_domain ); ?></th>
                    <td>
                        <a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo $post->post_title; ?></a>
                    </td>
                </tr>
                <tr>
                    <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                    <td>
                       <?php foreach ( $this->taxonomy_names as $taxonomy ): ?>
                           <?php echo get_the_term_list( $post->ID, $taxonomy, '', ', ', '' ) . ' '; ?>
                       <?php endforeach; ?>
                    </td>
                <tr>
                <tr>
                    <th><?php _e( 'Ends', $this->text_domain ); ?></th>
                    <td><?php //echo get_post_meta( $post->ID, $this->custom_fields['duration'], true ); ?></td>
                </tr>
            </table>
        </div>
        <form action="" method="post" id="action-form-<?php echo $post->ID; ?>" class="action-form">
            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
            <input type="hidden" name="url" value="<?php echo get_permalink( $post->ID ); ?>" />
            <?php if ( bp_is_my_profile() ): ?>
            <input type="submit" name="edit" value="<?php _e('Edit Ad', $this->text_domain ); ?>" />
            <input type="submit" name="delete" value="<?php _e('Delete Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_delete('<?php echo $post->ID; ?>'); return false;" />
            <?php endif; ?>
        </form>
        <?php if ( bp_is_my_profile() ): ?>
        <form action="" method="post" id="del-form-<?php echo $post->ID; ?>" class="del-form">
            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
            <input type="hidden" name="post_title" value="<?php echo $post->post_title; ?>" />
            <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
            <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onClick="classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
        </form>
        <?php endif; ?>

    </div>

<?php endforeach; ?>
    
</div>