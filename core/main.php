<?php

/**
 * Classifieds Core Main Class
 **/
if ( !class_exists('Classifieds_Core_Main') ):
class Classifieds_Core_Main extends Classifieds_Core {

    /**
     * Constructor.
     *
     * @return void
     **/
    function Classifieds_Core_Main() {
        /* Hook the entire class to WordPress init hook */
        add_action( 'init', array( &$this, 'init' ) );
        /* Initiate class variables from core class */
        add_action( 'init', array( &$this, 'init_vars' ) );
        /* Hook to bp_init so we can determine whether BuddyPress is active */
        add_action( 'bp_init', array( &$this, 'buddypress_active' ) );
    }

    /**
     * Initiate Main.
     *
     * @return void
     **/
    function init() {
        /* Load general WordPress front if BuddyPress is disabled and not admin */
        if ( !$this->bp_active && !is_admin() ) {
            /* Handle requests for plugin pages */
            add_action( 'template_redirect', array( &$this, 'handle_page_requests' ) );
            /* Enqueue styles */
            add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );
            /* Enqueue scripts */
            add_action( 'wp_print_scripts', array( &$this, 'enqueue_scripts' ) );
            /* Print scripts */
            add_action( 'wp_head', array( &$this, 'print_scripts' ) );
        }
    }

    /**
     * Determine whether BuddyPress is active and based on that disable functions
     * that may interfere with the BuddyPress install
     *
     * @return void
     **/
    function buddypress_active() {
        $this->bp_active = true;
    }

    /**
     * Handle $_REQUEST for main pages.
     *
     * @uses set_query_var() For passing variables to pages
     * @return void|die() if "_wpnonce" is not verified
     **/
    function handle_page_requests() {
        /* Handles request for my-classifieds page */
        if ( is_page('my-classifieds') ) {
            /* If edit button is pressed */
            if ( isset( $_POST['edit'] ) ) {
                /* Verify _wpnonce field */
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    /* Set the post ID which will be used by "page-my-classifieds.php" */
                    set_query_var( 'cf_post_id', $_POST['post_id'] );
                    /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                    set_query_var( 'cf_action', 'edit' );
                } else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            }
            /* If update button is pressed */
            elseif ( isset( $_POST['update'] ) ) {
                /* The credits required to renew the classified for the selected period */
                $credits_required = $this->get_credits_from_duration( $_POST['custom_fields'][$this->custom_fields['duration']] );
                /* If user have more credits of the required credits proceed with renewing the ad */
                if ( $this->user_credits >= $credits_required ) {
                    /* Update ad */
                    $this->update_ad( $_POST, $_FILES );
                    /* Save the expiration date */
                    $this->save_expiration_date( $_POST['post_id'] );
                    /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                    set_query_var( 'cf_action', 'my-classifieds' );
                    /* Update new credits amount */
                    $credits = $this->user_credits - $credits_required;
                    update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
                }
            }
            /* If confirm button is pressed */
            elseif ( isset( $_POST['confirm'] ) ) {
                /* Verify _wpnonce field */
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
                    /* Process posts based on the action variables. End action */
                    if ( $_POST['action'] == 'end' ) {
                        $this->process_status( (int) $_POST['post_id'], 'private' );
                        /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                        set_query_var( 'cf_action', 'my-classifieds' );
                    }
                    /* Renew action */
                    elseif ( $_POST['action'] == 'renew' ) {
                        /* The credits required to renew the classified for the selected period */
                        $credits_required = $this->get_credits_from_duration( $_POST['duration'] );
                        /* If user have more credits of the required credits proceed with renewing the ad */
                        if ( $this->user_credits >= $credits_required ) {
                            /* Process the status of the post */
                            $this->process_status( (int) $_POST['post_id'], 'publish' );
                            /* Save the expiration date */
                            $this->save_expiration_date( $_POST['post_id'] );
                            /* Update new credits amount */
                            $credits = $this->user_credits - $credits_required;
                            update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
                            /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                            set_query_var( 'cf_action', 'my-classifieds' );
                        } else {
                            set_query_var( 'cf_action', 'insufficient-credits' );
                        }
                        //$this->process_credits()
                    }
                    /* Delete action */
                    elseif ( $_POST['action'] == 'delete' ) {
                        wp_delete_post( $_POST['post_id'] );
                        /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                        set_query_var( 'cf_action', 'my-classifieds' );
                    }
                } else {
                    die( __( 'Security check failed!', $this->text_domain ) );
                }
            }
            /* If create new button is pressed */
            elseif ( isset( $_POST['create_new'] ) ) {
                /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                set_query_var( 'cf_action', 'create-new' );
            }
            /* If save new button is pressed */
            elseif ( isset( $_POST['save_new'] ) ) {
                /* Validate form fields */
                $this->validate_fields( $_POST, $_FILES );
                if ( $this->form_valid ) {
                    $post_id = $this->update_ad( $_POST, $_FILES );
                    $this->save_expiration_date( $post_id );
                    wp_redirect( get_bloginfo('url') . '/classifieds/my-classifieds/' );
                } else {
                    set_query_var( 'cf_action', 'create-new' );
                    $error = __( 'Please make sure you fill all fields marked with (required)', $this->text_domain );
                    set_query_var( 'cf_error', $error );
                }
            }
            /* If user wants to go to My Classifieds main page  */
            elseif ( isset( $_POST['go_my_classifieds'] ) ) {
                wp_redirect( get_bloginfo('url') . '/classifieds/my-classifieds/' );
            }
            /* If user wants to go to My Classifieds main page  */
            elseif ( isset( $_POST['go_purchase'] ) ) {
                wp_redirect( get_bloginfo('url') . '/checkout/' );
            } else {
                /* Set the proper step which will be loaded by "page-my-classifieds.php" */
                set_query_var( 'cf_action', 'my-classifieds' );
            }
        }
        /* Handles request for classifieds page */
        elseif ( is_page('classifieds') ) {
            /* Set the proper step which will be loaded by "page-my-classifieds.php" */
            set_query_var( 'cf_action', 'my-classifieds' );
        }
    }
    
    /**
     * Enqueue styles.
     *
     * @return void
     **/
    function enqueue_styles() {
        if ( file_exists( get_template_directory() . '/style-classifieds.css' ) )
            wp_enqueue_style( 'style-classifieds', get_template_directory() . '/style-classifieds.css' );
        elseif ( file_exists( $this->plugin_dir . 'ui-front/general/style-classifieds.css' ) )
            wp_enqueue_style( 'style-classifieds', $this->plugin_url . 'ui-front/general/style-classifieds.css' );
    }

    /**
     * Enqueue scripts.
     *
     * @return void
     **/
    function enqueue_scripts() {
            wp_enqueue_script('jquery');
    }

    /**
     * Print scripts for BuddyPress pages
     *
     * @global object $bp
     * @return void
     **/
    function print_scripts() { ?>
        <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function($) {
            $('form.confirm-form').hide();
            $('form.cf-contact-form').hide();
        });
        var classifieds = {
            toggle_end: function(key) {
                jQuery('#confirm-form-'+key).show();
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('end');
            },
            toggle_renew: function(key) {
                jQuery('#confirm-form-'+key).show();
                jQuery('#confirm-form-'+key+' select[name="duration"]' ).show();
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('renew');
            },
            toggle_delete: function(key) {
                jQuery('#confirm-form-'+key).show();
                jQuery('#confirm-form-'+key+' select[name="duration"]' ).hide();
                jQuery('#action-form-'+key).hide();
                jQuery('input[name="action"]').val('delete');
            },
            toggle_contact_form: function() {
                jQuery('.cf-ad-info').hide();
                jQuery('#action-form').hide();
                jQuery('#confirm-form').show();
            },
            cancel_contact_form: function() {
                jQuery('#confirm-form').hide();
                jQuery('.cf-ad-info').show();
                jQuery('#action-form').show();
            },
            cancel: function(key) {
                jQuery('#confirm-form-'+key).hide();
                jQuery('#action-form-'+key).show();
            }
        };
        //]]>
        </script> <?php
    }
}
endif;

/* Initiate Class */
if ( class_exists('Classifieds_Core_Main') ) {
	$__classifieds_core_main = new Classifieds_Core_Main();
}
?>