<?php

/**
 * Classifieds Core BuddyPress Class
 */
if ( !class_exists('Classifieds_Core_BuddyPress') ):
class Classifieds_Core_BuddyPress extends Classifieds_Core {

    var $bp_active;

    /**
     * Constructor. Hooks the whole module to the 'bp_init" hook.
     */
    function Classifieds_Core_BuddyPress() {
        /* Init plugin BuddyPress integration when BP is ready */
        add_action( 'bp_init', array( &$this, 'bp_init' ) );
    }

    /**
     * Initiate BuddyPress
     */
    function bp_init() {
        /* Set BuddyPress active state */
        $this->bp_active = true;
        add_action( 'wp', array( &$this, 'bp_add_navigation' ), 2 );
        add_action( 'admin_menu', array( &$this, 'bp_add_navigation' ), 2 );
        add_action( 'bp_head', array( &$this, 'bp_print_styles' ) );
        //add_action( 'bp_member_plugin_options_nav', array( &$this, 'bp_member_plugin_options_nav' ) );
        add_action( 'bp_template_content', array( &$this, 'bp_template_content' ) );
    }

    /**
     * Add BuddyPress navigation.
     */
    function bp_add_navigation() {
        global $bp;
        /* Set up classifieds as a sudo-component for identification and nav selection */
        $bp->classifieds->slug = 'classifieds';
        /* Construct URL to the BuddyPress profile URL */
        $user_domain = ( !empty( $bp->displayed_user->domain ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
        $parent_url = $user_domain . $bp->classifieds->slug . '/';
        /* Add the settings navigation item */
        bp_core_new_nav_item( array( 
            'name'                    => __('Classifieds', $this->text_domain ),
            'slug'                    => $bp->classifieds->slug,
            'position'                => 100,
            'show_for_displayed_user' => true,
            'screen_function'         => array( &$this, 'bp_load_plugins_template' ),
            'default_subnav_slug'     => 'my-classifieds'
        ));
        bp_core_new_subnav_item( array( 
            'name'            => __( 'My Classifieds', $this->text_domain ),
            'slug'            => 'my-classifieds',
            'parent_url'      => $parent_url,
            'parent_slug'     => $bp->classifieds->slug,
            'screen_function' => array( &$this, 'bp_load_plugins_template' ),
            'position'        => 10,
            'user_has_access' => true
        ));
        bp_core_new_subnav_item( array(
            'name'            => __( 'Create New Ad', $this->text_domain ),
            'slug'            => 'add-new',
            'parent_url'      => $parent_url,
            'parent_slug'     => $bp->classifieds->slug,
            'screen_function' => array( &$this, 'bp_load_plugins_template' ),
            'position'        => 10,
            'user_has_access' => true
        ));
    }

    /**
     * Load BuddyPress theme template file for plugin specific page.
     */
    function bp_load_plugins_template() {
        /* This is generic BuddyPress plugins file. All other functions hook
         * themselves into the plugins template hooks. Each BuddyPress component
         * "members", "groups", etc. offers different plugin file */
        bp_core_load_template( 'members/single/plugins', true );
    }

    /**
     * Load the content for the specific classifieds component
     *
     * @global <type> $bp
     */
    function bp_template_content() {
        global $bp;
        //cf_debug($bp);
        if ( $bp->current_component == 'classifieds' && $bp->current_action == 'my-classifieds' ) {
           $this->render_front('members/single/classifieds/my-classifieds');
        }
        if ( $bp->current_component == 'classifieds' && $bp->current_action == 'add-new' ) {
           if ( isset( $_POST['save'] ) ) {
               $this->bp_process_request( $_POST, $_FILE );
           }
           $this->render_front('members/single/classifieds/add-new');
        }
    }

    /**
     * 
     */
    function bp_process_request( $post, $file = NULL ) {
        cf_debug( $params );
    }

    /**
     *
     */
    function bp_member_plugin_options_nav() {
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
}
endif;

if ( class_exists('Classifieds_Core_BuddyPress') )
	$__classifieds_core_buddypress = new Classifieds_Core_BuddyPress();

?>
