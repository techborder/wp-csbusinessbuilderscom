=== Plugin Name ===
Contributors: easyvideoplayer
Tags: video, evs, easy video suite, easyvideosuite
Requires at least: 2.8
Tested up to: 4.4.1
Stable tag: trunk

Easily embed EasyVideoSuite videos into WordPress posts, even with the visual editor!

== Description ==

Easily embed EasyVideoSuite videos into WordPress posts, even with the visual editor! All you need to do is enter your EasyVideoSuite installation details into the plugin's settings, and you'll be able to point and click to embed videos, split tests and playlists right into your posts without messing with any code.

== Installation ==

1. Upload the `wp-evs` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click on the Settings link, either underneath the plugin in the list, or inside the Settings area
1. Enter the location to your EVS installation - for example, `http://yoursite.evsuite.com/`
1. Enter your username and password
1. Press the 'Test and save' button - if there are no errors, you're ready to proceed to make a new post or page
1. Click the EVS logo when editing or creating a post or page to bring up your list of videos
1. Click on the name of the video to embed it into your post

== Frequently Asked Questions ==

= How do I embed videos into my posts? =

Set up the plugin as explained in the installation instructions, and then click on the button on the post (or page!) editor. The plugin will fetch your list of videos right from your EasyVideoSuite installation and you can click on them to insert them into your post.

To select a Profile instead of a straight video you can click the small 'down' icon on a row and the Profiles created will drop down underneath the video.

= How do I embed playlists into my posts? =

Set up the plugin as explained in the installation instructions, and then click on the button on the post (or page!) editor. The plugin will fetch your list of playlists right from your EasyVideoSuite installation and you can click on them to insert them into your post.

= How do I embed split tests into my posts? =

Set up the plugin as explained in the installation instructions, and then click on the button on the post (or page!) editor. The plugin will fetch your list of split tests right from your EasyVideoSuite installation and you can click on them to insert them into your post.

== Changelog ==

= 1.1.12 =
* Added support for 'popup' embed code, via the 'Enter code manually' option

= 1.1.11 =
* Fixed infinite loop issue

= 1.1.10 =
* Added SSL support

= 1.1.9 =
* Added support for manual iframe embed code
* Fixed potential infinite loop with subfolder listing

= 1.1.8 =
* Fixed incompatibility with some strict mod_security rules, preventing saving settings

= 1.1.7 =
* Same improved distortion-prevention for EVP2 videos

= 1.1.6 =
* Improved support for overriding custom themes' CSS - should prevent video distortion

= 1.1.5 =
* Fixed an distortion issue with certain themes

= 1.1.4 =
* Fixed conflict with SimplePress forum plugin

= 1.1.3 =
* Fixed support for deep nesting of folders
* Added scrollbars to better navigate large lists of files/folders

= 1.1.2 =
* Improved CSS to try and prevent theme conflicts

= 1.1.1 =
* Fixed playlist and split test embedding in new WordPress

= 1.0 =
* Initial release of wp-evs