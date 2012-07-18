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
					var form = this.closest('.bulkImageUploadUpdateForm');
					if ( !$(form).hasClass('hasUpdate') ) {
						$(form).addClass('hasUpdate');
					}
					$('a#bulkImageUploadUpdateFinishBtn').addClass('dirty');
				}
			});

			$('#bulkImageUploadUpdateBtn').entwine({
				onmatch: function(){
					$(this).data('completedForms', 0);
				},
				onunmatch: function(){					
				},
				onclick: function(e){
					
					var formsWithUpadtes = $('form.bulkImageUploadUpdateForm.hasUpdate');
					$(this).data('formsToUpdate', $(formsWithUpadtes).length);
					var url = $(this).data('url');
					var cacheBuster = new Date().getTime();
					
					$(formsWithUpadtes).each(function(){
						var data = $(this).serialize();
						$.ajax({
							url: url + '?cacheBuster=' + cacheBuster,
							data: data,
							type: "POST",
							context: $(this)
						}).done(function() { 
							
							var btn = $('a#bulkImageUploadUpdateBtn');
							var totalForms = parseInt( $(btn).data('formsToUpdate') );				
							var counter = parseInt( $(btn).data('completedForms') );							
							counter = counter + 1;							
							$(btn).data('completedForms', counter);
							
							$(this).removeClass('hasUpdate');		
							$(this).parents('li').find('.ss-uploadfield-item-status').html('Updated');							
							$(this).parents('li').find('.ss-uploadfield-item-info').addClass('updated');
							
							var formHolder = $(this).parents('li').find('.ss-uploadfield-item-editform');
							console.log(formHolder);
							try{$(formHolder).toggleEditForm();}catch(e){}
							//$(this).parents('li').find('.ss-uploadfield-item-editform').entwine('ss').toggleEditForm();
							//this.closest('.ss-uploadfield-item').find('.ss-uploadfield-item-editform').toggleEditForm();
							
							$(this).removeClass('hasUpdate');		
							
							if ( counter == totalForms ) {
								$('#bulkImageUploadUpdateFinishBtn').removeClass('dirty');
								if ( $(this).hasClass('doFinish') ) {
									$('#bulkImageUploadUpdateFinishBtn').clcik();
								}
							}
							
						});
					})
					
					//@todo Fix IE, seems to go right through the prevent default and 
					e.preventDefault();
				}
			});
			
			$('#bulkImageUploadUpdateFinishBtn').entwine({
				onclick: function(e){										
					if ( $(this).hasClass('dirty') ) {
						$('#bulkImageUploadUpdateBtn').addClass('doFinish');
						$('#bulkImageUploadUpdateBtn').click();
						e.stopImmediatePropagation();
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
						
					e.stopImmediatePropagation();	
					e.preventDefault();		
				}				
			});	
			
			// 
		
		});
	});

}(jQuery));