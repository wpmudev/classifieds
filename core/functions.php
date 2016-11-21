<?php

/**
 * Does a Classifieds listing support a given taxonomy
 * @return bool
 */
function cf_supports_taxonomy($taxonomy = '')
{
    global $wp_taxonomies;

    if (empty($taxonomy)) return false;
    return (is_array($wp_taxonomies[$taxonomy]->object_type)) ? in_array('classifieds', $wp_taxonomies[$taxonomy]->object_type) : false;
}

function the_cf_categories_home($echo = true, $atts = null)
{

    extract(shortcode_atts(array(
        'style' => '', //list, grid
        'ccats' => '', //list, grid
    ), $atts));

    //get plugin options
    $options = get_option(CF_OPTIONS_NAME);

    $cat_num = (isset($options['general']['count_cat']) && is_numeric($options['general']['count_cat']) && 0 < $options['general']['count_cat']) ? $options['general']['count_cat'] : 10;
    $sub_cat_num = (isset($options['general']['count_sub_cat']) && is_numeric($options['general']['count_sub_cat']) && 0 < $options['general']['count_sub_cat']) ? $options['general']['count_sub_cat'] : 5;
    $hide_empty_sub_cat = (isset($options['general']['hide_empty_sub_cat']) && is_numeric($options['general']['hide_empty_sub_cat']) && 0 < $options['general']['hide_empty_sub_cat']) ? $options['general']['hide_empty_sub_cat'] : 0;

    $taxonomies = array_values(get_taxonomies(array('object_type' => array('classifieds'), 'hierarchical' => 1)));

    $args = array(
        //'parent' => 0,
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => 0,
        'hierarchical' => 1,
        'number' => $cat_num,
        'taxonomy' => $taxonomies,
        'pad_counts' => 1
    );

    if (!empty($ccats)) {
        $ccats = array_filter(explode(',', $ccats), 'is_numeric');
        asort($ccats);
        $ccats = implode(',', $ccats);
        $args['include'] = $ccats;
    }

    $categories = get_categories($args);

    $output = '<div id="cf_list_categories" class="cf_list_categories" >' . "\n";
    $output .= "<ul>\n";

    foreach ($categories as $category) {
        if ($category->category_parent != 0) {
            continue;
        }

        $output .= "<li>\n";

        if (isset($options['general']['display_parent_count']) && $options['general']['display_parent_count']) $parent_count = sprintf(' (%d)', $category->count);
        else $parent_count = '';

        $output .= sprintf('<h2><a href="%s" title="%s %s" >%s%s</a></h2>',
            get_term_link($category),
            esc_html__('View all posts in ', CF_TEXT_DOMAIN),
            $category->name,
            $category->name,
            $parent_count);

        $output .= '<div class="term-list">';

        $args = array(
            'show_option_all' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'style' => 'none',
            'show_count' => (!isset($options['general']['display_sub_count']) || $options['general']['display_sub_count'] == 1),
            'hide_empty' => $hide_empty_sub_cat,
            'use_desc_for_title' => 1,
            'child_of' => $category->term_id,
            'feed' => '',
            'feed_type' => '',
            'feed_image' => '',
            'exclude' => '',
            'exclude_tree' => '',
            'hierarchical' => true,
            'title_li' => '',
            'show_option_none' => '', //sprintf('<span class="cf-empty">%s</span>', __('No categories', CF_TEXT_DOMAIN ) ),
            'number' => $sub_cat_num,
            'echo' => 0,
            'depth' => 1,
            'current_category' => 0,
            'pad_counts' => 1,
            'taxonomy' => $category->taxonomy,
            'walker' => null
        );

        if (!empty($ccats)) {
            $args['include'] = $ccats;
        }

        $output .= wp_list_categories($args);

        $output .= "</div><!-- .term-list -->\n";

        $output .= "</li>\n";

    }

    $output .= "</ul>\n";
    $output .= "</div><!-- .cf_list_categories -->\n";

    return $output;
}

/**
 * the_dir_breadcrumbs
 *
 * @access public
 * @return void
 */
function the_cf_breadcrumbs()
{
    global $wp_query;

    $output = '';
    $category = get_queried_object();
    $category_parent_ids = get_ancestors($category->term_id, $category->taxonomy);
    $category_parent_ids = array_reverse($category_parent_ids);

    foreach ($category_parent_ids as $category_parent_id) {
        $category_parent = get_term($category_parent_id, $category->taxonomy);

        $output .= '<a href="' . get_term_link($category_parent) . '" title="' . sprintf(__('View all posts in %s', CF_TEXT_DOMAIN), $category_parent->name) . '" >' . $category_parent->name . '</a> / ';
    }

    $output .= '<a href="' . get_term_link($category) . '" title="' . sprintf(__('View all posts in %s', CF_TEXT_DOMAIN), $category->name) . '" >' . $category->name . '</a>';

    echo $output;
}

/**
 * Retrieve the URL to the author page for the user with the ID provided.
 *
 * @since 2.1.0
 * @uses $wp_rewrite WP_Rewrite
 * @return string The URL to the author's page.
 */
function get_author_classifieds_url($author_id, $author_nicename = '')
{
    global $wp_rewrite, $bp, $blog_id;
    $auth_ID = (int)$author_id;
    $link = $wp_rewrite->get_author_permastruct();

    $classifieds_obj = get_post_type_object('classifieds');

    if (is_object($classifieds_obj)) {
        $slug = $classifieds_obj->has_archive;
        if (!is_string($slug)) $slug = 'classifieds';
    }

    if (isset($bp) && $bp->root_blog_id == $blog_id) {
        $link = trailingslashit(bp_core_get_user_domain($author_id) . $slug);
        $link .= ($author_id == bp_loggedin_user_id()) ? 'my-classifieds' : 'all';
    } else {
        if (empty($link)) {
            $file = home_url('/');
            $link = $file . "?post_type={$slug}&author=" . $auth_ID;
        } else {
            $link = $link . "/{$slug}";
            if ('' == $author_nicename) {
                $user = get_userdata($author_id);
                if (!empty($user->user_nicename))
                    $author_nicename = $user->user_nicename;
            }
            $link = str_replace('%author%', $author_nicename, $link);
            $link = home_url(user_trailingslashit($link));
        }
    }

    /**
     * Filter the URL to the author's page.
     *
     * @since 2.1.0
     *
     * @param string $link The URL to the author's page.
     * @param int $author_id The author's id.
     * @param string $author_nicename The author's nice name.
     */
    $link = apply_filters('author_classifieds_link', $link, $author_id, $author_nicename);

    return $link;
}


function the_author_classifieds_link()
{

    global $authordata;

    if (!is_object($authordata))
        return false;

    $link = sprintf(
        '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
        esc_url(get_author_classifieds_url($authordata->ID, $authordata->user_nicename)),
        esc_attr(sprintf(__('Posts by %s'), get_the_author())),
        get_the_author()
    );
    return $link;
}

/**
 * Modifies the select box to display thre expiration date if available
 */
add_filter('ct_in_shortcode', 'duration_input_fix', 10, 3);
function duration_input_fix($result = '', $atts = array(), $content = null)
{
    global $post;

    extract(shortcode_atts(array(
        'id' => '',
        'property' => 'input',
        'class' => '',
    ), $atts));

    if ($id == '_ct_selectbox_4cf582bd61fa4' && $property == 'input') {
        if (!empty($post->ID)) {
            $expires = get_post_meta($post->ID, '_expiration_date', true);
            $expires_date = (empty($expires)) ? '' : date_i18n(get_option('date_format'), $expires);
            if (empty($expires_date)) {
                $result = preg_replace('#</option>#',
                    __('How long is this Ad open from today?</option>', CF_TEXT_DOMAIN),
                    $result, 1);
            } else {
                $result = preg_replace('#value=""#', 'value="0"', $result, 1); //Give it zero value so it will validate but not change anything.
                $result = preg_replace('#</option>#',
                    sprintf('%s %s</option>', __('Expires on', CF_TEXT_DOMAIN),
                        $expires_date),
                    $result, 1);
            }
        }
    }
    return $result;
}

//function allow_classifieds_filter($allow = false){
//
//  //Whatever logic to decide whether they should have access.
//  if(false ) $allow = true;
//
//  return $allow;
//}
//add_filter('classifieds_full_access', 'allow_classifieds_filter');


//function sort_alpha($query){
//
//  if( is_admin()
//  || !is_archive('directory_listing')
//  ) return;
//
//  $query->set('orderby', 'title');
//  $query->set('order', 'ASC');
//}
//add_action('pre_get_posts', 'sort_alpha');


add_action( 'admin_footer', function() {
        global $post;
        if( ! isset( $post ) ) return;
        if( 'classifieds' != $post->post_type ) return;
        ?>
        <script type="text/javascript">
        jQuery(function($){
                $( '#publish' ).click(function(){
                        $('.cf-error').remove();
                        var val = $('.ct-field.ct-selectbox').val();
                        if( val == '' ){
                                $('.ct-field.ct-selectbox').next('br').next('span').after('<div class="cf-error" style="color:red; font-size: 12px;">Please select a duration</div>');
                                $('html,body').animate({
                                        scrollTop: $(".ct-field.ct-selectbox").offset().top - 50},
                                        'slow');
                                return false;
                        }

                        return true;
                });
        });
        </script>
        <?php
} );