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

		/* Handle requests for plugin pages */
		add_action( 'template_redirect', array( &$this, 'process_page_requests' ) );

		add_action( 'template_redirect', array( &$this, 'handle_page_requests' ) );

		/* Enqueue scripts */
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
	}

	/**
	* Process $_REQUEST for main pages.
	*
	* @uses set_query_var() For passing variables to pages
	* @return void|die() if "_wpnonce" is not verified
	**/
	function process_page_requests() {

		//Manage Classifieds
		if (is_page($this->my_classifieds_page_id) ){
			// If confirm button is pressed
			if ( isset( $_POST['confirm'] ) ) {
				// Verify _wpnonce field
				if ( wp_verify_nonce( $_POST['_wpnonce'], 'verify' ) ) {
					// Process posts based on the action variables. End action
					if ( $_POST['action'] == 'end' ) {
						$this->process_status( (int) $_POST['post_id'], 'private' );
					}

					// Renew action
					elseif ( $_POST['action'] == 'renew' ) {
						// The credits required to renew the classified for the selected period
						$credits_required = $this->get_credits_from_duration( $_POST['duration'] );
						// If user have more credits of the required credits proceed with renewing the ad
						if ( $this->is_full_access() || ($credits_required && $this->user_credits >= $credits_required ) ){
							// Process the status of the post
							$this->process_status( (int) $_POST['post_id'], 'publish' );
							// Save the expiration date
							$this->save_expiration_date( $_POST['post_id'] );

							if ( ! $this->is_full_access() ) {
								/* Update new credits amount */
								$this->transactions->credits -= $credits_required;
							} else {
								//Check one_time
								if($this->transactions->billing_type == 'one_time') $this->transactions->status = 'used';
							}

						} else {
							$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select a shorter period, if available, or purchase more credits. Your ad has been saved as a Draft.', $this->text_domain );
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

			//Updating Classifieds
		}elseif(is_page($this->add_classified_page_id) || is_page($this->edit_classified_page_id)){

			if ( isset( $_POST['update_classified'] ) ) {
				// The credits required to renew the classified for the selected period

				$credits_required = $this->get_credits_from_duration( $_POST[$this->custom_fields['duration'] ] );
				// If user have more credits of the required credits proceed with renewing the ad
				if ( $this->is_full_access() || ($credits_required && $this->user_credits >= $credits_required ) ){
					// Update ad
					$this->update_ad( $_POST);
					// Save the expiration date
					$this->save_expiration_date( $_POST['post_id'] );
					// Set the proper step which will be loaded by "page-my-classifieds.php"
					set_query_var( 'cf_action', 'my-classifieds' );

					if ( ! $this->is_full_access() ) {
						// Update new credits amount
						$this->transactions->credits -= $credits_required;
					} else {
						//Check one_time
						if($this->transactions->billing_type == 'one_time') $this->transactions->status = 'used';
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
					$error = __( 'You do not have enough credits to publish your classified for the selected time period. Please select a shorter period, if available, or purchase more credits.<br />Your ad has been saved as a Draft.', $this->text_domain );
					set_query_var( 'cf_error', $error );
				}
			}
		}
	}


	/**
	* Handle $_REQUEST for main pages.
	*
	* @uses set_query_var() For passing variables to pages
	* @return void|die() if "_wpnonce" is not verified
	**/
	function handle_page_requests() {
		global $wp_query;

		//print_r($wp_query); exit;


		/* Handles request for classifieds page */
		$page_template = locate_template( array('page.php' ) );
		$templates = array();


		if ( is_post_type_archive('classifieds') ) {
			/* Set the proper step which will be loaded by "page-my-classifieds.php" */
			$templates = array( 'archive-classifieds.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				$wp_query->post_count = 1;
				add_filter('the_title', array(&$this,'no_title') );
				add_filter('the_content', array(&$this, 'classifieds_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}

		elseif(is_single() && 'classifieds' == get_query_var('post_type')){
			$templates = array( 'single-classifieds.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				add_filter('the_content', array(&$this, 'single_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}

		elseif(is_page($this->my_credits_page_id) ){
			$templates = array( 'page-my-credits.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				add_filter('the_content', array(&$this, 'my_credits_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}

		elseif(is_page($this->checkout_page_id) ){
			$templates = array( 'page-checkout.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				add_filter('the_content', array(&$this, 'checkout_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}

		elseif(is_page($this->signin_page_id) ){
			$templates = array( 'page-signin.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				add_filter( 'the_title', array( &$this, 'delete_post_title' ), 11 ); //after wpautop
				add_filter('the_content', array(&$this, 'signin_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}
		//My Classifieds page
		elseif (is_page($this->my_classifieds_page_id) ){
			$templates = array( 'page-my-classifieds.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				add_filter('the_content', array(&$this, 'my_classifieds_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}
		//Classifieds update pages
		elseif(is_page($this->add_classified_page_id) || is_page($this->edit_classified_page_id)){
			$templates = array( 'page-update-classified.php' );
			if ( ! $this->classifieds_template = locate_template( $templates ) ) {
				$this->classifieds_template = $page_template;
				add_filter('the_content', array(&$this, 'update_classified_content'));
			}
			add_filter( 'template_include', array( &$this, 'custom_classifieds_template' ) );
		}
		/* If user wants to go to My Classifieds main page  */
		elseif ( isset( $_POST['go_my_classifieds'] ) ) {
			wp_redirect( get_permalink($this->my_classifieds_page_id) );
		}
		/* If user wants to go to checkout page  */
		elseif ( isset( $_POST['purchase'] ) ) {
			wp_redirect(  get_permalink($this->checkout_page_id)  );
		} else {
			/* Set the proper step which will be loaded by "page-my-classifieds.php" */
			set_query_var( 'cf_action', 'my-classifieds' );
		}
	}


	/**
	* Enqueue scripts.
	*
	* @return void
	**/
	function enqueue_scripts() {
		if ( file_exists( get_template_directory() . '/style-classifieds.css' ) )
		wp_enqueue_style( 'style-classifieds', get_template_directory() . '/style-classifieds.css' );
		elseif ( file_exists( $this->plugin_dir . 'ui-front/general/style-classifieds.css' ) )
		wp_enqueue_style( 'style-classifieds', $this->plugin_url . 'ui-front/general/style-classifieds.css' );
	}
}

/* Initiate Class */
global $Classifieds_Core;
$Classifieds_Core = new Classifieds_Core_Main;

endif;
?>