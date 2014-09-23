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

global $bp, $wp_query, $paged;

$options = $this->get_options( 'general' );

$cf_path = $bp->displayed_user->domain . $this->classifieds_page_slug .'/' . $this->my_classifieds_page_slug;

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

//if(isset($bp)) $paged = $bp->action_variables[array_search('page',$bp->action_variables) + 1]; //find /page/x

$query_args = array(
'paged' => $paged,
'post_type' => 'classifieds',
'author' => bp_displayed_user_id(),
);

/* Get posts based on post_status */
if ( in_array( 'saved',  $bp->action_variables ) ) {
	$query_args['post_status'] = array('draft', 'pending');
	$sub = 'saved';
}
elseif ( in_array( 'ended',  $bp->action_variables ) ) {
	$query_args['post_status'] = 'private';
	$sub = 'ended';
}else {
	$query_args['post_status'] = 'publish';
	$sub = 'active';
}

//$wp_query = new WP_Query( array( $query_args);
query_posts($query_args);
?>

<script type="text/javascript" src="<?php echo $this->plugin_url . 'ui-front/js/ui-front.js'; ?>" >
</script>

<?php if ( !empty( $error ) ): ?>
<br /><div class="error"><?php echo $error . '<br />'; ?></div>
<?php endif; ?>

<div class="profile">

	<?php if ( bp_is_my_profile() ): ?>

	<?php if ( $this->is_full_access() ): ?>
	<div class="av-credits"><?php _e( 'You have access to create new ads', $this->text_domain ); ?></div>
	<?php elseif($this->use_credits): ?>
	<div class="av-credits"><?php _e( 'Available Credits:', $this->text_domain ); ?> <?php echo $this->transactions->credits; ?></div>
	<?php else:
	echo do_shortcode('[cf_checkout_btn text="' . __('Purchase ads', $this->text_domain) . '" view="loggedin"]');
	?>
	<?php endif; ?>

	<ul class="cf_tabs">
		<li class="<?php if ( in_array( 'active', $bp->action_variables ) || empty( $bp->action_variables ) ) echo 'cf_active current'; ?>"><a href="<?php echo $cf_path . '/active/'; ?>"><?php _e( 'Active Ads', $this->text_domain ); ?></a></li>
		<li class="<?php if ( in_array( 'saved',  $bp->action_variables ) ) echo 'cf_active current'; ?>"><a href="<?php echo $cf_path . '/saved/'; ?>"><?php _e( 'Saved Ads', $this->text_domain ); ?></a></li>
		<li class="<?php if ( in_array( 'ended',  $bp->action_variables ) ) echo 'cf_active current'; ?>"><a href="<?php echo $cf_path . '/ended/'; ?>"><?php _e( 'Ended Ads', $this->text_domain ); ?></a></li>
	</ul>

	<?php endif; ?>

	<div class="clear"></div>

	<?php

	/* Build messages */
	if ( ! have_posts() ) {
		$msg   = __( 'There were no ads found.', $this->text_domain );
		$class = 'info';
	} elseif ( isset( $action ) && $action == 'end' ) {
		$msg = sprintf( __( 'Ad "%s" ended.', $this->text_domain ), $post_title );
		$class = 'updated';
	} elseif ( isset( $action ) && $action == 'renew' ) {
		$msg = sprintf( __( 'Ad "%s" renewed.', $this->text_domain ), $post_title );
		$class = 'updated';
	} elseif ( isset( $action ) && $action == 'edit' ) {
		$msg = sprintf( __( 'Ad "%s" updated successfully.', $this->text_domain ), $post_title );
		$class = 'updated';
	} elseif ( isset( $action ) && $action == 'delete' ) {
		$msg = sprintf( __( 'Ad "%s" deleted successfully.', $this->text_domain ), $post_title );
		$class = 'updated';
	}
	?>

	<?php if ( isset( $msg ) ): ?>
	<div class="<?php echo $class; ?>" id="message">
		<p><?php echo $msg; ?></p>
	</div>
	<?php endif; ?>

	<div class="cf_tab_container">

		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php echo $this->pagination( $this->pagination_bottom ); ?>
		<br clear="both" />
		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
			<div class="cf-ad">
				<div class="cf-pad">
					<div class="cf-image">
						<?php
						if ( '' == get_post_meta( get_the_ID(), '_thumbnail_id', true ) ) {
							if ( ! empty( $options_general['field_image_def'] ) )
							echo '<img width="150" height="150" title="no image" alt="no image" class="cf-no-image wp-post-image" src="' . $options_general['field_image_def'] . '">';
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

						<?php if ( bp_is_my_profile() ):
						if(current_user_can('edit_classified', get_the_ID())){
							echo do_shortcode('[cf_edit_classified_btn text="' . __('Edit Ad', $this->text_domain) . '" view="always" post="' . get_the_ID() . '"]');
						}
						?>

						<?php if ( isset( $sub ) && $sub == 'active' ): ?>
						<button type="submit" name="end" value="<?php _e('End Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_end('<?php the_ID(); ?>'); return false;" ><?php _e('End Ad', $this->text_domain ); ?></button>
						<?php elseif ( isset( $sub ) && ( $sub == 'saved' || $sub == 'ended' ) ): ?>
						<button type="submit" name="renew" value="<?php _e('Renew Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_renew('<?php the_ID(); ?>'); return false;" ><?php _e('Renew Ad', $this->text_domain ); ?></button>
						<?php endif; ?>

						<?php if(current_user_can( 'delete_classifieds' )): ?>
						<button type="submit" name="delete" value="<?php _e('Delete Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_delete('<?php the_ID(); ?>'); return false;" ><?php _e('Delete Ad', $this->text_domain ); ?></button>
						<?php endif; ?>

						<?php endif; ?>

					</form>

					<?php if ( bp_is_my_profile() ): ?>

					<form action="#" method="post" id="confirm-form-<?php the_ID(); ?>" class="confirm-form">
						<?php wp_nonce_field('verify'); ?>
						<input type="hidden" name="action" />
						<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
						<input type="hidden" name="post_title" value="<?php the_title(); ?>" />

						<span id="cf-delete-<?php the_ID(); ?>"><?php _e('Delete Ad', $this->text_domain ); ?></span>
						<span id="cf-renew-<?php the_ID(); ?>"><?php _e('Renew Ad', $this->text_domain ); ?></span>
						<span id="cf-end-<?php the_ID(); ?>"><?php _e('End Ad', $this->text_domain ); ?></span>

						<?php if ( $sub == 'saved' || $sub == 'ended' ):
						$cf_payments = $this->get_options('payments');

						//Get the duration options
						global $CustomPress_Core;
						if(isset($CustomPress_Core)){
							$durations = $CustomPress_Core->all_custom_fields['selectbox_4cf582bd61fa4']['field_options'];
						}
						?>
						<select name="duration">
							<?php
							//make duration options
							foreach ( $durations as $key => $field_option ):
							if( empty($field_option ) ) continue;
							if($this->use_credits):
							?>
							<option value="<?php echo $field_option; ?>"><?php  echo sprintf(__('%s for %s Credits', $this->text_domain), $field_option, round($field_option + 0) * $cf_payments['credits_per_week']); ?></option>
							<?php else: ?>
							<option value="<?php echo $field_option; ?>"><?php  echo $field_option; ?></option>
							<?php endif; ?>
							<?php endforeach; ?>
						</select>
						<?php endif; ?>
						<input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
						<input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="classifieds.cancel('<?php the_ID(); ?>'); return false;" />
					</form>

					<?php endif; ?>
					<div class="clear"></div>

				</div>
			</div>
		</div><!-- #post-## -->
		<div class="clear"></div>

		<?php endwhile; ?>
		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php echo $this->pagination( $this->pagination_bottom ); ?>
	</div><!-- .cf_tab_container -->
	<?php if(is_object($wp_query)) $wp_query->post_count = 0; ?>
	<!-- End my Classifieds -->
</div>
