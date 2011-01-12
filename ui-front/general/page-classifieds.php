<?php
/**
 * The template for displaying My Classifieds page.
 *
 * @package Classifieds
 * @subpackage UI Front
 * @since Classifieds 2.0
 */

get_header(); ?>

		<div id="container">
            <div id="content" class="cf-bp-wrap" role="main">
                
            <?php /* For BuddyPress compatibility */ ?>
            <?php global $bp; if ( isset( $bp ) ): ?><div class="padder"><?php endif; ?>

                <h1 class="entry-title"><?php the_title(); ?></h1>

                <?php query_posts( array( 'post_status' => 'publish' , 'post_type' => array( 'classifieds' )  ) ); ?>
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
                                                <?php /* For BuddyPress compatibility */ ?>
                                                <?php if ( isset( $bp ) ): ?>
                                                <a href="<?php echo bp_core_get_user_domain( get_the_author_ID() ) . 'classifieds/';?>" alt="<?php echo get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name'). '\'s Profile';  ?>" >
                                                <?php else: ?>
                                                <a href="<?php echo get_author_posts_url( get_the_author_ID() ); ?>" alt="<?php echo get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name'). '\'s Profile';  ?>" >    
                                                <?php endif; ?>

                                                    <?php echo get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name'); ?></td>

                                                </a>
                                        </tr>
                                        <tr>
                                            <th><?php _e( 'Categories', 'classifieds' ); ?></th>
                                            <td>
                                               <?php $taxonomies = get_taxonomies( array( 'object_type' => array( 'classifieds' ), '_builtin' => false ), 'names' ); ?>
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
