<?php
/*
Plugin Name: WP-EVS (EasyVideoSuite WordPress plugin)
Plugin URI: http://easyvideosuite.com/
Description: Plugin to easily embed EasyVideoSuite videos into WordPress posts!
Date: 2015, April, 15
Author: WebActix
Author URI: http://webactix.com
Version: 1.1.12
*/

// Helpers
include('helpers/extract_tags.php');

// Admin area things - including settings and editor integrations
include('admin/admin.php');
include('integrate_tinymce.php');
include('integrate_basic.php');

// Include the widget things
//include('widgets/standard-video/widget.php');

// Set up oEmbed?
$evs_location = get_option('evs_location');
$evs_oembed_support = get_option('evs_oembed_support');
if(!empty($evs_location) && ($evs_oembed_support == 'true' || $evs_oembed_support === true)) {
	wp_oembed_add_provider($evs_location.'/*', $evs_location.'/oembed.php', false);
}

// Add our CSS file
function wp_evs_addcss() {
	wp_register_style('wp-evs-css', plugins_url('/assets/evs.css', __FILE__));
	wp_enqueue_style('wp-evs-css');
}
add_action('wp_enqueue_scripts', 'wp_evs_addcss');

// Now the actual filter to convert
if(!function_exists('wp_evs_filter')) {
	function wp_evs_filter($content) {
		$images = wp_evs_extract_tags($content, 'img', null, true);
		
		$evs_video_responsive = get_option('evs_video_responsive');
		$evs_video_responsive_onlymobile = get_option('evs_video_responsive_onlymobile');
		
		// Are we using SSL?
		$protocol = 'http';
		if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
			$protocol = 'https';
		}
		
		if(!empty($images)) {
			foreach($images as $img) {
				$alt = $img['attributes']['alt'];
				$evscode = '';
				
				if(!empty($alt)) {
					$evscode = @base64_decode($alt);
				}
				
				if(stripos($evscode, '<div id="evs') === false && stripos($evscode, '<div id="evp') === false && stripos($evscode, '<iframe') === false && stripos($evscode, '/console/') === false) { // If it doesn't contain valid evs code, it's a normal image!
					// Do nothing
				} else {
					$evsprefix = '';
					$evssuffix = '';
					/*
					if(stripos($img['full_tag'], 'alignleft')) {
						$evsprefix = '<div style="float:left;margin:0 10px 10px 0;">';
						$evssuffix = '</div>';
					} elseif(stripos($img['full_tag'], 'alignright')) {
						//
					} else */if(stripos($img['full_tag'], 'aligncenter')) {
						$evsprefix = '<center>';
						$evssuffix = '</center>';
					}
					
					// Should we make it responsive on mobile devices?
					if($evs_video_responsive_onlymobile == 1) {
						$evscode = str_replace('"></script>', '&responsive=1&autoResponsive=1&responsiveOnlyMobile=1"></script>', $evscode);
					} elseif($evs_video_responsive == 1) { // No? How about just all the time?
						$evscode = str_replace('"></script>', '&responsive=1&autoResponsive=1"></script>', $evscode);
					}
					
					// Are we using SSL? If so, we should try to make this SSL too
					if($protocol === 'https') {//strpos($evs_location, 'https://') !== false) {
						$evscode = str_replace('http://', 'https://', $evscode);
					}
					
					/*
					// Hack to make it responsive if WPTouch is included?
					if(class_exists('WPtouchPlugin') && function_exists('bnc_wptouch_is_mobile')) {
						if(bnc_wptouch_is_mobile()) { // It's mobile - make it responsive
							$evscode = str_replace('"></script>', '&responsive=1&autoResponsive=1"></script>', $evscode);
						}
					}
					*/
					
					$content = str_replace($img['full_tag'], $evsprefix.$evscode.$evssuffix, $content);
				}
			}
		}
		
		$content = str_replace(array('<!-- WP EVS Video - DO NOT MODIFY -->', '<!-- END WP EVS Video - DO NOT MODIFY -->'), '', $content);
		
		return $content;
	}
}

add_filter('the_content', 'wp_evs_filter', 9999);
add_filter('the_excerpt', 'wp_evs_filter', 9999);

add_filter('the_content', 'wp_evs_filter');
add_filter('the_excerpt', 'wp_evs_filter');
?>
