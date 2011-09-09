<?php

/**
 * Load core DB data.
 */
if ( !class_exists('Classifieds_Core_Data') ):
class Classifieds_Core_Data extends Classifieds_Core {

    /**
     * Constructor.
     *
     * @return void
     **/
    function Classifieds_Core_Data() {
        add_action( 'init', array( &$this, 'load_data' ), 0 );
    }

    /**
     * Load initial Content Types data for plugin
     *
     * @return void
     */
    function load_data() {
        /* Get sete options. If empty return an array */
        $options = ( get_site_option( $this->options_name ) ) ? get_site_option( $this->options_name ) : array();
        /* Check whether post types data is loaded */
        if ( !isset( $options['cf-data']['post_types_loaded'] ) ) {
            /* Unserialize raw array data */
            $post_types_tmp = unserialize( 'a:1:{s:11:"classifieds";a:10:{s:6:"labels";a:10:{s:4:"name";s:11:"Classifieds";s:13:"singular_name";s:10:"Classified";s:7:"add_new";s:7:"Add New";s:12:"add_new_item";s:18:"Add New Classified";s:9:"edit_item";s:15:"Edit Classified";s:8:"new_item";s:14:"New Classified";s:9:"view_item";s:15:"View Classified";s:12:"search_items";s:18:"Search Classifieds";s:9:"not_found";s:20:"No Classifieds Found";s:18:"not_found_in_trash";s:29:"No Classifieds Found In Trash";}s:8:"supports";a:5:{s:5:"title";s:5:"title";s:6:"editor";s:6:"editor";s:6:"author";s:6:"author";s:9:"thumbnail";s:9:"thumbnail";s:7:"excerpt";s:7:"excerpt";}s:15:"capability_type";s:10:"classified";s:11:"description";s:22:"Classifieds post type.";s:13:"menu_position";i:50;s:6:"public";b:1;s:12:"hierarchical";b:0;s:7:"rewrite";a:1:{s:4:"slug";s:10:"classified";}s:9:"query_var";b:1;s:10:"can_export";b:1;}}' );
            /* Get available post types */
            $post_types = ( get_site_option( 'ct_custom_post_types' ) ) ? array_merge( get_site_option( 'ct_custom_post_types' ), $post_types_tmp ) : $post_types_tmp;
            /* Update post types and delete tmp options */
            update_site_option( 'ct_custom_post_types', $post_types );
            update_site_option( 'ct_flush_rewrite_rules', true );
            /* Create data loaded flag so we don't load the data twice */
            $data_loaded = array( 'cf-data' => array( 'post_types_loaded' => true ));
            $options = array_merge_recursive( $options, $data_loaded );
            update_site_option( $this->options_name , $options );
        }
        /* Check whether taxonomies data is loaded */
        if ( !isset( $options['cf-data']['taxonomies_loaded'] ) )  {
            /* Unserialize raw array data */
            $taxonomies_tmp = unserialize( 'a:2:{s:16:"classifieds_tags";a:2:{s:11:"object_type";a:1:{i:0;s:11:"classifieds";}s:4:"args";a:6:{s:6:"labels";a:2:{s:4:"name";s:4:"Tags";s:13:"singular_name";s:3:"Tag";}s:6:"public";b:1;s:12:"hierarchical";b:0;s:7:"rewrite";a:1:{s:4:"slug";s:7:"cf-tags";}s:9:"query_var";b:1;s:12:"capabilities";a:1:{s:12:"assign_terms";s:12:"assign_terms";}}}s:22:"classifieds_categories";a:2:{s:11:"object_type";a:1:{i:0;s:11:"classifieds";}s:4:"args";a:6:{s:6:"labels";a:2:{s:4:"name";s:10:"Categories";s:13:"singular_name";s:8:"Categoru";}s:6:"public";b:1;s:12:"hierarchical";b:1;s:7:"rewrite";a:1:{s:4:"slug";s:13:"cf-categories";}s:9:"query_var";b:1;s:12:"capabilities";a:1:{s:12:"assign_terms";s:12:"assign_terms";}}}}' );
            /* Get available taxonomies */
            $taxonomies = ( get_site_option( 'ct_custom_taxonomies' ) ) ? array_merge( get_site_option( 'ct_custom_taxonomies' ), $taxonomies_tmp ) : $taxonomies_tmp;
            /* Update taxonomies and delete tmp options */
            update_site_option( 'ct_custom_taxonomies', $taxonomies );
            update_site_option( 'ct_flush_rewrite_rules', true );
            /* Create data loaded flag so we don't load the data twice */
            $data_loaded = array( 'cf-data' => array( 'taxonomies_loaded' => true ));
            $options = array_merge_recursive( $options, $data_loaded );
            update_site_option( $this->options_name, $options );
        }
        /* Check whether custom fields data is loaded */
        if ( !isset( $options['cf-data']['custom_fields_loaded'] ) ) {
            /* Unserialize raw array data */
            $custom_fields_tmp = unserialize( 'a:2:{s:23:"selectbox_4cf582bd61fa4";a:9:{s:11:"field_title";s:8:"Duration";s:10:"field_type";s:9:"selectbox";s:16:"field_sort_order";s:7:"default";s:13:"field_options";a:5:{i:1;s:10:"----------";i:2;s:6:"1 Week";i:3;s:7:"2 Weeks";i:4;s:7:"3 Weeks";i:5;s:7:"4 Weeks";}s:20:"field_default_option";s:1:"1";s:17:"field_description";s:25:"The duration of this ad. ";s:11:"object_type";a:1:{i:0;s:11:"classifieds";}s:8:"required";N;s:8:"field_id";s:23:"selectbox_4cf582bd61fa4";}s:18:"text_4cfeb3eac6f1f";a:8:{s:11:"field_title";s:4:"Cost";s:10:"field_type";s:4:"text";s:16:"field_sort_order";s:7:"default";s:20:"field_default_option";N;s:17:"field_description";s:21:"The cost of the item.";s:11:"object_type";a:1:{i:0;s:11:"classifieds";}s:8:"required";N;s:8:"field_id";s:18:"text_4cfeb3eac6f1f";}}' );
            /* Get available custom fields */
            $custom_fields = ( get_site_option( 'ct_custom_fields' ) ) ? array_merge( get_site_option( 'ct_custom_fields' ), $custom_fields_tmp ) : $custom_fields_tmp;
            /* Update custom fields options */
            update_site_option( 'ct_custom_fields', $custom_fields );
            /* Create data loaded flag so we don't load the data twice */
            $data_loaded = array( 'cf-data' => array( 'custom_fields_loaded' => true ));
            $options = array_merge_recursive( $options, $data_loaded );
            update_site_option( $this->options_name, $options );
        }

        //add rule for show cf-author page
        global $wp, $wp_rewrite;
        $wp->add_query_var( 'cf_author_name' );
        $result = add_query_arg(  array(
            'cf_author_name' => '$matches[1]',
        ), 'index.php' );
        add_rewrite_rule( 'cf-author/(.+?)/?$', $result, 'top' );
        $rules = get_option( 'rewrite_rules' );
        if ( ! isset( $rules['cf-author/(.+?)/?$'] ) )
            $wp_rewrite->flush_rules();

    }
}
endif;

/* Initiate Class */
if ( class_exists('Classifieds_Core_Data') )
	$__classifieds_core_data = new Classifieds_Core_Data();
?>