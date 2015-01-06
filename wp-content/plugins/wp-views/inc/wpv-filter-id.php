<?php

if(is_admin()){
	add_action('init', 'wpv_filter_id_init');
	
	function wpv_filter_id_init() {
        global $pagenow;
        
        if($pagenow == 'post.php' || $pagenow == 'post-new.php'){
            add_action('wpv_add_filter_table_row', 'wpv_add_filter_id_table_row', 1, 1);
            add_filter('wpv_add_filters', 'wpv_add_filter_id', 1, 1);
        }
    }
    
    /**
     * Add a search by id filter
     * This gets added to the popup that shows the available filters.
     *
     */
    
    function wpv_add_filter_id($filters) {
        $filters['post_id'] = array('name' => 'Post ID',
                                    'type' => 'callback',
                                    'callback' => 'wpv_add_id',
									'args' => array());
        return $filters;
    }
	
	/**
      * Get the table row to add to the available filters
    */
    function wpv_add_filter_id_table_row($view_settings) {
		if (isset($view_settings['id_mode'][0])) {
			global $view_settings_table_row;
			$td = wpv_get_table_row_ui_post_id($view_settings_table_row, null, $view_settings);
		
			echo '<tr class="wpv_filter_row wpv_post_type_filter_row" id="wpv_filter_row_' . 
			$view_settings_table_row . '"' . wpv_filter_type_hide_element($view_settings, 'posts') . '>' . $td . '</tr>';
			
			$view_settings_table_row++;
		}
    }
	
	
	/**
      * Get the table info for the ID
      * This is called (via ajax) when we add a post id filter
      * It's also called to display the existing post id filter for editing.
      */

    function wpv_get_table_row_ui_post_id($row, $selected, $view_settings = null) {
	
	if (isset($view_settings['id_mode']) && is_array($view_settings['id_mode'])) {
	    $view_settings['id_mode'] = $view_settings['id_mode'][0];
	}
	if (isset($_POST['id_mode'])) {
	    // coming from the add filter button
	    $defaults = array('id_mode' => $_POST['id_mode']);
	    if (isset($_POST['post_id_ids_list'])) {
		$defaults['post_id_ids_list'] = $_POST['post_id_ids_list'];
	    }
	    if (isset($_POST['post_ids_url'])) {
		$defaults['post_ids_url'] = $_POST['post_ids_url'];
	    }
	    if (isset($_POST['post_ids_shortcode'])) {
		$defaults['post_ids_shortcode'] = $_POST['post_ids_shortcode'];
	    }
	    
	    $view_settings = wp_parse_args($view_settings, $defaults);
	}
	
	ob_start();
	wpv_add_id(array('mode' => 'edit',
			      'view_settings' => $view_settings));
	$data = ob_get_clean();
	
	$td = '<td><img src="' . WPV_URL . '/res/img/delete.png" onclick="on_delete_wpv_filter(\'' . $row . '\')" style="cursor: pointer" />';
	$td .= '<td class="wpv_td_filter">';
	$td .= "<div id=\"wpv-filter-id-show\">\n";
	$td .= wpv_get_filter_id_summary($view_settings);
	$td .= "</div>\n";
	$td .= "<div id=\"wpv-filter-id-edit\" style='background:" . WPV_EDIT_BACKGROUND . ";display:none;'>\n";

	$td .= '<fieldset>';
	$td .= '<legend><strong>' . __('Post ID', 'wpv-views') . ':</strong></legend>';
	$td .= '<div id="wpv-filter-id">' . $data . '</div>';
		$td .= '<div id="wpv-id-info" style="margin-left: 20px;"></div>';
	$td .= '</fieldset>';
	ob_start();
	?>
	    <input class="button-primary" type="button" value="<?php echo __('OK', 'wpv-views'); ?>" 
        	name="<?php echo __('OK', 'wpv-views'); ?>" onclick="wpv_show_filter_id_edit_ok()"/>
	    <input class="button-secondary" type="button" value="<?php echo __('Cancel', 'wpv-views'); ?>" 
        	name="<?php echo __('Cancel', 'wpv-views'); ?>" onclick="wpv_show_filter_id_edit_cancel()"/>
	<?php
	$td .= ob_get_clean();
	$td .= '</div></td>';
	
	return $td;
    }
	
	
	/**
      * Display the summary text
      */

    function wpv_get_filter_id_summary_text($view_settings, $short=false) {
    global $wpdb;
     if (isset($_GET['post'])) {$view_name = get_the_title( $_GET['post']);} else {$view_name = 'view-name';}
	ob_start();
	
	switch ($view_settings['id_mode']) {
	
	    case 'by_ids':
		if (isset($view_settings['post_id_ids_list']) && '' != $view_settings['post_id_ids_list']){
		    $ids_list = $view_settings['post_id_ids_list'];
		} else {
		    $ids_list = '<i>' . __('None set', 'wpv-views') . '</i>';
		}
		echo sprintf(__('Select posts with the listed <strong>IDs</strong>: %s', 'wpv-views'), $ids_list);
		break;
	    case 'by_url':
		if (isset($view_settings['post_ids_url']) && '' != $view_settings['post_ids_url']){
		    $url_ids = $view_settings['post_ids_url'];
		} else {
		    $url_ids = '<i>' . __('None set', 'wpv-views') . '</i>';
		}
		
		echo sprintf(__('Select posts with the IDs determined by the URL parameter <strong>"%s"</strong>', 'wpv-views'), $url_ids);
		echo sprintf(__(' eg. yoursite/page-with-this-view/?<strong>%s</strong>=1', 'wpv-views'), $url_ids);
		break;
	    case 'shortcode':
		if (isset($view_settings['post_ids_shortcode']) && '' != $view_settings['post_ids_shortcode']) {
		    $id_short = $view_settings['post_ids_shortcode'];
		} else {
		    $id_short = 'None';
		}
		echo sprintf(__('Select posts which IDs is set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views'), $id_short);
		echo sprintf(__(' eg. [wpv-view name="%s" <strong>%s</strong>="1"]', 'wpv-views'), $view_name, $id_short);
		
		break;
	}
		
	$data = ob_get_clean();
	
		if ($short) {
			// this happens on the Views table under Filter column
			if (substr($data, -1) == '.') {
				$data = substr($data, 0, -1);
			}
		}
	
	return $data;
	
    }
	
	/**
      * Display the summary
      * This is called in the table row when an id filter is added
      * Is also used on the Views table under Filter column using the wpv-view-get-summary filter
      * Displays the summary text given by wpv_get_filter_id_summary_text()
      */

    function wpv_get_filter_id_summary($view_settings) {
	ob_start();

		echo wpv_get_filter_id_summary_text($view_settings);        
	?>
	<br />
	<input class="button-secondary" type="button" value="<?php echo __('Edit', 'wpv-views'); ?>" name="<?php echo __('Edit', 'wpv-views'); ?>" onclick="wpv_show_filter_id_edit()"/>
	<?php
	
	$data = ob_get_clean();
	
	return $data;
	
    }
	
    /**
    * Add the id filter to the filter popup.
    */

    function wpv_add_id($args) {
	    
	global $wpdb;
	
	$edit = isset($args['mode']) && $args['mode'] == 'edit'; 
	
	$view_settings = isset($args['view_settings']) ? $args['view_settings'] : array();
	
	$defaults = array('id_mode' => 'by_ids',
			  'post_id_ids_list' =>'',
			  'post_ids_url' => 'post_ids',
			  'post_ids_shortcode' => 'ids');
	$view_settings = wp_parse_args($view_settings, $defaults);

	wp_nonce_field('wpv_get_posts_select_nonce', 'wpv_get_posts_select_nonce');

	    ?>

	    <div class="id-div" style="margin-left: 20px;">

	    <ul>
		<?php $radio_name = $edit ? '_wpv_settings[id_mode][]' : 'id_mode[]' ?>
        <?php if ($edit): // only one instance of this filter by view ?>
			<input type="hidden" name="_wpv_settings[post_id]" value="1"/> 
		<?php endif; ?>
		<li>
		    <?php $checked = $view_settings['id_mode'] == 'by_ids' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="by_ids" <?php echo $checked; ?> />
            	&nbsp;<?php _e('One of these IDs ', 'wpv-views'); ?></label>
		    <?php $id_input_ids_name = $edit ? '_wpv_settings[post_id_ids_list]' : 'post_id_ids_list' ?>
		    <input type='text' name="<?php echo $id_input_ids_name; ?>" value="<?php echo $view_settings['post_id_ids_list']; ?>" size="15" />
		</li>
        
        <li>
		    <?php $checked = $view_settings['id_mode'] == 'by_url' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="by_url" <?php echo $checked; ?>>&nbsp;
				<?php _e('Value set by this URL parameter: ', 'wpv-views'); ?></label>
		    <?php $name = $edit ? '_wpv_settings[post_ids_url]' : 'post_ids_url' ?>
		    <input type='text' name="<?php echo $name; ?>" value="<?php echo $view_settings['post_ids_url']; ?>" size="10" />
		    <span class="wpv_id_url_param_missing" style="color:red;"><?php echo __('<- Please enter a value here', 'wpv-views'); ?></span>
		    <span class="wpv_id_url_param_ilegal" style="color:red;"><?php echo __('<- Only lowercase letters, numbers, hyphens and underscores allowed', 'wpv-views'); ?></span>
		    <span class="wpv_id_url_param_forbidden" style="color:red;"></span>
		</li>
        
        <li>
		    <?php $checked = $view_settings['id_mode'] == 'shortcode' ? 'checked="checked"' : ''; ?>
		    <label><input type="radio" name="<?php echo $radio_name; ?>" value="shortcode" <?php echo $checked; ?>>&nbsp;
			<?php _e('Value set by View shortcode attribute: ', 'wpv-views'); ?></label>
		    <?php $name = $edit ? '_wpv_settings[post_ids_shortcode]' : 'post_ids_shortcode' ?>
		    <input type='text' name="<?php echo $name; ?>" value="<?php echo $view_settings['post_ids_shortcode']; ?>" size="10" />
		    <span class="wpv_id_shortcode_param_missing" style="color:red;"><?php echo __('<- Please enter a value here', 'wpv-views'); ?></span>
		    <span class="wpv_id_shortcode_param_ilegal" style="color:red;"><?php echo __('<- Only lowercase letters and numbers allowed', 'wpv-views'); ?></span>
		</li>
        
        
        
       
	    </ul>
	    
	    <div class="wpv_id_helper"></div>
	    
	    </div>
	
	    <?php
	
	
    }
    
    /**
    * Add a filter to show the summary on the Views table under Filter column
    */

    add_filter('wpv-view-get-summary', 'wpv_id_summary_filter', 5, 3);

	function wpv_id_summary_filter($summary, $post_id, $view_settings) {
		if(isset($view_settings['query_type']) && $view_settings['query_type'][0] == 'posts' && isset($view_settings['id_mode'])) {
			$view_settings['id_mode'] = $view_settings['id_mode'][0];
			
			$result = wpv_get_filter_id_summary_text($view_settings, true);
			if ($result != '' && $summary != '') {
				$summary .= '<br />';
			}
			$summary .= $result;
		}
		
		return $summary;
	}
	
	
	
}

