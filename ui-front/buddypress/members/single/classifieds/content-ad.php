<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<?php $user = get_userdata( $post->post_author ); ?>

<div id="cf-fimage"><?php echo get_the_post_thumbnail( $post->ID, array( 300, 300 ) ); ?></div>
<table class="cf-ad-info">
   <tr>
       <th><?php _e( 'Posted By:', $this->text_domain ); ?></th>
       <td><?php echo $user->user_nicename; ?></td>
   </tr>
   <tr>
       <th><?php _e( 'Category:', $this->text_domain ); ?></th>
       <td>
           <?php foreach ( $this->taxonomy_names as $taxonomy ): ?>
               <?php echo get_the_term_list( $post->ID, $taxonomy, '', ', ', '' ) . ' '; ?>
           <?php endforeach; ?>
       </td>
   </tr>
   <tr>
       <th><?php _e( 'Description:', $this->text_domain ); ?></th>
       <td><?php echo $content; ?></td>
   </tr>
</table>
<div class="clear"></div>
<table>
    <thead>
        <tr>
        <?php foreach ( $this->custom_fields as $custom_field ): ?>
            <th><?php echo $custom_field['field_title']; ?></th>
        <?php endforeach; ?>        
        </tr>
    </thead>
    <tbody>
        <tr>
        <?php foreach ( $this->custom_fields as $custom_field ): ?>
        <?php $prefix = $this->custom_fields_prefix; ?>
        <?php $field_value = get_post_meta( $post->ID, $prefix . $custom_field['field_id'], true ); ?>
            <td><?php echo $field_value; ?> </td>
        <?php endforeach; ?>
        </tr>
    </tbody>
</table>