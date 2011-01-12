<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
/* Get posts based on post_status */
if ( isset( $_GET['active'] ) || empty( $_GET ) ) {
    $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'publish', 'numberposts' => 0 ) );
    $sub = 'active';
} elseif ( isset( $_GET['saved'] ) ) {
    $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'draft', 'numberposts' => 0 ) );
    $sub = 'saved';
} elseif ( isset( $_GET['ended'] ) ) {
    $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'private', 'numberposts' => 0 ) );
    $sub = 'ended';
}
/* Build messages */
if ( empty( $posts ) ) {
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

<div class="classifieds">

    <?php if ( true ): ?>
    <ul class="button-nav">
        <li class="<?php if ( isset( $_GET['active'] ) ) echo 'current'; ?>"><a href="<?php echo get_bloginfo('url') . '/classifieds/my-classifieds/?active'; ?>"><?php _e( 'Active Ads', $this->text_domain ); ?></a></li>
        <li class="<?php if ( isset( $_GET['saved'] ) )  echo 'current'; ?>"><a href="<?php echo get_bloginfo('url') . '/classifieds/my-classifieds/?saved'; ?>"><?php _e( 'Saved Ads', $this->text_domain ); ?></a></li>
        <li class="<?php if ( isset( $_GET['ended'] ) )  echo 'current'; ?>"><a href="<?php echo get_bloginfo('url') . '/classifieds/my-classifieds/?ended'; ?>"><?php _e( 'Ended Ads', $this->text_domain ); ?></a></li>
    </ul>
    <?php endif; ?>
    <div class="clear"></div>

    <?php if ( $msg ): ?>
    <div class="<?php echo $class; ?>" id="message">
		<p><?php echo $msg; ?></p>
	</div>
    <?php endif; ?>

<?php foreach ( $posts as $post ): ?>

    <div class="cf-ad">
        
        <div class="cf-image"><?php echo get_the_post_thumbnail( $post->ID, array( 200, 150 ) ); ?></div>
        <div class="cf-info">
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
                    <th><?php _e( 'Expires', $this->text_domain ); ?></th>
                    <td><?php echo $this->get_expiration_date( $post->ID ); ?></td>
                </tr>
            </table>
        </div>
        
        <form action="" method="post" id="action-form-<?php echo $post->ID; ?>" class="action-form">
            <?php wp_nonce_field('verify'); ?>
            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
            <input type="hidden" name="url" value="<?php echo get_permalink( $post->ID ); ?>" />
            <input type="submit" name="edit" value="<?php _e('Edit Ad', $this->text_domain ); ?>" />
            <?php if ( $sub == 'active' ): ?>
                <input type="submit" name="end" value="<?php _e('End Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_end('<?php echo $post->ID; ?>'); return false;" />
            <?php elseif ( $sub == 'saved' || $sub == 'ended' ): ?>
                <input type="submit" name="renew" value="<?php _e('Renew Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_renew('<?php echo $post->ID; ?>'); return false;" />
            <?php endif; ?>
            <input type="submit" name="delete" value="<?php _e('Delete Ad', $this->text_domain ); ?>" onClick="classifieds.toggle_delete('<?php echo $post->ID; ?>'); return false;" />
        </form>

        <form action="" method="post" id="confirm-form-<?php echo $post->ID; ?>" class="confirm-form">
            <?php wp_nonce_field('verify'); ?>
            <input type="hidden" name="action" />
            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
            <input type="hidden" name="post_title" value="<?php echo $post->post_title; ?>" />
            <?php if ( $sub == 'saved' || $sub == 'ended' ): ?>
                <select name="duration">
                    <option value="1 Week"><?php _e( '1 Week',  $this->text_domain ); ?></option>
                    <option value="2 Weeks"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                    <option value="3 Weeks"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                    <option value="4 Weeks"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
                </select>
            <?php endif; ?>
            <input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
            <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onClick="classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
        </form>

    </div>

<?php endforeach; ?>
    
</div>