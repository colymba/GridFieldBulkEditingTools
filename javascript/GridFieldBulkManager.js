(function($) {
	
	$.entwine('colymba', function($) {
		    
		$('td.col-bulkSelect').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onmouseover: function(){
				//disable default row click behaviour -> avoid navigation to edit form when clicking the checkbox
        $(this).parents('.ss-gridfield-item').find('.edit-link').removeClass('edit-link').addClass('tempDisabledEditLink');
			},
			onmouseout: function(){
				//re-enable default row click behaviour
				$(this).parents('.ss-gridfield-item').find('.tempDisabledEditLink').addClass('edit-link').removeClass('tempDisabledEditLink');
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
		
    $('.toggleSelectAll').entwine({
      onmatch: function(){
			},
			onunmatch: function(){				
			},
      onclick: function(){
        var state = $(this).prop('checked');
        $(this).parents('.ss-gridfield-table').find('td.col-bulkSelect input').each(function(){$(this).prop('checked', state);});
      }
    });
    
		$('select.bulkActionName').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onchange: function(e) {
				var value, btn, icon;
				value = $(this).val();
				btn = $(this).parents('.bulkManagerOptions').find('.doBulkActionButton');
				icon = $(this).parents('.bulkManagerOptions').find('.doBulkActionButton .ui-icon');
				
				switch (value) {
					case 'edit':
						$(btn).removeClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'pencil');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-pencil');
						
						$(btn).attr('href', $(btn).data('url')+'/edit');
						break;
						
					case 'unlink':
						$(btn).removeClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'chain--minus');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-chain--minus');
						$(btn).removeAttr('href');
						break;
						
					case 'delete':
						$(btn).addClass('ss-ui-action-destructive');
						$(btn).attr('data-icon', 'decline');
						$(icon).removeClass('btn-icon-decline btn-icon-pencil').addClass('btn-icon-decline');
						$(btn).removeAttr('href');
						break;
				}
				
			} 
		});
		
		//@TODO prevent button click to call default url request
		$('.doBulkActionButton').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onmouseover: function(){
				var action, ids = [];
				action = $(this).parents('.bulkManagerOptions').find('select.bulkActionName').val();
				if ( action == 'edit' )
				{
					$(this).parents('.ss-gridfield-table').find('td.col-bulkSelect input:checked').each(function(){
						ids.push( parseInt( $(this).attr('name').split('_')[1] ) );
					});
					if(ids.length > 0) $(this).attr('href', $(this).data('url')+'/'+action+'?records[]='+ids.join('&records[]=') );
				}
			},			
			onclick: function(e) {
				var action, url, data = {}, ids = [], cacheBuster;
				action = $(this).parents('.bulkManagerOptions').find('select.bulkActionName').val();
				
				if ( action != 'edit' )
				{				
					url = $(this).data('url');
					cacheBuster = new Date().getTime();
          
					$(this).parents('.ss-gridfield-table').find('td.col-bulkSelect input:checked').each(function(){
						ids.push( parseInt( $(this).attr('name').split('_')[1] ) );
					});				
					data.records = ids;

					if ( url.indexOf('?') !== -1 ) cacheBuster = '&cacheBuster=' + cacheBuster;
					else cacheBuster = '?cacheBuster=' + cacheBuster;

					$.ajax({
						url: url + '/' + action + cacheBuster,
						data: data,
						type: "POST",
						context: $(this)
					}).done(function() {
            $(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
					});
				}
				
			} 
		});

		/* **************************************************************************************
		 * EDITING */
		
		$('.bulkEditingFieldHolder').entwine({
			onmatch: function(){
				var id, name = 'bulkEditingForm';
				id = $(this).attr('id').split('_')[3];
				$(this).wrap('<form name="'+name+'_'+id+'" id="'+name+'_'+id+'" class="'+name+'"/>');
			},
			onunmatch: function(){					
			}
		});
		
		$('.bulkEditingForm input, .bulkEditingForm select, .bulkEditingForm textarea').entwine({
			onchange: function(){
				var form;

				form = this.parents('form.bulkEditingForm');

				if ( !$(form).hasClass('hasUpdate') ) {
					$(form).addClass('hasUpdate');
				}
			}
		});		
		
		$('#bulkEditingUpdateBtn').entwine({
				onmatch: function(){
					$(this).data('completedForms', 0);
				},
				onunmatch: function(){					
				},
				onclick: function(e){
					var formsWithUpadtes, url, data, cacheBuster;
					
					formsWithUpadtes = $('form.bulkEditingForm.hasUpdate');
					$(this).data('formsToUpdate', $(formsWithUpadtes).length);
					url = $(this).data('url');
					
					if ( $(formsWithUpadtes).length > 0 ) $(this).addClass('loading');
														
					$(formsWithUpadtes).each(function(){
						cacheBuster = new Date().getTime() + '_' + $(this).attr('name');
						data = $(this).serialize();
						
						if ( url.indexOf('?') !== -1 ) cacheBuster = '&cacheBuster=' + cacheBuster;
						else cacheBuster = '?cacheBuster=' + cacheBuster;

						$.ajax({
							url: url + '/' + cacheBuster,
							data: data,
							type: "POST",
							context: $(this)
						}).done(function() { 
							
							var btn = $('#bulkEditingUpdateBtn');
							var totalForms = parseInt( $(btn).data('formsToUpdate') );				
							var counter = parseInt( $(btn).data('completedForms') );							
							counter = counter + 1;							
							$(btn).data('completedForms', counter);
							
							$(this).removeClass('hasUpdate');		
														
							if ( counter == totalForms ) {
								$('#bulkEditingUpdateBtn').data('completedForms', 0);
								$('#bulkEditingUpdateBtn').removeClass('loading');
							}
							
						});
					})
					
				}
			});
		
	});
	
}(jQuery));