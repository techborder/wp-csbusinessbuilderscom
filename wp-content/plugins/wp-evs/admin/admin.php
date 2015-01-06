<?php
add_action('admin_menu', 'wp_evs_admin_menu');
add_filter('plugin_action_links', 'wp_evs_admin_settingslink', 10, 2);
add_action('admin_init', 'wp_evs_admin_init');

function wp_evs_admin_init() {
	wp_register_script('wp-evs-phpjs', plugins_url('scripts/phpjs.js', __FILE__));
	wp_register_script('wp-evs-spinner', plugins_url('scripts/spinner.js', __FILE__));
}

function wp_evs_admin_styles() {
	wp_enqueue_script('wp-evs-phpjs');
	wp_enqueue_script('wp-evs-spinner');
}

function wp_evs_admin_menu() {
	$page = add_options_page('WP EVS Options', 'EasyVideoSuite', 'manage_options', 'wp-evs', 'wp_evs_admin_options');
	add_action('admin_print_styles-' . $page, 'wp_evs_admin_styles');
}

function wp_evs_admin_settingslink($links, $file) {
	if ($file == 'wp-evs/wp-evs.php'){
		$settings_link = '<a href="options-general.php?page=wp-evs">'.__("Settings", "wp-evs").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

function wp_evs_admin_options() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.'));
	}
	
	// Display the header
	echo '<div class="wrap"><h2>'.__( 'WP-EVS Settings', 'wp-evs' ).'</h2>';

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if(isset($_POST['hidden_submit']) && $_POST['hidden_submit'] == 'Y') {
		// Save the posted value in the database
		update_option('wp-evs-configured', true);
		
		// Reconstruct the location
		$evs_location = 'http://'.implode('.', $_POST['evs_location_frags']);
		
		update_option('evs_location', $evs_location);
		update_option('evs_username', $_POST['evs_username']);
		update_option('evs_password', $_POST['evs_password']);
		update_option('evs_video_responsive', $_POST['evs_video_responsive']);
		update_option('evs_video_responsive_onlymobile', $_POST['evs_video_responsive_onlymobile']);
		update_option('evs_oembed_support', $_POST['evs_oembed_support']);

		// Put an settings updated message on the screen
		echo "<div class=\"updated\"><p><strong>".__('Your settings have been successfully saved! Now go create a post or page!', 'wp-evs' )."</strong></p></div>";
	}
	
	// Read in existing option value from database
	$evs_location = get_option('evs_location');
	$evs_username = get_option('evs_username');
	$evs_password = get_option('evs_password');
	$evs_video_responsive = get_option('evs_video_responsive');
	$evs_video_responsive_onlymobile = get_option('evs_video_responsive_onlymobile');
	$evs_oembed_support = get_option('evs_oembed_support');

	// Now display the settings editing screen
	?>
	
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#wp-evs-admin-test').click(function() {
			var evs_location = $('#wp-evs-admin-form input[name="evs_location"]').val();
			var evs_username = $('#wp-evs-admin-form input[name="evs_username"]').val();
			var evs_password = $('#wp-evs-admin-form input[name="evs_password"]').val();
			
			// Correct the location?
			if(evs_location.substr(0, 7) !== 'http://' && evs_location.substr(0, 8) !== 'https://') evs_location = 'http://'+evs_location;
			if(evs_location.substr(-1) == '/') evs_location = evs_location.substr(0, evs_location.length - 1);
			
			// Reset the location
			$('#wp-evs-admin-form input[name="evs_location"]').val(evs_location);
			
			// Set the API location
			var evs_api = evs_location+'/api.php';
			
			// Spin
			var button = $(this);
			button.spinner({
				'img': '<?php echo plugins_url('wp-evs/images/spinner.gif'); ?>',
				'position': 'center',
				'hide': true
			});
			
			$.ajax({
				'url': evs_api,
				'data': {'responseType': 'jsonp', 'method': 'integrate-details', 'username': evs_username, 'password': sha1(evs_password)},
				'dataType': 'jsonp',
				'timeout': 5000,
				'success': function(response) {
					if(response.success == true) {
						if(typeof response.project_location == 'string') {
							var loc = response.project_location.replace('http://', '').replace('https://', ''), loc_frags = loc.split('.');
							$('#evs_location').prop('name', ''); // remove this field
							$.each(loc_frags, function(idx, frag) {
								$('<input type="hidden" name="evs_location_frags[]" value="'+frag+'" />').appendTo($('#wp-evs-admin-form'));
							});
							$('#evs_oembed_support').val(''+(response.oembed_support || false));
							$('#wp-evs-admin-form').submit();
						} else { // This means it's a 2.x install
							button.spinner('remove');
							alert('This plugin is not compatible with EasyVideoPlayer 2! You must be using EasyVideoSuite to use this!');
						}
					} else {
						button.spinner('remove');
						alert('There has been an error!\r\r'+response.message);
					}
				},
				'error': function() {
					button.spinner('remove');
					alert('It looks like the URL you entered does not point directly to a valid EasyVideoSuite installation! Make sure you have entered the direct URL to EasyVideoSuite!');
				}
			});
		});
		
		$('#wp-evs-admin-form input').keyup(function(event) {
			if(event.which == 13) $('#wp-evs-admin-test').trigger('click');
		});
		
	});
	</script>

	<form name="form1" method="post" action="options-general.php?page=wp-evs" id="wp-evs-admin-form">
	<input type="hidden" name="hidden_submit" value="Y">
	<input type="hidden" name="evs_oembed_support" id="evs_oembed_support" value="">
	<p>You must have already set up your copy of EasyVideoSuite to use this plugin! If you don't have that, you can always use our old WP EVP plugin!</p>
	<p><strong>This plugin will not work if you're not using EasyVideoSuite!</strong> Please make sure you enter a full, proper URL to a valid EasyVideoSuite installation below.</p>
	
	<div style="height:1px;background:#aaa;margin:0;padding:0;"></div>
	
	<table class="form-table">
		<tbody>
			<tr align="top">
				<th scope="row">
					<label for="evs_location"><?php _e("EasyVideoSuite location:", 'wp-evs' ); ?></label>
				</th>
				<td>
					<input type="text" name="evs_location" id="evs_location" value="<?php echo $evs_location; ?>" size="20" class="regular-text code" />
					<span class="description">Example: http://www.mysite.com/evs/</span>
				</td>
			</tr>
			<tr align="top">
				<th scope="row">
					<label for="evs_location"><?php _e("EasyVideoSuite username:", 'wp-evs' ); ?></label>
				</th>
				<td>
					<input type="text" name="evs_username" id="evs_username" value="<?php echo $evs_username; ?>" size="20" class="regular-text code" />
				</td>
			</tr>
			<tr align="top">
				<th scope="row">
					<label for="evs_location"><?php _e("EasyVideoSuite password:", 'wp-evs' ); ?></label>
				</th>
				<td>
					<input type="password" name="evs_password" id="evs_password" value="<?php echo $evs_password; ?>" size="20" class="regular-text code" />
				</td>
			</tr>
		</tbody>
	</table>
	
	<div style="height:1px;background:#aaa;margin:20px 0 0 0;padding:0;"></div>
	
	<table class="form-table">
		<tbody>
			<tr align="top">
				<th scope="row">
					<label for="evs_video_responsive"><?php _e("Use responsive videos?", 'wp-evs' ); ?></label>
				</th>
				<td>
					<input type="checkbox" value="1" name="evs_video_responsive" id="evs_video_responsive" style="margin: 0 10px 0 0;" <?php echo ($evs_video_responsive == 1 ? 'checked="checked"' : ''); ?> />
					<span class="description">Tick this if you want video size to adjust depending on the device your viewer is using</span>
				</td>
			</tr>
			<tr align="top">
				<th scope="row">
					<label for="evs_video_responsive"><?php _e("Responsve <strong>only</strong> on mobiles?", 'wp-evs' ); ?></label>
				</th>
				<td>
					<input type="checkbox" value="1" name="evs_video_responsive_onlymobile" id="evs_video_responsive_onlymobile" style="margin: 0 10px 0 0;" <?php echo ($evs_video_responsive_onlymobile == 1 ? 'checked="checked"' : ''); ?> />
					<span class="description">Tick this if you only want videos responsive on mobile devices and not desktops</span>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php if($evs_oembed_support == 'true' || $evs_oembed_support === true) : ?>
	<div style="height:1px;background:#aaa;margin:20px 0 20px 0;padding:0;"></div>
	<div style="background:#EAF5DA;border:1px solid #A6B58D;padding:0 10px;"><p>Your installation of EasyVideoSuite supports oEmbed! You can simply paste the URL to the video page that EVS automatically builds for you, and it will be transformed into your video embed code!</p></div>
	<?php endif; ?>
	
	<p class="submit">
		<input type="button" id="wp-evs-admin-test" class="button-primary" value="<?php esc_attr_e('Test &amp; save') ?>" />
	</p>
	
	</form>
	</div>

<?php
}
?>