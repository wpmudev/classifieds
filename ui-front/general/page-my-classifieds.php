<?php
/**
* The template for displaying My Classifieds page.
* You can override this file in your active theme.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/

global $__classifieds_core;
$cf = &$__classifieds_core; //Shorthand


$options_general = $cf->get_options( 'general' );

$cf_path = home_url('/') . $cf->classifieds_page_slug .'/' . $cf->my_classifieds_page_slug;

get_header();

?>

<div id="container">
	<div id="content" class="my-classifieds" role="main">

		<?php if ( is_user_logged_in() ): ?>

		<?php $action = get_query_var('cf_action'); ?>

		<?php if ( empty($action) || $action == 'my-classifieds' ): ?>

		<h1 class="entry-title"><?php the_title(); ?></h1>

		<?php if ( ! $cf->is_full_access() && $cf->use_credits): ?>
		<div class="av-credits"><?php _e( 'Available Credits:', 'classifieds' ); ?> <?php $user_credits = ( get_user_meta( get_current_user_id(), 'cf_credits', true ) ) ? get_user_meta( get_current_user_id(), 'cf_credits', true ) : 0; echo $user_credits; ?></div>
		<?php endif; ?>

		<form method="POST" class="create-new-btn">
			<input type="submit" value="<?php _e( 'Create New Classified', 'classifieds' ); ?>" name="create_new">
			<?php if($cf->use_credits): ?>
			<input type="submit" value="<?php _e( 'My Credits', 'classifieds' ); ?>" name="my_credits">
			<?php endif; ?>
		</form>

		<ul class="button-nav">
			<li class="<?php if ( isset( $_GET['active'] ) || empty( $_GET ) ) echo 'current'; ?>"><a href="<?php echo $cf_path . '/?active'; ?>"><?php _e( 'Active Ads', 'classifieds' ); ?></a></li>
			<li class="<?php if ( isset( $_GET['saved'] ) )  echo 'current'; ?>"><a href="<?php echo $cf_path . '/?saved'; ?>"><?php _e( 'Saved Ads', 'classifieds' ); ?></a></li>
			<li class="<?php if ( isset( $_GET['ended'] ) )  echo 'current'; ?>"><a href="<?php echo $cf_path . '/?ended'; ?>"><?php _e( 'Ended Ads', 'classifieds' ); ?></a></li>
		</ul>
		<div class="clear"></div>


		<?php $error = get_query_var('cf_error'); ?>

		<?php
		/* Get current user so we can filter posts */
		$current_user = wp_get_current_user();
		/* Get posts based on post_status */
		if ( isset( $_GET['saved'] ) ) {
			query_posts( array( 'posts_per_page' => $cf->cf_ads_per_page, 'author' => $current_user->ID, 'post_type' => 'classifieds', 'post_status' => 'draft', 'paged' => $cf->cf_page ) );
			$sub = 'saved';
		} elseif ( isset( $_GET['ended'] ) ) {
			query_posts( array( 'posts_per_page' => $cf->cf_ads_per_page, 'author' => $current_user->ID, 'post_type' =>  'classifieds', 'post_status' => 'private', 'paged' => $cf->cf_page ) );
			$sub = 'ended';
		} else {
			query_posts( array( 'posts_per_page' => $cf->cf_ads_per_page, 'author' => $current_user->ID, 'post_type' => 'classifieds', 'post_status' => 'publish', 'paged' => $cf->cf_page ) );
			$sub = 'active';
		} ?>

		<?php if ( !have_posts() ): ?>
		<br /><br />
		<div class="info" id="message">
			<p><?php _e( 'No Classifieds found.', 'classifieds' ); ?></p>
		</div>
		<?php endif; ?>


		<?php if ( !empty( $error ) ): ?>
		<br /><div class="error"><?php echo $error . '<br />'; ?></div>
		<?php endif; ?>

		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php $cf->cf_display_pagination( 'top' ); ?>


		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		<?php // cf_debug( $wp_query ); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="position: static;">

			<div class="entry-content">

				<div class="classifieds">

					<div class="cf-ad">

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
									<th><?php _e( 'Title', 'classifieds' ); ?></th>
									<td>
										<a href="<?php the_permalink(); ?>"><?php echo $post->post_title; ?></a>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Categories', 'classifieds' ); ?></th>
									<td>
										<?php $taxonomies = get_object_taxonomies( 'classifieds', 'names' ); ?>
										<?php foreach ( $taxonomies as $taxonomy ): ?>
										<?php echo get_the_term_list( get_the_ID(), $taxonomy, '', ', ', '' ) . ' '; ?>
										<?php endforeach; ?>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Expires', 'classifieds' ); ?></th>
									<td><?php if ( class_exists('Classifieds_Core') ) echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
								</tr>
							</table>
						</div>

						<form action="" method="post" id="action-form-<?php the_ID(); ?>" class="action-form">
							<?php wp_nonce_field('verify'); ?>
							<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
							<input type="hidden" name="url" value="<?php the_permalink(); ?>" />
							<input type="submit" name="edit" value="<?php _e('Edit Ad', 'classifieds' ); ?>" />
							<?php if ( isset( $sub ) && $sub == 'active' ): ?>
							<input type="submit" name="end" value="<?php _e('End Ad', 'classifieds' ); ?>" onClick="classifieds.toggle_end('<?php echo $post->ID; ?>'); return false;" />
							<?php elseif ( isset( $sub ) && ( $sub == 'saved' || $sub == 'ended' ) ): ?>
							<input type="submit" name="renew" value="<?php _e('Renew Ad', 'classifieds' ); ?>" onClick="classifieds.toggle_renew('<?php echo $post->ID; ?>'); return false;" />
							<?php endif; ?>
							<input type="submit" name="delete" value="<?php _e('Delete Ad', 'classifieds' ); ?>" onClick="classifieds.toggle_delete('<?php echo $post->ID; ?>'); return false;" />
						</form>

						<form action="" method="post" id="confirm-form-<?php the_ID(); ?>" class="confirm-form">
							<?php wp_nonce_field('verify'); ?>
							<input type="hidden" name="action" />
							<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
							<input type="hidden" name="post_title" value="<?php the_title(); ?>" />
							<?php if ( isset( $sub ) && ( $sub == 'saved' || $sub == 'ended' ) ): ?>
							<select name="duration">
								<?php $cf_options = get_option('classifieds_options'); ?>
								<option value="1 Week"><?php _e( '1 Week for ',  'classifieds' ); ?> <?php echo 1 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  'classifieds' ); ?></option>
								<option value="2 Weeks"><?php _e( '2 Weeks for', 'classifieds' ); ?> <?php echo 2 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  'classifieds' ); ?></option>
								<option value="3 Weeks"><?php _e( '3 Weeks for', 'classifieds' ); ?> <?php echo 3 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  'classifieds' ); ?></option>
								<option value="4 Weeks"><?php _e( '4 Weeks for', 'classifieds' ); ?> <?php echo 4 * $cf_options['credits']['credits_per_week']; ?><?php _e( ' Credits',  'classifieds' ); ?></option>
							</select>
							<?php endif; ?>
							<input type="submit" class="button confirm" value="<?php _e( 'Confirm', 'classifieds' ); ?>" name="confirm" />
							<input type="submit" class="button cancel"  value="<?php _e( 'Cancel', 'classifieds' ); ?>" onClick="classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
						</form>

					</div>

				</div>

			</div><!-- .entry-content -->
		</div><!-- #post-## -->

		<?php endwhile; ?>

		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php $cf->cf_display_pagination( 'bottom' ); ?>


		<?php wp_reset_query(); ?>

		<!-- End my Classifieds -->

		<?php elseif ( $action == 'edit' ): ?>
		<!-- Begin Edit -->

		<?php $error = get_query_var('cf_error'); ?>

		<?php query_posts( array( 'posts_per_page' => $cf->cf_ads_per_page, 'p' => get_query_var('cf_post_id'), 'author' => get_current_user_id(), 'post_type' => 'classifieds') ); ?>

		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		<h1 class="entry-title">My Classifieds / Editing: <?php echo $post->post_title; ?></h1>

		<?php if ( !empty( $error ) ): ?>
		<br /><div class="error"><?php echo $error . '<br />'; ?></div>
		<?php endif; ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<div class="entry-content">

				<div class="profile">

					<form class="standard-form base" method="post" action="" enctype="multipart/form-data">

						<div class="editfield">
							<label for="title"><?php _e( 'Title', 'classifieds' ); ?></label>
							<input type="text" value="<?php echo $post->post_title; ?>" id="title" name="title">
							<p class="description"><?php _e( 'Enter title here.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield alt">
							<label for="description"><?php _e( 'Description', 'classifieds' ); ?></label>
							<textarea id="description" name="description" cols="40" rows="5"><?php echo $post->post_content; ?></textarea>
							<p class="description"><?php _e( 'The main description of your ad.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<label for="terms"><?php _e( 'Terms ( Categories / Tags )', 'classifieds' ); ?></label>
							<table class="cf-terms">
								<tr>
									<?php
									$taxonomies = get_object_taxonomies( 'classifieds', 'names' );
									$post_terms = wp_get_object_terms( get_the_ID(), $taxonomies );
									$taxonomies = get_object_taxonomies( 'classifieds', 'objects' ); ?>
									<?php foreach ( $taxonomies as $taxonomy_name => $taxonomy_object ): ?>
									<?php $terms = get_terms( $taxonomy_name, array( 'hide_empty' => 0 ) ); ?>
									<td>
										<select id="terms" name="terms[<?php echo $taxonomy_name; ?>][]" multiple="multiple">
											<optgroup label="<?php echo $taxonomy_object->labels->name; ?>">
												<?php foreach ( $terms as $term ): ?>
												<option value="<?php echo $term->slug; ?>" <?php foreach ( $post_terms as $post_term ) { if ( $post_term->term_id == $term->term_id  ) echo 'selected="selected"'; } ?>><?php echo $term->name; ?></option>
												<?php endforeach; ?>
											</optgroup>
										</select>
									</td>
									<?php endforeach; ?>
								</tr>
							</table>
							<p class="description"><?php _e( 'Select category for your ad.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<?php
							if ( '' == get_post_meta( get_the_ID(), '_thumbnail_id', true ) ) {
								if ( isset( $options_general['field_image_def'] ) && '' != $options_general['field_image_def'] )
								echo '<img width="150" height="150" title="no image" alt="no image" class="cf-no-imege wp-post-image" src="' . $options_general['field_image_def'] . '">';
							} else {
								echo get_the_post_thumbnail( get_the_ID(), array( 200, 150 ) );
							}
							?>
							<label for="image"><?php _e( 'Change Featured Image', 'classifieds' ); ?></label>
							<p id="featured-image">
								<input type="file" id="image" name="image">
								<input type="hidden" value="featured-image" id="action" name="action">
							</p>
						</div>

						<div class="editfield">
							<div class="radio">
								<span class="label"><?php _e( 'Ad Status' );  ?></span>
								<div id="status-box">
									<label><input type="radio" value="publish" name="status" <?php if ( $post->post_status == 'publish' ) echo 'checked="checked"'; ?>> <?php _e( 'Published', 'classifieds' ); ?></label>
									<label><input type="radio" value="draft" name="status" <?php if ( $post->post_status == 'draft' ) echo 'checked="checked"'; ?>> <?php _e( 'Draft', 'classifieds' ); ?></label>
								</div>
							</div>
							<p class="description"><?php _e( 'Check a status for your Ad.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<?php
							if ( file_exists( get_template_directory() . '/custom-fields.php' ) )
							get_template_part( 'custom-fields' );
							else
							load_template( CF_PLUGIN_DIR . 'ui-front/general/custom-fields.php', false );
							?>
						</div>

						<div class="submit">
							<input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
							<input type="hidden" name="post_title" value="<?php the_title(); ?>" />
							<input type="hidden" name="url" value="<?php the_guid(); ?>" />
							<input type="submit" value="Save Changes " name="update">
						</div>

					</form>
				</div>
			</div>
		</div>

		<?php endwhile; ?>
		<?php wp_reset_query(); ?>
		<!-- End Edit -->
		<?php elseif ( $action == 'create-new' ): ?>
		<!-- Begin Create New -->
		<h2 class="entry-title"><?php the_title(); ?> / <?php _e( 'Create New', 'classifieds' ); ?></h2>

		<?php $error = get_query_var('cf_error'); ?>
		<?php if ( !empty( $error ) ): ?>
		<br /><div class="error"><?php echo $error . '<br />'; ?></div>
		<?php endif; ?>

		<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<div class="entry-content">

				<div class="profile">

					<?php if ( isset( $msg ) ): ?>
					<div class="<?php echo $class; ?>" id="message">
						<p><?php echo $msg; ?></p>
					</div>
					<?php endif; ?>

					<form class="standard-form base" method="post" action="" enctype="multipart/form-data">

						<div class="editfield">
							<label for="title"><?php _e( 'Title', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
							<input type="text" value="<?php if ( isset( $_POST['title'] ) ) echo $_POST['title']; ?>" id="title" name="title">
							<p class="description"><?php _e( 'Enter title here.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<label for="description"><?php _e( 'Description', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
							<textarea id="description" name="description" cols="40" rows="5"><?php if ( isset( $_POST['description'] ) ) echo $_POST['description']; ?></textarea>
							<p class="description"><?php _e( 'The main description of your ad.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<label for="terms"><?php _e( 'Terms ( Categories / Tags )', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
							<table class="cf-terms">
								<tr>
									<?php $taxonomies = get_object_taxonomies( 'classifieds', 'objects' ); ?>
									<?php foreach ( $taxonomies as $taxonomy_name => $taxonomy_object ): ?>
									<?php $terms = get_terms( $taxonomy_name, array( 'hide_empty' => 0 ) ); ?>
									<td>
										<select id="terms" name="terms[<?php echo $taxonomy_name; ?>][]" multiple="multiple">
											<optgroup label="<?php echo $taxonomy_object->labels->name; ?>">
												<?php foreach ( $terms as $term ): ?>
												<option value="<?php echo $term->slug; ?>" <?php if ( isset( $_POST['terms'][$taxonomy_name] ) && is_array( $_POST['terms'][$taxonomy_name] ) && in_array( $term->slug, $_POST['terms'][$taxonomy_name] ) ) echo 'selected="selected"' ?>><?php echo $term->name; ?></option>
												<?php endforeach; ?>
											</optgroup>
										</select>
									</td>
									<?php endforeach; ?>
								</tr>
							</table>
							<p class="description"><?php _e( 'Select category for your ad.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<label for="image"><?php _e( 'Select Featured Image', 'classifieds' ); ?> <?php echo ( !isset( $options_general['field_image_req'] ) || '1' != $options_general['field_image_req'] ) ? '(' . __( 'required', 'classifieds' ) . ')' : ''; ?></label>
							<p id="featured-image">
								<input type="file" id="image" name="image">
								<input type="hidden" value="featured-image" id="action" name="action">
							</p>
						</div>

						<div class="editfield">
							<div class="radio">
								<span class="label"><?php _e( 'Ad Status' );  ?> (<?php _e( 'required', 'classifieds' ); ?>)</span>
								<div id="status-box">
									<label><input type="radio" value="publish" name="status" <?php if ( isset( $_POST['status'] ) && $_POST['status'] == 'publish' ) echo 'checked="checked"'; ?>> <?php _e( 'Published', 'classifieds' ); ?></label>
									<label><input type="radio" value="draft" name="status" <?php if ( isset( $_POST['status'] ) && $_POST['status'] == 'draft' ) echo 'checked="checked"'; ?>> <?php _e( 'Draft', 'classifieds' ); ?></label>
								</div>
							</div>
							<p class="description"><?php _e( 'Check a status for your Ad.', 'classifieds' ); ?></p>
						</div>

						<div class="editfield">
							<?php
							if ( file_exists( get_template_directory() . '/custom-fields.php' ) )
							get_template_part( 'custom-fields' );
							else
							load_template( CF_PLUGIN_DIR . 'ui-front/general/custom-fields.php', false );
							?>
						</div>

						<div class="submit">
							<input type="submit" value="Save Changes " name="save_new">
						</div>

					</form>

				</div>
			</div><!-- .entry-content -->
		</div><!-- #post-## -->

		<?php endwhile; ?>
		<!-- End CreateNew -->

		<?php elseif ( $action == 'my-credits' ): ?>
		<!-- Begin My Credits -->

		<?php $cf_options = $cf->get_options('payments'); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<div class="entry-content">

		<div class="my-credits">

			<form method="post">
				<h3><?php _e( 'Available Credits', $cf->text_domain ); ?></h3>
				<table class="form-table">
					<tr>
						<th>
							<label for="available_credits"><?php _e('Available Credits', $cf->text_domain ) ?></label>
						</th>
						<td>
							<input type="text" id="available_credits" class="small-text" name="available_credits" value="<?php echo $cf->get_user_credits(); ?>" disabled="disabled" />
							<span class="description"><?php _e( 'All of your currently available credits.', $cf->text_domain ); ?></span>
						</td>
					</tr>
				</table>

			</form>

			<form method="post" class="purchase_credits" action="" >
				<h3><?php _e( 'Purchase Additional Credits', $cf->text_domain ); ?></h3>
				<table class="form-table">
					<tr>
						<th>
							<label for="purchase_credits"><?php _e('Purchase Additional Credits', $cf->text_domain ) ?></label>
						</th>
						<td>
							<p class="submit">
								<?php wp_nonce_field('verify'); ?>
								<input type="submit" class="button-secondary" name="purchase" value="<?php _e( 'Purchase', $cf->text_domain ); ?>" />
							</p>
						</td>
					</tr>
				</table>

			</form>

			<?php $credits_log = $cf->get_user_credits_log(); ?>
			<h3><?php _e( 'Purchase History', $cf->text_domain ); ?></h3>
			<table class="form-table">
				<?php if ( is_array( $credits_log ) ): ?>
				<?php foreach ( $credits_log as $log ): ?>
				<tr>
					<th>
						<label for="available_credits"><?php _e('Purchase Date:', $cf->text_domain ) ?> <?php echo $cf->format_date( $log['date'] ); ?></label>
					</th>
					<td>
						<input type="text" id="available_credits" class="small-text" name="available_credits" value="<?php echo $log['credits']; ?>" disabled="disabled" />
						<span class="description"><?php _e( 'Credits purchased.', $cf->text_domain ); ?></span>
					</td>
				</tr>
				<?php endforeach; ?>
				<?php else: ?>
				<?php echo $credits_log; ?>
				<?php endif; ?>
			</table>

		</div>
</div>
</div>

		<!-- End My Credits -->

		<?php endif; ?>


		<?php else: ?>
		<!-- Not logged In -->
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<br />
		<div class="info" id="message">
			<p><?php _e( 'You have to login to view the contents of this page.', 'classifieds' ); ?></p>
		</div>
		<form action="<?php echo get_permalink($cf->checkout_page_id); ?>" method="post" class="cf-login">
			<strong><?php _e( 'Create new account', 'classifieds' ); ?></strong>
			<div class="submit">
				<input type="submit" name="new_account" value="<?php _e( 'New account', 'classifieds' ); ?>" />
			</div>
		</form>
		<br />
		<form action="" method="post" class="cf-login">
			<strong><?php _e( 'Existing client', 'classifieds' ); ?></strong>
			<table  <?php do_action( 'login_invalid' ); ?>>
				<tr>
					<td><label for="username"><?php _e( 'Username', 'classifieds' ); ?>:</label></td>
					<td><input type="text" id="username" name="username" value="<?php echo ( isset( $_POST['username'] ) ) ? $_POST['username'] : ''; ?>" /></td>
				</tr>
				<tr>
					<td><label for="password"><?php _e( 'Password', 'classifieds' ); ?>:</label></td>
					<td><input type="password" id="password" name="password" /></td>
				</tr>
			</table>

			<div class="clear"></div>

			<div class="submit">
				<input type="submit" name="login_submit" value="<?php _e( 'Continue', 'classifieds' ); ?>" />
			</div>
		</form>

		<?php $error = get_query_var('cf_error'); ?>

		<?php if ( $error ): ?>
		<div class="invalid-login"><?php echo $error; ?></div>

		<?php endif; ?>
		<?php endif; ?>

	</div><!-- #content -->
</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>