(function($) {
  $.entwine('colymba', function($) {

    $('#bulkEditToggle') .entwine({
      onmatch: function(){},
      onunmatch: function(){},
      onclick: function(e)
      {
        var toggleFields = $(this).parents('#Form_bulkEditingForm').find('.ss-toggle h4'),
            state = this.data('state')
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
    
    
    $('.bulkEditingFieldHolder').entwine({
      onmatch: function(){
        var id    = this.attr('id').split('_')[3],
            name  = 'bulkEditingForm',
            $wrap = $('<div/>')
            ;

        $wrap.attr('id', name + '_' + id).addClass(name).data('id', id);
        this.wrap($wrap);
      },
      onunmatch: function(){}
    });

    $('.bulkEditingForm').entwine({
      onchange: function(){
        this.removeClass('updated');
        if ( !this.hasClass('hasUpdate') )
        {
          this.addClass('hasUpdate');
        }
      }
    });
    
    $('#bulkEditingUpdateBtn').entwine({
        onmatch: function(){},
        onunmatch: function(){},
        onclick: function(e){
          e.stopImmediatePropagation();

          var $formsWithUpadtes = $('div.bulkEditingForm.hasUpdate'),
              url               = this.data('url'),
              data              = {},
              cacheBuster       = new Date().getTime() + '_' + this.attr('name')
              ;
          
          if ( $formsWithUpadtes.length > 0 )
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

          $formsWithUpadtes.each(function(){
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
              var $form       = $('#bulkEditingForm_'+record.id)
                  $formHeader = $form.find('.ui-accordion-header')
                  ;

              $form.removeClass('hasUpdate').addClass('updated');
              $formHeader.find('a').html(record.title);
              $formHeader.click();
            });

            this.removeClass('loading');
          });
        }
    });
    
  });
}(jQuery));