var WPEVSRemoteDialog = {
	init : function() {
		//
	},

	insert : function(evscode, splash, plugin_url) {
		if(evscode.length <= 0) {
			tinyMCEPopup.close();
			return;
		}

		var url = splash || plugin_url+'/../images/placeholder.png?v=1';
		
		/*
		var wrap_text = confirm('Would you like text to wrap around this '+type+'?');
		if(wrap_text == true) {
			evscode = '<div style="float:left;margin:0 10px 10px 0;">'+evscode+'</div>';
		}
		*/
		
		var evscode = base64_encode(evscode);
		
		var width = 400;// (type == 'video' ? 400 : );
		var height = 200;// (type == 'video' ? 200 : 100);
		
		var html = '<img class="wpevs-container" src="'+url+'" style="display: block; width: '+width+'px; height: '+height+'px;" alt="'+evscode+'" />';
		
		// And insert html
		
		if(typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
			try {
				ed.focus();
				if(tinymce.isIE) ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
				ed.execCommand('mceInsertContent', false, html);
			} catch(e){}
		} else {
			edInsertContent(edCanvas, html);
		}
		
		
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(WPEVSRemoteDialog.init, WPEVSRemoteDialog);