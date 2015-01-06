var WPEVSDialog = {
	init : function() {
		this.resize();
	},

	insert : function() {
		var evscode = document.getElementById('content').value;
		if(evscode.length <= 0) {
			tinyMCEPopup.close();
			return;
		}

		var url = '../wp-content/plugins/wp-evs/images/placeholder.png?v=2';
		
		/*
		var wrap_text = confirm('Would you like text to wrap around this video?');
		if(wrap_text == true) {
			evscode = '<div style="float:left;margin:0 10px 10px 0;">'+evscode+'</div>';
		}
		*/
		
		evscode = base64_encode(evscode);
		
		html = '<img class="wpevs-container" src="'+url+'" style="display: block; width: 400px; height: 200px;" alt="'+evscode+'" />';
		
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
	},

	resize : function() {
		/*
		var vp = tinyMCEPopup.dom.getViewPort(window), el;

		el = document.getElementById('content');

		el.style.width  = (vp.w - 20) + 'px';
		el.style.height = (vp.h - 150) + 'px';
		*/
	}
};

tinyMCEPopup.onInit.add(WPEVSDialog.init, WPEVSDialog);