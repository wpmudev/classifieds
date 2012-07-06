
<?php
/**
* The template for displaying Classifieds page.
* You can override this file in your active theme.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/

global $post,$wp_query, $paged;

$options = $this->get_options( 'general' );
$field_image = (empty($options['field_image_def'])) ? $this->plugin_url . 'ui-front/general/images/blank.gif' : $options['field_image_def'];

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$query_args = array(
'posts_per_page' => $this->cf_ads_per_page,
'paged' => $paged,
'post_status' => 'publish',
'post_type' => 'classifieds');

query_posts($query_args);

?>

<?php if ( !have_posts() ): ?>
<br />
<div class="info" id="message">
	<p><?php _e( 'No Classifieds found.', $this->text_domain ); ?></p>
</div>
<?php endif; ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php $this->cf_display_pagination( 'top' ); ?>
<div class="clear"></div>

<?php while( have_posts() ): the_post(); ?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
	<div class="cf-ad">
		<div class="cf-pad">
			<div class="cf-image">
				<?php
				if(has_post_thumbnail()){
					$thumbnail = get_the_post_thumbnail( $post->ID, array( 150, 150 ) );
				} else {
					$thumbnail = '<img width="150" height="150" title="no image" alt="no image" class="cf-no-image wp-post-image" src="' . $field_image . '">';
				}
				?>
				<a href="<?php the_permalink(); ?>" ><?php echo $thumbnail; ?></a>
			</div>

			<div class="cf-info">
				<table>
					<tr>
						<th><?php _e( 'Title', $this->text_domain ); ?></th>
						<td>
							<a href="<?php the_permalink(); ?>"><?php echo the_title(); ?></a>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Posted By', $this->text_domain ); ?></th>
						<td>
							<?php
							$user = get_userdata( get_the_author_meta('ID') );

							if ( '' == get_option( 'permalink_structure' ) )
							$cf_author_url = '?cf_author=' . $user->user_login;
							else
							$cf_author_url = '/cf-author/'. $user->user_login .'/';

							/* For BuddyPress compatibility */
							$alink = ( isset( $bp ) ) ? bp_core_get_user_domain( get_the_author_meta('ID') ) . 'classifieds/' : get_option( 'siteurl' ) . $cf_author_url;
							?>
							<a href="<?php echo $alink;?>" ><?php echo $user->display_name; ?></a>
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
		</div>
	</div>
</div>

<?php endwhile; ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php $this->cf_display_pagination( 'bottom' ); ?>

<?php wp_reset_query(); ?>

