<?php

/**
 * Add a filter to add the query by post search to the $query
 *
 */

add_filter('wpv_filter_query', 'wpv_filter_post_search', 10, 2);
function wpv_filter_post_search($query, $view_settings) {
    
    if (isset($view_settings['post_search_value']) && $view_settings['post_search_value'] != '' && isset($view_settings['search_mode']) && $view_settings['search_mode'][0] == 'specific') {
        $query['s'] = $view_settings['post_search_value'];
    }
    if (isset($view_settings['search_mode']) && isset($_GET['wpv_post_search'])) {
        $query['s'] = esc_attr($_GET['wpv_post_search']);
    }
    if (isset($view_settings['post_search_content']) && 'just_title' == $view_settings['post_search_content']) {
	add_filter( 'posts_search', 'wpv_search_by_title_only', 500, 2 );
    }
    
    return $query;
}

/**
 * Add a filter to for taxonomy search
 *
 */

add_filter('wpv_filter_taxonomy_query', 'wpv_filter_taxonomy_search', 10, 2);
function wpv_filter_taxonomy_search($query, $view_settings) {
    
    if (isset($view_settings['taxonomy_search_value']) && $view_settings['taxonomy_search_value'] != '' && isset($view_settings['taxonomy_search_mode']) && $view_settings['taxonomy_search_mode'][0] == 'specific') {
        $query['search'] = $view_settings['taxonomy_search_value'];
    }
    if (isset($view_settings['taxonomy_search_mode']) && isset($_GET['wpv_taxonomy_search'])) {
        $query['search'] = esc_attr($_GET['wpv_taxonomy_search']);
    }
    
    return $query;
}

function wpv_search_by_title_only( $search, &$wp_query )
{
    global $wpdb;
    if ( empty( $search ) )
        return $search; // skip processing - no search term in query
    $q = $wp_query->query_vars;
    $n = ! empty( $q['exact'] ) ? '' : '%';
    $search = '';
    $searchand = '';
    foreach ( (array) $q['search_terms'] as $term ) {
        $term = esc_sql( like_escape( $term ) );
        $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
        $searchand = ' AND ';
    }
    if ( ! empty( $search ) ) {
        $search = " AND ({$search}) ";
        if ( ! is_user_logged_in() )
            $search .= " AND ($wpdb->posts.post_password = '') ";
    }
    return $search;
}

