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
			<div id="content" role="main">
            
            <h1 class="entry-title"><?php the_title(); ?></h1>

            <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">

                        <div class="profile">

                            <?php if ( $msg ): ?>
                            <div class="<?php echo $class; ?>" id="message">
                                <p><?php echo $msg; ?></p>
                            </div>
                            <?php endif; ?>

                            <form class="standard-form base" method="post" action="" enctype="multipart/form-data">

                                <div class="editfield">
                                    <label for="title"><?php _e( 'Title', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                    <input type="text" value="<?php echo $_POST['title']; ?>" id="title" name="title">
                                    <p class="description"><?php _e( 'Enter title here.', 'classifieds' ); ?></p>
                                </div>

                                <div class="editfield">
                                    <label for="description"><?php _e( 'Description', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                    <textarea id="description" name="description" cols="40" rows="5"><?php echo $_POST['description']; ?></textarea>
                                    <p class="description"><?php _e( 'The main description of your ad.', 'classifieds' ); ?></p>
                                </div>

                                <div class="editfield">
                                    <label for="terms"><?php _e( 'Terms ( Categories / Tags )', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                    <table class="cf-terms">
                                        <tr>
                                            <?php $taxonomies = get_taxonomies( array( 'object_type' => array( 'classifieds' ), '_builtin' => false ), 'objects' ); ?>
                                            <?php foreach ( $taxonomies as $taxonomy_name => $taxonomy_object ): ?>
                                                <?php $terms = get_terms( $taxonomy_name, array( 'hide_empty' => 0 ) ); ?>
                                                <td>
                                                    <select id="terms" name="terms[<?php echo $taxonomy_name; ?>][]" multiple="multiple">
                                                        <optgroup label="<?php echo $taxonomy_object->labels->name; ?>">
                                                            <?php foreach ( $terms as $term ): ?>
                                                                <option value="<?php echo $term->slug; ?>" <?php if ( is_array( $_POST['terms'][$taxonomy_name] ) && in_array( $term->slug, $_POST['terms'][$taxonomy_name] ) ) echo 'selected="selected"' ?>><?php echo $term->name; ?></option>
                                                            <?php endforeach; ?>
                                                        </optgroup>
                                                    </select>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    </table>
                                    <p class="description"><?php _e( 'Select category for your ad.', 'classifieds' ); ?></p>
                                </div>

                                <div class="editfield">
                                    <label for="image"><?php _e( 'Select Featured Image', 'classifieds' ); ?> (<?php _e( 'required', 'classifieds' ); ?>)</label>
                                    <p id="featured-image">
                                        <input type="file" id="image" name="image">
                                        <input type="hidden" value="featured-image" id="action" name="action">
                                    </p>
                                </div>

                                <div class="editfield">
                                    <div class="radio">
                                        <span class="label"><?php _e( 'Ad Status' );  ?> (<?php _e( 'required', 'classifieds' ); ?>)</span>
                                        <div id="status-box">
                                            <label><input type="radio" value="publish" name="status" <?php if ( $_POST['status'] == 'publish' ) echo 'checked="checked"'; ?>> <?php _e( 'Published', 'classifieds' ); ?></label>
                                            <label><input type="radio" value="draft" name="status" <?php if ( $_POST['status'] == 'draft' ) echo 'checked="checked"'; ?>> <?php _e( 'Draft', 'classifieds' ); ?></label>
                                        </div>
                                    </div>
                                    <p class="description"><?php _e( 'Check a status for your Ad.', 'classifieds' ); ?></p>
                                </div>

                                <div class="editfield">
                                    <?php
                                    if ( file_exists( get_template_directory() . '/custom-fields.php' ) )
                                        get_template_part( 'custom-fields' );
                                    else
                                        load_template( CF_PLUGIN_DIR . 'ui-front/general/custom-fields.php', false );
                                    ?>
                                </div>

                                <div class="submit">
                                    <input type="submit" value="Save Changes " name="save">
                                </div>

                            </form>

                        </div>

						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

            <?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
