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
            <div id="content" class="my-classifieds" role="main">

            <?php $action = get_query_var('cf_action'); ?>
            <?php if ( $action == 'my-classifieds' ): ?>
                
                <h1 class="entry-title"><?php the_title(); ?></h1>

                <form method="post" action="" class="create-new-btn">
                    <input type="submit" value="Create New Classified" name="create_new">
                </form>

                <?php if ( is_user_logged_in() ): ?>
                <ul class="button-nav">
                    <li class="<?php if ( isset( $_GET['active'] ) || empty( $_GET ) ) echo 'current'; ?>"><a href="<?php echo get_bloginfo('url') . '/classifieds/my-classifieds/?active'; ?>"><?php _e( 'Active Ads', 'classifieds' ); ?></a></li>
                    <li class="<?php if ( isset( $_GET['saved'] ) )  echo 'current'; ?>"><a href="<?php echo get_bloginfo('url') . '/classifieds/my-classifieds/?saved'; ?>"><?php _e( 'Saved Ads', 'classifieds' ); ?></a></li>
                    <li class="<?php if ( isset( $_GET['ended'] ) )  echo 'current'; ?>"><a href="<?php echo get_bloginfo('url') . '/classifieds/my-classifieds/?ended'; ?>"><?php _e( 'Ended Ads', 'classifieds' ); ?></a></li>
                </ul>
                <?php endif; ?>
                <div class="clear"></div>

                <?php
                /* Get posts based on post_status */
                if ( isset( $_GET['active'] ) || empty( $_GET ) ) {
                    query_posts( array( 'author' => 1, 'post_type' => array( 'classifieds' ), 'post_status' => 'publish' ) );
                    $sub = 'active';
                } elseif ( isset( $_GET['saved'] ) ) {
                    query_posts( array( 'author' => 1, 'post_type' => array( 'classifieds' ), 'post_status' => 'draft' ) );
                    $sub = 'saved';
                } elseif ( isset( $_GET['ended'] ) ) {
                    query_posts( array( 'author' => 1, 'post_type' => array( 'classifieds' ), 'post_status' => 'private' ) );
                    $sub = 'ended';
                } ?>
                
                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

                    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        <div class="entry-content">

                            <div class="classifieds">

                                <?php if ( $msg ): ?>
                                <div class="<?php echo $class; ?>" id="message">
                                    <p><?php echo $msg; ?></p>
                                </div>
                                <?php endif; ?>


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
                                                <td><?php echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <form action="" method="post" id="action-form-<?php the_ID(); ?>" class="action-form">
                                        <?php wp_nonce_field('verify'); ?>
                                        <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                                        <input type="hidden" name="url" value="<?php the_permalink(); ?>" />
                                        <input type="submit" name="edit" value="<?php _e('Edit Ad', 'classifieds' ); ?>" />
                                        <?php if ( $sub == 'active' ): ?>
                                            <input type="submit" name="end" value="<?php _e('End Ad', 'classifieds' ); ?>" onClick="classifieds.toggle_end('<?php echo $post->ID; ?>'); return false;" />
                                        <?php elseif ( $sub == 'saved' || $sub == 'ended' ): ?>
                                            <input type="submit" name="renew" value="<?php _e('Renew Ad', 'classifieds' ); ?>" onClick="classifieds.toggle_renew('<?php echo $post->ID; ?>'); return false;" />
                                        <?php endif; ?>
                                        <input type="submit" name="delete" value="<?php _e('Delete Ad', 'classifieds' ); ?>" onClick="classifieds.toggle_delete('<?php echo $post->ID; ?>'); return false;" />
                                    </form>

                                    <form action="" method="post" id="confirm-form-<?php the_ID(); ?>" class="confirm-form">
                                        <?php wp_nonce_field('verify'); ?>
                                        <input type="hidden" name="action" />
                                        <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                                        <input type="hidden" name="post_title" value="<?php the_title(); ?>" />
                                        <?php if ( $sub == 'saved' || $sub == 'ended' ): ?>
                                            <select name="duration">
                                                <option value="1 Week"><?php _e( '1 Week',  'classifieds' ); ?></option>
                                                <option value="2 Weeks"><?php _e( '2 Weeks', 'classifieds' ); ?></option>
                                                <option value="3 Weeks"><?php _e( '3 Weeks', 'classifieds' ); ?></option>
                                                <option value="4 Weeks"><?php _e( '4 Weeks', 'classifieds' ); ?></option>
                                            </select>
                                        <?php endif; ?>
                                        <input type="submit" class="button confirm" value="<?php _e( 'Confirm', 'classifieds' ); ?>" name="confirm" />
                                        <input type="submit" class="button cancel"  value="<?php _e( 'Cancel', 'classifieds' ); ?>" onClick="classifieds.cancel('<?php echo $post->ID; ?>'); return false;" />
                                    </form>

                                </div>

                            </div>

                        </div><!-- .entry-content -->
                    </div><!-- #post-## -->

                <?php endwhile; ?>
                <?php wp_reset_query(); ?>

            <?php elseif ( $action == 'edit' ): ?>

                <?php query_posts( array( 'p' => get_query_var('cf_post_id')  , 'author' => 1, 'post_type' => array( 'classifieds' ) ) ); ?>
                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

                <h1 class="entry-title">My Classifieds / Editing: <?php echo $post->post_title; ?></h1>

                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <div class="entry-content">
                        
                        <div class="profile">

                            <form class="standard-form base" method="post" action="" enctype="multipart/form-data">

                                <div class="editfield">
                                    <label for="title"><?php _e( 'Title', 'classifieds' ); ?></label>
                                    <input type="text" value="<?php echo $post->post_title; ?>" id="title" name="title">
                                    <p class="description"><?php _e( 'Enter title here.', 'classifieds' ); ?></p>
                                </div>

                                <div class="editfield alt">
                                    <label for="description"><?php _e( 'Description', 'classifieds' ); ?></label>
                                    <textarea id="description" name="description" cols="40" rows="5"><?php echo $post->post_content; ?></textarea>
                                    <p class="description"><?php _e( 'The main description of your ad.', 'classifieds' ); ?></p>
                                </div>

                                <div class="editfield">
                                    <label for="terms"><?php _e( 'Terms ( Categories / Tags )', 'classifieds' ); ?></label>
                                    <table class="cf-terms">
                                        <tr>
                                        <?php 
                                              $taxonomies = get_taxonomies( array( 'object_type' => array( 'classifieds' ), '_builtin' => false ), 'names' );
                                              $post_terms = wp_get_object_terms( get_the_ID(), $taxonomies );
                                              $taxonomies = get_taxonomies( array( 'object_type' => array( 'classifieds' ), '_builtin' => false ), 'objects' ); ?>
                                        <?php foreach ( $taxonomies as $taxonomy_name => $taxonomy_object ): ?>
                                            <?php $terms = get_terms( $taxonomy_name, array( 'hide_empty' => 0 ) ); ?>
                                            <td>
                                                <select id="terms" name="terms[<?php echo $taxonomy_name; ?>][]" multiple="multiple">
                                                    <optgroup label="<?php echo $taxonomy_object->labels->name; ?>">
                                                        <?php foreach ( $terms as $term ): ?>
                                                        <option value="<?php echo $term->slug; ?>" <?php foreach ( $post_terms as $post_term ) { if ( $post_term->term_id == $term->term_id  ) echo 'selected="selected"'; } ?>><?php echo $term->name; ?></option>
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
                                    <?php echo get_the_post_thumbnail( get_the_ID(), array( 200, 150 ) ); ?>
                                    <label for="image"><?php _e( 'Change Featured Image', 'classifieds' ); ?></label>
                                    <p id="featured-image">
                                        <input type="file" id="image" name="image">
                                        <input type="hidden" value="featured-image" id="action" name="action">
                                    </p>
                                </div>

                                <div class="editfield">
                                    <div class="radio">
                                        <span class="label"><?php _e( 'Ad Status' );  ?></span>
                                        <div id="status-box">
                                            <label><input type="radio" value="publish" name="status" <?php if ( $post->post_status == 'publish' ) echo 'checked="checked"'; ?>> <?php _e( 'Published', 'classifieds' ); ?></label>
                                            <label><input type="radio" value="draft" name="status" <?php if ( $post->post_status == 'draft' ) echo 'checked="checked"'; ?>> <?php _e( 'Draft', 'classifieds' ); ?></label>
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
                                    <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                                    <input type="hidden" name="post_title" value="<?php the_title(); ?>" />
                                    <input type="hidden" name="url" value="<?php the_guid(); ?>" />
                                    <input type="submit" value="Save Changes " name="update">
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>
                <?php wp_reset_query(); ?>

            <?php elseif ( $action == 'create-new' ): ?>
            
                <h1 class="entry-title"><?php the_title(); ?> / <?php _e( 'Create New', 'classifieds' ); ?></h1>

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
                                        <input type="submit" value="Save Changes " name="save_new">
                                    </div>

                                </form>

                            </div>
                        </div><!-- .entry-content -->
                    </div><!-- #post-## -->

                <?php endwhile; ?>

            <?php endif; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
