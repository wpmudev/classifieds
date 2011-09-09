<?php
/**
 * The Template for displaying all single classifieds posts.
 * You can override this file in your active theme.
 *
 * @package Classifieds
 * @subpackage UI Front
 * @since Classifieds 2.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

            <?php /* For BuddyPress compatibility */ ?>
            <?php global $bp; if ( isset( $bp ) ): ?><div class="cf-padder"><?php endif; ?>

             <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-content">

                        <div class="cf-image"><?php echo get_the_post_thumbnail( get_the_ID(), array( 300, 300 ) ); ?></div>

                        <table class="cf-ad-info">

                           <tr>
                               <th><?php _e( 'Posted By', 'classifieds' ); ?></th>
                               <td>

                                   <?php
                                   $user = get_userdata( get_the_author_ID() );

                                   if ( '' == get_option( 'permalink_structure' ) )
                                        $cf_author_url = '?cf_author=' . $user->user_login;
                                   else
                                        $cf_author_url = '/cf-author/'. $user->user_login .'/';

                                   /* For BuddyPress compatibility */
                                   if ( isset( $bp ) ): ?>
                                   <a href="<?php echo bp_core_get_user_domain( get_the_author_ID() ) . 'classifieds/';?>" alt="<?php the_author(); ?> Profile" >
                                   <?php else: ?>
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

                        <form method="post" action="" class="contact-user-btn action-form" id="action-form">
                            <input type="submit" name="contact_user" value="<?php _e('Contact User', 'classifieds' ); ?>" onClick="classifieds.toggle_contact_form(); return false;" />
                        </form>


                        <form method="post" action="" class="standard-form base cf-contact-form" id="confirm-form">

                            <div class="editfield">
                                <label for="name"><?php _e( 'Name', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                <input type="text" id="name" name ="name" value="" />
                                <p class="description"><?php _e( 'Enter your full name here.', 'classifieds' ); ?></p>
                            </div>
                            <div class="editfield">
                                <label for="email"><?php _e( 'Email', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                <input type="text" id="email" name ="email" value="" />
                                <p class="description"><?php _e( 'Enter a valid email address here.', 'classifieds' ); ?></p>
                            </div>
                            <div class="editfield">
                                <label for="subject"><?php _e( 'Subject', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                <input type="text" id="subject" name ="subject" value="" />
                                <p class="description"><?php _e( 'Enter the subject of your inquire here.', 'classifieds' ); ?></p>
                            </div>
                            <div class="editfield">
                                <label for="message"><?php _e( 'Message', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                <textarea id="message" name="message"></textarea>
                                <p class="description"><?php _e( 'Enter the content of your inquire here.', 'classifieds' ); ?></p>
                            </div>

                            <div class="submit">
                                <p>
                                    <input type="submit" class="button confirm" value="<?php _e( 'Send', 'classifieds' ); ?>" name="contact_form_send" />
                                    <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', 'classifieds' ); ?>" onClick="classifieds.cancel_contact_form(); return false;" />
                                </p>
                            </div>

                        </form>

                        <div class="clear"></div>

                        <table class="cf-description">
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

            <?php /* For BuddyPress compatibility */ ?>
            <?php if ( isset( $bp ) ): ?></div><?php endif; ?>

			</div><!-- #content -->

            <?php /* For BuddyPress compatibility */ ?>
            <?php if ( isset( $bp ) ): ?><?php locate_template( array( 'sidebar.php' ), true ); ?><?php endif; ?>

		</div><!-- #container -->

<?php /* For BuddyPress compatibility */ ?>
<?php if ( !isset( $bp ) ): ?><?php get_sidebar(); ?><?php endif; ?>

<?php get_footer(); ?>