<?php

/**
 * Add a filter to add the query by author to the $query
 */

add_filter('wpv_filter_query', 'wpv_filter_post_id', 13, 2); // we need to set a higher priority than the limit filter has because we use $query['post__in'] = array('0') on failure
function wpv_filter_post_id($query, $view_settings) {

	global $WP_Views;

	if (isset($view_settings['id_mode'][0])) {
		global $wpdb;
		$id_parameter = '';
		$show_id_array = array();
		$id_shortcode = '';
		$id_ids_list = '';
        
		if ($view_settings['id_mode'][0] == 'by_ids') {
			if (isset($view_settings['post_id_ids_list']) && '' != $view_settings['post_id_ids_list']) {
				$id_ids_list = $view_settings['post_id_ids_list'];
			}
			if ( '' != $id_ids_list){
				$id_ids_list = explode(',', $id_ids_list);
				
				for ( $i = 0; $i < count($id_ids_list); $i++){
					$show_id_array[] = trim( $id_ids_list[$i] );	
				}
			}
			else {
					$show_id_array = null; // if the View shortcode attribute is missing
			}
		}
		
		if ($view_settings['id_mode'][0] == 'by_url') {
			if (isset($view_settings['post_ids_url']) && '' != $view_settings['post_ids_url']) {
				$id_parameter = $view_settings['post_ids_url'];	
			}
			if ('' != $id_parameter) {
				if (isset($_GET[$id_parameter])) {  // if the URL parameter is present
					$ids_to_load = $_GET[$id_parameter]; // get the array of possible authors from the URL parameter
					if ( is_array( $ids_to_load ) ){
						for ( $i = 0; $i < count($ids_to_load ); $i++){
							$show_id_array[] = trim( $ids_to_load[$i] );	
						}
					}
					else{
						$show_id_array[] = $ids_to_load;	
					}
				} else {
					$show_author_array = null; // if the URL parameter is missing
				}
			}
		}
		
		if ($view_settings['id_mode'][0] == 'shortcode') {
			if (isset($view_settings['post_ids_shortcode']) && '' != $view_settings['post_ids_shortcode']) {
				$id_shortcode = $view_settings['post_ids_shortcode'];	
			}
			if ('' != $id_shortcode) {
				$view_attrs = $WP_Views->get_view_shortcodes_attributes();
				if (isset($view_attrs[$id_shortcode])) { // if the defined shortcode attribute is present
					$ids_to_load = explode(',', $view_attrs[$id_shortcode]); // allow for multiple ids
					if ( count( $ids_to_load ) > 0 ){
						for ( $i = 0; $i < count( $ids_to_load ); $i++){
							$show_id_array[] = trim( $ids_to_load[$i] );	
						}
					}
				} else {
					$show_author_array = null; // if the URL parameter is missing
				}
			}
		}
		
        
		
		if (isset($show_id_array)) { // only modify the query if the URL parameter is present and not empty
			if (count($show_id_array) > 0) {
				
				if ( isset($query['post__in']) ) {
					$query['post__in'] = array_merge((array)$query['post__in'], $show_id_array);
				} else {
					$query['post__in'] = $show_id_array;
				}
			} else {
				$query['post__in'] = array('0');
			}
		}
        
		
    }
    
	
	
	
	return $query;
}