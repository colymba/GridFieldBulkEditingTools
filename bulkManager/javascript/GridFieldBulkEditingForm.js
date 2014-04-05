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
		
		$('.bulkEditingForm input.text, .bulkEditingForm select, .bulkEditingForm textarea, .bulkEditingForm input.checkbox').entwine({
			onchange: function(){
				var $form = this.parents('form.bulkEditingForm');

				$form.removeClass('updated');
				if ( !$form.hasClass('hasUpdate') )
				{
					$form.addClass('hasUpdate');
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
					e.stopImmediatePropagation();

          var $formsWithUpadtes = $('form.bulkEditingForm.hasUpdate'),
              url               = $(this).data('url'),
							data,
							cacheBuster
							;
					
					$(this).data('formsToUpdate', $formsWithUpadtes.length);
					
					if ( $formsWithUpadtes.length > 0 ) $(this).addClass('loading');
														
					$formsWithUpadtes.each(function(){
						cacheBuster = new Date().getTime() + '_' + $(this).attr('name');
						data = $(this).serialize();
						
						if ( url.indexOf('?') !== -1 ) cacheBuster = '&cacheBuster=' + cacheBuster;
						else cacheBuster = '?cacheBuster=' + cacheBuster;

						$.ajax({
							url: url + cacheBuster,
							data: data,
							type: "POST",
							context: $(this)
						}).success(function(data, textStatus, jqXHR) { 							
              var $btn       = $('#bulkEditingUpdateBtn'),
                  totalForms = parseInt( $btn.data('formsToUpdate') ),
                  counter    = parseInt( $btn.data('completedForms') ),
                  title
									;							

							counter = counter + 1;							
							$btn.data('completedForms', counter);
							
							$(this).removeClass('hasUpdate');
							$(this).addClass('updated');

							try{
								data = $.parseJSON( data );
							}catch(er){}

							if ( data.title )
							{
								$(this).find('.ui-accordion-header a').html(data.title);
							}							
							$(this).find('.ui-accordion-header').click();
														
							if ( counter == totalForms )
							{
								$('#bulkEditingUpdateBtn').data('completedForms', 0);
								$('#bulkEditingUpdateBtn').removeClass('loading');
							}							
						});
					})
					
				}
		});

		
	});	
}(jQuery));