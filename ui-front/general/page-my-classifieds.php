<?php
/**
* The template for displaying My Classifieds page.
* You can override this file in your active theme.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/

global $current_user;

$current_user = wp_get_current_user();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$options_general = $this->get_options( 'general' );

$query_args = array(
'posts_per_page' => $this->cf_ads_per_page,
'paged' => $paged,
'post_type' => 'classifieds',
'author' => $current_user->ID,
);

if(isset($_GET['saved']) ) {
	$query_args['post_status'] = 'draft';
	$sub = 'saved';
}elseif(isset($_GET['ended'])){
	$query_args['post_status'] = 'private';
	$sub = 'ended';
}else{
	$query_args['post_status'] = 'publish';
	$sub = 'active';
}

query_posts($query_args);

$cf_path = get_permalink($this->my_classifieds_page_id);

?>

<?php if ( is_user_logged_in() ): ?>

<?php $action = get_query_var('cf_action'); ?>

<?php if ( empty($action) || $action == 'my-classifieds' ): ?>

<?php if ( ! $this->is_full_access() && $this->use_credits): ?>
<div class="av-credits"><?php _e( 'Available Credits:', $this->text_domain ); ?> <?php $user_credits = ( get_user_meta( get_current_user_id(), 'cf_credits', true ) ) ? get_user_meta( get_current_user_id(), 'cf_credits', true ) : 0; echo $user_credits; ?></div>
<?php endif; ?>

<?php echo do_shortcode('[cf_add_classified_btn text="Create New Classified" view="loggedin"]'); ?>
<?php //if($this->use_credits): ?>
<?php echo do_shortcode('[cf_my_credits_btn text="My Credits" view="loggedin"]'); ?>
<?php //endif; ?>

<ul class="button-nav">
	<li class="<?php if ( $sub == 'active') echo 'current'; ?>"><a href="<?php echo $cf_path . '/?active'; ?>"><?php _e( 'Active Ads', $this->text_domain ); ?></a></li>
	<li class="<?php if (  $sub == 'saved') echo 'current'; ?>"><a href="<?php echo $cf_path . '/?saved'; ?>"><?php _e( 'Saved Ads', $this->text_domain ); ?></a></li>
	<li class="<?php if (  $sub == 'ended') echo 'current'; ?>"><a href="<?php echo $cf_path . '/?ended'; ?>"><?php _e( 'Ended Ads', $this->text_domain ); ?></a></li>
</ul>
<div class="clear"></div>


<?php $error = get_query_var('cf_error'); ?>

<?php if ( !have_posts() ): ?>
<br /><br />
<div class="info" id="message">
	<p><?php _e( 'No Classifieds found.', $this->text_domain ); ?></p>
</div>
<?php endif; ?>

<?php if ( !empty( $error ) ): ?>
<br /><div class="error"><?php echo $error . '<br />'; ?></div>
<?php endif; ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php $this->cf_display_pagination( 'top' ); ?>
<div class="clear"></div>
<?php while ( have_posts() ) : the_post(); ?>

<?php // cf_debug( $wp_query ); ?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
	<div class="cf-ad">
		<div class="cf-pad">
			<div class="cf-image">
				<?php
				if ( '' == get_post_meta( get_the_ID(), '_thumbnail_id', true ) ) {
					if ( ! empty( $options_general['field_image_def'] ) )
					echo '<img width="150" height="150" title="no image" alt="no image" class="cf-no-imege wp-post-image" src="' . $options_general['field_image_def'] . '">';
				} else {
					echo get_the_post_thumbnail( get_the_ID(), array( 200, 150 ) );
				}
				?>
			</div>
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
					</tr>
					<tr>
						<th><?php _e( 'Expires', $this->text_domain ); ?></th>
						<td><?php echo $this->get_expiration_date( get_the_ID() ); ?></td>
					</tr>
				</table>
			</div>

			<form action="#" method="post" id="action-form-<?php the_ID(); ?>" class="action-form">
				<?php wp_nonce_field('verify'); ?>
				<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
				<input type="hidden" name="url" value="<?php the_permalink(); ?>" />
				<?php echo do_shortcode('[cf_edit_classified_btn text="Edit Ad" view="always" post="' . get_the_ID() . '"]'); ?>

				<?php if ( isset( $sub ) && $sub == 'active' ): ?>
				<button type="submit" name="end" value="<?php _e('End Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_end('<?php the_ID(); ?>'); return false;" ><?php _e('End Ad', $this->text_domain ); ?></button>
				<?php elseif ( isset( $sub ) && ( $sub == 'saved' || $sub == 'ended' ) ): ?>
				<button type="submit" name="renew" value="<?php _e('Renew Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_renew('<?php the_ID(); ?>'); return false;" ><?php _e('Renew Ad', $this->text_domain ); ?></button>
				<?php endif; ?>
				<button type="submit" name="delete" value="<?php _e('Delete Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_delete('<?php the_ID(); ?>'); return false;" ><?php _e('Delete Ad', $this->text_domain ); ?></button>
			</form>

			<form action="#" method="post" id="confirm-form-<?php the_ID(); ?>" class="confirm-form">
				<?php wp_nonce_field('verify'); ?>
				<input type="hidden" name="action" />
				<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
				<input type="hidden" name="post_title" value="<?php the_title(); ?>" />
				<?php if ( isset( $sub ) && ( $sub == 'saved' || $sub == 'ended' ) ): ?>
				<select name="duration">
					<?php $cf_options = get_option('classifieds_options'); ?>
					<option value="1 Week"><?php _e( '1 Week for ',  $this->text_domain ); ?> <?php echo 1 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  $this->text_domain ); ?></option>
					<option value="2 Weeks"><?php _e( '2 Weeks for', $this->text_domain ); ?> <?php echo 2 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  $this->text_domain ); ?></option>
					<option value="3 Weeks"><?php _e( '3 Weeks for', $this->text_domain ); ?> <?php echo 3 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  $this->text_domain ); ?></option>
					<option value="4 Weeks"><?php _e( '4 Weeks for', $this->text_domain ); ?> <?php echo 4 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  $this->text_domain ); ?></option>
				</select>
				<?php endif; ?>
				<input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
				<input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="classifieds.cancel('<?php the_ID(); ?>'); return false;" />
			</form>
		</div>
	</div>
</div><!-- #post-## -->
<div class="clear"></div>

<?php endwhile; ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php $this->cf_display_pagination( 'bottom' ); ?>
<?php wp_reset_query(); ?>
<!-- End my Classifieds -->
<?php endif; ?>

<?php /** Login Required page **/
else:

require($this->template_file('login-required'));

endif;
