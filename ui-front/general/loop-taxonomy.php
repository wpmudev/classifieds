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
* @subpackage Taxonomy
* @since Classifieds 2.0
*/
global $bp, $Classifieds_Core;
$cf = $Classifieds_Core; //shorthand

$cf_options = $cf->get_options( 'general' );

$field_image = (empty($cf_options['field_image_def'])) ? $cf->plugin_url . 'ui-front/general/images/blank.gif' : $cf_options['field_image_def'];

?>

<?php if(! is_post_type_archive('classifieds') ) the_cf_breadcrumbs(); ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php echo $cf->pagination( $cf->pagination_top ); ?>
<div class="clear"></div>
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
*/  ?>
<?php while ( have_posts() ) : the_post(); ?>

<?php
$cost = do_shortcode('[ct id="_ct_text_4cfeb3eac6f1f"]');
$cost = is_numeric($cost) ? sprintf(__('%01.2f',CF_TEXT_DOMAIN), $cost) : $cost;
?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="entry-content">
		<div class="cf-ad">

			<div class="cf-image">
				<?php
				if ( '' == get_post_meta( get_the_ID(), '_thumbnail_id', true ) ) {
					if ( isset( $cf_options['field_image_def'] ) && '' != $cf_options['field_image_def'] )
					echo '<img width="150" height="150" title="no image" alt="no image" class="cf-no-image wp-post-image" src="' . $field_image . '">';
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
							<span class="cf-title"><a href="<?php the_permalink(); ?>"><?php echo $post->post_title; ?></a></span>
							<span class="cf-price"><?php echo $cost; ?></span>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Posted By', CF_TEXT_DOMAIN ); ?></th>

						<td>

							<span class="cf-author"><?php echo the_author_posts_link(); ?></a></span>

						</td>
					</tr>
					<tr>
						<th><?php _e( 'Categories', CF_TEXT_DOMAIN ); ?></th>
						<td><span class="cf-terms">
							<?php $taxonomies = get_object_taxonomies( 'classifieds', 'names' ); ?>
							<?php foreach ( $taxonomies as $taxonomy ): ?>
							<?php echo get_the_term_list( get_the_ID(), $taxonomy, '', ', ', '' ) . ' '; ?>
							<?php endforeach; ?>
						</span>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Expires', CF_TEXT_DOMAIN ); ?></th>
					<td><span class="cf-expires"><?php echo $cf->get_expiration_date( get_the_ID() ); ?></span></td>
				</tr>
				<tr>
					<td colspan="2"><span class="cf-excerpt"><?php the_excerpt(); ?></span></td>
				</tr>
			</table>
		</div>

	</div>
</div><!-- .entry-content -->

</div><!-- #post-## -->

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php echo $cf->pagination( $cf->pagination_bottom ); ?>