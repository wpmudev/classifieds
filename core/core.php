<?php

/**
 * Classifieds Core Class
 */
if ( !class_exists('Classifieds_Core') ):
class Classifieds_Core {

    var $options_name     = 'classifieds_options';
    var $plugin_url       = CF_PLUGIN_URL;
    var $plugin_dir       = CF_PLUGIN_DIR;
    var $plugin_prefix    = 'cf_';
    var $text_domain      = 'classifieds';
    var $post_type        = 'classifieds';

    /**
     * Initiate the custom field keys
     */
    var $custom_fields = array();


    /**
     * Constructor.
     * @todo remove constructor
     */
    function Classifieds_Core() {
        /* Init plugin */
        add_action( 'init', array( &$this, 'init' ) );
        /* Add theme support for featured images */
        add_theme_support( 'post-thumbnails' );
    }

    /**
     * 
     */
    function init() {
        /* Filter the_content function output and add ad specific data */
        add_filter( 'the_content', array( &$this, 'filter_the_content' ) );
        /* Set user frindly custom fields objects */
        $this->custom_fields['price']    = '_ct_selectbox_4ce176abe31a3';
        $this->custom_fields['duration'] = '_ct_selectbox_4ce176abe31a3';
    }

    /**
     * Save plugin options.
     *
     * @param array
     * @return die() if _wpnonce is not verified
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
     * @return array $options Plugin options or empty array if no options are found
     */
    function get_options( $key = NULL ) {
        $options = get_option( $this->options_name );
        $options = is_array( $options ) ? $options : array();
        /* Check if specific plugin option is requested and return it */
        if ( isset( $key ) && array_key_exists( $key, $options ) ) 
            return $options[$key];
        else 
            return $options;
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
		if ( file_exists( "{$this->plugin_dir}/ui-admin/{$name}.php" ) )
			include "{$this->plugin_dir}/ui-admin/{$name}.php";
		else
			echo "<p>Rendering of admin template {$this->plugin_dir}/ui-admin/{$name}.php failed</p>";
	}

    /**
	 * Renders a section of user display code.  The code is first checked for in the current theme display directory
	 * before defaulting to the plugin
	 *
	 * @param string $name Name of the admin file(without extension)
	 * @param string $vars Array of variable name=>value that is available to the display code(optional)
	 * @return void
	 **/
	function render_front( $name, $vars = array() ) {
        /* Construct extra arguments */
		foreach ( $vars as $key => $val )
			$$key = $val;
        /* Include templates */
		if ( file_exists( get_template_directory() . "/$name.php" ) )
			include get_template_directory() . "/$name.php";
        elseif ( file_exists( get_template_directory() . "/members/single/$name.php" ) && $this->bp_active == true )
			include get_template_directory() . "/members/single/$name.php";
        elseif ( file_exists( "{$this->plugin_dir}/ui-front/buddypress/$name.php" ) && $this->bp_active == true )
			include "{$this->plugin_dir}/ui-front/buddypress/$name.php";
		elseif ( file_exists( "{$this->plugin_dir}/ui-front/$name.php" ) )
			include "{$this->plugin_dir}/ui-front/$name.php";
		else
			echo "<p>Rendering of template $name.php failed</p>";
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
}
endif;

if ( class_exists('Classifieds_Core') )
	$__classifieds_core = new Classifieds_Core();

?>
