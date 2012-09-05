(function($) {
	
	$.entwine('ss', function($) {
				
		// start SS overhides
								
		$('div.ss-upload .ss-uploadfield-item-edit, div.ss-upload .ss-uploadfield-item-name').entwine({
			onclick: function(e){
				this.closest('.ss-uploadfield-item').find('.ss-uploadfield-item-editform').toggleEditForm();
			}
		});
		
		$('div.ss-upload .fileOverview .ss-uploadfield-item-edit-all').entwine({
			onmatch: function(){
				if( !$(this).hasClass('opened') ){					
					$(this).addClass('opened');
				}
			},
			onunmatch: function(){
				
			},
			onclick: function(e) {
				if($(this).hasClass('opened')){					
					$('.ss-uploadfield-files .ss-uploadfield-item-editform').hide();					
					$(this).removeClass('opened');
				}else{
					$('.ss-uploadfield-files .ss-uploadfield-item-editform').show();					
					$(this).addClass('opened');
				}
				e.preventDefault();
			} 
		});
		
		$('div.ss-upload .ss-uploadfield-item-editform').entwine({
			toggleEditForm: function() {
				if( this.css('display') == 'none' ) {
					this.show();
				}else{
					this.hide();
				}
			}
		});
		
		// end SS overhides
		// start add-on behaviours
		
		$.entwine('colymba', function($) {		
						
			$('.bulkImageUploadUpdateForm input.text, .bulkImageUploadUpdateForm input.checkbox, .bulkImageUploadUpdateForm select, .bulkImageUploadUpdateForm textarea').entwine({
				onchange: function(){
					var form, itemInfo, itemStatus;
					
					form = this.closest('.bulkImageUploadUpdateForm');
					itemStatus = (this).parents('li').find('.ss-uploadfield-item-status');					
					itemInfo = $(this).parents('li').find('.ss-uploadfield-item-info');
					
					if ( !$(form).hasClass('hasUpdate') ) {
						$(form).addClass('hasUpdate');
					}
					
					$(itemStatus).removeClass('updated').addClass('dirty').html('Changed');
					if ( $(itemInfo).hasClass('updated') ) $(itemInfo).removeClass('updated');
					if ( !$(itemInfo).hasClass('dirty') ) $(itemInfo).addClass('dirty');
					
					$('#bulkImageUploadUpdateFinishBtn').addClass('dirty');
				}
			});

			$('#bulkImageUploadUpdateBtn').entwine({
				onmatch: function(){
					$(this).data('completedForms', 0);
				},
				onunmatch: function(){					
				},
				onclick: function(e){		
          
					var formsWithUpadtes, url, data, cacheBuster;
					
					formsWithUpadtes = $('form.bulkImageUploadUpdateForm.hasUpdate');
					$(this).data('formsToUpdate', $(formsWithUpadtes).length);
					url = $(this).data('url');
					
					if ( $(formsWithUpadtes).length > 0 ) $(this).addClass('loading');
					
					//@TODO execute 'doFinish' even when no form have been changed					
					$(formsWithUpadtes).each(function(){
						cacheBuster = new Date().getTime() + '_' + $(this).attr('name');
						data = $(this).serialize();
						$.ajax({
							url: url + '?cacheBuster=' + cacheBuster,
							data: data,
							type: "POST",
							context: $(this)
						}).done(function() { 					 
							
							var btn = $('#bulkImageUploadUpdateBtn');
							var totalForms = parseInt( $(btn).data('formsToUpdate') );				
							var counter = parseInt( $(btn).data('completedForms') );							
							counter = counter + 1;							
							$(btn).data('completedForms', counter);
							
							$(this).removeClass('hasUpdate');		
							$(this).parents('li').find('.ss-uploadfield-item-status').removeClass('dirty').addClass('updated').html('Updated');							
							$(this).parents('li').find('.ss-uploadfield-item-info').removeClass('dirty').addClass('updated');
							$(this).parents('li').find('.ss-uploadfield-item-editform').css('display', 'none');
							
							$(this).removeClass('hasUpdate');
							
							if ( counter == totalForms ) {
								$('#bulkImageUploadUpdateFinishBtn').removeClass('dirty');
								$('#bulkImageUploadUpdateBtn').data('completedForms', 0);
								$('#bulkImageUploadUpdateBtn').removeClass('loading');
								if ( $('#bulkImageUploadUpdateBtn').hasClass('doFinish') ) {
									//@TODO find a way to pass this as CMS navigation through AJAX
									window.location = $('#bulkImageUploadUpdateFinishBtn').data('return-url');
								}								
							}
							
						});
					})
					
				}
			});
			
			$('#bulkImageUploadUpdateFinishBtn').entwine({
				onclick: function(e){										
					if ( $(this).hasClass('dirty') ) {
						$('#bulkImageUploadUpdateBtn').addClass('doFinish');
						$('#bulkImageUploadUpdateBtn').click();
					}					
				}				
			});	
			
			$('#bulkImageUploadUpdateCancelBtn').entwine({
				onclick: function(e){
					
					var url = $(this).data('url');
					var cacheBuster = new Date().getTime();
					
					$('form.bulkImageUploadUpdateForm').each(function(){
						var data = $(this).serialize();
						
						$.ajax({
							url: url + '?cacheBuster=' + cacheBuster,
							data: data,
							type: "POST",
							context: $(this)
						}).done(function() { 

							$(this).parents('li.ss-uploadfield-item').empty().remove();
							
							if ( $('li.ss-uploadfield-item').length == 0 ) {
								$('.ss-uploadfield-editandorganize').css('display', 'none');
								$('#Form_bulkImageUploadForm').removeClass('loading');
							}

						});
					});	
					
				}				
			});	
			
			// 
		
		});
	});

}(jQuery));