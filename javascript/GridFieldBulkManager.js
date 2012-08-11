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
		
		$('#bulkActionName').entwine({
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
					case 'Edit':
						$(btn).removeClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'pencil');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-pencil');
						break;
						
					case 'UnLink':
						$(btn).removeClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'chain--minus');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-chain--minus');
						break;
						
					case 'Delete':
						$(btn).addClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'decline');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-decline');
						break;
				}
				
			} 
		});
		
		$('#doBulkActionButton').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onclick: function(e) {
			} 
		});
		
	});
	
}(jQuery));