(function($) {
  $.entwine('colymba', function($) {

    /**
     * Toggle all accordion forms
     * open or closed
     */
    $('#bulkEditToggle') .entwine({
      onmatch: function(){},
      onunmatch: function(){},
      onclick: function(e)
      {
        var toggleFields = this.parents('#Form_BulkEditingForm').find('.ss-toggle .ui-accordion-header'),
            state        = this.data('state')
            ;

        if ( !state || state === 'close' )
        {
          state = 'open';
        }
        else {
          state = 'close';
        }

        toggleFields.each(function()
        {
          var $this = $(this);
          
          if ( state === 'open' && !$this.hasClass('ui-state-active') )
          {
            $this.click();
          }

          if ( state === 'close' && $this.hasClass('ui-state-active') )
          {
            $this.click();
          } 
        });

        this.data('state', state);
      }
    });
    
    
    /**
     * Contains each rocrds editing fields,
     * tracks changes and updates...
     */
    $('.bulkEditingFieldHolder').entwine({
      onmatch: function(){},
      onunmatch: function(){},
      onchange: function(){
        this.removeClass('updated');
        if ( !this.hasClass('hasUpdate') )
        {
          this.addClass('hasUpdate');
        }
      }
    });
    

    /**
     * Save all button
     * process all field holders with updates
     */
    $('#bulkEditingUpdateBtn').entwine({
        onmatch: function(){},
        onunmatch: function(){},
        onclick: function(e){
          e.stopImmediatePropagation();

          var $fieldHolders     = $('div.bulkEditingFieldHolder.hasUpdate'),
              url               = this.data('url'),
              data              = {},
              cacheBuster       = new Date().getTime() + '_' + this.attr('name')
              ;
          
          if ( $fieldHolders.length > 0 )
          {
            this.addClass('loading');
          }
          else{
            return;
          }

          if ( url.indexOf('?') !== -1 )
          {
            cacheBuster = '&cacheBuster=' + cacheBuster;
          }
          else{
            cacheBuster = '?cacheBuster=' + cacheBuster;
          }

          $fieldHolders.each(function(){
            var $this = $(this);
            data[$this.data('id')] = $this.find(':input').serializeArray();
          });

          $.ajax({
            url:     url + cacheBuster,
            data:    data,
            type:    "POST",
            context: this
          }).success(function(data, textStatus, jqXHR){
            try{
              data = $.parseJSON(data);
            }catch(er){}

            $.each(data.records, function(index, record){
              var $fieldHolder = $('#Form_BulkEditingForm_RecordFields_'+record.id),
                  $header      = $fieldHolder.find('.ui-accordion-header')
                  ;

              $fieldHolder.removeClass('hasUpdate').addClass('updated');
              $header.find('a').html(record.title);
              if ( $header.hasClass('ui-state-active') )
              {
                $header.click();
              }              
            });

            this.removeClass('loading');
          });
        }
    });
    
  });
}(jQuery));