<?php

/**
 * Classifieds FRONTEND
 * Handles the overall frontend output of the plugin
 *
 * @package Classifieds
 * @subpackage Frontend
 * @since 1.1.0
 */

function classifieds_frontend_display_ad_information( $tmp_current_ad, $tmp_base_url ) {
	global $wpdb;

	$tmp_ad_user_ID            = $wpdb->get_var("SELECT ad_user_ID            FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_description        = $wpdb->get_var("SELECT ad_description        FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_first_name         = $wpdb->get_var("SELECT ad_first_name         FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_last_name          = $wpdb->get_var("SELECT ad_last_name          FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_primary_category   = $wpdb->get_var("SELECT ad_primary_category   FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_secondary_category = $wpdb->get_var("SELECT ad_secondary_category FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_price              = $wpdb->get_var("SELECT ad_price              FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_currency           = $wpdb->get_var("SELECT ad_currency           FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_phone_number       = $wpdb->get_var("SELECT ad_phone_number       FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
    
	$tmp_ad_phone_number = trim($tmp_ad_phone_number);

	//get primary blog
	$tmp_blog_id     = $wpdb->get_var("SELECT meta_value FROM " . $wpdb->base_prefix . "usermeta WHERE meta_key = 'primary_blog' AND user_id = '" . $tmp_ad_user_ID . "'");
	$tmp_blog_domain = $wpdb->get_var("SELECT domain     FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = '" . $tmp_blog_id . "'");
	$tmp_blog_path   = $wpdb->get_var("SELECT path       FROM " . $wpdb->base_prefix . "blogs WHERE blog_id = '" . $tmp_blog_id . "'");

	$tmp_blog_display = '<a href="http://' . $tmp_blog_domain . $tmp_blog_path . '">' . $tmp_blog_domain . $tmp_blog_path . '</a>';

	if ($tmp_ad_last_name != '')
		$tmp_name = $tmp_ad_first_name . ' ' . $tmp_ad_last_name;
	else
		$tmp_name = $tmp_ad_first_name; ?>

    <center>
        <img class="cf-main-img" src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_current_ad . "-500.png"; ?>" width="500px" height="375px" alt="Classifieds Ad" />
    </center>
    <br />
    
    <div class="cf-ad-table">
        <table>
            <tr class="odd">
                <td class="key cf-title">Information</td>
                <td class="value cf-title"></td>
            </tr>
            <tr class="even">
                <td class="key"><?php _e('Posted By:', 'classifieds'); ?></td>
                <td class="value"><?php echo $tmp_name; ?></td>
            </tr>
            <tr class="odd">
                <?php if ( is_multisite() ): ?>
                <td class="key"><?php _e('Blog:', 'classifieds'); ?></td>
                <td class="value"><?php echo $tmp_blog_display; ?></td>
                <?php else: ?>
                    <?php do_action('classifieds_bp_ad_info', $tmp_ad_user_ID ); ?>
                <?php endif; ?>
            </tr>
            <tr class="even">
                <td class="key"><?php _e('Price:', 'classifieds'); ?></td>
                <td class="value"><?php echo $tmp_ad_price . ' ' . $tmp_ad_currency; ?></td>
            </tr>
            <tr class="odd">
                <td class="key"><?php _e('Description:', 'classifieds'); ?></td>
                <td class="value"><?php echo $tmp_ad_description; ?></td>
            </tr>
            <?php if ( $tmp_ad_phone_number != '' ): ?>
            <tr class="even">
                <td class="key"><?php _e('Phone:', 'classifieds'); ?></td>
                <td class="value"><?php echo $tmp_ad_phone_number; ?></td>
            </tr>
            <?php endif; ?>
        </table>
        <br />
    </div> <?php
}

function classifieds_frontend_display_ad_contact_form( $tmp_current_ad, $tmp_base_url ) {
	global $wpdb;

	$tmp_ad_email_address = $wpdb->get_var("SELECT ad_email_address FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");
	$tmp_ad_title         = $wpdb->get_var("SELECT ad_title FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "' AND ad_status = 'active'");

	if ( $_GET['action'] == 'process' ) {
		if ( $_POST['email_name'] == '' || $_POST['email_email'] == '' || $_POST['email_spam_check'] == '' || $_POST['email_content'] == '') { ?>
    
			<div class="cf-form-error"><?php _e('You must complete all fields!', 'classifieds'); ?></div>
            <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url );
            
		} elseif ($_POST['email_spam_check'] != '13') { ?>
    
			<div class="cf-form-error"><?php _e('Please correctly answer the spam question!', 'classifieds'); ?></div>
            <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url );
            
		} else {
			classifieds_send_email( $tmp_current_ad, $tmp_ad_title, $tmp_ad_email_address, $_POST['email_email'], $_POST['email_name'], $_POST['email_content'] ); ?>
    
			<div class="cf-form-ok"><?php _e('Your email has been sent.', 'classifieds'); ?></div>
            <?php classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url );
		}
	} else {
        classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url );
	}
}

function classifieds_send_email_html_template( $tmp_current_ad, $tmp_base_url ) { ?>
    
    <form action="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_current_ad; ?>&action=process" method="post">
        <table class="cf-ad-table">
            <tr class="odd">
                <td class="key cf-title"><?php _e('Send Email', 'classifieds'); ?></td>
                <td class="value cf-title"><?php _e('Please complete all fields!', 'classifieds'); ?></td>
            </tr>
            <tr class="even">
                <td class="key"><?php _e('Your Name:', 'classifieds'); ?></td>
                <td class="value"><input name="email_name" id="email_name" maxlength="50" value="" type="text"></td>
            </tr>
            <tr class="odd">
                <td class="key"><?php _e('Your Email:', 'classifieds'); ?></td>
                <td class="value"><input name="email_email" id="email_email" maxlength="50" value="" type="text"></td>
            </tr>
            <tr class="even">
                <td class="key">2 + 11 = </td>
                <td class="value"><input name="email_spam_check" id="email_spam_check" maxlength="50" value="" type="text"></td>
            </tr>
            <tr class="odd">
                <td class="key" valign="top"><?php _e('Message:', 'classifieds'); ?></td>
                <td class="value"><textarea name="email_content" id="email_content" rows="10"></textarea></td>
            </tr>
            <tr class="even">
                <td class="key"></td>
                <td class="value"><input name="Submit" value="<?php _e('Submit', 'classifieds'); ?>" id="submit" type="submit"></td>
            </tr>
        </table>
    </form> <?php
}

function classifieds_frontend_search_results_paginated($tmp_search_query,$tmp_per_page,$tmp_current_page,$tmp_base_url){
	global $wpdb;
    
	//cleanup search query
	$tmp_clean_search_query = $tmp_search_query;
	$tmp_clean_search_query = strtolower($tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('and','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('the','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('to','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('but','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('or','',$tmp_clean_search_query);
	$tmp_clean_search_query = str_replace('+','',$tmp_clean_search_query);

	$tmp_clean_search_query = str_replace(' ','%',$tmp_clean_search_query);

	if ( $tmp_current_page == '' )
		$tmp_current_page = 1;
	if ( $tmp_current_page == 1 )
		$tmp_start = 0;
	else {
		$tmp_math = $tmp_current_page - 1;
		$tmp_math = $tmp_per_page * $tmp_math;
		$tmp_start = $tmp_math;
	}

	$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_title LIKE '%" . $tmp_clean_search_query . "%' OR ad_description LIKE '%" . $tmp_clean_search_query . "%') AND ad_status = 'active'";
	$query .= " ORDER BY ad_ID DESC";
	$query .= " LIMIT " . intval( $tmp_start ) . ", " . intval( $tmp_per_page );
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

	if ( count($tmp_ads) > 0 ) {
		classifieds_check_expired( $tmp_ads );
		$tmp_ads = $wpdb->get_results( $query, ARRAY_A );
		classifieds_frontend_display_search_navigation( $tmp_search_query, $tmp_clean_search_query, $tmp_per_page, $tmp_current_page, $tmp_base_url ); ?>

		<table class="cf-ad-table">
            <tr class="odd">
                <td class="key cf-title">Image</td>
                <td class="value cf-title">Title</td>
            </tr>
            <?php $i = 0; foreach ( $tmp_ads as $tmp_ad ): ?>
                <?php $class = ( $i % 2 ) ? 'odd' : 'even'; $i++; ?>
                <tr class="<?php echo $class; ?>">
                    <td class="key cf-cat"><a href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-80.png"; ?>" /></a></td>
                    <td class="value cf-cat"><a href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><?php echo $tmp_ad['ad_title']; ?></a></td>
                </tr>
            <?php endforeach; ?>
		</table> <?php
        
	} else { ?>
        <p><?php _e('Your search - '); ?><?php echo $tmp_search_query; ?><?php _e(' - did not match any classified ads. '); ?></p> <?php
	}
}

function classifieds_frontend_display_ads_paginated( $tmp_cat, $tmp_per_page, $tmp_current_page, $tmp_base_url ) {
	global $wpdb;

	if ( $tmp_current_page == '' )
		$tmp_current_page = 1;
	if ( $tmp_current_page == 1 )
		$tmp_start = 0;
	else {
		$tmp_math = $tmp_current_page - 1;
		$tmp_math = $tmp_per_page * $tmp_math;
		$tmp_start = $tmp_math;
	}
    
	$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $tmp_cat . "' OR ad_secondary_category = '" . $tmp_cat . "') AND ad_status = 'active'";
	$query .= " ORDER BY ad_ID DESC";
	$query .= " LIMIT " . intval( $tmp_start ) . ", " . intval( $tmp_per_page );
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

	if ( count( $tmp_ads ) > 0 ) {
		classifieds_check_expired( $tmp_ads );
		$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

		classifieds_frontend_display_navigation( $tmp_cat, $tmp_per_page, $tmp_current_page, $tmp_base_url ); ?>
		
		<table class="cf-ad-table">
            <tr class="odd">
                <td class="key cf-title">Image</td>
                <td class="value cf-title">Title</td>
            </tr>
            <?php $i = 0; foreach ( $tmp_ads as $tmp_ad ): ?>
                <?php $class = ( $i % 2 ) ? 'odd' : 'even'; $i++; ?>
                <tr class="<?php echo $class; ?>">
                    <td class="key cf-cat"><a href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-80.png"; ?>" /></a></td>
                    <td class="value cf-cat"><a href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><?php echo $tmp_ad['ad_title']; ?></a></td>
                </tr>
            <?php endforeach; ?>
		</table> <?php
        
	} else { ?>
        <p><?php _e('Nothing to display...'); ?></p> <?php
	}
}

function classifieds_frontend_display_ads( $tmp_cat, $tmp_limit, $tmp_sort, $tmp_base_url ) {
	global $wpdb;

	if ( $tmp_cat == '' ) {
		$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire
                  FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_status = 'active'";
		if ($tmp_sort == 'random')
            $query .= " ORDER BY RAND()";
		if ($tmp_sort == 'newest')
            $query .= " ORDER BY ad_ID DESC";
		$query .= " LIMIT " . $tmp_limit;
	} else {
		$query = "SELECT ad_ID, ad_title, ad_description, ad_price, ad_currency, ad_primary_category, ad_secondary_category, ad_expire
                  FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $tmp_cat . "' OR ad_secondary_category = '" . $tmp_cat . "') AND ad_status = 'active'";
		if ( $tmp_sort == 'random' )
            $query .= " ORDER BY RAND()";
		if ( $tmp_sort == 'newest')
            $query .= " ORDER BY ad_ID DESC";
		$query .= " LIMIT " . $tmp_limit;
	}

	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

	if ( count($tmp_ads ) > 0 )
		classifieds_check_expired($tmp_ads);
	$tmp_ads = $wpdb->get_results( $query, ARRAY_A );

	if ( count( $tmp_ads ) > 0 ) { ?>
		<table class="cf-ad-table">
            <tr class="odd">
                <td class="key cf-title">Image</td>
                <td class="value cf-title">Title</td>
            </tr>
            <?php $i = 0; foreach ( $tmp_ads as $tmp_ad ): ?>
                <?php $class = ( $i % 2 ) ? 'odd' : 'even'; $i++; ?>
                <tr class="<?php echo $class; ?>" >
                    <td class="key cf-cat"><a href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><img src="<?php echo get_option('siteurl') . "/wp-content/classifieds-images/" . $tmp_ad['ad_ID'] . "-80.png"; ?>" /></a></td>
                    <td class="value cf-cat"><a href="<?php echo $tmp_base_url; ?>?ad=<?php echo $tmp_ad['ad_ID']; ?>"><?php echo $tmp_ad['ad_title']; ?></a></td>
                </tr>
            <?php endforeach; ?>
		</table> <?php

	} else { ?>
        <p><center>Nothing to Display</center></p> <?php
	}
}

function classifieds_frontend_display_search_navigation( $tmp_search_query, $tmp_clean_search_query, $tmp_per_page, $tmp_current_page, $tmp_base_url ) {
	global $wpdb;

	//get ad count
	$tmp_ad_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_title LIKE '%" . $tmp_clean_search_query . "%' OR ad_description LIKE '%" . $tmp_clean_search_query . "%') AND ad_status = 'active'");
	//generate page
	$tmp_total_pages = classifieds_roundup( $tmp_ad_count / $tmp_per_page, 0 ); ?>

	<table>
        <tr> <?php

            $tmp_showing_low = ( $tmp_current_page * $tmp_per_page ) - ( $tmp_per_page - 1 );
            if ( $tmp_total_pages == $tmp_current_page ) {
                //last page...
                $tmp_showing_high = $tmp_ad_count - (( $tmp_total_pages - 1 ) * $tmp_per_page );
            } else {
                $tmp_showing_high = $tmp_current_page * $tmp_per_page;
            } ?>

            <td class="cf-nav-left">Showing <?php echo $tmp_showing_low; ?> > <?php echo $tmp_showing_high; ?> of <?php echo $tmp_ad_count; ?> Ads</td>
            <td class="cf-nav-right"> <?php

            if ( $tmp_ad_count > $tmp_per_page ) {
                if ( $tmp_current_page == '' || $tmp_current_page == '1' ) {
                    _e('Previous | ');
                } else {
                    $tmp_previus_page = $tmp_current_page - 1; ?>
                    <a href="<?php echo $tmp_base_url; ?>?search=<?php echo urlencode($tmp_search_query); ?>&page=<?php echo $tmp_previus_page ?>"><?php _e('Previous') ?></a> | <?php
                }
                $counter + 0;
                while ( $counter < $tmp_total_pages ) {
                    $counter = $counter + 1;
                    if ( $counter == $tmp_current_page ) {
                        echo $counter . ' | ';
                    } else { ?>
                        <a href="<?php echo $tmp_base_url; ?>?search=<?php echo urlencode($tmp_search_query); ?>&page=<?php echo $counter; ?>"><?php echo $counter; ?></a> | <?php
                    }
                }
                if ( $tmp_current_page == $tmp_total_pages ) {
                    _e('Next');
                } else {
                    if ( $tmp_total_pages == 1 ) {
                        _e('Next');
                    } else {
                        $tmp_next_page = $tmp_current_page + 1; ?>
                        <a href="<?php echo $tmp_base_url; ?>?search=<?php echo urlencode($tmp_search_query); ?>&page=<?php echo $tmp_next_page; ?>"><?php _e('Next') ?></a> <?php
                    }
                }
            } ?>
            </td>
        </tr>
    </table> <?php
}

function classifieds_frontend_display_navigation( $tmp_cat, $tmp_per_page, $tmp_current_page, $tmp_base_url ) {
	global $wpdb;

	//get ad count
	$tmp_ad_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads
                                    WHERE (ad_primary_category = '" . $tmp_cat . "' OR ad_secondary_category = '" . $tmp_cat . "') AND ad_status = 'active'");
	//generate page div
	$tmp_total_pages = classifieds_roundup($tmp_ad_count / $tmp_per_page, 0); ?>

	<table border="0" border="0" cellpadding="2px" cellspacing="2px" width="100%" bgcolor="">
        <tr> <?php

            $tmp_showing_low = ( $tmp_current_page * $tmp_per_page ) - ( $tmp_per_page - 1 );
            if ( $tmp_total_pages == $tmp_current_page )
                $tmp_showing_high = $tmp_ad_count - (( $tmp_total_pages - 1 ) * $tmp_per_page );
            else
                $tmp_showing_high = $tmp_current_page * $tmp_per_page; ?>

            <td class="cf-nav-left">Showing <?php echo $tmp_showing_low; ?> > <?php echo $tmp_showing_high; ?> of <?php echo $tmp_ad_count; ?> Ads</td>
            <td class="cf-nav-right"> <?php

            if ( $tmp_ad_count > $tmp_per_page ) {
                if ($tmp_current_page == '' || $tmp_current_page == '1')
                    _e('Previous | ', 'classifieds');
                else {
                    $tmp_previus_page = $tmp_current_page - 1; ?>
                    <a href="<?php echo $tmp_base_url; ?>?cat=<?php echo $tmp_cat; ?>&page=<?php echo $tmp_previus_page ?>"><?php _e('Previous', 'classifieds') ?></a> | <?php
                }

                $counter + 0;
                while ( $counter < $tmp_total_pages ) {
                    $counter = $counter + 1;
                    if ( $counter == $tmp_current_page )
                        echo $counter . '|';
                    else { ?>
                        <a href="<?php echo $tmp_base_url; ?>?cat=<?php echo $tmp_cat; ?>&page=<?php echo $counter; ?>"><?php echo $counter; ?></a> | <?php
                    }
                }

                if ( $tmp_current_page == $tmp_total_pages )
                    _e('Next', 'classifieds');
                else {
                    if ( $tmp_total_pages == 1 )
                        _e('Next', 'classifieds');
                    else {
                        $tmp_next_page = $tmp_current_page + 1; ?>
                        <a href="<?php echo $tmp_base_url; ?>?cat=<?php echo $tmp_cat; ?>&page=<?php echo $tmp_next_page; ?>"><?php _e('Next') ?></a> <?php
                    }
                }
            } ?>
            </td>
        </tr>
    </table> <?php
}

function classifieds_frontend_search_form() { 
    $cf_path = ( is_multisite() ) ? 'classifieds/' : '/classifieds/'; ?>

    <form method="post" action="<?php echo get_site_option('siteurl') . $cf_path; ?>">
        <input name="search" size="12" maxlength="50" value="" type="text">
        <input value="Go &raquo;" type="submit">
    </form> <?php
}

function classifieds_frontend_list_categories() {
	global $wpdb;
    
	$query = "SELECT category_ID, category_name, category_description 
              FROM " . $wpdb->base_prefix . "classifieds_categories
              ORDER BY category_name ASC";
	$categories = $wpdb->get_results( $query, ARRAY_A ); ?>
    
    <ul>
        <?php
        if ( count( $categories ) > 0) {
            foreach ( $categories as $categories ) {
                $tmp_ad_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->base_prefix . "classifieds_ads WHERE (ad_primary_category = '" . $categories['category_ID'] . "' OR ad_secondary_category = '" . $categories['category_ID'] . "') AND ad_status = 'active'");
                if ( $tmp_ad_count > 0 ) { ?>
                    <li>
                        <?php $cf_path = ( is_multisite() ) ? 'classifieds/' : '/classifieds/'; ?>
                        <a href="<?php echo get_site_option('siteurl') . $cf_path; ?>?cat=<?php echo $categories['category_ID']; ?>" title="<?php echo $categories['category_description']; ?>">
                        <?php echo $categories['category_name']; ?> (<?php echo $tmp_ad_count; ?>)</a>
                    </li><?php
                }
            }
        } else { ?>  
            <p>Nothing to Display</p> <?php
        } ?>
    </ul> <?php
}

function classifieds_frontend_display_ad_title( $the_title ) {
    if ( in_the_loop() ) {
        global $wpdb;
        
        $tmp_current_ad = $_GET['ad'];
        $tmp_current_cat  = $_GET['cat'];
        $tmp_search_query = $_POST['search'];
        $tmp_search_query = urldecode( $tmp_search_query );

        if ( $tmp_search_query != '' ) {
            return $the_title . ' &raquo; Search Results &raquo; ' . $tmp_search_query;
        } else if ( $tmp_current_ad != '' ) {
            $tmp_current_ad_title = $wpdb->get_var("SELECT ad_title FROM " . $wpdb->base_prefix . "classifieds_ads WHERE ad_ID = '" . $tmp_current_ad . "'");
            return $the_title . ' &raquo; ' . $tmp_current_ad_title;
        } else if ( $tmp_current_cat != '') {
            $tmp_current_cat_name = $wpdb->get_var("SELECT category_name FROM " . $wpdb->base_prefix . "classifieds_categories WHERE category_ID = '" . $tmp_current_cat . "'");
            return $the_title . ' &raquo; Category &raquo; ' . $tmp_current_cat_name;
        } else {
            return $the_title;
        }
    } else {
        return $the_title;
    }
}
add_filter( 'the_title', 'classifieds_frontend_display_ad_title' );

?>