<?php
/**
 * Class
 */
class WP_EVS_Widget_StandardVideo extends WP_Widget {
	function WP_EVS_Widget_StandardVideo() {
		$widget_ops = array('classname' => 'example', 'description' => 'Select an EasyVideoSuite to show in your website!');

		/* Widget control settings. */
		$control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'wp-evs-widget-standard-video');

		/* Create the widget. */
		$this->WP_Widget('wp-evs-widget-standard-video', 'EasyVideoSuite Video', $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$javascript_code = $instance['javascript_code'];
		$iframe_code = $instance['iframe_code'];
		$object_code = $instance['object_code'];
		?>
			<?php echo $before_widget; ?>
					<?php if($title) echo $before_title . $title . $after_title; ?>
					<?php echo $javascript_code; ?>
			<?php echo $after_widget; ?>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$evs_location = get_option('evs_location');
		$evs_username = get_option('evs_username');
		$evs_password = sha1(get_option('evs_password'));
		$evs_api = $evs_location.'/api.php';
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['file_id'] = strip_tags($new_instance['file_id']);
		
		$file_id = $new_instance['file_id'];
		$profile = '';
		$parts = explode('::', $file_id, 2);
		$profile = $parts[1];
		$file_id = $parts[0];
		
	//if($instance['file_id'] !== $new_instance['file_id']) {
			$api_response = wp_remote_get($evs_api.'?'.http_build_query(array(
				'username' => $evs_username,
				'password' => $evs_password,
				'method' => 'files-embedcode',
				'file_id' => $file_id,
				'profile' => $profile
			)));
			
			$embed_code = json_decode($api_response['body']);
			
			/*
			$instance['javascript_code'] = $embed_code->javascript_code;
			$instance['iframe_code'] = $embed_code->iframe_code;
			$instance['object_code'] = $embed_code->object_code;
			*/
			$instance['javascript_code'] = $embed_code->embed_code;
	//}
		
		return $instance;
	}
	
	function wp_evs_list_select($folder, $selected_file = '') {
		echo '<optgroup label="'.$folder->name.'">';
		if($folder->id !== 0 && !empty($folder->subfolders)) {
			foreach($folder->subfolders as $sf) {
				$this->wp_evs_list_select($sf, $selected_file);
			}
		}
		if(!empty($folder->files)) {
			foreach($folder->files as $file) {
				$display_file_id = $file->id;
				if($display_file_id == $selected_file) {
					echo '<option value="'.$display_file_id.'" selected="selected">'.$file->file_name.'</option>';
				} else {
					echo '<option value="'.$display_file_id.'">'.$file->file_name.'</option>';
				}
				if(!empty($file->profiles)) {
					foreach($file->profiles as $prof) {
						if($prof->id == $selected_file) {
							echo '<option value="'.$prof->id.'" selected="selected">&nbsp;&raquo;&nbsp;'.$prof->name.'</option>';
						} else {
							echo '<option value="'.$prof->id.'">&nbsp;&raquo;&nbsp;'.$prof->name.'</option>';
						}
					}
				}
			}
		} else {
			echo '<option disabled="disabled">No videos in this folder.</option>';
		}
		echo '</optgroup>';
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
		$selected_file = $instance['file_id'];
		
		$evs_location = get_option('evs_location');
		$evs_username = get_option('evs_username');
		$evs_password = sha1(get_option('evs_password'));
		$evs_api = $evs_location.'/api.php';
		
		$ui_title_id = $this->get_field_id('title');
		$ui_title_name = $this->get_field_name('title');
		$ui_file_id_id = $this->get_field_id('file_id');
		$ui_file_id_name = $this->get_field_name('file_id');
		
		$api_response = wp_remote_get($evs_api.'?'.http_build_query(array(
			'username' => $evs_username,
			'password' => $evs_password,
			'method' => 'files-list-complete',
			'getProfiles' => true
		)));
		
		$files_list = json_decode($api_response['body']);
		
		if(empty($files_list)) {
			//
		} else {
		?>
		<p>
		 <label for="<?php echo $ui_title_id; ?>"><?php _e('Title:'); ?></label> 
		 <input class="widefat" id="<?php echo $ui_title_id; ?>" name="<?php echo $ui_title_name; ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		 <label for="<?php echo $ui_file_id_id; ?>" style="margin-right:5px;"><?php _e('File:'); ?></label>
		 <select name="<?php echo $ui_file_id_name; ?>" id="<?php echo $ui_file_id_id; ?>" style="width:270px;">
			<?php
			if(!empty($files_list->folders)) {
				foreach($files_list->folders as $folder) {
					$this->wp_evs_list_select($folder, $selected_file);
				}
			}
			?>
		 </select>
		</p>
		<?php
		}
	}
}

add_action('widgets_init', create_function('', 'return register_widget("wp_evs_Widget_StandardVideo");'));
?>