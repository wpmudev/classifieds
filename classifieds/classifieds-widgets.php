<?php

/*
Plugin Name: Classifieds
Plugin URI: http://premium.wpmudev.org/project/classifieds
Description: A brief description of the Plugin.
Version: 1.1.0
Author: Andrew Billits, Ivan Shaovchev
Author URI:
License: GNU General Public License (Version 2 - GPLv2)
*/

/*
Copyright 2007-2010 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
 * Classifieds WIDGETS
 * Handles the widget operations
 *
 * @package Classifieds
 * @subpackage Widgets
 * @since 1.1.0
 */

function widget_classifieds_main_blog_init() {
	global $wpdb;

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_classifieds_main_blog_control() {
		global $wpdb;
		$options = $newoptions = get_option('widget_classifieds_main_blog');
		if ( $_POST['classifieds_main_blog_submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['title']));
			$newoptions['number'] = strip_tags(stripslashes($_POST['number']));
			$newoptions['order_by'] = strip_tags(stripslashes($_POST['order_by']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_classifieds_main_blog', $options);
		}
	?>
				<div style="text-align:right">
                <?php
                if ($options['title'] == ''){
					$tmp_widget_title = 'Classifieds';
				} else {
					$tmp_widget_title = wp_specialchars($options['title'], true);
				}
				?>
				<label for="title" style="line-height:35px;display:block;"><?php _e('Title:', 'widgets'); ?> <input type="text" id="classifieds_title" name="title" value="<?php echo $tmp_widget_title; ?>" /></label>
				<label for="number" style="line-height:35px;display:block;"><?php _e('Number of Ads:', 'widgets'); ?> <select id="classifieds_number" name="number">
                <option value="1" <?php if ($options['number'] == '1'){ echo 'selected="selected"'; } ?>><?php _e('1'); ?></option>
                <option value="2" <?php if ($options['number'] == '2'){ echo 'selected="selected"'; } ?>><?php _e('2'); ?></option>
                <option value="3" <?php if ($options['number'] == '3'){ echo 'selected="selected"'; } ?>><?php _e('3'); ?></option>
                <option value="4" <?php if ($options['number'] == '4'){ echo 'selected="selected"'; } ?>><?php _e('4'); ?></option>
                <option value="5" <?php if ($options['number'] == '5'){ echo 'selected="selected"'; } ?>><?php _e('5'); ?></option>
                <option value="6" <?php if ($options['number'] == '6'){ echo 'selected="selected"'; } ?>><?php _e('6'); ?></option>
                <option value="7" <?php if ($options['number'] == '7'){ echo 'selected="selected"'; } ?>><?php _e('7'); ?></option>
                <option value="8" <?php if ($options['number'] == '8'){ echo 'selected="selected"'; } ?>><?php _e('8'); ?></option>
                <option value="9" <?php if ($options['number'] == '9'){ echo 'selected="selected"'; } ?>><?php _e('9'); ?></option>
                <option value="10" <?php if ($options['number'] == '10'){ echo 'selected="selected"'; } ?>><?php _e('10'); ?></option>
                </select></label>
				<label for="order_by" style="line-height:35px;display:block;"><?php _e('Order By:', 'widgets'); ?> <select  id="classifieds_order_by" name="order_by">
                <option value="most_recent" <?php if ($options['order_by'] == 'most_recent'){ echo 'selected="selected"'; } ?>><?php _e('Most Recent'); ?></option>
                <option value="random" <?php if ($options['order_by'] == 'random'){ echo 'selected="selected"'; } ?>><?php _e('Random'); ?></option>
                </select></label>

				<input type="hidden" name="classifieds_main_blog_submit" id="classifieds_main_blog_submit" value="1" />
				</div>
	<?php
	}
// This prints the widget
	function widget_classifieds_main_blog($args) {
		global $wpdb, $current_site;
		extract($args);
		$defaults = array('count' => 10, 'username' => 'wordpress');
		$options = (array) get_option('widget_classifieds_main_blog');

		foreach ( $defaults as $key => $value )
			if ( !isset($options[$key]) )
				$options[$key] = $defaults[$key];

		?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $options['title'] . $after_title; ?>
			<?php //echo $before_title . __('Classifieds') . $after_title; ?>
            <?php
			$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_status = 'active'";
			if ($options['order_by'] == 'random'){
			$query .= " ORDER BY RAND()";
			}
			if ($options['order_by'] == 'most_recent'){
			$query .= " ORDER BY ad_ID DESC";
			}
			$query .= " LIMIT " . $options['number'];
			$tmp_ads = $wpdb->get_results( $query, ARRAY_A );
			?>
            <ul style="list-style: none;">
            <?php
			if (count($tmp_ads) > 0){
				foreach ($tmp_ads as $tmp_ad){
					?>
					<li style="margin-left:-20px;">
                        <a style="text-decoration:none; color:#1793AD;" href="http://<?php echo $current_site->domain . $current_site->path . CLASSIFIEDS_PATH ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-40.png"; ?>" width="40px" height="40px" /></a>
					<a style="text-decoration:none; color:#1793AD;" href="http://<?php echo $current_site->domain . $current_site->path . CLASSIFIEDS_PATH ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"> <?php echo $tmp_ad['ad_title']; ?></a>
					</li>
					<?php
				}
			} else {
				?>
				<li><?php _e('Nothing to display...'); ?></li>
				<?php
			}
			?>
            </ul>
		<?php echo $after_widget; ?>
<?php
	}
	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('Classifieds', 'widgets'), 'widget_classifieds_main_blog');
	register_widget_control(array('Classifieds', 'widgets'), 'widget_classifieds_main_blog_control');

}

if ( $wpdb->blogid == 1 ) {
	add_action('widgets_init', 'widget_classifieds_main_blog_init');
}

/*
 * Classifieds - Widget
 * *****************************************************************************
 */

function widget_classifieds_init() {
	global $wpdb;

	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	// This saves options and prints the widget's config form.
	function widget_classifieds_control() {
		global $wpdb;
		$options = $newoptions = get_option('widget_classifieds');
		if ( $_POST['classifieds_main_blog_submit'] ) {
			$newoptions['title'] = strip_tags(stripslashes($_POST['title']));
			$newoptions['user'] = strip_tags(stripslashes($_POST['user']));
			$newoptions['number'] = strip_tags(stripslashes($_POST['number']));
			$newoptions['order_by'] = strip_tags(stripslashes($_POST['order_by']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_classifieds', $options);
		}
	?>
				<div style="text-align:right">
                <?php
                if ($options['title'] == ''){
					$tmp_widget_title = 'Classifieds';
				} else {
					$tmp_widget_title = wp_specialchars($options['title'], true);
				}
				?>
				<label for="title" style="line-height:35px;display:block;"><?php _e('Title:', 'widgets'); ?> <input type="text" id="classifieds_title" name="title" value="<?php echo $tmp_widget_title; ?>" /></label>
                <?php
				$query = "SELECT ID FROM $wpdb->users, $wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id AND meta_key = '" . $wpdb->prefix . "capabilities'";
				$tmp_blog_users = $wpdb->get_results( $query, ARRAY_A );

				if (count($tmp_blog_users) < 2){
				?>
				<label for="order_by" style="line-height:35px;display:block;"><?php _e('User:', 'widgets'); ?> <select disabled="disabled" id="classifieds_user" name="user">
                <?php
				} else {
				?>
				<label for="order_by" style="line-height:35px;display:block;"><?php _e('User:', 'widgets'); ?> <select  id="user" name="user">
                <?php
				}
				$query = "SELECT ID FROM $wpdb->users, $wpdb->usermeta WHERE $wpdb->users.ID = $wpdb->usermeta.user_id AND meta_key = '" . $wpdb->prefix . "capabilities'";
				$tmp_blog_users = $wpdb->get_results( $query, ARRAY_A );
				if (count($tmp_blog_users) > 0){
					foreach ($tmp_blog_users as $tmp_blog_user){
						$tmp_classifieds_user = $tmp_blog_user['ID'];
						?>
		                <option value="<?php echo $tmp_blog_user['ID']; ?>" <?php if ($options['user'] == $tmp_blog_user['ID']){ echo 'selected="selected"'; } ?>><?php echo classifieds_get_user_login($tmp_blog_user['ID']); ?></option>
						<?php
					}
				}
				if (count($tmp_blog_users) < 2){
				?>
                <input type="hidden" name="user" value="<?php echo $tmp_classifieds_user; ?>" />
                <?php
				}
				?>
                </select></label>
				<label for="number" style="line-height:35px;display:block;"><?php _e('Number of Ads:', 'widgets'); ?> <select id="classifieds_number" name="number">
                <option value="1" <?php if ($options['number'] == '1'){ echo 'selected="selected"'; } ?>><?php _e('1'); ?></option>
                <option value="2" <?php if ($options['number'] == '2'){ echo 'selected="selected"'; } ?>><?php _e('2'); ?></option>
                <option value="3" <?php if ($options['number'] == '3'){ echo 'selected="selected"'; } ?>><?php _e('3'); ?></option>
                <option value="4" <?php if ($options['number'] == '4'){ echo 'selected="selected"'; } ?>><?php _e('4'); ?></option>
                <option value="5" <?php if ($options['number'] == '5'){ echo 'selected="selected"'; } ?>><?php _e('5'); ?></option>
                <option value="6" <?php if ($options['number'] == '6'){ echo 'selected="selected"'; } ?>><?php _e('6'); ?></option>
                <option value="7" <?php if ($options['number'] == '7'){ echo 'selected="selected"'; } ?>><?php _e('7'); ?></option>
                <option value="8" <?php if ($options['number'] == '8'){ echo 'selected="selected"'; } ?>><?php _e('8'); ?></option>
                <option value="9" <?php if ($options['number'] == '9'){ echo 'selected="selected"'; } ?>><?php _e('9'); ?></option>
                <option value="10" <?php if ($options['number'] == '10'){ echo 'selected="selected"'; } ?>><?php _e('10'); ?></option>
                </select></label>
				<label for="order_by" style="line-height:35px;display:block;"><?php _e('Order By:', 'widgets'); ?> <select  id="classifieds_order_by" name="order_by">
                <option value="most_recent" <?php if ($options['order_by'] == 'most_recent'){ echo 'selected="selected"'; } ?>><?php _e('Most Recent'); ?></option>
                <option value="random" <?php if ($options['order_by'] == 'random'){ echo 'selected="selected"'; } ?>><?php _e('Random'); ?></option>
                </select></label>

				<input type="hidden" name="classifieds_main_blog_submit" id="classifieds_main_blog_submit" value="1" />
				</div>
	<?php
	}
// This prints the widget
	function widget_classifieds($args) {
		global $wpdb, $current_site;
		extract($args);
		$defaults = array('count' => 10, 'username' => 'wordpress');
		$options = (array) get_option('widget_classifieds');

		foreach ( $defaults as $key => $value )
			if ( !isset($options[$key]) )
				$options[$key] = $defaults[$key];

		?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $options['title'] . $after_title; ?>
			<?php //echo $before_title . __('Classifieds') . $after_title; ?>
            <?php
			$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_status = 'active' AND ad_user_ID = '" . $options['user'] . "'";
			if ($options['order_by'] == 'random'){
			$query .= " ORDER BY RAND()";
			}
			if ($options['order_by'] == 'most_recent'){
			$query .= " ORDER BY ad_ID DESC";
			}
			$query .= " LIMIT " . $options['number'];
			$tmp_ads = $wpdb->get_results( $query, ARRAY_A );
			?>
            <ul style="list-style: none;">
            <?php
			if (count($tmp_ads) > 0){
				foreach ($tmp_ads as $tmp_ad){
					?>
					<li>
                        <a style="text-decoration:none; color:#1793AD;" href="http://<?php echo $current_site->domain . $current_site->path . CLASSIFIEDS_PATH ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-20.png"; ?>" width="20px" height="16px" /></a>
                        <a style="text-decoration:none; color:#1793AD;" href="http://<?php echo $current_site->domain . $current_site->path . CLASSIFIEDS_PATH ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><strong><?php echo $tmp_ad['ad_title']; ?></strong></a>
					</li>
					<?php
				}
			} else {
				?>
				<li><?php _e('Nothing to display...'); ?></li>
				<?php
			}
			?>
            </ul>
            <?php
			echo '<br />';
			?>
		<?php echo $after_widget; ?>
<?php
	}
	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget(array('Classifieds', 'widgets'), 'widget_classifieds');
	register_widget_control(array('Classifieds', 'widgets'), 'widget_classifieds_control');

}

if ($wpdb->blogid != 1){
	add_action('widgets_init', 'widget_classifieds_init');
}

/**
 * Classifieds: Categories Widget
 * *****************************************************************************
 */
class ClassifiedsCategoriesWidget extends WP_Widget {

    function ClassifiedsCategoriesWidget() {
        parent::WP_Widget( false, $name = 'Classifieds: Categories Widget' );
    }

    function widget( $args, $instance ) {
        global $wpdb;

        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;

        $this->categories();

        echo $after_widget;

    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
    }

    function categories() {
            $tmp_base_url = get_option('siteurl') . '/classifieds/';
            classifieds_frontend_list_categories( $tmp_current_cat, $tmp_base_url );
    }
}

/*
 * Register Classifieds Search Widget
 */
add_action('widgets_init', create_function('', 'return register_widget("ClassifiedsCategoriesWidget");'));

/**
 * Classifieds: Search Widget
 * *****************************************************************************
 */
class ClassifiedsSearchWidget extends WP_Widget {

    function ClassifiedsSearchWidget() {
        parent::WP_Widget( false, $name = 'Classifieds: Search Widget' );
    }

    function widget( $args, $instance ) {
        global $wpdb;

        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;

        $this->search();

        echo $after_widget;

    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
    }

    function search() {
            $tmp_base_url = get_option('siteurl') . '/classifieds/';
            classifieds_frontend_search_form( $tmp_base_url );
    }
}

/*
 * Register Classifieds Search Widget
 */
add_action('widgets_init', create_function('', 'return register_widget("ClassifiedsSearchWidget");'));

?>
