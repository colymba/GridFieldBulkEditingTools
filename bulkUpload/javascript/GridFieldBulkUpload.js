(function($) {	
	$.entwine('ss', function($) {
				
		// start SS namespace overrides

		
		// end SS namespace overrides
		

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


      /**
       * Track upload progress...
       */      
      $('ul.ss-uploadfield-files').entwine({
        onmatch: function(){},
        onunmatch: function(){},
        trackProgress: function()
        {
          var $li = this.find('li.ss-uploadfield-item'),
              total = $li.length,
              $done = $li.filter('.done'),
              done = $done.length,
              $errors = $li.not($done).find('.ui-state-warning-text,.ui-state-error-text'),
              errors = $errors.length
              ;
          
          this.parents('.ss-uploadfield').find('.colymba-bulkupload-buttons').refresh(total, done, errors);
          /*
          this.closest('.colymba-bulkupload-info').html(ss.i18n.sprintf(
            ss.i18n._t('GRIDFIELD_BULK_UPLOAD.PROGRESS_INFO'),
            total,
            done,
            total
          ));*/
        }
      });


      /**
       * Track new and canceled updloads
       */
      $('li.ss-uploadfield-item').entwine({
        onmatch: function(){
          this.parents('ul.ss-uploadfield-files').trackProgress();
        },
        onunmatch: function(){
          $('ul.ss-uploadfield-files').trackProgress();
        },
      });

      /**
       * Track updload warning/errors
       */
      $('li.ss-uploadfield-item .ui-state-warning-text,li.ss-uploadfield-item .ui-state-error-text').entwine({
        onmatch: function(){
          this.parents('ul.ss-uploadfield-files').trackProgress();
        },
        onunmatch: function(){
          $('ul.ss-uploadfield-files').trackProgress();
        },
      });


      /**
       * Track completed uploads
       *//*
      $('li.ss-uploadfield-item.done').entwine({
        onmatch: function(){
          this.parents('ul.ss-uploadfield-files').trackProgress();
        },
        onunmatch: function(){},
      });*/


      /**
       * Update buttons state and progress info...
       */      
      $('.colymba-bulkupload-buttons').entwine({
        onmatch: function(){},
        onunmatch: function(){},
        refresh: function(total, done, error)
        {
          var $info          = this.find('.colymba-bulkupload-info'),
              $editBtn       = this.find('.bulkUploadEditButton'),
              $cancelBtn     = this.find('.bulkUploadCancelButton'),
              $finishBtn     = this.find('.bulkUploadFinishButton'),
              $clearErrorBtn = this.find('.bulkUploadClearErrorButton')
              ;

          if ( total > 0 )
          {
            this.css({display: 'block'});

            $info.html(ss.i18n.sprintf(
              ss.i18n._t('GRIDFIELD_BULK_UPLOAD.PROGRESS_INFO'),
              total,
              done,
              error
            ));

            $cancelBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');
            $finishBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');

            if ( total === done )
            {
              $editBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');
            }
            else{
              $editBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            }

            if ( error > 0 )
            {
              $clearErrorBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');
            }
            else{
              $clearErrorBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            }
          }
          else{
            this.css({display: 'none'});
            $editBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $cancelBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $finishBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $clearErrorBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
          }       
        }
      });


      $('.bulkUploadClearErrorButton').entwine({
        onmatch: function(){
          this.removeClass('action');
        },
        onunmatch: function(){},
        onclick: function(e)
        {
          var $bulkUpload = this.parents('.bulkUpload'),
              $errors = $bulkUpload.find('li.ss-uploadfield-item .ui-state-warning-text,li.ss-uploadfield-item .ui-state-error-text').parents('li')
              ;

          $($errors.get().reverse()).each(function(index, Element){            
            $(this).remove();
          });
        }
      });

      $('.bulkUploadCancelButton').entwine({
        onmatch: function(){
          this.removeClass('action');
        },
        onunmatch: function(){},
        onclick: function()
        {
          var $bulkUpload = this.parents('.bulkUpload'),
              $li = $bulkUpload.find('li.ss-uploadfield-item'),
              $records = $li.filter('[data-recordid]'),              
              recordsID,
              $other = $li.not($records),
              $doBulkActionButton = $bulkUpload.parents('.ss-gridfield-table').find('.doBulkActionButton')
              ;

          $other.each(function(index, Element){
            // skip in progress         
            $(this).remove();
          });

          if ( $doBulkActionButton.length > 0 )
          {
            recordsID = $records.map(function() {  
              return parseInt( $(this).data('recordid') )
            }).get();

            $doBulkActionButton.doBulkAction('delete', recordsID);
          }
        }
      });

      $('.bulkUploadFinishButton').entwine({
        onmatch: function(){
          this.removeClass('action');
        },
        onunmatch: function(){},
        onclick: function()
        {          
          var $bulkUpload = this.parents('.bulkUpload'),
              $li = $bulkUpload.find('li.ss-uploadfield-item')
              ;

          $li.each(function(index, Element){
            // skip in progress         
            $(this).remove();
          });
        }
      });

      $('.bulkUploadEditButton').entwine({
        onmatch: function(){
          this.removeClass('action');
        },
        onunmatch: function(){},
        onclick: function()
        {
          var $bulkUpload = this.parents('.bulkUpload'),
              $li = $bulkUpload.find('li.ss-uploadfield-item'),
              $records = $li.filter('[data-recordid]'),              
              recordsID,
              $doBulkActionButton = $bulkUpload.parents('.ss-gridfield-table').find('.doBulkActionButton')
              ;

          if ( $doBulkActionButton.length > 0 )
          {
            recordsID = $records.map(function() {  
              return parseInt( $(this).data('recordid') )
            }).get();

            $doBulkActionButton.doBulkAction('edit', recordsID);
          }
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

		}); // colymba namespace

	}); // ss namespace
}(jQuery));
