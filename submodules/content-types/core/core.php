<?php


/**
 * Classifieds Core Class
 **/
if ( !class_exists('Content_Types_Core') ):
class Content_Types_Core {

    /** @var string Url to the submodule directory */
    var $submodule_url = CT_SUBMODULE_URL;
    /** @var string Path to the submodule directory */
    var $submodule_dir = CT_SUBMODULE_DIR;
    /** @var string Parent menu slug */
    var $parent_menu_slug = CT_SUBMENU_PARENT_SLUG;
    /** @var string Parent menu slug */
    var $text_domain = 'content_types';
    /** @var array Avilable Post Types */
    var $post_types;
    /** @var array Avilable Taxonomies */
    var $taxonomies;
    /** @var array Avilable Custom Fields */
    var $custom_fields;
    /** @var array Avilable Custom Fields */
    var $registered_post_type_names;
    /** @var boolean Flag whether to redirect or not */
    var $allow_redirect;

    /**
     * Constructor.
     **/
    function Content_Types_Core() {
        /* Hook the entire class to WordPress init hook */
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'init', array( &$this, 'init_vars' ) );
    }

    /**
     * 
     */
    function init() {
        add_action( 'init', array( &$this,'handle_post_type_requests' ), 0 );
        add_action( 'init', array( &$this, 'handle_delete_post_type_requests' ), 0 );
        add_action( 'init', array( &$this, 'register_post_types' ), 2 );
        add_action( 'init', array( &$this, 'handle_taxonomy_requests' ), 0 );
        add_action( 'init', array( &$this, 'handle_delete_taxonomy_requests' ), 0 );
        add_action( 'init', array( &$this, 'register_taxonomies' ), 1 );
        add_action( 'init', array( &$this, 'handle_custom_field_requests' ), 0 );
        add_action( 'init', array( &$this, 'handle_delete_custom_fields_requests' ), 0 );
        add_action( 'init', array( &$this, 'load_plugin_textdomain' ), 0 );
        add_action( 'admin_menu', array( &$this, 'create_custom_fields' ), 2 );
        add_action( 'save_post', array( &$this, 'save_custom_fields' ), 1, 1 );
        add_action( 'user_register', array( &$this, 'check_user_registration' ) );
    }

    /**
     * 
     */
    function init_vars() {
        $this->post_types = get_site_option( 'ct_custom_post_types' );
        $this->taxonomies = get_site_option( 'ct_custom_taxonomies' );
        $this->custom_fields = get_site_option( 'ct_custom_fields' );
        $this->registered_post_type_names = get_post_types('','names');
    }

    /**
     * Loads "content_types-[xx_XX].mo" language file from the "ct-languages" directory
     */
    function load_plugin_textdomain() {
        $submodule_dir = $this->submodule_dir . 'languages';
        load_plugin_textdomain( $this->text_domain, null, $submodule_dir );
    }

    /**
     * Intercept $_POST request and processes the custom post type submissions.
     *
     * @global bool $ct_redirect
     */
    function handle_post_type_requests() {
        global $ct_redirect;

        // stop execution and return if no add/update request is made
        if ( !( isset( $_POST['ct_submit_add_post_type'] ) || isset( $_POST['ct_submit_update_post_type'] )))
            return;

        // verify wp_nonce
        if ( !wp_verify_nonce( $_POST['ct_submit_post_type_secret'], 'ct_submit_post_type_verify'))
            return;

        // validate input fields and set redirect bool
        if ( isset( $_POST['ct_submit_add_post_type'] )) {
            if ( $this->validate_field( 'post_type', $_POST['post_type'] )) {
                $this->allow_redirect = true;
            } else {
                $this->allow_redirect = false;
                return;
            }
        }

        $post_type = ( isset( $_POST['post_type'] )) ? $_POST['post_type'] : $_GET['ct_edit_post_type'];

        $labels = array(
            'name'               => $_POST['labels']['name'],
            'singular_name'      => $_POST['labels']['singular_name'],
            'add_new'            => $_POST['labels']['add_new'],
            'add_new_item'       => $_POST['labels']['add_new_item'],
            'edit_item'          => $_POST['labels']['edit_item'],
            'new_item'           => $_POST['labels']['new_item'],
            'view_item'          => $_POST['labels']['view_item'],
            'search_items'       => $_POST['labels']['search_items'],
            'not_found'          => $_POST['labels']['not_found'],
            'not_found_in_trash' => $_POST['labels']['not_found_in_trash'],
            'parent_item_colon'  => $_POST['labels']['parent_item_colon']
        );
        $args = array(
            'labels'              => $labels,
            'supports'            => $_POST['supports'],
            'capability_type'     => 'post',
            'description'         => $_POST['description'],
            'menu_position'       => (int)  $_POST['menu_position'],
            'public'              => (bool) $_POST['public'] ,
            'show_ui'             => (bool) $_POST['show_ui'],
            'show_in_nav_menus'   => (bool) $_POST['show_in_nav_menus'],
            'publicly_queryable'  => (bool) $_POST['publicly_queryable'],
            'exclude_from_search' => (bool) $_POST['exclude_from_search'],
            'hierarchical'        => (bool) $_POST['hierarchical'],
            'rewrite'             => (bool) $_POST['rewrite'],
            'query_var'           => (bool) $_POST['query_var'],
            'can_export'          => (bool) $_POST['can_export']
        );

        // if custom capability type is set use it
        if ( !empty( $_POST['capability_type'] ))
            $args['capability_type'] = $_POST['capability_type'];

        if ( !empty( $_POST['menu_icon'] ))
           $args['menu_icon'] = $_POST['menu_icon'];

        // if custom rewrite slug is set use it
        if ( $_POST['rewrite'] == 'advanced' && !empty( $_POST['rewrite_slug'] )) {
            $args['rewrite'] = array( 'slug' => $_POST['rewrite_slug'] );
            $this->set_flush_rewrite_rules( true );
        }

        // remove empty labels so we can use the defaults
        foreach( $args['labels'] as $key => $value ) {
            if ( empty( $value ))
                unset( $args['labels'][$key] );
        }
        // remove keys so we can use the defaults
        if ( $_POST['public'] == 'advanced' ) {
            unset( $args['public'] );
        }
        else {
            unset( $args['show_ui'] );
            unset( $args['show_in_nav_menus'] );
            unset( $args['publicly_queryable'] );
            unset( $args['exclude_from_search'] );
        }

        // store multiple custom post types in the same array and in a single wp_options entry
        if ( !get_site_option('ct_custom_post_types' )) {
            $new_post_types = array( $post_type => $args );
            // set the flush rewrite rules
            $this->set_flush_rewrite_rules( true );
        }
        else {
            $old_post_types = get_site_option( 'ct_custom_post_types' );
            $new_post_types = array_merge( $old_post_types, array( $post_type => $args ));
            // set the flush rewrite rules
            if ( count( $new_post_types ) > count( $old_post_types ))
                $this->set_flush_rewrite_rules( true );
        }

        // update wp_options with the post type options
        update_site_option( 'ct_custom_post_types', $new_post_types );
    }

    /**
     * Intercepts delete $_POST request and removes the deleted post type
     * from the database post types array
     **/
    function handle_delete_post_type_requests() {

        // verify wp_nonce
        if ( !wp_verify_nonce( $_REQUEST['ct_delete_post_type_secret'], 'ct_delete_post_type_verify' ))
            return;

        // get available post types from db
        $post_types = get_site_option( 'ct_custom_post_types' );

        // remove the deleted post type
        unset( $post_types[$_GET['ct_delete_post_type']] );

        // update the available post types
        update_site_option( 'ct_custom_post_types', $post_types );
    }

    /**
     * Get available custom post types from database and register them.
     * The function attach itself to the init hook and uses priority of 2.It loads
     * after the ct_admin_register_taxonomies() func which hook itself to the init
     * hook with priority of 1.
     */
    function register_post_types() {
        /* Get the available post types */
        $post_types = get_site_option( 'ct_custom_post_types' );
        /* Register each post type if array of data is returned */
        if ( is_array( $post_types ) ) {
            foreach ( $post_types as $post_type => $args ) {
                register_post_type( $post_type, $args );
            }
            /* Flush the rewrite rules if necessary */
            $this->flush_rewrite_rules();
        }
    }

    /**
     * Intercepts $_POST request and processes the taxonomy submissions
     *
     * @global bool $ct_redirect
     */
    function handle_taxonomy_requests() {
        global $ct_redirect;

        // stop execution and return if no add/edit taxonomy request is made
        if ( !( isset( $_POST['ct_submit_add_taxonomy'] ) || isset( $_POST['ct_submit_update_taxonomy'] )))
            return;

        // verify wp_nonce
        if ( !wp_verify_nonce( $_POST['ct_submit_taxonomy_secret'], 'ct_submit_taxonomy_verify'))
            return;

        // validate input fields and set redirect rules
        if ( isset( $_POST['ct_submit_add_taxonomy'] )) {
            $field_taxonomy_valid    = $this->validate_field( 'taxonomy', $_POST['taxonomy'] );
            $field_object_type_valid = $this->validate_field( 'object_type', $_POST['object_type'] );

            if ( $field_taxonomy_valid && $field_object_type_valid ) {
                $this->allow_redirect = true;
            } else {
                $this->allow_redirect = false;
                return;
            }
        }

        $taxonomy = ( isset( $_POST['taxonomy'] )) ? $_POST['taxonomy'] : $_GET['ct_edit_taxonomy'];

        $object_type = $_POST['object_type'];
        $labels = array(
            'name'                       => $_POST['labels']['name'],
            'singular_name'              => $_POST['labels']['singular_name'],
            'add_new_item'               => $_POST['labels']['add_new_item'],
            'new_item_name'              => $_POST['labels']['new_item_name'],
            'edit_item'                  => $_POST['labels']['edit_item'],
            'update_item'                => $_POST['labels']['update_item'],
            'search_items'               => $_POST['labels']['search_items'],
            'popular_items'              => $_POST['labels']['popular_items'],
            'all_items'                  => $_POST['labels']['all_items'],
            'parent_item'                => $_POST['labels']['parent_item'],
            'parent_item_colon'          => $_POST['labels']['parent_item_colon'],
            'add_or_remove_items'        => $_POST['labels']['add_or_remove_items'],
            'separate_items_with_commas' => $_POST['labels']['separate_items_with_commas'],
            'choose_from_most_used'      => $_POST['labels']['all_items']
        );
        $args = array(
            'labels'              => $labels,
            'public'              => (bool) $_POST['public'] ,
            'show_ui'             => (bool) $_POST['show_ui'],
            'show_tagcloud'       => (bool) $_POST['show_tagcloud'],
            'show_in_nav_menus'   => (bool) $_POST['show_in_nav_menus'],
            'hierarchical'        => (bool) $_POST['hierarchical'],
            'rewrite'             => (bool) $_POST['rewrite'],
            'query_var'           => (bool) $_POST['query_var'],
            'capabilities'        => array ( 'assign_terms' => 'assign_terms' )
        );

        // if custom rewrite slug is set use it
        if ( $_POST['rewrite'] == 'advanced' && !empty( $_POST['rewrite_slug'] )) {
            $args['rewrite'] = array( 'slug' => $_POST['rewrite_slug'] );
            $this->set_flush_rewrite_rules( true );
        }

        // remove empty values from labels so we can use the defaults
        foreach( $args['labels'] as $key => $value ) {
            if ( empty( $value ))
                unset( $args['labels'][$key] );
        }
        // if no advanced is set, unset values so we can use the defaults
        if ( $_POST['public'] == 'advanced' ) {
            unset( $args['public'] );
        }
        else {
            unset( $args['show_ui'] );
            unset( $args['show_tagcloud'] );
            unset( $args['show_in_nav_menus'] );
        }

        // store multiple custom taxonomies in the same array and in a single wp_options entry
        if ( !get_site_option( 'ct_custom_taxonomies' )) {
            $new_taxonomies = array( $taxonomy => array( 'object_type' => $object_type, 'args' => $args ));
            /* set the flush rewrite rules */
            $this->set_flush_rewrite_rules( true );
        }
        else {
            $old_taxonomies = get_site_option( 'ct_custom_taxonomies' );
            $new_taxonomies = array_merge( $old_taxonomies, array( $taxonomy => array( 'object_type' => $object_type, 'args' => $args )));
            /* set the flush rewrite rules */
            if ( count( $new_taxonomies ) > count( $old_taxonomies ))
                $this->set_flush_rewrite_rules( true );
        }

        // update wp_options with the taxonomies options
        update_site_option( 'ct_custom_taxonomies', $new_taxonomies );
    }

    /**
     * Intercepts delete $_POST request and removes the deleted taxonomy
     * from the database taxonomies array
     */
    function handle_delete_taxonomy_requests() {

        // verify wp_nonce
        if ( !wp_verify_nonce( $_REQUEST['ct_delete_taxonomy_secret'], 'ct_delete_taxonomy_verify' ))
            return;

        // get available taxonomies from db
        $taxonomies = get_site_option( 'ct_custom_taxonomies' );

        // remove the deleted taxonomy
        unset( $taxonomies[$_GET['ct_delete_taxonomy']] );

        // update the available taxonomies
        update_site_option( 'ct_custom_taxonomies', $taxonomies );
    }

    /**
     * Get available custom taxonomies from database and register them.
     * The function atach itself to the init hook and uses priority of 1. It loads
     * before the ct_admin_register_post_types() func which hook itself to the init
     * hook with priority of 2.
     */
    function register_taxonomies() {

        $taxonomies = get_site_option( 'ct_custom_taxonomies' );
        $options    = get_site_option('dp_options'); // @todo Make it part of Content Types Submodule

        if ( !empty( $taxonomies )) {
            if ( $options['general_settings']['order_taxonomies'] == 'alphabetical' ) {
                ksort( $taxonomies );
            }
            foreach ( $taxonomies as $taxonomy => $args )
                register_taxonomy( $taxonomy, $args['object_type'], $args['args'] );
        }
    }


    /**
     * Intercepts $_POST request and processes the custom fields submissions
     */
    function handle_custom_field_requests() {
        global $ct_redirect;

        // stop execution and return if no add/edit custom field request is made
        if ( !( isset( $_POST['ct_submit_add_custom_field'] ) || isset( $_POST['ct_submit_update_custom_field'] )))
            return;

        // verify wp_nonce
        if ( !wp_verify_nonce( $_POST['ct_submit_custom_field_secret'], 'ct_submit_custom_field_verify'))
            return;

        // validate fields data
        $field_title_valid        = $this->validate_field( 'field_title', $_POST['field_title'] );
        $field_object_type_valid  = $this->validate_field( 'object_type', $_POST['object_type'] );

        if ( in_array( $_POST['field_type'], array( 'radio', 'checkbox', 'selectbox', 'multiselectbox' ))) {
            $field_options_valid = $this->validate_field( 'field_options', $_POST['field_options'][1] );

            if ( $field_title_valid && $field_object_type_valid && $field_options_valid ) {
                $this->allow_redirect = true;
            } else {
                $this->allow_redirect = false;
                return;
            }
        } else {
            if ( $field_title_valid && $field_object_type_valid ) {
                $this->allow_redirect = true;
            } else {
                $this->allow_redirect = false;
                return;
            }
        }

        $field_id = ( empty( $_GET['ct_edit_custom_field'] )) ? $_POST['field_type'] . '_' . uniqid() : $_GET['ct_edit_custom_field'];

        $args = array(
            'field_title'          => $_POST['field_title'],
            'field_type'           => $_POST['field_type'],
            'field_sort_order'     => $_POST['field_sort_order'],
            'field_options'        => $_POST['field_options'],
            'field_default_option' => $_POST['field_default_option'],
            'field_description'    => $_POST['field_description'],
            'object_type'          => $_POST['object_type'],
            'required'             => $_POST['required'],
            'field_id'             => $field_id
        );

        // unset if there are no options to be stored in the db
        if ( $args['field_type'] == 'text' || $args['field_type'] == 'textarea' )
            unset( $args['field_options'] );

        if ( !get_site_option( 'ct_custom_fields' )) {
            $new_custom_fields = array( $field_id => $args );
        }
        else {
            $old_custom_fields = get_site_option( 'ct_custom_fields' );
            $new_custom_fields = array_merge( $old_custom_fields, array( $field_id => $args ));
        }

        update_site_option('ct_custom_fields', $new_custom_fields );
    }

    /**
     * ct_admin_delete_custom_fields()
     *
     * Delete custom fields
     */
    function handle_delete_custom_fields_requests() {

        // verify wp_nonce
        if ( !wp_verify_nonce( $_REQUEST['ct_delete_custom_field_secret'], 'ct_delete_custom_field_verify' ))
            return;

        // get available custom fields from db
        $custom_fields = get_site_option( 'ct_custom_fields' );

        // remove the deleted custom field
        unset( $custom_fields[$_GET['ct_delete_custom_field']] );

        // update the available custom fields
        update_site_option( 'ct_custom_fields', $custom_fields );
    }

    /**
     * Create the custom fields
     */
    function create_custom_fields() {
        $custom_fields = get_site_option( 'ct_custom_fields' );

        if ( !empty( $custom_fields )) {
            foreach ( $custom_fields as $custom_field ) {
                foreach ( $custom_field['object_type'] as $object_type )
                    add_meta_box( 'ct-custom-fields', 'Custom Fields', 'ct_admin_ui_display_custom_fields', $object_type, 'normal', 'high' );
            }
        }
    }

    /**
     * Save custom fields data
     *
     * @param int $post_id The post id of the post being edited
     */
    function save_custom_fields( $post_id ) {

        // prevent autosave from deleting the custom fields
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
            return;

        $prefix = '_ct_';
        $custom_fields = get_site_option( 'ct_custom_fields' );

        if ( !empty( $custom_fields )) {
            foreach ( $custom_fields as $custom_field ) {
                if ( isset( $_POST[$prefix . $custom_field['field_id']] ))
                    update_post_meta( $post_id, $prefix . $custom_field['field_id'], $_POST[$prefix . $custom_field['field_id']] );
                else
                    delete_post_meta( $post_id, $prefix . $custom_field['field_id'] );
            }
        }
    }

    /**
     * Set the flush rewrite rules and write then in db for later reference
     *
     * @param bool $bool
     */
    function set_flush_rewrite_rules( $bool ) {
        // set the flush rewrite rules
        update_site_option( 'ct_flush_rewrite_rules', $bool );
    }

    /**
     * Flush rewrite rules based on db options check
     */
    function flush_rewrite_rules() {
        // flush rewrite rules
        if ( get_site_option('ct_flush_rewrite_rules')) {
            flush_rewrite_rules();
            $this->set_flush_rewrite_rules( false );
        }
    }

    /**
     * Check whether new users are registered, since we need to flush the rewrite
     * rules for them, and upadte the rewrite options
     */
    function check_user_registration() {
        $this->set_flush_rewrite_rules( true );
    }

    /**
	 * Renders an admin section of display code.
	 *
	 * @param  string $name Name of the admin file(without extension)
	 * @param  string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
    function render_admin( $name, $vars = array() ) {
		foreach ( $vars as $key => $val )
			$$key = $val;
		if ( file_exists( "{$this->submodule_dir}/ui-admin/{$name}.php" ) )
			include "{$this->submodule_dir}/ui-admin/{$name}.php";
		else
			echo "<p>Rendering of admin template {$this->submodule_dir}/ui-admin/{$name}.php failed</p>";
	}

}
endif;

/* Initiate Class */
if ( class_exists('Content_Types_Core') )
	$__content_types_core = new Content_Types_Core();
?>