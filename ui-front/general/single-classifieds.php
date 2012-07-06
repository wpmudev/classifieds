<?php
/**
* The Template for displaying all single classifieds posts.
* You can override this file in your active theme.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/

global $post, $wp_query;
$options = $this->get_options( 'general' );
$field_image = (empty($options['field_image_def'])) ? $this->plugin_url . 'ui-front/general/images/blank.gif' : $options['field_image_def'];

/**
* $content is already filled with the database html.
* This template just adds classifieds specfic code around it.
*/
?>

<?php if ( isset( $_POST['_wpnonce'] ) ): ?>
<br clear="all" />
<div id="cf-message-error">
	<?php _e( "Send message failed: you didn't filled all required fields in contact form!", $this->text_domain ); ?>
</div>
<br clear="all" />
<?php elseif ( isset( $_GET['sent'] ) && 1 == $_GET['sent'] ): ?>
<br clear="all" />
<div id="cf-message">
	<?php _e( 'Message is sent!', $this->text_domain ); ?>
</div>
<br clear="all" />
<?php endif; ?>
<div class="cf-post">
	<div class="cf-pad">

		<div class="cf-image">
			<?php
			if(has_post_thumbnail()){
				$thumbnail = get_the_post_thumbnail( $post->ID, array( 300, 300 ) );
			} else {
				$thumbnail = '<img width="300" height="300" title="no image" alt="no image" class="cf-no-image wp-post-image" src="' . $field_image . '">';
			}
			?>
			<a href="<?php the_permalink(); ?>" ><?php echo $thumbnail; ?></a>
		</div>
<div class="clear"></div>
<div>
		<table class="cf-ad-info">

			<tr>
				<th><?php _e( 'Posted By', $this->text_domain ); ?></th>
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
						<?php else: ?>
						<a href="<?php echo get_option( 'siteurl' ) . $cf_author_url; ?>" alt="<?php echo $user->display_name; ?> Profile" >
							<?php endif; ?>

							<?php echo $user->display_name; ?>

						</a>

					</td>
				</tr>
				<tr>
					<th><?php _e( 'Categories', $this->text_domain ); ?></th>
					<td>
						<?php $taxonomies = get_object_taxonomies( 'classifieds', 'names' ); ?>
						<?php foreach ( $taxonomies as $taxonomy ): ?>
						<?php echo get_the_term_list( $post->ID, $taxonomy, '', ', ', '' ) . ' '; ?>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Posted On', $this->text_domain ); ?></th>
					<td><?php the_date(); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Expires On', $this->text_domain ); ?></th>
					<td><?php if ( class_exists('Classifieds_Core') ) echo Classifieds_Core::get_expiration_date( get_the_ID() ); ?></td>
				</tr>
			</table>
</div>
			<form method="post" action="#" class="contact-user-btn action-form" id="action-form">
				<input type="submit" name="contact_user" value="<?php _e('Contact User', $this->text_domain ); ?>" onclick="classifieds.toggle_contact_form(); return false;" />
			</form>

			<form method="post" action="#" class="standard-form base cf-contact-form" id="confirm-form">
				<?php
				global $current_user;

				$name   = ( isset( $current_user->display_name ) && '' != $current_user->display_name ) ? $current_user->display_name :
				( ( isset( $current_user->first_name ) && '' != $current_user->first_name ) ? $current_user->first_name : '' );
				$email  = ( isset( $current_user->user_email ) && '' != $current_user->user_email ) ? $current_user->user_email : '';
				?>
				<div class="editfield">
					<label for="name"><?php _e( 'Name', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
					<input type="text" id="name" name ="name" value="<?php echo ( isset( $_POST['name'] ) ) ? $_POST['name'] : $name; ?>" />
					<p class="description"><?php _e( 'Enter your full name here.', $this->text_domain ); ?></p>
				</div>
				<div class="editfield">
					<label for="email"><?php _e( 'Email', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
					<input type="text" id="email" name ="email" value="<?php echo ( isset( $_POST['email'] ) ) ? $_POST['email'] : $email; ?>" />
					<p class="description"><?php _e( 'Enter a valid email address here.', $this->text_domain ); ?></p>
				</div>
				<div class="editfield">
					<label for="subject"><?php _e( 'Subject', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
					<input type="text" id="subject" name ="subject" value="<?php echo ( isset( $_POST['subject'] ) ) ? $_POST['subject'] : ''; ?>" />
					<p class="description"><?php _e( 'Enter the subject of your inquire here.', $this->text_domain ); ?></p>
				</div>
				<div class="editfield">
					<label for="message"><?php _e( 'Message', $this->text_domain ); ?> (<?php _e( 'required', $this->text_domain ); ?>)</label>
					<textarea id="message" name="message"><?php echo ( isset( $_POST['message'] ) ) ? $_POST['message'] : ''; ?></textarea>
					<p class="description"><?php _e( 'Enter the content of your inquire here.', $this->text_domain ); ?></p>
				</div>

				<div class="submit">
					<p>
						<?php wp_nonce_field( 'send_message' ); ?>
						<input type="submit" class="button confirm" value="<?php _e( 'Send', $this->text_domain ); ?>" name="contact_form_send" />
						<input type="submit" class="button cancel"  value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="classifieds.cancel_contact_form(); return false;" />
					</p>
				</div>

			</form>

			<div class="clear"></div>

			<table class="cf-description">
				<thead>
					<tr>
						<th><?php _e( 'Description', $this->text_domain ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php
							//$content is already filled with the database text. This just add classified specfic code around it.
							echo $content;
							?>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="cf-custom-fields" >
				<?php $prefix = '_ct_'; $i = 1; ?>
				<?php $custom_fields = get_site_option('ct_custom_fields'); ?>
				<?php foreach ( $custom_fields as $custom_field ):
				$output = false;
				foreach ( $custom_field['object_type'] as $custom_field_object_type ){
					if ( $custom_field_object_type == 'classifieds' ){
						$output = true; break;
					}
				}

				if($output){ ?>

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
					<?php $i++;
				}
				endforeach; ?>
			</table>

		</div>
	</div>