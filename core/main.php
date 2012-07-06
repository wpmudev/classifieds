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

	function Classifieds_Core_Main() { __construct();}

	function __construct(){

		parent::__construct(); //Get the inheritance right

	}

	function init(){

		parent::init();

		/* Handle requests for plugin pages */
		add_action( 'template_redirect', array( &$this, 'handle_page_requests' ) );
		/* Enqueue styles */
		add_action( 'wp_print_styles', array( &$this, 'enqueue_styles' ) );
		/* Enqueue scripts */
		add_action( 'wp_print_scripts', array( &$this, 'enqueue_scripts' ) );
		/* Print scripts */
		add_action( 'wp_head', array( &$this, 'print_scripts' ) );
	}

	function custom_classifieds_template( $template ) {
		return $this->classifieds_template;
	}

	/**
	* Handle $_REQUEST for main pages.
	*
	* @uses set_query_var() For passing variables to pages
	* @return void|die() if "_wpnonce" is not verified
	**/
	function handle_page_requests() {
		global $wp_query;

		/* Handles request for classifieds page */

		if ( is_page($this->classifieds_page_id) ) {
			/* Set the proper step which will be loaded by "page-my-classifieds.php" */
			//			set_query_var( 'cf_action', 'my-classifieds' );
			$templates = array( 'page-classifieds.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_classifieds'));
			}


		}elseif(is_page($this->add_classified_page_id) || is_page($this->edit_classified_page_id)){
			$templates = array( 'page-update-classified.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_update_classified'));
			}
		}elseif(is_single() && 'classifieds' == get_query_var('post_type')){
			$templates = array( 'single-classifieds.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_single_classifieds'));
			}
		}elseif(is_page($this->my_credits_page_id) ){
			$templates = array( 'page-my-credits.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_my_credits'));
			}
		}elseif(is_page($this->checkout_page_id) ){
			$templates = array( 'page-checkout.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_checkout'));
			}
		}elseif(is_page($this->signin_page_id) ){
			$templates = array( 'page-signin.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_signin'));
			}
		}

		if (is_page($this->my_classifieds_page_id) ){
			$templates = array( 'page-my-classifieds.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_my_classifieds'));
			}

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
			elseif ( isset( $_POST['update_classified'] ) ) {
				/* The credits required to renew the classified for the selected period */
				$credits_required = $this->get_credits_from_duration( $_POST['custom_fields'][$this->custom_fields['duration']] );
				/* If user have more credits of the required credits proceed with renewing the ad */
				if ( $this->is_full_access() || $this->user_credits >= $credits_required ) {
					/* Update ad */
					$this->update_ad( $_POST, $_FILES );
					/* Save the expiration date */
					$this->save_expiration_date( $_POST['post_id'] );
					/* Set the proper step which will be loaded by "page-my-classifieds.php" */
					set_query_var( 'cf_action', 'my-classifieds' );

					if ( ! $this->is_full_access() ) {
						/* Update new credits amount */
						$credits = $this->user_credits - $credits_required;
						update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
					}
				} else {
					set_query_var( 'cf_post_id', $_POST['post_id'] );
					/* Set the proper step which will be loaded by "page-my-classifieds.php" */
					set_query_var( 'cf_action', 'edit' );
					$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select lower period if available or purchase more credits.', $this->text_domain );
					set_query_var( 'cf_error', $error );
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
						if ( $this->is_full_access() || $this->user_credits >= $credits_required ) {
							/* Process the status of the post */
							$this->process_status( (int) $_POST['post_id'], 'publish' );
							/* Save the expiration date */
							$this->save_expiration_date( $_POST['post_id'] );

							if ( ! $this->is_full_access() ) {
								/* Update new credits amount */
								$credits = $this->user_credits - $credits_required;
								update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
							}
							/* Set the proper step which will be loaded by "page-my-classifieds.php" */
							set_query_var( 'cf_action', 'my-classifieds' );
						} else {
							set_query_var( 'cf_action', 'my-classifieds' );
							$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select lower period if available or purchase more credits.', $this->text_domain );
							set_query_var( 'cf_error', $error );
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
			}            /* If create new button is pressed */
			elseif ( isset( $_POST['my_credits'] ) ) {
				/* Set the proper step which will be loaded by "page-my-classifieds.php" */
				set_query_var( 'cf_action', 'my-credits' );
			}
			/* If save new button is pressed */
			elseif ( isset( $_POST['save_new'] ) ) {
				/* Validate form fields */
				$this->validate_fields( $_POST, $_FILES );
				if ( $this->form_valid ) {
					/* The credits required to create the classified for the selected period */
					$credits_required = $this->get_credits_from_duration( $_POST['custom_fields'][$this->custom_fields['duration']] );
					/* If user have more credits of the required credits proceed with create the ad */
					if ( $this->is_full_access() || $this->user_credits >= $credits_required ) {
						/* Create ad */
						$post_id = $this->update_ad( $_POST, $_FILES );
						/* Save the expiration date */
						$this->save_expiration_date( $post_id );

						if ( ! $this->is_full_access() ) {
							/* Update new credits amount */
							$credits = $this->user_credits - $credits_required;
							update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
						}
						/* Set the proper step which will be loaded by "page-my-classifieds.php" */
						set_query_var( 'cf_action', 'my-classifieds' );
					} else {
						//save ad if have not credits but select draft
						if ( isset( $_POST['status'] ) && 'draft' == $_POST['status'] ) {
							/* Create ad */
							$post_id = $this->update_ad( $_POST, $_FILES );
							set_query_var( 'cf_action', 'my-classifieds' );
						} else {
							set_query_var( 'cf_action', 'create-new' );
							$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select lower period if available or save this ad with status as "draft" and after purchase more credits you will can edit this ad.', $this->text_domain );
							set_query_var( 'cf_error', $error );
						}
					}
				} else {
					set_query_var( 'cf_action', 'create-new' );
					$error = __( 'Please make sure you fill all fields marked with (required)', $this->text_domain );
					set_query_var( 'cf_error', $error );
				}
			}
			/* If user wants to go to My Classifieds main page  */
			elseif ( isset( $_POST['new_account'] ) ) {
				wp_redirect( get_permalink($this->checkout_page_id) . '?cf_step=terms' );
			}
			/* If user wants to go to My Classifieds main page  */
			elseif ( isset( $_POST['go_my_classifieds'] ) ) {
				wp_redirect( get_permalink($this->my_classifieds_page_id) );
			}
			/* If user wants to go to My Classifieds main page  */
			elseif ( isset( $_POST['purchase'] ) ) {
				wp_redirect(  get_permalink($this->checkout_page_id)  );
			} else {
				/* Set the proper step which will be loaded by "page-my-classifieds.php" */
				set_query_var( 'cf_action', 'my-classifieds' );
			}
		}
	}

	/**
	* Update Classifieds.
	*
	* @return void
	**/
	function content_classifieds($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('classifieds'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* Update Classifieds.
	*
	* @return void
	**/
	function content_update_classified($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('update-classified'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* My Classifieds.
	*
	* @return void
	**/
	function content_my_classifieds($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('my-classifieds'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* My Classifieds.
	*
	* @return void
	**/
	function content_checkout($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('checkout'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* Signin.
	*
	* @return void
	**/
	function content_signin($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->template_file('signin'));
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

/**
	* My Classifieds Credits.
	*
	* @return void
	**/
	function content_my_credits($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->plugin_dir . 'ui-front/general/page-my-classifieds-credits.php');
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	/**
	* Single Classifieds.
	*
	* @return void
	**/
	function content_single_classifieds($content = null){
		if(! in_the_loop()) return $content;
		ob_start();
		require($this->plugin_dir . 'ui-front/general/single-classifieds.php');
		$new_content = ob_get_contents();
		ob_end_clean();
		return $new_content;
	}

	function update_classified($params){
		/* Construct args for the new post */
		$args = array(
		/* If empty ID insert Listing instead of updating it */
		'ID'             => ( isset( $params['classified_data']['ID'] ) ) ? $params['classified_data']['ID'] : '',
		'post_title'     => wp_strip_all_tags($params['classified_data']['post_title']),
		'post_content'   => $params['classified_data']['post_content'],
		'post_excerpt'   => (isset($params['classified_data']['post_excerpt'])) ? $params['classified_data']['post_excerpt'] : '',
		'post_status'    => $params['classified_data']['post_status'],
		'post_author'    => get_current_user_id(),
		'post_type'      => 'classifieds',
		'ping_status'    => 'closed',
		//'comment_status' => 'closed'
		);

		/* Insert page and get the ID */
		if (empty($args['ID']))
		$post_id = wp_insert_post( $args );
		else
		$post_id = wp_update_post( $args );

		if (! empty( $post_id ) ) {

			//Save custom tags
			if(is_array($params['tag_input'])){
				foreach($params['tag_input'] as $key => $tags){
					wp_set_post_terms($post_id, $params['tag_input'][$key], $key);
				}
			}
			
			//Save categories
			if(is_array($params['post_category'])){
				wp_set_post_terms($post_id, $params['post_category'], 'category');
			}

			//Save custom terms
			if(is_array($params['tax_input'])){
				foreach($params['tax_input'] as $key => $term_ids){
					if ( is_array( $params['tax_input'][$key] ) ) {
						wp_set_post_terms($post_id, $params['tax_input'][$key], $key);
					}
				}
			}

			if ( class_exists( 'CustomPress_Core' ) ) {
				global $CustomPress_Core;
				$CustomPress_Core->save_custom_fields( $post_id );
			}

			return $post_id;
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
	function print_scripts() {
		?>
		<script type="text/javascript">
			-			//<![CDATA[
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
		</script>
		<?php
	}
}

/* Initiate Class */
global $Classifieds_Core;
$Classifieds_Core = new Classifieds_Core_Main();

endif;
?>