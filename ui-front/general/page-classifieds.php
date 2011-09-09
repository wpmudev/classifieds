<?php
/**
 * The template for displaying My Classifieds page.
 * You can override this file in your active theme.
 *
 * @package Classifieds
 * @subpackage UI Front
 * @since Classifieds 2.0
 */

get_header(); ?>

		<div id="container">
            <div id="content" class="cf-bp-wrap" role="main">

            <?php /* For BuddyPress compatibility */ ?>
            <?php global $bp; if ( isset( $bp ) ): ?><div class="cf-padder"><?php endif; ?>

                <h1 class="entry-title"><?php the_title(); ?></h1>

                <?php query_posts( array( 'post_status' => 'publish' , 'post_type' => array( 'classifieds' )  ) ); ?>

                <?php if ( !have_posts() ): ?>
                        <br />
                        <div class="info" id="message">
                            <p><?php _e( 'No Classifieds found.', 'classifieds' ); ?></p>
                        </div>
                    <?php endif; ?>

                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

                    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        <div class="entry-content">

                            <div class="cf-ad">

                                <div class="cf-image"><?php echo get_the_post_thumbnail( get_the_ID(), array( 200, 150 ) ); ?></div>
                                <div class="cf-info">
                                    <table>
                                        <tr>
                                            <th><?php _e( 'Title', 'classifieds' ); ?></th>
                                            <td>
                                                <a href="<?php the_permalink(); ?>"><?php echo $post->post_title; ?></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php _e( 'Posted By', 'classifieds' ); ?></th>
                                            <td>
                                                <?php
                                                $user = get_userdata( get_the_author_meta('ID') );

                                                if ( '' == get_option( 'permalink_structure' ) )
                                                    $cf_author_url = '?cf_author=' . $user->user_login;
                                                else
                                                    $cf_author_url = '/cf-author/'. $user->user_login .'/';

                                                /* For BuddyPress compatibility */
                                                if ( isset( $bp ) ): ?>
                                                    <a href="<?php echo bp_core_get_user_domain( get_the_author_meta('ID') ) . 'classifieds/';?>" alt="<?php the_author(); ?> Profile" >
                                                <?php else:
                                                ?>
                                                    <a href="<?php echo get_option( 'siteurl' ) . $cf_author_url; ?>" alt="<?php echo $user->display_name; ?> Profile" >
                                                <?php endif; ?>

                                                    <?php echo $user->display_name; ?>
                                                    </a>
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
                                        <tr>
                                        <tr>
                                            <th><?php _e( 'Expires', 'classifieds' ); ?></th>
                                            <td><?php if ( class_exists('Classifieds_Core') ) echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
                                        </tr>
                                    </table>
                                </div>

                            </div>

                        </div><!-- .entry-content -->
                    </div><!-- #post-## -->

                <?php endwhile; ?>
                <?php wp_reset_query(); ?>


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