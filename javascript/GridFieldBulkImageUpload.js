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

		$('#bulkImageUploadUpdateBtn,#bulkImageUploadUpdateCancelBtn').entwine({
			onmatch: function(){
				$(this).addClass('ui-state-disabled ssui-button-disabled');
        $(this).attr('aria-disabled', 'true');
        $(this).attr('disabled', 'true');
			},
			onunmatch: function(){}
		});
		
		// end SS overhides
		// start add-on behaviours
		
		$.entwine('colymba', function($) {		
		
      $('.bulkImageUploadUpdateForm').entwine({
        onmatch: function(){          
        },
				onunmatch: function(){					
				},
        haschanged: function(){
          var itemInfo, itemStatus;
					
					itemStatus = $(this).parents('li').find('.ss-uploadfield-item-status');					
					itemInfo = $(this).parents('li').find('.ss-uploadfield-item-info');
					
					if ( !$(this).hasClass('hasUpdate') ) {
						$(this).addClass('hasUpdate');
					}
					
					$(itemStatus).removeClass('updated').addClass('dirty').html('Changed');
					if ( $(itemInfo).hasClass('updated') ) $(itemInfo).removeClass('updated');
					if ( !$(itemInfo).hasClass('dirty') ) $(itemInfo).addClass('dirty');
					
					$('#bulkImageUploadUpdateFinishBtn').addClass('dirty');

          $('#bulkImageUploadUpdateBtn').removeClass('ui-state-disabled ssui-button-disabled');
          $('#bulkImageUploadUpdateBtn').attr('aria-disabled', 'false');
          $('#bulkImageUploadUpdateBtn').removeAttr('disabled');
        }
      });
     
			$('.bulkImageUploadUpdateForm input.text, .bulkImageUploadUpdateForm input.checkbox, .bulkImageUploadUpdateForm select, .bulkImageUploadUpdateForm textarea').entwine({
				onchange: function(){
					this.closest('.bulkImageUploadUpdateForm').haschanged();
				}
			});
    
      //textarea node is being removed from the DOM when the HTMLEditorFieldChanges, not the best but works
      $('.field.htmleditor textarea').entwine({
        onmatch: function(){          
        },
				onunmatch: function(){	
          //note sure why querying straight from the texarea doesn't work... maybe because it is already removed from DOM?
          $('input[type="hidden"][name="'+$(this).attr('name')+'"]').parents('.bulkImageUploadUpdateForm').haschanged();
				}
      });
      
			$('#bulkImageUploadUpdateBtn:not(.ui-state-disabled)').entwine({
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
            if ( url.indexOf('?') !== -1 ) cacheBuster = '&cacheBuster=' + cacheBuster;
            else cacheBuster = '?cacheBuster=' + cacheBuster;
            
            data = $(this).serialize();

						$.ajax({
							url: url + cacheBuster,
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
                $('#bulkImageUploadUpdateBtn').addClass('ui-state-disabled');
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
			
      $('.ss-uploadfield-item-editform').entwine({
        onmatch: function(e){     
          $('#bulkImageUploadUpdateCancelBtn').removeClass('ui-state-disabled ssui-button-disabled');
          $('#bulkImageUploadUpdateCancelBtn').attr('aria-disabled', 'false');
          $('#bulkImageUploadUpdateCancelBtn').removeAttr('disabled');
        },
				onunmatch: function(){					
				}
      });
      
			$('#bulkImageUploadUpdateCancelBtn:not(.ui-state-disabled)').entwine({
				onclick: function(e){
					
					var url = $(this).data('url');
					var cacheBuster = new Date().getTime();
          if ( url.indexOf('?') !== -1 ) cacheBuster = '&cacheBuster=' + cacheBuster;
          else cacheBuster = '?cacheBuster=' + cacheBuster;
					
					$('form.bulkImageUploadUpdateForm').each(function(){
						var data = $(this).serialize();
						
						$.ajax({
							url: url + cacheBuster,
							data: data,
							type: "POST",
							context: $(this)
						}).done(function() { 

							$(this).parents('li.ss-uploadfield-item').empty().remove();
							
							if ( $('li.ss-uploadfield-item').length == 0 ) {
								$('.ss-uploadfield-editandorganize').css('display', 'none');
								$('#Form_bulkImageUploadForm').removeClass('loading');
                $('#bulkImageUploadUpdateCancelBtn').addClass('ui-state-disabled');
							}

						});
					});	
					
				}				
			});	
			
			// 
		
		});
	});

}(jQuery));