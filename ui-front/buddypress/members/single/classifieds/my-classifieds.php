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

$cf_options = $this->get_options( 'general' );

$cf_path = $bp->displayed_user->domain . $this->classifieds_page_slug .'/' . $this->my_classifieds_page_slug;

/* Get posts based on post_status */
if ( in_array( 'saved',  $bp->action_variables ) ) {
	$sub = 'saved';
	$status = 'draft';
}
elseif ( in_array( 'ended',  $bp->action_variables ) ) {
	$sub = 'ended';
	$status = 'private';
}else {
	$sub = 'active';
	$status = 'publish';
}


/* Build messages */
if ( isset( $cl_credits_error ) && '1' == $cl_credits_error ) {
	$msg = __( 'You do not have enough credits to publish your classified for the selected time period. Please select lower period if available or purchase more credits.', $this->text_domain );
	$class = 'error';
}

if(empty($paged)) $paged = 1;

if(isset($bp)){
  $paged = $bp->action_variables[array_search('page',$bp->action_variables) + 1]; //find /page/x
}

?>

<div class="profile">

	<?php if ( bp_is_my_profile() ): ?>
	<ul class="button-nav">
		<li class="<?php if ( in_array( 'active', $bp->action_variables ) || empty( $bp->action_variables ) ) echo 'current'; ?>"><a href="<?php echo $cf_path . '/active/'; ?>"><?php _e( 'Active Ads', $this->text_domain ); ?></a></li>
		<li class="<?php if ( in_array( 'saved',  $bp->action_variables ) ) echo 'current'; ?>"><a href="<?php echo $cf_path . '/saved/'; ?>"><?php _e( 'Saved Ads', $this->text_domain ); ?></a></li>
		<li class="<?php if ( in_array( 'ended',  $bp->action_variables ) ) echo 'current'; ?>"><a href="<?php echo $cf_path . '/ended/'; ?>"><?php _e( 'Ended Ads', $this->text_domain ); ?></a></li>
	</ul>

	<?php if ( $this->use_credits && ! $this->is_full_access() ): ?>
	<div class="av-credits"><?php _e( 'Available Credits:', 'classifieds' ); ?> <?php $user_credits = ( get_user_meta( get_current_user_id(), 'cf_credits', true ) ) ? get_user_meta( get_current_user_id(), 'cf_credits', true ) : 0; echo $user_credits; ?></div>
	<?php endif; ?>

	<?php endif; ?>

	<div class="clear"></div>

	<?php
	$wp_query = new WP_Query( array( 'author' => bp_displayed_user_id(), 'post_type' => 'classifieds', 'post_status' => $status,  'paged' => $paged ) );

	/* Build messages */
	if ( !$wp_query->have_posts() ) {
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

	<?php /* Display navigation to next/previous pages when applicable */ ?>
	<?php $this->cf_display_pagination( 'top' ); ?>
	<br />
	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

	<div class="cf-ad">

		<div class="cf-image">
			<?php
			if ( '' == get_post_meta( get_the_ID(), '_thumbnail_id', true ) ) {
				if ( isset( $cf_options['field_image_def'] ) && '' != $cf_options['field_image_def'] )
				echo '<img width="150" height="150" title="no image" alt="no image" class="cf-no-imege wp-post-image" src="' . $cf_options['field_image_def'] . '">';
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
					<td><?php if ( class_exists('Classifieds_Core') ) echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
				</tr>
			</table>
		</div>

		<form action="#" method="post" id="action-form-<?php the_ID(); ?>" class="action-form">
			<?php wp_nonce_field('verify'); ?>
			<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
			<input type="hidden" name="url" value="<?php the_permalink(); ?>" />
			<?php if ( bp_is_my_profile() ): ?>
			<input type="submit" name="edit" value="<?php _e( 'Edit Ad', $this->text_domain ); ?>" />
			<?php if ( $sub == 'active' ): ?>
			<input type="submit" name="end" value="<?php _e( 'End Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_end('<?php the_ID(); ?>'); return false;" />
			<?php elseif ( $sub == 'saved' || $sub == 'ended' ): ?>
			<input type="submit" name="renew" value="<?php _e( 'Renew Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_renew('<?php the_ID(); ?>'); return false;" />
			<?php endif; ?>
			<input type="submit" name="delete" value="<?php _e( 'Delete Ad', $this->text_domain ); ?>" onclick="classifieds.toggle_delete('<?php the_ID(); ?>'); return false;" />
			<?php endif; ?>
		</form>

		<?php if ( bp_is_my_profile() ): ?>

		<form action="#" method="post" id="confirm-form-<?php the_ID(); ?>" class="confirm-form">
			<?php wp_nonce_field('verify'); ?>
			<input type="hidden" name="action" />
			<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
			<input type="hidden" name="post_title" value="<?php the_title(); ?>" />
			<?php if ( $sub == 'saved' || $sub == 'ended' ): ?>
					<?php
					//Get the duration options
					global $CustomPress_Core;
					if(isset($CustomPress_Core)){
						$durations = $CustomPress_Core->all_custom_fields['selectbox_4cf582bd61fa4']['field_options'];
					}
					?>
					<select name="duration" id="duration-<?php the_ID(); ?>>
						<?php 
						//make duration options
						foreach ( $durations as $key => $field_option ):
						if( empty($field_option ) ) continue;
						?>
						<option value="<?php echo $field_option; ?>"><?php  echo sprintf(__('%s', $this->text_domain), $field_option); ?></option>
						<?php endforeach; ?>
					</select>
			<?php endif; ?>
			<input type="submit" class="button confirm" value="<?php _e( 'Confirm', $this->text_domain ); ?>" name="confirm" />
			<input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="classifieds.cancel('<?php the_ID(); ?>'); return false;" />
		</form>

		<?php endif; ?>

	</div>

	<?php endwhile; ?>

	<?php /* Display navigation to next/previous pages when applicable */ ?>
	<?php $this->cf_display_pagination( 'bottom' ); ?>


	<?php wp_reset_query(); ?>

</div>