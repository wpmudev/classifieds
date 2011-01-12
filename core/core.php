<?php

/**
 * Classifieds Core Class
 */
if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

    var $hook;
    var $options_name     = 'classifieds_options';
    var $plugin_url       = CF_PLUGIN_URL;
    var $plugin_dir       = CF_PLUGIN_DIR;
    var $plugin_prefix    = 'cf_';
    var $text_domain      = 'classifieds';
    var $post_type        = 'classifieds';
    var $menu_slug        = 'classifieds';
    var $sub_menu_slug    = 'classifieds_credits';

    /**
     * Initiate the custom field keys
     */
    var $custom_fields = array();


    function Classifieds_Core() {
        /* Init plugin */
        add_action( 'init', array( &$this, 'init' ) );
        /* Init plugin BuddyPress integration */
        add_action( 'bp_init', array( &$this, 'bp_init' ) );
        /* Add theme support for featured images */
        add_theme_support( 'post-thumbnails' );
    }

    function init() {
        /* Initiate admin menus and admin head */
        if ( is_admin() ) {
            add_action( 'admin_menu',  array( &$this, 'admin_menu' ) );
            add_action( 'admin_init',  array( &$this, 'admin_head' ) );
        }
        /* Filter the_content function output and add ad specific data */
        add_filter( 'the_content', array( &$this, 'filter_the_content' ) );
        /* Set user frindly custom fields objects */
        $this->custom_fields['price']    = '_ct_selectbox_4ce176abe31a3';
        $this->custom_fields['duration'] = '_ct_selectbox_4ce176abe31a3';
    }

    /**
     * Initiate BuddyPress
     */
    function bp_init() {
        add_action( 'wp', array( &$this, 'bp_add_navigation' ), 2 );
        add_action( 'admin_menu', array( &$this, 'bp_add_navigation' ), 2 );
        add_action( 'bp_template_content', array( &$this, 'bp_print_user_classifieds' ) );
        add_action( 'bp_member_plugin_options_nav', array( &$this, 'bp_admin_menu_item' ) );
        add_action( 'bp_head', array( &$this, 'bp_print_styles' ) );
    }

    /**
     * Add plugin main menu
     */
    function admin_menu() {
        add_menu_page( __( 'Classifieds', $this->text_domain ), __( 'Classifieds', $this->text_domain ), 'read', $this->menu_slug, array( &$this, 'admin_flow' ) );
        add_submenu_page( $this->menu_slug, __( 'Dashboard', $this->text_domain ), __( 'Dashboard', $this->text_domain ), 'edit_users', $this->menu_slug, array( &$this, 'admin_flow' ) );
        add_submenu_page( $this->menu_slug, __( 'Settings', $this->text_domain ), __( 'Settings', $this->text_domain ), 'edit_users', $this->sub_menu_slug, array( &$this, 'admin_flow' ) );
    }

    /**
     * Flow of a typical admin plugin request.
     */
    function admin_flow() {
        if ( $_GET['page'] == $this->menu_slug ) {
            if ( isset( $_POST['confirm'] ) ) {
                /* Change post status */
                if ( $_POST['action'] == 'end' )
                    $this->process_status( $_POST['post_id'], 'private' );
                /* Change post status */
                if ( $_POST['action'] == 'publish' )
                    $this->process_status( $_POST['post_id'], 'publish' );
                /* Delete post */
                if ( $_POST['action'] == 'delete' )
                    wp_delete_post( $_POST['post_id'] );
                /* Render admin template */
                $this->render_admin( 'dashboard' );
            } else {
                /* Render admin template */
                $this->render_admin( 'dashboard' );
            }
        } elseif ( $_GET['page'] == $this->sub_menu_slug ) {
            if ( $_GET['tab'] == 'payments' ) {
                if ( $_GET['sub'] == 'authorizenet' ) {
                    $this->render_admin( 'authorizenet' );
                } else {
                    /* Save options */
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    /* Render admin template */
                    $this->render_admin( 'paypal' );
                }
            } else {
                if ( $_GET['sub'] == 'something' ) {

                } else {
                    if ( isset( $_POST['save'] ) ) {
                        $this->save_options( $_POST );
                    }
                    $this->render_admin( 'credits' );
                }
            }
        }
    }

    /**
     * Hook styles and scripts into plugin admin head
     */
    function admin_head() {
        /* Get plugin hook */
        $this->hook = get_plugin_page_hook( $_GET['page'], $this->menu_slug );
        /* Add actions for printing the styles and scripts of the document */
        add_action( 'admin_print_scripts-' . $this->hook, array( &$this, 'enqueue_scripts' ) );
        add_action( 'admin_head-' . $this->hook, array( &$this, 'print_admin_styles' ) );
        add_action( 'admin_head-' . $this->hook, array( &$this, 'print_admin_scripts' ) );
    }

    /**
     * Save plugin options.
     *
     */
    function save_options( $params ) {
        if ( wp_verify_nonce( $params['_wpnonce'], 'verify' ) ) {
            /* Remove unwanted parameters */
            unset( $params['_wpnonce'], $params['_wp_http_referer'], $params['save'] );
            /* Update options by merging the old ones */
            $options = $this->get_options();
            $options = array_merge( $options, array( $params['key'] => $params ) );
            update_option( $this->options_name, $options );
        } else {
            die( __( 'Security check failed!', $this->text_domain ) );
        }           
    }

    /**
     * Get plugin options.
     *
     * @param string|NULL $key The key for that plugin option.
     * @return array Plugin options or empty array if no options are found
     */
    function get_options( $key = NULL ) {
        $options = get_option( $this->options_name );
        $options = is_array( $options ) ? $options : array();
        /* Check if specific plugin option is requested and return it */
        if ( isset( $key ) && array_key_exists( $key, $options ) ) {
            return $options[$key];
        } else {
            return $options;
        }
    }

    /**
     * 
     */
    function bp_print_styles() {
        global $bp;
        if ( $bp->current_component == 'classifieds' ) { ?>
            <style type="text/css">
                .bp-cf-ad    { border: 1px solid #ddd; padding: 10px; display: block; overflow: hidden; float: left; margin: 0 15px 15px 0;  }
                .bp-cf-ad table { margin: 5px 5px 5px 15px ; width: 280px; border-width: 1px; border-style: solid; border-color: #ddd; border-collapse: collapse; }
                .bp-cf-ad table th { text-align: right; }
                .bp-cf-ad table th, .bp-cf-ad table td { border-width: 1px; border-style: inset; border-color: #ddd; }
                .bp-cf-ad form { padding-left: 56px; overflow: hidden; }
                .bp-cf-image { float: left;  }
                .bp-cf-info  { float: left; }
            </style> <?php
        }
    }

    /**
     * Add BuddyPress navigation.
     */
    function bp_add_navigation() {
        global $bp;
        /* Set up classifieds as a sudo-component for identification and nav selection */
        $bp->classifieds->slug = 'classifieds';
        $user_domain = ( !empty( $bp->displayed_user->domain ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
        $classifieds_link = $user_domain . $bp->classifieds->slug . '/';
        /* Add the settings navigation item */
        bp_core_new_nav_item( array( 
            'name'                    => __('Classifieds', $this->text_domain ),
            'slug'                    => $bp->classifieds->slug,
            'position'                => 100,
            'show_for_displayed_user' => true,
            'screen_function'         => array( &$this, 'bp_load_page_template' ),
            'default_subnav_slug'     => 'my-classifieds'
        ));
        bp_core_new_subnav_item( array( 
            'name'            => __( 'My Classifieds', $this->text_domain ),
            'slug'            => 'my-classifieds',
            'parent_url'      => $classifieds_link,
            'parent_slug'     => $bp->classifieds->slug,
            'screen_function' => array( &$this, 'bp_load_page_template' ),
            'position'        => 10,
            'user_has_access' => true
        ));
    }

    /**
     * Load BuddyPress theme template file for plugin specific page.
     */
    function bp_load_page_template() {
        bp_core_load_template( 'members/single/plugins', true );
    }

    /**
     *
     * @global <type> $wpdb
     * @global <type> $bp
     * @global <type> $current_site 
     */
    function bp_print_user_classifieds() {
        global $bp;
        if ( $bp->current_component == 'classifieds' ) {
            $posts = get_posts( array( 'post_type' => $this->post_type, 'post_status' => 'publish' ) ); ?>
            <?php foreach ( $posts as $post ): ?>
            <?php $terms = wp_get_object_terms( $post->ID, $taxonomies ); ?>
            <div class="bp-cf-ad">
                <div class="bp-cf-image"><?php echo get_the_post_thumbnail( $post->ID, array( 200, 150 ) ); ?></div>
                <div class="bp-cf-info">
                    <table>
                        <tr>
                            <th><?php _e( 'Title', $this->text_domain ); ?></th>
                            <td><?php echo $post->post_title; ?></td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Categories', $this->text_domain ); ?></th>
                            <td><?php foreach ( $terms as $term ) echo $term->name . ' '; ?></td>
                        <tr>
                        <tr>
                            <th><?php _e( 'Ends', $this->text_domain ); ?></th>
                            <td><?php echo get_post_meta( $post->ID, $this->custom_fields['duration'], true ); ?></td>
                        </tr>
                    </table>
                </div>
                <form action="" method="post">
                    <input type="submit" name="bp-view-ad" value="<?php _e('View Ad', $this->text_domain ); ?>" />
                    <input type="submit" name="bp-edit-ad" value="<?php _e('Edit Ad', $this->text_domain ); ?>" />
                    <input type="submit" name="bp-delete-ad" value="<?php _e('Delete Ad', $this->text_domain ); ?>" />
                </form>
            </div>
            <?php endforeach; ?> <?php
        }
    }

    function bp_admin_menu_item() {
        global $bp;
        if ( bp_is_my_profile() && $bp->current_component == 'classifieds' ) { ?>
            <li>
                <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=classifieds_new">Create New Add <span style="color:#888">(wp-admin)</span></a>
            </li>
            <li>
                <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=classifieds">Manage Ads <span style="color:#888">(wp-admin)</span></a>
            </li>
            <?php if ( get_site_option('classifieds_credits_enabled') ): ?>
            <li>
                <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=classifieds_credits_management">My Credits <span style="color:#888">(wp-admin)</span></a>
            </li>
            <?php endif;
        }
    }

    /**
     * Process post status. 
     *
     * @global object $wpdb
     * @param string $post_id
     * @param string $status
     */
    function process_status( $post_id, $status ) {
        global $wpdb;
        $wpdb->update( $wpdb->posts, array( 'post_status' => $status ), array( 'ID' => $post_id ), array( '%s' ), array( '%d' ) );
    }

    /**
	 * Renders an admin section of display code.
	 *
	 * @param string $name Name of the admin file(without extension)
	 * @param string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 */
    function render_admin( $name, $vars = array() ) {
		foreach ( $vars as $key => $val )
			$$key = $val;
		if ( file_exists( "{$this->plugin_dir}/admin-ui/{$name}.php" ) )
			include "{$this->plugin_dir}/admin-ui/{$name}.php";
		else
			echo "<p>Rendering of admin template {$this->plugin_dir}/admin-ui/{$name}.php failed</p>";
	}

    /**
     * Filter the_content() function output.
     *
     * @global <type> $post
     * @param <type> $content
     * @return <type> 
     */
    function filter_the_content( $content ) {
        global $post;
 
        if ( is_single() && $post->post_type == $this->post_type ) {
            $user = get_userdata( $post->post_author );
            $content =  get_the_post_thumbnail( $post->ID, array( 200, 200 ) ) .
                        "<table>
                           <tr>
                               <th>" . __( 'Posted By:', $this->text_domain ) . "</th>
                               <td>{$user->user_nicename}</td>
                           </tr>
                           <tr>
                               <th>" . __( 'Description:', $this->text_domain ) . "</th>
                               <td>{$content}</td>
                           </tr>
                        </table>";
            return $content;
        } else {
            return $content;
        }
    }
    
    /**
     * Enqueue scripts.
     */
    function enqueue_scripts() {
        wp_enqueue_script('jquery');
    }

    /**
     * Print document styles.
     */
    function print_admin_styles() { ?>
        <style type="text/css">
            .wrap table    { text-align: left; }
            .wrap table th { width: 200px; }
            .classifieds_page_classifieds_credits .wrap h2 { border-bottom:1px solid #CCCCCC; padding-bottom:0; }
        </style> <?php
    }

    /**
     * Print document scripts
     */
    function print_admin_scripts() { ?>
        <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function($) {
            $('form.cf-form').hide();
        });
        var classifieds = {
            toggle_end: function(key) {
                jQuery('#form-'+key).show();
                jQuery('.action-links-'+key).hide();
                jQuery('.separators-'+key).hide();
                jQuery('input[name="action"]').val('end');
            },
            toggle_publish: function(key) {
                jQuery('#form-'+key).show();
                jQuery('#form-'+key+' select').show();
                jQuery('.action-links-'+key).hide();
                jQuery('.separators-'+key).hide();
                jQuery('input[name="action"]').val('publish');
            },
            toggle_delete: function(key) {
                jQuery('#form-'+key).show();
                jQuery('#form-'+key+' select').hide();
                jQuery('.action-links-'+key).hide();
                jQuery('.separators-'+key).hide();
                jQuery('input[name="action"]').val('delete');
            },
            cancel: function(key) {
                jQuery('#form-'+key).hide();
                jQuery('.action-links-'+key).show();
                jQuery('.separators-'+key).show();
            }
        };
        //]]>
        </script> <?php
    }
}
endif;

if ( class_exists('Classifieds_Core') )
	$__classifieds_core = new Classifieds_Core();

?>
