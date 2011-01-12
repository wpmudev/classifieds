<?php

/**
 * Classifieds WIDGETS
 * Handles the widget operations
 *
 * @package Classifieds
 * @subpackage Widgets
 * @since 1.1.0
 */

/**
 * Classifieds Ads Widget
 */
class Classifieds_Ads_Widget extends WP_Widget {

    function Classifieds_Ads_Widget() {
        $widget_options = array( 'classname' => 'classifieds-ads-widget', 'description' => __('The Classifieds widget displays ads based on your configurations.', 'classifieds') );
        $control_options = array( 'id_base' => 'classifieds-ads-id' );
                
        parent::WP_Widget( 'classifieds-ads-id', __('Classifieds - Ads', 'classifieds'), $widget_options, $control_options );
    }

    function widget( $args, $instance ) {
        global $wpdb, $current_site;
        extract( $args );
        
        $title = apply_filters( 'widget_title', $instance['title'] );
        
        echo $before_widget;
        
        if ( $title )
            echo $before_title . $title . $after_title;

        /* Construct query and fetch ads info  */
		$query = "SELECT ad_ID, ad_title FROM {$wpdb->base_prefix}classifieds_ads WHERE ad_status = 'active'";
        $users = ( $instance['user'] == 'all-users' ) ? '' : "AND ad_user_ID = {$instance['user']}";
        $order = ( $instance['order'] == 'random' ) ? 'ORDER BY RAND()' : 'ORDER BY ad_ID DESC';
        $query .= " {$users} {$order} LIMIT {$instance['number']}";
        $result = $wpdb->get_results( $query, ARRAY_A ); ?>

        <ul>
        <?php if ( count( $result ) > 0 ): ?>
            <?php foreach ( $result as $result ): ?>
            <li>
                <a href="<?php if ( is_multisite() ) echo 'http://' . $current_site->domain . $current_site->path . CLASSIFIEDS_PATH; else echo get_bloginfo('url') . '/' . CLASSIFIEDS_PATH; ?>?ad=<?php echo $result['ad_ID']; ?>"><img src="<?php echo get_bloginfo('url') . "/wp-content/classifieds-images/" . $result['ad_ID'] . "-80.png"; ?>" /></a>
                <a href="<?php if ( is_multisite() ) echo 'http://' . $current_site->domain . $current_site->path . CLASSIFIEDS_PATH; else echo get_bloginfo('url') . '/' . CLASSIFIEDS_PATH; ?>?ad=<?php echo $result['ad_ID']; ?>"><strong><?php echo $result['ad_title']; ?></strong></a>
            </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><?php _e('Nothing to display...', 'classifieds'); ?></li>
        <?php endif; ?>
        </ul>
        
        <?php
        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        
        $instance['title']   = strip_tags( $new_instance['title'] );
        $instance['user']    = $new_instance['user'];
        $instance['number']  = $new_instance['number'];
        $instance['order']   = $new_instance['order'];
        
        return $instance;
    }

    function form( $instance ) {
        global $wpdb, $current_user;

        if ( current_user_can('edit_users') ) {
            $query = "SELECT DISTINCT ad_user_ID FROM {$wpdb->base_prefix}classifieds_ads";
            $result = $wpdb->get_results( $query, ARRAY_A );
            $user_default = 'all-users';
        } else {
            $result = array( array('ad_user_ID' => $current_user->ID) );
            $user_default = $current_user->ID;
        }
       
        $defaults = array( 'title' => __('Classifieds', 'classifieds'), 'user' => $user_default, 'number' => 10, 'order' => __('Most Recent', 'classifieds') );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'classifieds'); ?></label>
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
            <br><br>
            <label for="<?php echo $this->get_field_id( 'user' ); ?>" ><?php _e('Display ads from:', 'classifieds'); ?></label>
            <select id="<?php echo $this->get_field_id( 'user' ); ?>" name="<?php echo $this->get_field_name( 'user' ); ?>">
                <?php if ( $user_default == 'all-users' ): ?>
                <option value="all-users" <?php if ( $instance['user'] == 'all-users' ) echo 'selected="selected"'; ?>><?php _e('All Users', 'classifieds'); ?></option>
                <?php endif; ?>
                <?php foreach( $result as $result ): ?>
                <option value="<?php echo $result['ad_user_ID']; ?>" <?php if ( $instance['user'] == $result['ad_user_ID'] ) echo 'selected="selected"'; ?>>
                    <?php $user_info = get_userdata( $result['ad_user_ID'] ); ?>
                    <?php echo $user_info->user_login; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e('Number of ads:', 'classifieds'); ?></label>
            <select id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>">
                <?php for( $i = 1; $i <= 10; $i++ ): ?>
                <option value="<?php echo $i; ?>"<?php if ( $instance['number'] == $i ) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <br><br>
            <label for="<?php echo $this->get_field_id( 'order' ); ?>" ><?php _e('Order by:', 'classifieds'); ?></label>
            <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
                <option value="most-recent" <?php if ( $instance['order'] == 'most-recent' ) echo 'selected="selected"'; ?>><?php _e('Most Recent', 'classifieds'); ?></option>
                <option value="random" <?php if ( $instance['order'] == 'random' ) echo 'selected="selected"'; ?>><?php _e('Random', 'classifieds'); ?></option>
            </select>
        </p> <?php
    }
}
/* Register Classifieds Ads Widget */
add_action( 'widgets_init', create_function( '', 'return register_widget("Classifieds_Ads_Widget");' ) );

/**
 * Classifieds Categories Widget
 */
class Classifieds_Categories_Widget extends WP_Widget {

    function Classifieds_Categories_Widget() {
        parent::WP_Widget( false, $name = 'Classifieds - Categories' );
    }

    function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );

        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;

        if ( $title )
            echo $before_title . $title . $after_title;

        classifieds_frontend_list_categories();

        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']); ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
                <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
        </p> <?php
    }
}
/* Register Classifieds Search Widget */
add_action('widgets_init', create_function('', 'return register_widget("Classifieds_Categories_Widget");'));

/**
 * Classifieds: Search Widget
 */
class Classifieds_Search_Widget extends WP_Widget {

    function Classifieds_Search_Widget() {
        parent::WP_Widget( false, $name = 'Classifieds - Search' );
    }

    function widget( $args, $instance ) {
        global $wpdb;

        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;

        classifieds_frontend_search_form();

        echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']); ?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
        </p> <?php
    }
}
/* Register Classifieds Search Widget */
add_action('widgets_init', create_function('', 'return register_widget("Classifieds_Search_Widget");'));

?>