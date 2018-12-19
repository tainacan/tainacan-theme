<?php

/** The breadcrump function
 * Display parent and current page
 **/    
function the_breadcrumb() {

	$showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
	$delimiter = '>'; // delimiter between crumbs
	$home = __('Home', 'tainacan-interface'); // text for the 'Home' link
	$showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
	$before = '<span class="current">'; // tag before the current crumb
	$after = '</span>'; // tag after the current crumb

	global $post;
	$homeLink = get_bloginfo('url');

	if (is_home() || is_front_page()) {

		if ($showOnHome == 1) echo '<nav aria-label="breadcrumb" class="d-none d-md-flex mt-3 border-bottom-0 max-large margin-one-column"><a href="' . $homeLink . '">' . $home . '</a></nav>';

	} else {

		echo '<nav aria-label="breadcrumb" class="d-none d-md-flex mt-3 border-bottom-0 margin-one-column"><a href="' . $homeLink . '">' . $home . '</a>&nbsp;' . $delimiter . '&nbsp;';

		if ( is_category() ) {
			$thisCat = get_category(get_query_var('cat'), false);
			if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, '&nbsp;' . $delimiter . '&nbsp;');
			echo $before . __('Archive by category "', 'tainacan-interface') . single_cat_title('', false) . '"' . $after;

		} elseif ( is_search() ) {
			echo $before . __('Search results for "', 'tainacan-interface') . get_search_query() . '"' . $after;

		} elseif ( is_day() ) {
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . '&nbsp;';
			echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . '&nbsp;';
			echo $before . get_the_time('d') . $after;

		} elseif ( is_month() ) {
			echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a>&nbsp;' . $delimiter . '&nbsp;';
			echo $before . get_the_time('F') . $after;

		} elseif ( is_year() ) {
			echo $before . get_the_time('Y') . $after;

		} elseif ( is_single() && !is_attachment() ) {
			if ( get_post_type() != 'post' ) {
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				if(!is_page() && get_post_type() != 'tainacan-collection') {
					echo '<a href="'. get_post_type_archive_link('tainacan-collection') .'">'; _e( 'Collections', 'tainacan-interface' ); echo '</a>&nbsp;' . $delimiter . '&nbsp;';
				}
				echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
				if ($showCurrent == 1) echo '&nbsp;' . $delimiter . '&nbsp;' . $before . get_the_title() . $after;
			} else {
				$cat = get_the_category(); $cat = $cat[0];
				$cats = get_category_parents($cat, TRUE, '&nbsp;' . $delimiter . '&nbsp;');
				if ($showCurrent == 0) $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
				echo $cats;
				if ($showCurrent == 1) echo $before . get_the_title() . $after;
			}

		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
			$post_type = get_post_type_object(get_post_type());
			if(!is_page() && get_post_type() != 'tainacan-collection') {
				echo '<a href="'. get_post_type_archive_link('tainacan-collection') .'">'; _e( 'Collections', 'tainacan-interface' ); echo '</a>&nbsp;' . $delimiter . '&nbsp;';
			}
			echo $before . $post_type->labels->singular_name . $after;

		} elseif ( is_attachment() ) {
			$parent = get_post($post->post_parent);
			$cat = get_the_category($parent->ID); $cat = $cat[0];
			echo get_category_parents($cat, TRUE, '&nbsp;' . $delimiter . '&nbsp;');
			echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
			if ($showCurrent == 1) echo '&nbsp;' . $delimiter . '&nbsp;' . $before . get_the_title() . $after;

		} elseif ( is_page() && !$post->post_parent ) {
			if ($showCurrent == 1) echo $before . get_the_title() . $after;

		} elseif ( is_page() && $post->post_parent ) {
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();
			while ($parent_id) {
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			for ($i = 0; $i < count($breadcrumbs); $i++) {
				echo $breadcrumbs[$i];
				if ($i != count($breadcrumbs)-1) echo '&nbsp;' . $delimiter . '&nbsp;';
			}
			if ($showCurrent == 1) echo '&nbsp;' . $delimiter . '&nbsp;' . $before . get_the_title() . $after;

		} elseif ( is_tag() ) {
			echo $before . __('Posts tagged "', 'tainacan-interface') . single_tag_title('', false) . '"' . $after;

		} elseif ( is_author() ) {
			global $author;
			$userdata = get_userdata($author);
			echo $before . __('Articles posted by ', 'tainacan-interface') . $userdata->display_name . $after;

		} elseif ( is_404() ) {
			echo $before . __('Error 404', 'tainacan-interface') . $after;
		}

		if ( get_query_var('paged') ) {
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo '&nbsp;(';
			echo __('Page', 'tainacan-interface') . '&nbsp;' . get_query_var('paged');
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
		}

		echo '</nav>';

	}
} // end the_breadcrumb()
