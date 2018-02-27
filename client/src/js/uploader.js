(function($) {	
	$.entwine('ss', function($) {
		

		$.entwine('colymba', function($) {

      /**
       * Makes sure the component is at the top :)
       */
      $('.bulkUploader').entwine({
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
              $finishBtn     = this.find('.bulkUploadFinishButton')
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

          }
          else{
            //if not uploading, reset + hide
            this.css({display: 'none'}).removeClass('loading');
            $finishBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
            $clearErrorBtn.addClass('ui-state-disabled ssui-button-disabled').attr('aria-disabled', 'true').attr('disabled', 'true');
          }       
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

		}); // colymba namespace

	}); // ss namespace
}(jQuery));