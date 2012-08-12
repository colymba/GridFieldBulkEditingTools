(function($) {
	
	$.entwine('colymba', function($) {
		
		$('td.col-bulkSelect').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onmouseover: function(){
				//disable default row click behaviour -> avoid navigation to edit form when clicking the checkbox
				$(this).parents('.ss-gridfield-item').find('.edit-link').addClass('tempDisabledEditLink').removeClass('edit-link').css('display','none');
			},
			onmouseout: function(){
				//re-enable default row click behaviour
				$(this).parents('.ss-gridfield-item').find('.tempDisabledEditLink').addClass('edit-link').removeClass('tempDisabledEditLink').css('display','inline-block');
			},
			onclick: function(e) {
				//check/uncheck checkbox when clicking cell
				var cb = $(e.target).find('input');
				if ( !$(cb).prop('checked') ) $(cb).prop('checked', true);
				else $(cb).prop('checked', false);
			}
		});
		
		$('td.col-bulkSelect input').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onclick: function(e) {
			} 
		});
		
		$('select#bulkActionName').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onchange: function(e) {
				var value, btn, icon;
				value = $(this).val();
				btn = $('#doBulkActionButton');
				icon = $('#doBulkActionButton .ui-icon');
				
				switch (value) {
					case 'edit':
						$(btn).removeClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'pencil');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-pencil');
						break;
						
					case 'unlink':
						$(btn).removeClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'chain--minus');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-chain--minus');
						break;
						
					case 'delete':
						$(btn).addClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'decline');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-decline');
						break;
				}
				
			} 
		});
		
		//@TODO prevent button click to call default url request
		$('#doBulkActionButton').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onclick: function(e) {
				var action, url, data = {}, ids = [], cacheBuster;
				action = $('select#bulkActionName').val();
				url = $(this).data('url');
				cacheBuster = new Date().getTime();
				
				$('.col-bulkSelect input:checked').each(function(){
					ids.push( parseInt( $(this).attr('name').split('_')[1] ) );
				});				
				data.records = ids;
				
				$.ajax({
					url: url + '/' + action + '?cacheBuster=' + cacheBuster,
					data: data,
					type: "POST",
					context: $(this)
				}).done(function() {
					//@TODO refresh GridField
				});
			} 
		});
		
	});
	
}(jQuery));