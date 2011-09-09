<?php
/**
 * The template for displaying Author Archive pages.
 * You can override this file in your active theme.
 *
 * @package Classifieds
 * @subpackage UI Front
 * @since Classifieds 2.0
 */
global $query_string;
if ( '' == get_option( 'permalink_structure' ) )
    $cf_author_name = $_REQUEST['cf_author'];
else
    $cf_author_name = get_query_var( 'cf_author_name' );
$query_string = 'author_name=' . $cf_author_name;
$user_data = get_userdatabylogin( $cf_author_name );

if ( '' == get_option( 'permalink_structure' ) )
    $cf_author_url = '?cf_author=' . $user_data->user_login;
else
    $cf_author_url = '/cf-author/'. $user_data->user_login .'/';

get_header(); ?>

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
				<h1 class="page-title author"><?php printf( __( 'Classifieds By: %s', 'classifieds' ), "<span class='vcard'><a class='url fn n' href='" . get_option( 'siteurl' ) . $cf_author_url . "' title='" . esc_attr( $user_data->display_name ) . "' rel='me'>" . $user_data->display_name . "</a></span>" ); ?></h1>

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
                 if ( file_exists( get_template_directory() . "/loop-author.php" ) )
                    get_template_part( 'loop', 'author' );
                 else
                    load_template( CF_PLUGIN_DIR . 'ui-front/general/loop-author.php' );
                ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>