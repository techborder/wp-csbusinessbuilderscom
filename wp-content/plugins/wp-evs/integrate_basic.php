<?php
// And for non-tinyMCE and general functions
function wp_evs_integrate_basic() {
	?>
	<script type="text/javascript">
	function WPEVSPopup() {
	 var evscode = prompt('Please enter your EasyVideoSuite code here. Lightboxes, split tests and playlists will all work as well!');
	 if(typeof(evscode) == 'string' && evscode.length > 0) {
			var url = '<?php echo plugins_url('/wp-evs/images/placeholder.png?v=1'); ?>';
			
			evscode = evscode.replace('<!--', '').replace('//-->', '');
			
			evscode = base64_encode(evscode);
			
			var html = '<img class="wpevs-container" src="'+url+'" style="display: block; width: 400px; height: 200px;" alt="'+evscode+'" />';
			edInsertContent(edCanvas, html);
	 }
	}
	
	jQuery(document).ready(function() {
		setTimeout(function() {
			jQuery('#ed_toolbar').append('<input type="button" class="ed_button" onclick="WPEVSPopup()" title="Click" value="Embed an evs video" />');
		}, 500);
	});
	</script>
	<?php
}
add_action('edit_form_advanced', 'wp_evs_integrate_basic');
add_action('edit_page_form', 'wp_evs_integrate_basic');
?>