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
			$templates = array( 'page-classifieds.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_classifieds'));
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

			//My Classifieds page
		}elseif (is_page($this->my_classifieds_page_id) ){
			$templates = array( 'page-my-classifieds.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_my_classifieds'));
			}

			/* If confirm button is pressed */
			if ( isset( $_POST['confirm'] ) ) {
				/* Verify _wpnonce field */
				if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
					/* Process posts based on the action variables. End action */
					if ( $_POST['action'] == 'end' ) {
						$this->process_status( (int) $_POST['post_id'], 'private' );
					}
					/* Renew action */
					elseif ( $_POST['action'] == 'renew' ) {
						/* The credits required to renew the classified for the selected period */
						$credits_required = $this->get_credits_from_duration( $_POST['duration'] );
						/* If user have more credits of the required credits proceed with renewing the ad */
						if ( $this->is_full_access() || ($credits_required && $this->user_credits >= $credits_required ) ){
							/* Process the status of the post */
							$this->process_status( (int) $_POST['post_id'], 'publish' );
							/* Save the expiration date */
							$this->save_expiration_date( $_POST['post_id'] );

							if ( ! $this->is_full_access() ) {
								/* Update new credits amount */
								$credits = $this->user_credits - $credits_required;
								update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
							} else {
								//Check one_time
								$cf_order = get_user_meta( $this->current_user->ID, 'cf_order', true );
								if ( 'one_time' == $cf_order['billing'] && 'success' == $cf_order['order_info']['status'] ) {
									delete_user_meta($this->current_user->ID, 'cf_order');
								}
							}
						} else {
							$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select lower period if available or purchase more credits.<br />Your Ad has been saved as a Draft.', $this->text_domain );
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

			//Classifieds update pages
		}elseif(is_page($this->add_classified_page_id) || is_page($this->edit_classified_page_id)){
			$templates = array( 'page-update-classified.php' );
			if ( $this->classifieds_template = locate_template( $templates ) ) {
				add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
			} else {
				add_filter('the_content', array(&$this, 'content_update_classified'));
			}

			if ( isset( $_POST['update_classified'] ) ) {
				/* The credits required to renew the classified for the selected period */

				$credits_required = $this->get_credits_from_duration( $_POST[$this->custom_fields['duration'] ] );
				/* If user have more credits of the required credits proceed with renewing the ad */
				if ( $this->is_full_access() || ($credits_required && $this->user_credits >= $credits_required ) ){
					/* Update ad */
					$this->update_ad( $_POST);
					/* Save the expiration date */
					$this->save_expiration_date( $_POST['post_id'] );
					/* Set the proper step which will be loaded by "page-my-classifieds.php" */
					set_query_var( 'cf_action', 'my-classifieds' );

					if ( ! $this->is_full_access() ) {
						/* Update new credits amount */
						$credits = $this->user_credits - $credits_required;
						update_user_meta( $this->current_user->ID, 'cf_credits', $credits );
					} else {
						//Check one_time
						$cf_order = get_user_meta( $this->current_user->ID, 'cf_order', true );
						if ( 'one_time' == $cf_order['billing'] && 'success' == $cf_order['order_info']['status'] ) {
							delete_user_meta($this->current_user->ID, 'cf_order');
						}
					}
					wp_redirect(get_permalink($_POST['post_id']) ); exit;
				} else {
					//save ad if have no credits
					$_POST['classified_data']['post_status'] = 'draft';
					/* Create ad */
					$post_id = $this->update_ad( $_POST );
					set_query_var( 'cf_post_id', $_POST['post_id'] );
					/* Set the proper step which will be loaded by "page-my-classifieds.php" */
					set_query_var( 'cf_action', 'edit' );
					$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select lower period if available or purchase more credits.<br />Your Ad has been saved as a Draft.', $this->text_domain );
					set_query_var( 'cf_error', $error );
					//wp_redirect(get_permalink($this->edit_classified_page_id) ); exit;
				}
			}
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
		require($this->plugin_dir . 'ui-front/general/page-my-credits.php');
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

}

/* Initiate Class */
global $Classifieds_Core;
$Classifieds_Core = new Classifieds_Core_Main();

endif;
?>