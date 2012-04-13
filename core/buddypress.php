<?php

/**
* Classifieds Core BuddyPress Class
**/
if ( !class_exists('Classifieds_Core_BuddyPress') ):
class Classifieds_Core_BuddyPress extends Classifieds_Core {

	/**
	* Constructor. Hooks the whole module to the 'bp_init" hook.
	*
	* @return void
	**/
	function Classifieds_Core_BuddyPress() { __construct(); }

	function __construct(){

		parent::__construct(); //Inheritance

	}

	function init(){

		parent::init(); //Inheritance

		/* Set BuddyPress active state */
		$this->bp_active = true;

		/* Add navigation */
		add_action( 'wp', array( &$this, 'add_navigation' ), 2 );

		/* Add navigation */
		add_action( 'admin_menu', array( &$this, 'add_navigation' ), 2 );

		/* Enqueue styles */
		add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );
		add_action( 'wp_head', array( &$this, 'print_scripts' ) );
		add_action( 'bp_template_content', array( &$this, 'handle_template_requests' ) );

		/* template for  page */
		add_action( 'template_redirect', array( &$this, 'handle_nav' ) );

	}





	/**
	* Add BuddyPress navigation.
	*
	* @return void
	**/
	function add_navigation() {
		global $bp;

		/* Set up classifieds as a sudo-component for identification and nav selection */
		$classifieds_page = get_page($this->classifieds_page_id);

		$bp->classifieds->slug = $classifieds_page->post_name;
		/* Construct URL to the BuddyPress profile URL */
		$user_domain = ( !empty( $bp->displayed_user->domain ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;
		$parent_url = $user_domain . $bp->classifieds->slug . '/';

		/* Add the settings navigation item */
		//		$__classifieds_core = new Classifieds_Core();
		//		$classifieds_page = $__classifieds_core->get_page_by_meta( 'classifieds' );

		if ( 0 < $classifieds_page->ID )
		$nav_title = $classifieds_page->post_title;
		else
		$nav_title = 'Classifieds';

		bp_core_new_nav_item( array(
		'name'                    => __( $nav_title, $this->text_domain ),
		'slug'                    => $bp->classifieds->slug,
		'position'                => 100,
		'show_for_displayed_user' => true,
		'screen_function'         => array( &$this, 'load_template' )
		));

		if ( bp_is_my_profile() ) {

			$classifieds_page = get_page($this->my_classifieds_page_id);;

			if ( 0 < $classifieds_page->ID )
			$nav_title = $classifieds_page->post_title;
			else
			$nav_title = 'My Classifieds';

			bp_core_new_subnav_item( array(
			'name'            => __( $nav_title, $this->text_domain ),
			'slug'            => $classifieds_page->post_name,
			'parent_url'      => $parent_url,
			'parent_slug'     => $bp->classifieds->slug,
			'screen_function' => array( &$this, 'load_template' ),
			'position'        => 10,
			'user_has_access' => true
			));

			if($this->use_credits && ! $this->is_full_access()){
				bp_core_new_subnav_item( array(
				'name'            => __( 'My Credits', $this->text_domain ),
				'slug'            => 'my-credits',
				'parent_url'      => $parent_url,
				'parent_slug'     => $bp->classifieds->slug,
				'screen_function' => array( &$this, 'load_template' ),
				'position'        => 10,
				'user_has_access' => true
				));
			}

			bp_core_new_subnav_item( array(
			'name'            => __( 'Create New Ad', $this->text_domain ),
			'slug'            => 'create-new',
			'parent_url'      => $parent_url,
			'parent_slug'     => $bp->classifieds->slug,
			'screen_function' => array( &$this, 'load_template' ),
			'position'        => 10,
			'user_has_access' => true
			));

		} else {
			//display author classifids page
			bp_core_new_subnav_item( array(
			'name'            => __( 'All', $this->text_domain ),
			'slug'            => 'all',
			'parent_url'      => $parent_url,
			'parent_slug'     => $bp->classifieds->slug,
			'screen_function' => array( &$this, 'load_template' ),
			'position'        => 10,
			'user_has_access' => true
			));
		}
	}

	/**
	* Load BuddyPress theme template file for plugin specific page.
	*
	* @return void
	**/
	function load_template() {
		/* This is generic BuddyPress plugins file. All other functions hook
		* themselves into the plugins template hooks. Each BuddyPress component
		* "members", "groups", etc. offers different plugin file and different hooks */

		bp_core_load_template( 'members/single/plugins', true );
	}

	function handle_nav(){

		global $bp, $post;

		/* Handles request for classifieds page */
		if ( $bp->current_component == $this->classifieds_page_slug && $bp->current_action == 'all' ) {
			$this->js_redirect( trailingslashit($bp->loggedin_user->domain) . $this->classifieds_page_slug .'/' . $this->my_classifieds_page_slug . '/active', true);

		}
		elseif( $post->ID == $this->my_classifieds_page_id ) {
			/* Set the proper step which will be loaded by "page-my-classifieds.php" */
			$this->js_redirect( trailingslashit($bp->loggedin_user->domain) . $this->classifieds_page_slug .'/' . $this->my_classifieds_page_slug . '/active', true);
		}
	}


	/**
	* Load the content for the specific classifieds component and handle requests
	*
	* @global object $bp
	* @return void
	**/
	function handle_template_requests() {
		global $bp;

		//Component my-classifieds page
		if ( $bp->current_component == $this->classifieds_page_slug && $bp->current_action == $this->my_classifieds_page_slug ) {

			if ( isset( $_POST['edit'] ) ) {
				if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) )
				$this->render_front('edit-ad', array( 'post_id' => (int) $_POST['post_id'] ));
				else
				die( __( 'Security check failed!', $this->text_domain ) );
			}

			elseif ( isset( $_POST['update'] ) ) {
				/* The credits required to renew the classified for the selected period */
				$credits_required = $this->get_credits_from_duration( $_POST['custom_fields'][$this->custom_fields['duration']] );
				/* If user have more credits of the required credits proceed with renewing the ad */
				if ( $this->is_full_access() || $this->user_credits >= $credits_required ) {
					/* Update ad */
					$this->update_ad( $_POST, $_FILES );
					/* Save the expiration date */
					$this->save_expiration_date( $_POST['post_id'] );

					if ( ! $this->is_full_access() ) {
						/* Update new credits amount */
						$credits = $this->user_credits - $credits_required;
						update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
					}

					$this->render_front('my-classifieds', array( 'action' => 'edit', 'post_title' => $_POST['post_title'] ));
				} else {
					$this->render_front('edit-ad', array( 'post_id' => (int) $_POST['post_id'], 'cl_credits_error' => '1' ));
				}
			}
			elseif ( isset( $_POST['confirm'] ) ) {
				if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
					if ( $_POST['action'] == 'end' ) {
						$this->process_status( (int) $_POST['post_id'], 'private' );
						$this->render_front('my-classifieds', array( 'action' => 'end', 'post_title' => $_POST['post_title'] ));
					} elseif ( $_POST['action'] == 'renew' ) {
						/* The credits required to renew the classified for the selected period */
						$credits_required = $this->get_credits_from_duration( $_POST['duration'] );
						/* If user have more credits of the required credits proceed with renewing the ad */
						if ( $this->is_full_access() || $this->user_credits >= $credits_required ) {
							/* Process the status of the post */
							$this->process_status( (int) $_POST['post_id'], 'publish' );
							/* Save the expiration date */
							$this->save_expiration_date( $_POST['post_id'] );

							if ( ! $this->is_full_access() ) {
								/* Update new credits amount */
								$credits = $this->user_credits - $credits_required;
								update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
								/* Set the proper step which will be loaded by "page-my-classifieds.php" */
							}

							$this->render_front('my-classifieds', array( 'action' => 'renew', 'post_title' => $_POST['post_title'] ));
						} else {
							$this->render_front('my-classifieds', array( 'cl_credits_error' => '1' ));
						}
					} elseif ( $_POST['action'] == 'delete' ) {
						wp_delete_post( $_POST['post_id'] );
						$this->render_front('my-classifieds', array( 'action' => 'delete', 'post_title' => $_POST['post_title'] ));
					}
				} else {
					die( __( 'Security check failed!', $this->text_domain ) );
				}
			} else {
				$this->render_front('my-classifieds');
			}
		}
		//Component create-new page
		elseif ( $bp->current_component == $this->classifieds_page_slug && $bp->current_action == 'create-new' ) {

			if ( isset( $_POST['save'] ) ) {

				$this->validate_fields( $_POST, $_FILES );
				if ( $this->form_valid ) {
					/* The credits required to create the classified for the selected period */
					$credits_required = $this->get_credits_from_duration( $_POST['custom_fields'][$this->custom_fields['duration']] );
					/* If user have more credits of the required credits proceed with create the ad */
					if ( $this->is_full_access() || $this->user_credits >= $credits_required ) {
						global $bp;
						/* Create ad */
						$post_id = $this->update_ad( $_POST, $_FILES );
						/* Save the expiration date */
						$this->save_expiration_date( $post_id );

						if ( ! $this->is_full_access() ) {
							/* Update new credits amount */
							$credits = $this->user_credits - $credits_required;
							update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
						}

						//						if ( "" != $bp->loggedin_user->userdata->user_url )
						//						$this->js_redirect( trailingslashit($bp->loggedin_user->userdata->user_url) . $this->classifieds_page_slug . '/' . $this->my_classifieds_page_slug );
						//						else
						$this->js_redirect( trailingslashit($bp->loggedin_user->domain) . $this->classifieds_page_slug . '/' . $this->my_classifieds_page_slug );
					} else {
						//save ad if have not credits but select draft
						if ( isset( $_POST['status'] ) && 'draft' == $_POST['status'] ) {
							/* Create ad */
							$post_id = $this->update_ad( $_POST, $_FILES );
							//							if ( "" != $bp->loggedin_user->userdata->user_url )
							//							$this->js_redirect( trailingslashit($bp->loggedin_user->userdata->user_url) . $this->classifieds_page_slug . '/' . $this->my_classifieds_page_slug );
							//							else
							$this->js_redirect( trailingslashit($bp->loggedin_user->domain) . $this->classifieds_page_slug . '/' . $this->my_classifieds_page_slug );
						} else {
							$this->render_front('create-new', array( 'cl_credits_error' => '1' ));
						}
					}
				} else {
					$this->render_front('create-new');
				}
			} else {
				$this->render_front('create-new');
			}

		}
		//Component my-credits page
		elseif ( $bp->current_component == $this->classifieds_page_slug && $bp->current_action == 'my-credits' ) {
			//redirect on checkout page
			if ( isset( $_POST['purchase'] ) ) {
				$this->js_redirect( get_permalink($this->checkout_page_id) );
				exit;
			}
			//show credits page
			$this->render_front('my-credits');
		}
		//Component Author classifieds page (classifieds/all)
		elseif ( $bp->current_component == $this->classifieds_page_slug && $bp->current_action == 'all' ) {

			//show author classifieds page
			$this->render_front('my-classifieds');
		}
		//default for classifieds page
		elseif ( $bp->current_component == $this->classifieds_page_slug ) {
			if ( bp_is_my_profile() ) {
				$this->js_redirect( trailingslashit($bp->loggedin_user->domain) . $this->classifieds_page_slug . '/' . $this->my_classifieds_page_slug );
			} else {
				$this->js_redirect( trailingslashit($bp->displayed_user->domain) . $this->classifieds_page_slug . '/' . 'all' );
			}
		}
	}

	/**
	* Enqueue styles.
	*
	* @return void
	**/
	function enqueue_styles() {
		if ( file_exists( get_template_directory() . '/style-bp-classifieds.css' ) )
		wp_enqueue_style( 'style-classifieds', get_template_directory() . '/style-bp-classifieds.css' );
		elseif ( file_exists( $this->plugin_dir . 'ui-front/buddypress/style-bp-classifieds.css' ) )
		wp_enqueue_style( 'style-classifieds', $this->plugin_url . 'ui-front/buddypress/style-bp-classifieds.css' );
	}

	/**
	* Print scripts for BuddyPress pages
	*
	* @global object $bp
	* @return void
	**/
	function print_scripts() {
		global $bp;
		if ( $bp->current_component == $this->classifieds_page_slug || is_single() ) {
			?>
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
						jQuery('#duration-'+key).show();
						jQuery('#action-form-'+key).hide();
						jQuery('input[name="action"]').val('renew');
					},
					toggle_delete: function(key) {
						jQuery('#confirm-form-'+key).show();
						jQuery('#action-form-'+key).hide();
						jQuery('#duration-'+key).hide();
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
			</script>
			<?php
		}
	}
}

/* Initiate Class */
//Only gets called if code is included by bp_include action from Buddypress
global $__classifieds_core;
$__classifieds_core = new Classifieds_Core_BuddyPress();

endif;
?>