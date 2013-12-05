(function($) {	
	$.entwine('colymba', function($) {	    
		
		
		$('.bulkEditingFieldHolder').entwine({
			onmatch: function(){
				var id, name = 'bulkEditingForm';
				id = $(this).attr('id').split('_')[3];
				$(this).wrap('<form name="'+name+'_'+id+'" id="'+name+'_'+id+'" class="'+name+'"/>');
			},
			onunmatch: function(){					
			}
		});

		$('.bulkEditingForm').entwine({
			onsubmit: function(){
				return false;
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