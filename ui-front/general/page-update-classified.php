<?php
/**
* The template for displaying the Add/edit classified page.
* You can override this file in your active theme.
*
* @license GNU General Public License (Version 2 - GPLv2) {@link http://www.gnu.org/licenses/gpl-2.0.html}
*/
if (!defined('ABSPATH')) die('No direct access allowed!');

global $post, $post_ID, $CustomPress_Core;

$classified_data   = '';
$selected_cats  = '';
$error = get_query_var('cf_error');
$post_statuses = get_post_statuses(); // get the wp post status list

$options = $this->get_options('general');

$allowed_statuses['moderation'] = (empty($options['moderation']) ) ? array('publish' => 1, 'draft'=> 1 ) : $options['moderation']; // Get the ones we allow
$allowed_statuses = array_reverse(array_intersect_key($post_statuses, $allowed_statuses['moderation']) ); //return the reduced list

//Are we adding a Classified?
if(! isset($_REQUEST['post_id']) ){

	//Make an auto-draft so we have a post id to connect attachments to. Set global $post_ID so media editor can hook up. Watch the case
	$post_ID = wp_insert_post( array( 'post_title' => __( 'Auto Draft' ), 'post_type' => 'classifieds', 'post_status' => 'auto-draft', 'comment_status' => 'closed', 'ping_status' => 'closed'), true );
	$classified_data = get_post($post_ID, ARRAY_A );
	$classified_data['post_title'] = ''; //Have to have a title to insert the auto-save but we don't want it as final.
	$editing = false;
}

//Or are we editing a Classified?
elseif( isset($_REQUEST['post_id']) ) {
	$classified_data = get_post(  $_REQUEST['post_id'], ARRAY_A );
	$post_ID = $classified_data['ID'];
	$editing = true;
}
$post = get_post($post_ID);

if ( isset( $_POST['classified_data'] ) ) $classified_data = $_POST['classified_data'];

require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/post.php');

$editor_settings =   array(
'wpautop' => true, // use wpautop?
'media_buttons' => true, // show insert/upload button(s)
'textarea_name' => 'classified_data[post_content]', // set the textarea name to something different, square brackets [] can be used here
'textarea_rows' => 10, //get_option('default_post_edit_rows', 10), // rows="..."
'tabindex' => '',
'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
'editor_class' => 'required', // add extra class(es) to the editor textarea
'teeny' => false, // output the minimal editor config used in Press This
'dfw' => false, // replace the default fullscreen with DFW (needs specific css)
'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
);

$classified_content = (empty( $classified_data['post_content'] ) ) ? '' : $classified_data['post_content'];

wp_enqueue_script('set-post-thumbnail');
?>

<!-- Begin Update Classifieds -->
<script type="text/javascript" src="<?php echo $this->plugin_url . 'ui-front/js/jquery.tagsinput.min.js?ver=2'; ?>" ></script>
<script type="text/javascript" src="<?php echo $this->plugin_url . 'ui-front/js/media-post.js'; ?>" ></script>
<script type="text/javascript" src="<?php echo $this->plugin_url . 'ui-front/js/ui-front.js'; ?>" >
</script>

<?php if ( !empty( $error ) ): ?>
<br /><div class="error"><?php echo $error . '<br />'; ?></div>
<?php endif; ?>


<div class="cf_update_form">

	<?php if ( isset( $msg ) ): ?>
	<div class="<?php echo $class; ?>" id="message">
		<p><?php echo $msg; ?></p>
	</div>
	<?php endif; ?>

	<form class="standard-form base" method="post" action="#" enctype="multipart/form-data" id="cf_update_form" >
		<input type="hidden" id="post_ID" name="classified_data[ID]" value="<?php echo ( empty( $classified_data['ID'] ) ) ? '' : $classified_data['ID']; ?>" />
		<input type="hidden" name="post_id" value="<?php echo ( empty( $classified_data['ID'] ) ) ? '' : $classified_data['ID']; ?>" />

		<?php if(post_type_supports('classifieds','title') ): ?>
		<div class="editfield">
			<label for="title"><?php _e( 'Title', $this->text_domain ); ?></label>
			<input class="required" type="text" id="title" name="classified_data[post_title]" value="<?php echo ( empty( $classified_data['post_title'] ) ) ? '' : esc_attr($classified_data['post_title']); ?>" />
			<p class="description"><?php _e( 'Enter title here.', $this->text_domain ); ?></p>
		</div>
		<?php endif; ?>

		<?php if(post_type_supports('classifieds','thumbnail') && current_theme_supports('post-thumbnails') ): ?>
		<div class="editfield">

			<?php if(empty($options['media_manager']) ): ?>

			<?php if(has_post_thumbnail()) the_post_thumbnail('thumbnail'); ?><br />
			<script type="text/javascript">js_translate.image_chosen = '<?php _e("Feature Image Chosen", $this->text_domain); ?>';</script>
			<span class="upload-button">

				<?php $class = ( empty($options['field_image_req']) && !has_post_thumbnail() ) ? 'required' : ''; ?>

				<input type="file" name="feature_image" size="1" id="image" class="<?php echo $class; ?>" />
				<button type="button" class="button"><?php _e('Set Feature Image', $this->text_domain); ?></button>
			</span>
			<br />

			<?php else: ?>

			<div id="postimagediv">
				<div class="inside">
					<?php
					$thumbnail_id = get_post_meta( $post_ID, '_thumbnail_id', true );
					echo _wp_post_thumbnail_html($thumbnail_id, $post_ID);
					?>
				</div>
			</div>
			<?php endif; ?>

		</div>
		<?php endif; ?>

		<?php if(post_type_supports('classifieds','editor') ): ?>
		<label for="classifiedcontent"><?php _e( 'Content', $this->text_domain ); ?></label>

		<?php wp_editor( $classified_content, 'classifiedcontent', $editor_settings); ?>

		<p class="description"><?php _e( 'The content of your classified.', $this->text_domain ); ?></p>
		<?php endif; ?>

		<?php if(post_type_supports('classifieds','excerpt') ): ?>
		<div class="editfield alt">
			<label for="excerpt"><?php _e( 'Excerpt', $this->text_domain ); ?></label>
			<textarea id="excerpt" name="classified_data[post_excerpt]" rows="2" ><?php echo (empty( $classified_data['post_excerpt'] ) ) ? '' : esc_textarea($classified_data['post_excerpt']); ?></textarea>
			<p class="description"><?php _e( 'A short excerpt of your classified.', $this->text_domain ); ?></p>
		</div>
		<?php endif; ?>

		<?php
		//get related hierarchical taxonomies
		$taxonomies = get_object_taxonomies('classifieds', 'objects');
		//Loop through the taxonomies that apply
		foreach($taxonomies as $taxonomy):
		if( ! $taxonomy->hierarchical) continue;
		$tax_name = $taxonomy->name;
		$labels = $taxonomy->labels;
		//Get this Taxonomies terms
		$selected_cats = array_values( wp_get_post_terms($classified_data['ID'], $tax_name, array('fields' => 'ids') ) );

		?>

		<div id="taxonomy-<?php echo $tax_name; ?>" class="cf_taxonomydiv">
			<label><?php echo $labels->all_items; ?></label>

			<div id="<?php echo $tax_name; ?>_all" class="cf_tax_panel">
				<?php
				$name = ( $tax_name == 'category' ) ? 'post_category' : 'tax_input[' . $tax_name . ']';
				echo "<input type='hidden' name='{$name}[]' value='0' />"; 		// Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
				?>
				<ul id="<?php echo $tax_name; ?>_checklist" class="list:<?php echo $labels->name; ?> categorychecklist form-no-clear">
					<?php wp_terms_checklist( 0, array( 'taxonomy' => $tax_name, 'selected_cats' => $selected_cats, 'checked_ontop' => false ) ) ?>
				</ul>
			</div>
			<span class="description"><?php echo $labels->add_or_remove_items; ?></span>
		</div>
		<?php endforeach; ?>

		<?php
		//Loop through the taxonomies that apply
		foreach($taxonomies as $tag):
		if( $tag->hierarchical) continue;

		$tag_name = $tag->name;
		$labels = $tag->labels;

		//Get this Taxonomies terms
		$tag_list = strip_tags(get_the_term_list( $classified_data['ID'], $tag_name, '', ',', '' ));

		?>

		<div class="cf_taxonomy">
			<div id="<?php echo $tag_name; ?>-checklist" class="tagchecklist">
				<label><?php echo $labels->name; ?>
					<input id="tag_<?php echo $tag_name; ?>" name="tag_input[<?php echo $tag_name; ?>]" type="text" value="<?php echo $tag_list?>" />
				</label>
				<span class="description"><?php echo $labels->add_or_remove_items; ?></span>
			</div>

			<script type="text/javascript" > jQuery('#tag_<?php echo $tag_name; ?>').tagsInput({width:'auto', height:'150px', defaultText: '<?php _e("add a tag", $this->text_domain); ?>'}); </script>
		</div>
		<?php endforeach; ?>

		<div class="clear"><br /></div>

		<div class="editfield" >
			<label for="title"><?php _e( 'Status', $this->text_domain ); ?></label>
			<div id="status-box">
				<select name="classified_data[post_status]" id="classified_data[post_status]">
					<?php
					foreach($allowed_statuses as $key => $value): ?>

					<option value="<?php echo $key; ?>" <?php selected( ! empty($classified_data['post_status'] ) && $key == $classified_data['post_status'] ); ?> ><?php echo $value; ?></option>

					<?php endforeach; ?>
				</select>
			</div>
			<p class="description"><?php _e( 'Select a status for your classified.', $this->text_domain ); ?></p>
		</div>

		<?php if ( isset( $CustomPress_Core ) ) : ?>
		<?php echo do_shortcode('[custom_fields_input style="editfield"]'); ?>
		<?php endif; ?>

		<?php if ( !empty( $error ) ): ?>
		<br /><div class="error"><?php echo $error . '<br />'; ?></div>
		<?php endif; ?>

		<div class="submit">
			<?php wp_nonce_field( 'verify' ); ?>
			<input type="submit" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" name="update_classified">

			<input type="button" value="<?php _e( 'Cancel', $this->text_domain ); ?>" onclick="location.href='<?php echo get_permalink($this->my_classifieds_page_id); ?>'">
		</div>
	</form>
</div><!-- .cf_update_form -->
<!-- End Update Classifieds -->
<script type="text/javascript">
	jQuery('input[name="update_classified"]').mousedown( function() {
		tinyMCE.triggerSave();
	});
</script>