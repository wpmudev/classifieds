<?php
/**
* The template for displaying Author Archive pages.
* You can override this file in your active theme/classifieds/ folder.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/
global $query_string, $Classifieds_Core;
//reset query for correct $wp_query->max_num_pages in loop-author.php (for pagination)
wp_reset_query();

if ( '' == get_option( 'permalink_structure' ) )
$cf_author_name = $_REQUEST['cf_author'];
else
$cf_author_name = get_query_var( 'cf_author_name' );
$query_string = 'author_name=' . $cf_author_name;
$user_data = get_user_by( 'login' ,$cf_author_name );

if ( '' == get_option( 'permalink_structure' ) )
$cf_author_url = '?cf_author=' . $user_data->user_login;
else
$cf_author_url = '/cf-author/'. $user_data->user_login .'/';

$user_display = ( $user_data->display_name ) ? $user_data->display_name : $user_data->user_login;

?>

<div id="container">
	<div id="content" role="main">

		<?php
		/* Queue the first post, that way we know who
		* the author is when we try to get their name,
		* URL, description, avatar, etc.
		*
		* We reset this later so we can run the loop
		* properly with a call to rewind_posts().
		*/
		if ( have_posts() ) the_post();
		?>
		<h2 class="page-title author">
			<?php 
				printf( "%s <span class='vcard'><a class='url fn n' href='%s' title='%s' rel='me'>%s</a></span>",
					__( 'Classifieds By: ', CF_TEXT_DOMAIN ),
					get_option( 'siteurl' ) . $cf_author_url,
					$user_display,
					$user_display
				);
			?>
		</h2>

		<?php
		/* Since we called the_post() above, we need to
		* rewind the loop back to the beginning that way
		* we can run the loop properly, in full.
		*/
		rewind_posts();

		/* Run the loop for the author archive page to output the authors posts
		* If you want to overload this in a child theme then include a file
		* called loop-author.php and that will be used instead.
		*/
		load_template( $Classifieds_Core->custom_classifieds_template( 'loop-author' ) );
		?>

	</div><!-- #content -->
</div><!-- #container -->
