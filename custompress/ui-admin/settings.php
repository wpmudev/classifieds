<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php
$enable_subsite_content_types = get_site_option('allow_per_site_content_types');
$keep_network_content_types   = get_site_option('keep_network_content_types');

if ( is_network_admin() )
$post_types = get_site_option('ct_custom_post_types');
else
$post_types = $this->post_types;


if ( $enable_subsite_content_types && $keep_network_content_types )
$network_post_types = get_site_option('ct_custom_post_types');


?>

<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('CustomPress Settings', $this->text_domain); ?></h2>

	<?php $this->render_admin('message'); ?>

	<form action="" method="post" class="cp-main">

		<?php if ( is_multisite() && is_super_admin() && is_network_admin() ): ?>
		<h3><?php _e( 'General', $this->text_domain );  ?></h3>
		<table class="form-table">
			<tr>
				<th>
					<label for="enable_subsite_content_types"><?php _e('Enable sub-site content types.', $this->text_domain) ?></label>
				</th>
				<td>
					<input type="checkbox" id="enable_subsite_content_types" name="enable_subsite_content_types" value="1" <?php if ( !empty( $enable_subsite_content_types ) ) echo 'checked="checked"'; ?>  />
					<span class="description"><?php _e('If you enable this option, sub-sites on your network will be able to define their own content types. If this option is not enabled ( default ) all sites on your network will be forced to use the network-wide content types defined by you, the Super Admin.', $this->text_domain); ?></span>
					<br /><br />
					<input type="radio" name="keep_network_content_types" value="1" <?php if ( !empty( $keep_network_content_types ) ) echo 'checked="checked"'; ?> />
					<span class="description"><?php _e('Keep the network-wide content types on sub-sites.', $this->text_domain); ?></span>
					<br />
					<input type="radio" name="keep_network_content_types" value="0" <?php if ( empty( $keep_network_content_types ) ) echo 'checked="checked"'; ?> />
					<span class="description"><?php _e('Remove the network-wide content types from sub-sites.', $this->text_domain); ?></span>
				</td>
			</tr>
		</table>
		<?php endif; ?>


		<?php if ( is_admin() && !is_network_admin() ): ?>
		<h3><?php _e( 'Post Types', $this->text_domain ); ?></h3>
		<table class="form-table">
			<tr>
				<th>
					<label for="post_type"><?php _e('Display post types on "Home": ', $this->text_domain) ?></label>
				</th>
				<td>
					<input type="checkbox" name="post_type[]" value="post" />
					<span class="description"><strong>post</strong></span>
					<br />
					<input type="checkbox" name="post_type[]" value="page" />
					<span class="description"><strong>page</strong></span>
					<br />
					<input type="checkbox" name="post_type[]" value="attachment" />
					<span class="description"><strong>attachment</strong></span>
					<br />
					<?php if ( !empty( $post_types ) ): ?>
					<?php foreach ( $post_types as $post_type => $args ): ?>
					<input type="checkbox" name="post_type[]" value="<?php echo( $post_type ); ?>" />
					<span class="description"><strong><?php echo $post_type; ?></strong></span>
					<br />
					<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( $enable_subsite_content_types && $keep_network_content_types ): ?>
					<?php if ( !empty( $network_post_types ) ): ?>
					<?php foreach ( $network_post_types as $post_type => $args ): ?>
					<input type="checkbox" name="post_type[]" value="<?php echo( $post_type ); ?>" />
					<span class="description"><strong><?php echo $post_type; ?></strong></span>
					<br />
					<?php endforeach; ?>
					<?php endif; ?>
					<?php endif; ?>
					<br />
					<span class="description"><?php _e('Check the custom post types you want to display on the "Home" page.', $this->text_domain); ?></span>
					<br /><br />
					<input type="checkbox" name="post_type[]" value="default" />
					<span class="description"><strong>default</strong></span><br /><br />
					<span class="description"><?php _e('If "default" is checked the "Home" page will display the default post types.', $this->text_domain); ?></span>
				</td>
			</tr>
		</table>
		<?php endif; ?>

		<?php if ( is_admin() && !is_network_admin() ): ?>
		<h3><?php _e( 'Date Picker Settings', $this->text_domain );  ?></h3>
		<table class="form-table">
			<tr>
				<th>
				</th>
				<td style="vertical-align:top; width:240px;">

					<select id="datepicker_theme" name="datepicker_theme" style="width:230px" onchange="jQuery('#custom_date_format').val(''); update_stylesheet('<?php echo $this->plugin_url . 'datepicker/css/'; ?>' + this.options[this.selectedIndex].value + '/datepicker.css'); " >
						<?php
						$date_format = $this->get_options('date_format');
						$date_format = (is_array($date_format)) ? 'mm/dd/yy' : $date_format;

						$datepicker_theme = $this->get_options('datepicker_theme');
						$datepicker_theme = (is_array($datepicker_theme)) ? 'excite-bike' : $datepicker_theme;

						$this->jquery_ui_css($datepicker_theme); //Load the current ui theme css
						
						$themes = glob($this->plugin_dir . 'datepicker/css/*', GLOB_ONLYDIR);
						foreach($themes as $theme){
							$theme = basename($theme);
							$selected = ($theme == $datepicker_theme) ? 'selected="selected"' : '';
							echo "<option value=\"$theme\" $selected >" . ucwords(str_replace('-',' ', $theme)) . "</option>\n";
						}
						?>
					</select><br />
					<span class="description"><?php _e('Select Datepicker Theme.', $this->text_domain) ?></span>
					<br /><br />
					<div class="pickdate"></div>
				</td>
				<td style="vertical-align:top;">
					<input type="text" id="date_format" name="date_format" size="38" value="<?php echo $date_format; ?>" onchange="jQuery('.pickdate').datepicker( 'option', 'dateFormat', this.value );"/><br />
					<span class="description"><?php _e('Select Date Format option or type your own', $this->text_domain) ?></span>
					<br /><br />
					<input class="pickdate" id="datepicker" type="text" size="38" value="" /><br />
					<span class="description"><?php _e('Date picker sample', $this->text_domain) ?></span>
					<script type="text/javascript">
						//Make em pickers
						jQuery('.pickdate').datepicker({ dateFormat : '<?php echo $date_format; ?>' });
						//Default date for display
						jQuery('#datepicker').attr('value', jQuery.datepicker.formatDate('<?php echo $date_format; ?>', new Date(), {}) );
					</script>

				</td>
			</tr>
		</table>
		<?php endif; ?>

		<?php if ( ( is_super_admin() && is_network_admin() ) || !is_multisite() ): ?>
		<h3><?php _e( 'Template Files', $this->text_domain ); ?></h3>
		<table class="form-table">
			<tr>
				<th>
					<label for="post_type"><?php _e('Create template file for: ', $this->text_domain) ?></label>
				</th>
				<td>
					<?php if ( !empty( $post_types )): ?>
					<?php foreach ( $post_types as $post_type => $args ): ?>
					<input type="checkbox" name="post_type_file[]" value="<?php echo $post_type; ?>" <?php if ( file_exists( TEMPLATEPATH . '/single-' .  strtolower( $post_type ) . '.php' )) echo( 'checked="checked" disabled="disabled"' ); ?> />
					<span class="description"><strong><?php echo $post_type; ?></strong></span>
					<br />
					<?php endforeach; ?>
					<?php else: ?>
					<span class="description"><strong><?php _e('No custom post types available', $this->text_domain); ?></strong></span>
					<?php endif; ?>
					<br />
					<span class="description"><?php _e('This will create "single-[post_type].php" file inside your active theme directory by copying your current single.php template. This file will be the custom template for your custom post type. You can then edit and customize it.', $this->text_domain); ?></span><br />
					<span class="description"><?php _e('In some cases you may not want to do that. For example if you don\'t have a template for your custom post type the default "single.php" will be used.', $this->text_domain); ?></span><br />
					<span class="description"><?php _e('Your active theme folder permissions have to be set to 777 for this option to work. After the file is created you can set your active theme directory permissions back to 755.', $this->text_domain); ?></span>
				</td>
			</tr>
		</table>
		<?php endif; ?>

		<p class="submit">
			<?php wp_nonce_field('verify'); ?>
			<input type="hidden" name="key" value="general_settings" />
			<input type="submit" class="button-primary" name="save" value="Save Changes">
		</p>

	</form>
</div>
