(function($) {	
	$.entwine('ss', function($) {
		

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

            //if there are still uploads going
            if ( (done + error) < total )
            {
              if ( !this.hasClass('loading') )
              {
                this.addClass('loading');
                $finishBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'disabled');
              }              
            }
            else{
              this.removeClass('loading');
              $finishBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');
            }            

            //if all done and OK, enable edit
            if ( total === done )
            {
              $editBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');
            }
            else{
              $editBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            }

            //toggle clear error button
            if ( error > 0 )
            {
              $clearErrorBtn.removeClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'false').removeAttr('disabled');
            }
            else{
              $clearErrorBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            }
          }
          else{
            //if not uploading, reset + hide
            this.css({display: 'none'}).removeClass('loading');
            $editBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $cancelBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $finishBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $clearErrorBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
          }       
        }
      });


      /**
       * Clears all updloads with warning or error
       */
      $('.bulkUploadClearErrorButton:not(.ui-state-disabled)').entwine({
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


      /**
       * Cancel all uploads
       * Clear the ones with warnings/error and delete dataObjects from the successful ones
       */
      $('.bulkUploadCancelButton:not(.ui-state-disabled)').entwine({
        onmatch: function(){
          this.removeClass('action');
        },
        onunmatch: function(){},
        onclick: function()
        {
          var $bulkUpload         = this.parents('.bulkUpload'),
              $li                 = $bulkUpload.find('li.ss-uploadfield-item'),
              $records            = $li.filter('[data-recordid]'),
              $other              = $li.not($records),
              $doBulkActionButton = $bulkUpload.parents('.ss-gridfield-table').find('.doBulkActionButton'),              
              recordsID
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

            this.addClass('loading');
            $doBulkActionButton.doBulkAction('delete', recordsID, this.cancelCallback, this);
          }
        },
        cancelCallback: function(data)
        {
          var $bulkUpload = this.parents('.bulkUpload'),
              $li         = $bulkUpload.find('li.ss-uploadfield-item'),
              ids
              ;

          if ( data )
          {
            ids = data.records;

            $li.each(function(index, Element){
              var $this    = $(this),
                  recordID = parseInt( $this.data('recordid') )
                  ;

              if ( ids.indexOf(recordID) !== -1 )
              {
                $this.remove();
              }
            });

            $(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
          }

          this.removeClass('loading');
        }
      });

      
      /**
       * Clear all the warning/error/finished uploads
       */
      $('.bulkUploadFinishButton:not(.ui-state-disabled)').entwine({
        onmatch: function(){
          this.removeClass('action');
        },
        onunmatch: function(){},
        onclick: function()
        {          
          var $bulkUpload = this.parents('.bulkUpload'),
              $li = $bulkUpload.find('li.ss-uploadfield-item')
              ;

          this.addClass('loading');
          $li.each(function(index, Element){
            // skip in progress         
            $(this).remove();
          });

          $(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
          
          this.removeClass('loading');
        }
      });

      $('.bulkUploadEditButton:not(.ui-state-disabled)').entwine({
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
            this.addClass('loading');

            recordsID = $records.map(function() {  
              return parseInt( $(this).data('recordid') )
            }).get();

            $doBulkActionButton.doBulkAction('bulkedit', recordsID);
          }
        }
      });

		}); // colymba namespace

	}); // ss namespace
}(jQuery));