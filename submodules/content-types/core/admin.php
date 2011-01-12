<?php
/**
 * Classifieds Core Admin Class
 */
if ( !class_exists('Content_Types_Core_Admin') ):
class Content_Types_Core_Admin extends Content_Types_Core {

    /** @var string Current page hook */
    var $hook;

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
        add_action( 'admin_menu', array( &$this, 'admin_menu' ), 20 );
        add_action( 'admin_init', array( &$this, 'get_hook' ) );
        add_action( 'admin_init', array( &$this, 'handle_admin_redirects' ), 10 );
        add_action( 'admin_print_styles-post.php', array( &$this, 'enqueue_custom_field_styles') );
        add_action( 'admin_print_styles-post-new.php', array( &$this, 'enqueue_custom_field_styles') );
    }

    /**
     * Register submodule submenue
     *
     * @return void
     **/
    function admin_menu() {
        add_submenu_page( $this->parent_menu_slug , __( 'Content Types', $this->text_domain ), __( 'Content Types', $this->text_domain ), 'edit_users', 'ct_content_types', array( &$this, 'handle_admin_requests' ) );
    }

    /**
     * Get page hook and hook ct_core_enqueue_styles() and ct_core_enqueue_scripts() to it.
     *
     * @return void
     **/
    function get_hook() {
        $this->hook = get_plugin_page_hook( $_GET['page'], $this->parent_menu_slug );
        add_action( 'admin_print_styles-' .  $this->hook, array( &$this, 'enqueue_styles' ) );
        add_action( 'admin_print_scripts-' . $this->hook, array( &$this, 'enqueue_scripts' ) );
    }

    /**
     * Load styles on plugin admin pages only.
     */
    function enqueue_styles() {
        wp_enqueue_style( 'ct-admin-styles',
                           $this->submodule_url . 'ui-admin/css/ct-admin-ui-styles.css');
    }

    /**
     * Load scripts on plugin specific admin pages only.
     */
    function enqueue_scripts() {
        wp_enqueue_script( 'ct-admin-scripts',
                            $this->submodule_url . 'ui-admin/js/ct-admin-ui-scripts.js',
                            array( 'jquery' ) );
    }

    /**
     * Load styles for "Custom Fields" on add/edit post type pages only.
     *
     * @return void
     **/
    function enqueue_custom_field_styles() {
        wp_enqueue_style( 'ct-admin-custom-field-styles',
                           $this->submodule_url . 'ui-admin/css/ct-admin-ui-custom-field-styles.css' );
    }

    /**
     * Hooks itself after the add/update/delete functions and redirects to the
     * appropriate pages.
     *
     * @global bool $ct_redirect Switch turning redirect on/off
     */
    function handle_admin_redirects() {
        // after post type is added redirect back to the post types page
        if ( isset( $_POST['ct_submit_add_post_type'] ) && $this->allow_redirect )
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=post_type' ));

        // after post type is edited/deleted redirect back to the post types page
        if ( isset( $_POST['ct_submit_update_post_type'] ) || isset( $_REQUEST['ct_delete_post_type_secret'] ))
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=post_type' ));

        // redirect to add post type page
        if ( isset( $_POST['ct_redirect_add_post_type'] ))
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=post_type&ct_add_post_type=true' ));

        // after taxonomy is added redirect back to the taxonomies page
        if ( isset( $_POST['ct_submit_add_taxonomy'] ) && $this->allow_redirect )
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=taxonomy' ));

        // after taxonomy is edited/deleted redirect back to the taxonomies page
        if ( isset( $_POST['ct_submit_update_taxonomy'] ) || isset( $_REQUEST['ct_delete_taxonomy_secret'] ))
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=taxonomy' ));

        // redirect to add taxonomy page
        if ( isset( $_POST['ct_redirect_add_taxonomy'] ))
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=taxonomy&ct_add_taxonomy=true' ));

        // after custom field is added redirect to custom fields page
        if ( isset( $_POST['ct_submit_add_custom_field'] ) && $this->allow_redirect )
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=custom_field' ));

        // after custom field add/update/deleted redirect back to the custom fields page
        if ( isset( $_POST['ct_submit_update_custom_field'] ) || isset( $_REQUEST['ct_delete_custom_field_secret'] ))
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=custom_field' ));

        // redirect to add custom field page
        if ( isset( $_POST['ct_redirect_add_custom_field'] ))
            wp_redirect( admin_url( 'admin.php?page=ct_content_types&ct_content_type=custom_field&ct_add_custom_field=true' ));
    }

    function handle_admin_requests() {

        if ( $_GET['page'] == 'ct_content_types' ) {
            $this->render_admin('content-types');
            
            if ( $_GET['ct_content_type'] == 'post_type' || !isset( $_GET['ct_content_type'] )) {
                if ( isset( $_GET['ct_add_post_type'] ) )
                    $this->render_admin('add-post-type');
                elseif ( isset( $_GET['ct_edit_post_type'] ) )
                    $this->render_admin('edit-post-type');
                elseif ( isset( $_GET['ct_delete_post_type'] ) )
                    $this->render_admin('delete-post-type');
                else 
                    $this->render_admin('post-types');
            }
            elseif ( $_GET['ct_content_type'] == 'taxonomy' ) {
                if ( isset( $_GET['ct_add_taxonomy'] )) {
                    include_once 'ct-admin-ui-add-taxonomy.php';
                    $post_types = get_post_types('','names');
                    ct_admin_ui_add_taxonomy( $post_types );
                }
                elseif ( isset( $_GET['ct_edit_taxonomy'] )) {
                    $this->render_admin('edit-taxonomy');
                }
                elseif ( isset( $_GET['ct_delete_taxonomy'] )) {
                    include_once 'ct-admin-ui-delete-taxonomy.php';
                    $taxonomies = get_site_option( 'ct_custom_taxonomies' );
                    ct_admin_ui_delete_taxonomy( $taxonomies[$_GET['ct_delete_taxonomy']] );
                }
                else {
                    $this->render_admin('taxonomies');
                }
            }
            elseif ( $_GET['ct_content_type'] == 'custom_field' ) {
                if ( isset( $_GET['ct_add_custom_field'] )) {
                    include_once 'ct-admin-ui-add-custom-field.php';
                    $post_types = get_post_types('','names');
                    ct_admin_ui_add_custom_field( $post_types );
                }
                elseif ( isset( $_GET['ct_edit_custom_field'] )) {
                    include_once 'ct-admin-ui-edit-custom-field.php';
                    $custom_fields = get_site_option( 'ct_custom_fields' );
                    $post_types = get_post_types('','names');
                    ct_admin_ui_edit_custom_field( $custom_fields[$_GET['ct_edit_custom_field']], $post_types );
                }
                elseif ( isset( $_GET['ct_delete_custom_field'] )) {
                    include_once 'ct-admin-ui-delete-custom-field.php';
                    $custom_fields = get_site_option( 'ct_custom_fields' );
                    ct_admin_ui_delete_custom_field( $custom_fields[$_GET['ct_delete_custom_field']] );
                }
                else {
                    $this->render_admin('custom-fields');
                }
            }
        }
    }

    /**
     * Validates input fields data
     *
     * @param string $field
     * @param mixed $value
     * @return bool true/false depending on validation outcome
     */
    function validate_field( $field, $value ) {

        if ( $field == 'taxonomy' || $field == 'post_type' ) {
            if ( preg_match('/^[a-zA-Z0-9_]{2,}$/', $value )) {
                return true;
            } else {
                if ( $field == 'post_type' )
                    add_action( 'ct_invalid_field_post_type', create_function( '', 'echo "form-invalid";' ) );
                if ( $field == 'taxonomy' )
                    add_action( 'ct_invalid_field_taxonomy', create_function( '', 'echo "form-invalid";' ) );
                return false;
            }
        }

        if ( $field == 'object_type' || $field == 'field_title' || $field == 'field_options' ) {
            if ( empty( $value )) {
                if ( $field == 'object_type' )
                    add_action( 'ct_invalid_field_object_type', create_function( '', 'echo "form-invalid";' ) );
                if ( $field == 'field_title' )
                    add_action( 'ct_invalid_field_title', create_function( '', 'echo "form-invalid";' ) );
                if ( $field == 'field_options' )
                    add_action( 'ct_invalid_field_options', create_function( '', 'echo "form-invalid";' ) );
                return false;
            } else {
                return true;
            }
        }
    }

}
endif;

if ( class_exists('Content_Types_Core_Admin') )
	$__content_types_core_admin = new Content_Types_Core_Admin();
?>
