jQuery(document).ready(function(){
	window.tmpl.cache['GridFieldBulkImageUpload_downloadtemplate'] = tmpl(
		'{% for (var i=0, files=o.files, l=files.length, file=files[0]; i<l; file=files[++i]) { %}' +
			'<li class="ss-uploadfield-item template-download{% if (file.error) { %} ui-state-error{% } %}" data-fileid="{%=file.id%}">' + 
				'<div class="ss-uploadfield-item-preview preview"><span>' +
					'<img src="{%=file.thumbnail_url%}" alt="" />' +
				'</span></div>' +
				'<div class="ss-uploadfield-item-info">' +
					'<label class="ss-uploadfield-item-name">' + 
						'<span class="name" title="{%=file.name%}">{%=file.name%}</span> ' + 
						'{% if (!file.error) { %}' +
							'<div class="ss-uploadfield-item-status ui-state-success-text" title="'+ss.i18n._t('UploadField.Uploaded', 'Uploaded')+'">'+ss.i18n._t('UploadField.Uploaded', 'Uploaded')+'</div>' +						
						'{% } else {  %}' +
							'<div class="ss-uploadfield-item-status ui-state-error-text" title="{%=o.options.errorMessages[file.error] || file.error%}">{%=o.options.errorMessages[file.error] || file.error%}</div>' + 
						'{% } %}' + 
						'<div class="clear"><!-- --></div>' + 
					'</label>' +
					'{% if (file.error) { %}' +
						'<div class="ss-uploadfield-item-actions">' + 
							'<div class="ss-uploadfield-item-cancel ss-uploadfield-item-cancelfailed"><button class="icon icon-16">' + ss.i18n._t('UploadField.CANCEL', 'Cancel') + '</button></div>' +
						'</div>' +
					'{% } %}' + 
				'</div>' +				
				'{% if (!file.error) { %}' +
					'<div class="ss-uploadfield-item-editform">'+
						'<form action="update" method="post" class="bulkImageUploadUpdateForm" name="BIUUF_{%=file.record.ID%}">'+
								'<input type="hidden" name="record_{%=file.record.ID%}_ID" value="{%=file.record.ID%}"/>'+
								'<img class="imgPreview" src="{%=file.preview_url%}" />'+

								'{% for (var key in file.record.fields) { %}' +								
									'{%#file.record.fields[key]%}' +								
								'{% } %}' +

						'</form>'+
					'</div>' + 
				'{% } %}' + 				
			'</li>' + 
		'{% } %}'
	);
});