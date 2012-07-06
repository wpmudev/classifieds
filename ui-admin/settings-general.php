<?php if (!defined('ABSPATH')) die('No direct access allowed!');

global $wp_roles;
$options = $this->get_options( 'general' );

?>

<div class="wrap">

	<?php screen_icon('options-general'); ?>

	<?php $this->render_admin( 'navigation', array( 'page' => 'classifieds_settings', 'tab' => 'general' ) ); ?>

	<?php $this->render_admin( 'message' ); ?>

	<form action="#" method="post">
		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Classified Member Role', $this->text_domain ); ?></span></h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th>
							<label for="roles"><?php _e( 'Assign Member\'s Role', $this->text_domain ) ?></label>
						</th>
						<td>
							<select id="member_role" name="member_role" style="width:200px;">
								<?php foreach ( $wp_roles->role_names as $role => $name ): ?>
								<option value="<?php echo $role; ?>" <?php selected(isset($options['member_role'] ) && $role == $options['member_role']); ?> ><?php echo $name; ?></option>
								<?php endforeach; ?>
							</select>
							<br /><span class="description"><?php _e('Select the role to which you want to assign a Classifieds member on signup.', $this->text_domain); ?></span>
							<br /><span class="description"><?php _e('If you are running multiple plugins that have signups use the same role for both.', $this->text_domain); ?></span>
						</td>
					</tr>
					<tr>
						<th>
							<label>Manage Member Roles</label>
						</th>
						<td>
							<label>Add Role Name</label><br />
							<input type="text" id="new_role" name="new_role" size="30"/>
							<input type="submit" class="button" id="add_role" name="add_role" value="<?php _e( 'Add a Role', $this->text_domain ); ?>" />
							<br /><span class="description"><?php _e('Add a new role. Alphanumerics only.', $this->text_domain); ?></span>
							<br /><span class="description"><?php _e('When you add a new role you must add the appropriate capabilities to make it functional.', $this->text_domain); ?></span>
							<br /><br />
							<label>Custom Roles</label><br />
							<select id="delete_role" name="delete_role"  style="width:200px;">
								<?php 
								$system_roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
								$role_names = $wp_roles->role_names;
								foreach ( $role_names as $role => $name ): 
								if(! in_array($role, $system_roles) ): //Don't delete system roles.
								?>
								<option value="<?php echo $role; ?>"><?php echo $name; ?></option>
								<?php 
								endif;
								endforeach; 
								?>
							</select>
							<input type="button" class="button" onclick="jQuery(this).hide(); jQuery('#remove_role').show();" value="<?php _e( 'Remove a Role', $this->text_domain ); ?>" />
							<input type="submit" class="button-primary" id="remove_role" name="remove_role" value="<?php _e( 'Confirm Remove this Role', $this->text_domain ); ?>" style="display: none;" />

						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Form Fields', $this->text_domain ); ?></span></h3>
			<div class="inside">

				<table class="form-table">
					<tr>
						<th><label for="field_image_req"><?php _e( 'Image field:', $this->text_domain ); ?></label></th>
						<td>
							<input type="checkbox" id="field_image_req" name="field_image_req" value="1" <?php echo ( isset( $options['field_image_req'] ) && 1 == $options['field_image_req'] ) ? 'checked' : ''; ?> />
							<span class="description"><?php _e( 'not required', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="field_image_def"><?php _e( 'Use default image (URL):', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="field_image_def" name="field_image_def" size="70" value="<?php echo ( isset( $options['field_image_def'] ) && '' != $options['field_image_def'] ) ? $options['field_image_def'] : ''; ?>" />
							<br />
							<span class="description"><?php _e( 'this image will be show for all ads without images', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="postbox">
			<h3 class='hndle'><span><?php _e( 'Pagination Settings', $this->text_domain ); ?></span></h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th><label for="field_image_req"><?php _e( 'Pagination position:', $this->text_domain ); ?></label></th>
						<td>
							<input type="checkbox" id="pagination_top" name="pagination_top" value="1" <?php echo ( isset( $options['pagination_top'] ) && 1 == $options['pagination_top'] ) ? 'checked' : ''; ?> />
							<span class="description"><?php _e( 'display at top of page.', $this->text_domain ); ?></span>
							<br />
							<input type="checkbox" id="pagination_bottom" name="pagination_bottom" value="1" <?php echo ( isset( $options['pagination_bottom'] ) && 1 == $options['pagination_bottom'] ) ? 'checked' : ''; ?> />
							<span class="description"><?php _e( 'display at bottom of page.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="ads_per_page"><?php _e( 'Ads per Page:', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="ads_per_page" name="ads_per_page" size="4" value="<?php echo ( isset( $options['ads_per_page'] ) && '' != $options['ads_per_page'] ) ? $options['ads_per_page'] : '10'; ?>" />
							<br />
							<span class="description"><?php _e( 'Number of ads displayed on each page.', $this->text_domain ); ?></span>
						</td>
					</tr>
					<tr>
						<th><label for="pagination_range"><?php _e( 'Pagination Range:', $this->text_domain ); ?></label></th>
						<td>
							<input type="text" id="pagination_range" name="pagination_range" size="4" value="<?php echo ( isset( $options['pagination_range'] ) && '' != $options['pagination_range'] ) ? $options['pagination_range'] : '4'; ?>" />
							<br />
							<span class="description"><?php _e( 'Number of page links to show at one time in pagination', $this->text_domain ); ?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<p class="submit">
			<?php wp_nonce_field( 'verify' ); ?>
			<input type="hidden" name="key" value="general" />
			<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" />
		</p>
	</form>

</div>