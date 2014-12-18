<?php
/**
* The template for displaying Taxonomy pages.
*
* Learn more: http://codex.wordpress.org/Template_Hierarchy
*
* @package Classifieds
* @subpackage Taxonomy
* @since Classifieds 2.0
*/

get_header(); 

?>

<div id="container">
	<div id="content" role="main">

		<?php /* For BuddyPress compatibility */ ?>
		<?php global $bp; if ( isset( $bp ) ): ?>
		<div class="cf-padder">
			<?php endif; ?>

			<?php
			/* Queue the first post, that way we know
			* what date we're dealing with (if that is the case).
			*
			* We reset this later so we can run the loop
			* properly with a call to rewind_posts().
			*/
			if ( have_posts() ) the_post(); ?>

			<h1 class="page-title"><?php _e( 'Classifieds', CF_TEXT_DOMAIN ); ?> / <?php echo get_query_var('taxonomy'); ?> / <?php echo get_query_var('term'); ?></h1>

			<?php
			/* Since we called the_post() above, we need to
			* rewind the loop back to the beginning that way
			* we can run the loop properly, in full.
			*/
			rewind_posts();

			/* Run the loop for the archives page to output the posts.
			* If you want to overload this in a child theme then include a file
			* called loop-archives.php and that will be used instead.
			*/
			load_template( $this->custom_classifieds_template( 'loop-taxonomy' ) );?>

			<?php /* For BuddyPress compatibility */ ?>
			<?php if ( isset( $bp ) ): ?>
		</div>
		<?php endif; ?>

	</div><!-- #content -->

	<?php /* For BuddyPress compatibility */ ?>
	<?php if ( isset( $bp ) ): ?>
	<?php locate_template( array( 'sidebar.php' ), true ); ?>
	<?php endif; ?>

</div><!-- #container -->

<?php /* For BuddyPress compatibility */ ?>
<?php if ( !isset( $bp ) ): ?>
<?php get_sidebar(); ?>
<?php endif; ?>

<?php get_footer(); ?>
