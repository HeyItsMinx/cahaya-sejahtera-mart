/*Dropify Init*/
"use strict";
$(function() {
	/* Basic Init*/
	$('.dropify').dropify({
		  messages: {
			'default': '<span class="sub-text">Upload the image/file</span>',
			'replace': 'Drag and drop or click to replace',
			'remove':  'Remove',
			'error':   'Ooops, something wrong happended.'
		},
		tpl: {
			message:'<div class="dropify-message"><span class="file-icon"></span> <p>{{ default }}</p></div>',
		}
	});
	$('.dropify-1').dropify({
		  messages: {
			'default': 'Upload Photo',
		},
		tpl: {
			message:'<div class="dropify-message"><span class="file-icon"></span> <p>{{ default }}</p></div>',
		}
	});
	$('.dropify-2').dropify({
		  messages: {
			'default': 'Upload Logo',
		},
		tpl: {
			message:'<div class="dropify-message"><span class="file-icon"></span> <p>{{ default }}</p></div>',
		}
	});
});
