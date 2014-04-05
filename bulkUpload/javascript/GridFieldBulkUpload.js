(function($) {	
	$.entwine('ss', function($) {
				
		// ============================================================================================
		// start SS namespace overrides
		// ============================================================================================
						
		/*
		 * open/close edit form		
		 */
		$('div.ss-upload .ss-uploadfield-item-edit, div.ss-upload .ss-uploadfield-item-name').entwine({
			onclick: function(e)
			{
				this.closest('.ss-uploadfield-item').find('.ss-uploadfield-item-editform').toggleEditForm();
			}
		});
		
		/*
		 * edit all button
		 * @TODO fix		
		 */
		$('div.ss-upload .fileOverview .ss-uploadfield-item-edit-all').entwine({
			onmatch: function()
			{
				if( !$(this).hasClass('opened') ){					
					$(this).addClass('opened');
				}        
			},
			onunmatch: function(){},
			onclick: function(e)
			{
				if( $(this).hasClass('opened') )
				{					
					$('.ss-uploadfield-files .ss-uploadfield-item-editform').hide();					
					$(this).removeClass('opened');
				}
				else{
					$('.ss-uploadfield-files .ss-uploadfield-item-editform').show();					
					$(this).addClass('opened');
				}

				e.preventDefault();
			} 
		});
		
		/*
		 * show/hide edit form
		 * overrides default behaviour		
		 */
		$('div.ss-upload .ss-uploadfield-item-editform').entwine({
			toggleEditForm: function()
			{
				if( this.css('display') == 'none' ) {
					this.show();
				}
				else{
					this.hide();
				}
			}
		});

		/*
		 * prevent submitting of individual edit forms		
		 */
		$('#Form_uploadForm, div.ss-upload .ss-uploadfield-item-editform form').entwine({
			onsubmit: function(e)
			{
				return false;
			}
		});

		/*
		 * initialise disabled state		
		 */
		$('#bulkImageUploadUpdateBtn,#bulkImageUploadUpdateCancelBtn').entwine({
			onmatch: function()
			{
				$(this).addClass('ui-state-disabled ssui-button-disabled');
        $(this).attr('aria-disabled', 'true');
        $(this).attr('disabled', 'true');
			},
			onunmatch: function(){}
		});

		/*
		 * finish/return button		
		 */
		$('#bulkImageUploadFinishBtn').entwine({
			onmatch: function(){},
			onunmatch: function(){},
			onclick: function(e)
			{
        var formsWithUpadtes = $('form.bulkImageUploadUpdateForm.hasUpdate').length,
            confirmed        = true;

				if ( formsWithUpadtes > 0 )
				{
					confirmed = confirm( ss.i18n._t('GridFieldBulkTools.FINISH_CONFIRM') );  				
				}

				if (confirmed)
				{
					$('.cms-container').loadPanel(this.attr('href'), null, {});
				}
			}
		});
		
		// ============================================================================================
		// end SS namespace overrides
		// ============================================================================================
		
		// ============================================================================================
		// start add-on behaviours
		// ============================================================================================
				
		$.entwine('colymba', function($) {

      /**
       * Makes sure the component is at the top :)
       */
      $('.bulkUpload').entwine({
        onmatch: function(){
          var $tr = this.parents('thead').find('tr'),
              $component = this.clone(),
              index = $tr.index(this)
              ;
          if ( index > 1 )
          {
            $component.insertAfter($tr.eq(0));
            this.remove();
          }          
        },
        onunmatch: function(){}
      });

		
      /*
			 * handles individual edit forms changes
			 * updates buttons and visual styles 		
			 */
			$('.bulkImageUploadUpdateForm').entwine({
        onmatch: function(){},
				onunmatch: function(){},
        haschanged: function()
        {
          var itemInfo,
              itemStatus
              ;
					
          itemStatus = $(this).parents('li').find('.ss-uploadfield-item-status');
          itemInfo   = $(this).parents('li').find('.ss-uploadfield-item-info');
					
					if ( !$(this).hasClass('hasUpdate') )
					{
						$(this).addClass('hasUpdate');
					}
					
					$(itemStatus).removeClass('updated').addClass('dirty').html(ss.i18n._t('GridFieldBulkTools.EDIT_CHANGED'));
					if ( $(itemInfo).hasClass('updated') ) $(itemInfo).removeClass('updated');
					if ( !$(itemInfo).hasClass('dirty') ) $(itemInfo).addClass('dirty');

          $('#bulkImageUploadUpdateBtn').removeClass('ui-state-disabled ssui-button-disabled');
          $('#bulkImageUploadUpdateBtn').attr('aria-disabled', 'false');
          $('#bulkImageUploadUpdateBtn').removeAttr('disabled');
        }
      });
     
			/*
			 * catches edit form changes 		
			 */
			$('.bulkImageUploadUpdateForm input.text, .bulkImageUploadUpdateForm input.checkbox, .bulkImageUploadUpdateForm select, .bulkImageUploadUpdateForm textarea').entwine({
				onchange: function()
				{
					this.closest('.bulkImageUploadUpdateForm').haschanged();
				}
			});
    
      /*
			 * catches edit form changes 	
			 * HTMLEditorField hack	
			 */
			//textarea node is being removed from the DOM when the HTMLEditorFieldChanges, not the best but works
      $('.field.htmleditor textarea').entwine({
        onmatch: function(){},
				onunmatch: function()
				{	
          //note sure why querying straight from the texarea doesn't work... maybe because it is already removed from DOM?
          $('input[type="hidden"][name="'+$(this).attr('name')+'"]').parents('.bulkImageUploadUpdateForm').haschanged();
				}
      });
      
			/*
			 * save changes button behaviour
			 * loop through edited forms and submit data
			 */
			$('#bulkImageUploadUpdateBtn:not(.ui-state-disabled)').entwine({
				onmatch: function()
				{
					$(this).data('completedForms', 0);
				},
				onunmatch: function(){},
				onclick: function(e)
				{
					var formsWithUpadtes,
							url,
							data,
							cacheBuster
							;
					
					formsWithUpadtes = $('form.bulkImageUploadUpdateForm.hasUpdate');
					$(this).data('formsToUpdate', $(formsWithUpadtes).length);
					url = $(this).data('url');
					
					if ( $(formsWithUpadtes).length > 0 )
					{
						$(this).addClass('loading');
					}
					
					$(formsWithUpadtes).each(function()
					{
						cacheBuster = new Date().getTime() + '_' + $(this).attr('name');
            if ( url.indexOf('?') !== -1 )
          	{
          		cacheBuster = '&cacheBuster=' + cacheBuster;
          	}
            else{
            	cacheBuster = '?cacheBuster=' + cacheBuster;
          	}
            
            data = $(this).serialize();

						$.ajax({
              url:     url + cacheBuster,
              data:    data,
              type:    "POST",
              context: $(this)
						}).done(function() {				
              var btn        = $('#bulkImageUploadUpdateBtn'),
                  totalForms = parseInt( $(btn).data('formsToUpdate') ),
                  counter    = parseInt( $(btn).data('completedForms') )
									;

							counter = counter + 1;							
							$(btn).data('completedForms', counter);
							
							$(this).removeClass('hasUpdate');		
							$(this).parents('li').find('.ss-uploadfield-item-status').removeClass('dirty').addClass('updated').html(ss.i18n._t('GridFieldBulkTools.EDIT_UPDATED'));														
							$(this).parents('li').find('.ss-uploadfield-item-info').removeClass('dirty').addClass('updated');
							$(this).parents('li').find('.ss-uploadfield-item-editform').css('display', 'none');
							
							$(this).removeClass('hasUpdate');
							
							if ( counter == totalForms )
							{
								$('#bulkImageUploadUpdateBtn').data('completedForms', 0);
								$('#bulkImageUploadUpdateBtn').removeClass('loading');
                $('#bulkImageUploadUpdateBtn').addClass('ui-state-disabled');
							}							
						});

					});

					return false;					
				}
			});
			
      /*
			 * edit forms ovverides 		
			 */
			$('.ss-uploadfield-item-editform').entwine({
        onmatch: function(e)
        {     
          $('#bulkImageUploadUpdateCancelBtn').removeClass('ui-state-disabled ssui-button-disabled');
          $('#bulkImageUploadUpdateCancelBtn').attr('aria-disabled', 'false');
          $('#bulkImageUploadUpdateCancelBtn').removeAttr('disabled');
        },
				onunmatch: function(){}
      });
      
			/*
			 * cancel button behaviour
			 * loop through edit forms and submit for deletion
			 */
			$('#bulkImageUploadUpdateCancelBtn:not(.ui-state-disabled)').entwine({
				onclick: function(e)
				{					
          var url         = $(this).data('url'),
              cacheBuster = new Date().getTime()
							;

          if ( url.indexOf('?') !== -1 )
          {
          	cacheBuster = '&cacheBuster=' + cacheBuster;
        	}
          else{
          	cacheBuster = '?cacheBuster=' + cacheBuster;
        	}
					
					$('form.bulkImageUploadUpdateForm').each(function()
					{
						var data = $(this).serialize();
						
						$.ajax({
              url:     url + cacheBuster,
              data:    data,
              type:    "POST",
              context: $(this)
						}).done(function() { 

							$(this).parents('li.ss-uploadfield-item').empty().remove();
							
							if ( $('li.ss-uploadfield-item').length == 0 )
							{
								$('.ss-uploadfield-editandorganize').css('display', 'none');
								$('#Form_bulkImageUploadForm').removeClass('loading');
                $('#bulkImageUploadUpdateCancelBtn').addClass('ui-state-disabled');
							}

						});
					});

					return false;					
				}				
			});	

		});
		// ============================================================================================
		// end add-on behaviours
		// ============================================================================================		

	});
}(jQuery));