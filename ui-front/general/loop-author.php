<?php
/**
* The loop that displays posts.
* You can override this file in your active theme.
*
* The loop displays the posts and the post content.  See
* http://codex.wordpress.org/The_Loop to understand it and
* http://codex.wordpress.org/Template_Tags to understand
* the tags used in it.
*
* This can be overridden in child themes with loop.php or
* loop-template.php, where 'template' is the loop context
* requested by a template. For example, loop-index.php would
* be used if it exists and we ask for the loop with:
* <code>get_template_part( 'loop', 'index' );</code>
*
* @package Classifieds
* @subpackage Author
* @since Classifieds 2.0
*/

global $query_string, $Classifieds_Core;

$cf = &$Classifieds_Core; //shorthand

//page for pagination
if ( 1 < get_query_var( 'cf_author_page' ) )
$cf_page = get_query_var( 'cf_author_page' );
else
$cf_page = '1';

$GLOBALS['paged'] = $cf_page;

query_posts( array( 
	'posts_per_page' => $cf->cf_ads_per_page, 
	'paged' => $cf_page, 
	'author_name' => get_query_var( 'cf_author_name' ), 
	'post_status' => 'publish', 
	'post_type' => 'classifieds',
) );

$cf_options = $cf->get_options( 'general' );

?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php $cf->pagination( $cf->pagination_top ); ?>


<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
<div id="post-0" class="post error404 not-found">
	<h1 class="entry-title"><?php _e( 'Not Found', CF_TEXT_DOMAIN ); ?></h1>
	<div class="entry-content">
		<p><?php _e( 'Apologies, but no results were found for the requested classifieds. Perhaps searching will help find a related classified.', CF_TEXT_DOMAIN ); ?></p>
		<?php get_search_form(); ?>
	</div><!-- .entry-content -->
</div><!-- #post-0 -->
<?php endif; ?>

<?php
/* Start the Loop.
*
* In Twenty Ten we use the same loop in multiple contexts.
* It is broken into three main parts: when we're displaying
* posts that are in the gallery category, when we're displaying
* posts in the asides category, and finally all other posts.
*
* Additionally, we sometimes check for whether we are on an
* archive page, a search page, etc., allowing for small differences
* in the loop on each template without actually duplicating
* the rest of the loop that is shared.
*
* Without further ado, the loop:
*/ ?>
<?php while ( have_posts() ) : the_post(); ?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<div class="cf-ad">

			<div class="cf-image">
				<?php
				if ( '' == get_post_meta( get_the_ID(), '_thumbnail_id', true ) ) {
					if ( isset( $cf_options['field_image_def'] ) && '' != $cf_options['field_image_def'] )
					echo '<img width="150" height="150" title="no image" alt="no image" class="cf-no-image wp-post-image" src="' . $cf_options['field_image_def'] . '">';
				} else {
					echo get_the_post_thumbnail( get_the_ID(), array( 200, 150 ) );
				}
				?>
			</div>
			<div class="cf-info">
				<table>
					<tr>
						<th><?php _e( 'Title', CF_TEXT_DOMAIN ); ?></th>
						<td>
							<a href="<?php the_permalink(); ?>"><?php echo $post->post_title; ?></a>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Posted By', CF_TEXT_DOMAIN ); ?></th>

						<td>
							<?php echo the_author_posts_link(); ?>
							<?php /* For BuddyPress compatibility */ ?>
							<?php global $bp;
							if ( isset( $bp ) ):
							$obj = get_post_type_object('classifieds');
							$rewrite_slug = ($obj->has_archive) ? $obj->has_archive : '';
							?><a href="<?php echo bp_core_get_user_domain( get_the_author_meta('ID') ) . $rewrite_slug;?>" alt="<?php the_author(); ?> Profile" >
							<?php endif; ?>

								<?php /* For BuddyPress compatibility */ ?>
							<?php if ( isset( $bp ) ): ?></a><?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Categories', CF_TEXT_DOMAIN ); ?></th>
						<td>
							<?php $taxonomies = get_object_taxonomies( 'classifieds', 'names' ); ?>
							<?php foreach ( $taxonomies as $taxonomy ): ?>
							<?php echo get_the_term_list( get_the_ID(), $taxonomy, '', ', ', '' ) . ' '; ?>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Expires', CF_TEXT_DOMAIN ); ?></th>
						<td><?php echo $cf->get_expiration_date( get_the_ID() ); ?></td>
					</tr>
				</table>
			</div>

		</div>
	</div><!-- .entry-content -->

</div><!-- #post-## -->

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php $cf->pagination( $cf->pagination_bottom ); ?>