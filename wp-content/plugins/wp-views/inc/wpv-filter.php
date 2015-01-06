<?php

require WPV_PATH . '/inc/wpv-filter-add-filter.php';
require WPV_PATH . '/inc/wpv-filter-types.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-types-embedded.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-post-types-embedded.php';
require WPV_PATH . '/inc/wpv-filter-post-types.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-taxonomy-embedded.php';
require WPV_PATH . '/inc/wpv-filter-taxonomy.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-order-by-embedded.php';
require WPV_PATH . '/inc/wpv-filter-order-by.php';
require WPV_PATH . '/inc/wpv-filter-taxonomy-order-by.php';
require WPV_PATH . '/inc/wpv-pagination.php';
require WPV_PATH . '/inc/wpv-filter-meta-html.php';
require WPV_PATH . '/inc/wpv-filter-limit.php';
require WPV_PATH_EMBEDDED . '/inc/wpv-filter-limit-embedded.php';


function wpv_filter_interface_select($view_settings, $key, $output_text, $short_code, $allow_multiple = false) {

    if (!isset($view_settings[$key])) {
        $view_settings[$key] = '';
    }
    ?>
    
    <select class="wpv_interface_select" name="_wpv_settings[<?php echo $key; ?>]" output_text="<?php echo $output_text; ?>" short_code="<?php echo $short_code; ?>">
        <option value="none"><?php _e('None', 'wpv-views'); ?></option>
        <?php if($allow_multiple): ?>
            <?php $selected = $view_settings[$key]=='checkboxes' ? ' selected="selected"' : ''; ?>
            <option value="checkboxes" <?php echo $selected ?>><?php _e('Checkboxes', 'wpv-views'); ?></option>
        <?php endif; ?>
        <?php $selected = $view_settings[$key]=='radios' ? ' selected="selected"' : ''; ?>
        <option value="radios" <?php echo $selected ?>><?php _e('Radios', 'wpv-views'); ?></option>
        <?php $selected = $view_settings[$key]=='drop_down' ? ' selected="selected"' : ''; ?>
        <option value="drop_down" <?php echo $selected ?>><?php _e('Drop down list', 'wpv-views'); ?></option>
    </select>
    
    <?php
}


function wpv_filter_add_js() {    
    wp_enqueue_script( 'views-filter-script' , WPV_URL . '/res/js/views_filter.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-search-script' , WPV_URL . '/res/js/views_filter_search.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-parent-script' , WPV_URL . '/res/js/views_filter_parent.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-custom-fields-script' , WPV_URL . '/res/js/views_filter_custom_fields.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-custom-taxonomy-script' , WPV_URL . '/res/js/views_filter_taxonomy.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-add-filter-script' , WPV_URL . '/res/js/views_add_filter.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-admin-pagination-script' , WPV_URL . '/res/js/views_pagination.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-meta-html-script' , WPV_URL . '/res/js/views_filter_meta_html.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-post-relationship-script' , WPV_URL . '/res/js/views_filter_post_relationship.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-author-script' , WPV_URL . '/res/js/views_filter_author.js', array(), WPV_VERSION);
	wp_enqueue_script( 'views-filter-id-script' , WPV_URL . '/res/js/views_filter_id.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-insert-controls-script' , WPV_URL . '/res/js/views_insert_controls.js', array(), WPV_VERSION);
    wp_enqueue_script( 'views-filter-controls-script' , WPV_URL . '/res/js/views_filter_controls.js', array(), WPV_VERSION);
}

if(is_admin()){
        add_action('admin_head', 'wpv_filter_url_check_js');            
}

function wpv_filter_url_check_js() {

	$reserved_list = array(
		'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in',
		'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel',
		'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute',
		'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id',
		'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status',
		'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search',
		'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in',
		'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments',
		'year '
	);
	
	global $wp_post_types;
    	$reserved_post_types = array_keys( $wp_post_types );
    	
    	$wpv_taxes = get_taxonomies();
    	$reserved_taxonomies = array_keys( $wpv_taxes );
    	
    	$wpv_forbidden_parameters = array(
		'wordpress' => $reserved_list,
		'post_type' => $reserved_post_types,
		'taxonomy' => $reserved_taxonomies,
    	);
    	$wpv_forbidden_parameters_error = array(
		'wordpress' => esc_js(__('<-- This word is reserved by WordPress', 'wpv-views')),
		'post_type' => esc_js(__('<-- You can not use this word because there is a post type with this same name', 'wpv-views')),
		'taxonomy' => esc_js(__('<-- You can not use this word because there is a taxonomy with this same name', 'wpv-views')),
    	);
    	
	?>	
    <script type="text/javascript">
		var wpv_forbidden_parameters = <?php echo json_encode($wpv_forbidden_parameters); ?>;
		var wpv_forbidden_parameters_error = <?php echo json_encode($wpv_forbidden_parameters_error); ?>;
	</script>
	<?php
}