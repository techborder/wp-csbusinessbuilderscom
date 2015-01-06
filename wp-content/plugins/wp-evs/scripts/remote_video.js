(function() {
	tinymce.create('tinymce.plugins.wpevs_remote', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t = this;

			t.editor = ed;
			
			// Set the evs settings
			var evs_location = WPEVSSettings.location;
			var evs_username = WPEVSSettings.username;
			var evs_password = WPEVSSettings.password;

			// Register commands
			ed.addCommand('mceWPEVSRemote', function(ui) {
				ed.windowManager.open({
					file : url + '/remote_video.html?v=10-1',
					width : 800,
					height : 600,
					inline : 1
				}, {
					'plugin_url': url,
					'evs_location': evs_location,
					'evs_username': evs_username,
					'evs_password': evs_password
				});
			});

			ed.addCommand('mceInsertTemplate', t._insertTemplate, t);

			// Register buttons
			ed.addButton('wpevs_remote', {'title': 'Grab a video from your EVS installation!', 'image': url + '/../images/evs.png?v=1', 'cmd': 'mceWPEVSRemote'});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : "WP EVS",
				author : 'WebActix',
				authorurl : 'http://webactix.com',
				infourl : 'http://webactix.com/wp-evs-plugin/',
				version : "2.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wpevs_remote', tinymce.plugins.wpevs_remote);
})();