<?php
/**
 * The Template for displaying all single classifieds posts.
 *
 * @package Classifieds
 * @subpackage UI Front
 * @since Classifieds 2.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

             <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-content">

                        <div class="cf-image"><?php echo get_the_post_thumbnail( get_the_ID(), array( 300, 300 ) ); ?></div>

                        <table class="cf-ad-info">
                           <tr>
                               <th><?php _e( 'Posted By', 'classifieds' ); ?></th>
                               <td><?php echo get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name'); ?></td>
                           </tr>
                           <tr>
                               <th><?php _e( 'Categories', 'classifieds' ); ?></th>
                               <td>
                                   <?php $taxonomies = get_taxonomies( array( 'object_type' => array( 'classifieds' ), '_builtin' => false ), 'names' ); ?>
                                   <?php foreach ( $taxonomies as $taxonomy ): ?>
                                       <?php echo get_the_term_list( $post->ID, $taxonomy, '', ', ', '' ) . ' '; ?>
                                   <?php endforeach; ?>
                               </td>
                           </tr>
                           <tr>
                               <th><?php _e( 'Posted On', 'classifieds' ); ?></th>
                               <td><?php the_date(); ?></td>
                           </tr>
                           <tr>
                               <th><?php _e( 'Expires On', 'classifieds' ); ?></th>
                               <td><?php if ( class_exists('Classifieds_Core') ) echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
                           </tr>
                        </table>

                        <form method="post" action="" class="contact-user-btn">
                            <input type="submit" value="Contact User" name="contact_user">
                        </form>
                        <div class="clear"></div>

                        <table>
                            <thead>
                                <tr>
                                    <th><?php _e( 'Description', 'classifieds' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php the_content(); ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <table class="cf-custom-fields" >
                            <?php $prefix = '_ct_'; $i = 1; ?>
                            <?php $custom_fields = get_site_option('ct_custom_fields'); ?>
                            <?php foreach ( $custom_fields as $custom_field ): ?>
                                <?php $field_value = get_post_meta( get_the_ID(), $prefix . $custom_field['field_id'], true ); ?>
                                <tr class="<?php if ( $i % 2 == 0 ) echo 'alt' ?>">
                                    <th><?php echo $custom_field['field_title']; ?></th>
                                    <td>
                                        <?php
                                        if ( is_array( $field_value ) ) {
                                            foreach ( $field_value as $value )
                                                echo $value  . ' ';
                                        } else {
                                            echo $field_value;
                                        } ?>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </table>

					</div><!-- .entry-content -->
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

            <?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>