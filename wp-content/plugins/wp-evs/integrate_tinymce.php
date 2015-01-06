<?php
// Load the custom TinyMCE buttons
function wp_evs_integrate_tinymce($buttons) {
//array_push($buttons, 'wpevs');
	if(get_option('wp-evs-configured') == true && is_admin()) {
		array_push($buttons, 'wpevs_remote');
	}
	return $buttons;
}
add_filter('mce_buttons', 'wp_evs_integrate_tinymce');

// Load the custom TinyMCE plugin
function wp_evs_integrate_tinymce_plugin($plugins) {
//$plugins['wpevs'] = plugins_url('/wp-evs/scripts/basic_embed.js');
	if(get_option('wp-evs-configured') == true && is_admin()) {
		$plugins['wpevs_remote'] = plugins_url('/wp-evs/scripts/remote_video.js');
	}
	return $plugins;
}
add_filter('mce_external_plugins', 'wp_evs_integrate_tinymce_plugin');

// Break the browser cache of TinyMCE
function wp_evs_integrate_tinymce_version($version) {
	return $version . '-wp-evs';
}
add_filter('tiny_mce_version', 'wp_evs_integrate_tinymce_version');

// Are we an admin and do we need to load things up?
add_action('admin_print_scripts', 'wp_evs_integrate_tinymce_admin');
function wp_evs_integrate_tinymce_admin() {
	//if(in_array(basename($_SERVER['PHP_SELF']), array('post-new.php', 'page-new.php', 'post.php', 'page.php'))) {
	if(is_admin()) {
		wp_enqueue_script('phpjs', plugins_url('/wp-evs/admin/scripts/phpjs.js'));
		$wpevssettings = array(
			'location' => get_option('evs_location'),
			'username' => get_option('evs_username'),
			'password' => sha1(get_option('evs_password'))
		);
		echo '<script type="text/javascript">';
		echo '(function() {';
		echo "window.WPEVSSettings = ".json_encode($wpevssettings);
		echo '})();';
		echo '</script>';
	}
	//}
}
?>