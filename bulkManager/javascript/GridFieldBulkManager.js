(function($) {	
	$.entwine('ss', function($) {		

		$.entwine('colymba', function($) {

			/**
       * Makes sure the component is above the headers
       */
      $('.bulkManagerOptions').entwine({
        onmatch: function(){
          var $parent = this.parents('thead'),
          		$tr = $parent.find('tr'),

          		targets = ['.filter-header', '.sortable-header'],
          		$target = $parent.find(targets.join(',')),

              index = $tr.index(this),
              newIndex = $tr.length - 1
              ;

          $target.each(function(index, Element){
          	var idx = $tr.index(Element);
          	if ( idx < newIndex )
          	{
          		newIndex = idx;
          	}
          });

          if ( index > newIndex )
          {
            $tr.eq(newIndex).insertAfter($(this));
          }
        },
        onunmatch: function(){}
      });

		  
		  /**
		   * Bulkselect table cell behaviours
		   */
			$('td.col-bulkSelect').entwine({
				onmatch: function(){
				},
				onunmatch: function(){				
				},
				onmouseover: function(){
					//disable default row click behaviour -> avoid navigation to edit form when clicking the checkbox
	        $(this).parents('.ss-gridfield-item').find('.edit-link').removeClass('edit-link').addClass('tempDisabledEditLink');
				},
				onmouseout: function(){
					//re-enable default row click behaviour
					$(this).parents('.ss-gridfield-item').find('.tempDisabledEditLink').addClass('edit-link').removeClass('tempDisabledEditLink');
				},
				onclick: function(e) {
					//check/uncheck checkbox when clicking cell
					var cb = $(e.target).find('input');
					if ( !$(cb).prop('checked') ) $(cb).prop('checked', true);
					else $(cb).prop('checked', false);
				}
			});

			
			/**
			 * Individual select checkbox behaviour
			 */
			$('td.col-bulkSelect input').entwine({
				onmatch: function(){
				},
				onunmatch: function(){				
				},
				onclick: function(e) {
					$(this).parents('.ss-gridfield-table').find('input.bulkSelectAll').prop('checked', '');
				}
			});
			

			/**
			 * Bulkselect checkbox behaviours
			 */
	    $('input.bulkSelectAll').entwine({
	      onmatch: function(){
				},
				onunmatch: function(){				
				},
	      onclick: function()
	      {
	        var state = $(this).prop('checked');
	        $(this).parents('.ss-gridfield-table')
	        			 .find('td.col-bulkSelect input')
	        			 .prop('checked', state)
	        			 .trigger('change');
	      },
	      getSelectRecordsID: function()
	      {
	      	return $(this).parents('.ss-gridfield-table')
					      				.find('td.col-bulkSelect input:checked')
					      				.map(function() {  
					      					return parseInt( $(this).data('record') )
					      				})
											  .get();
	      }
	    });

	    
	    /**
	     * Bulk action dropdown behaviours
	     */
			$('select.bulkActionName').entwine({
				onmatch: function(){
				},
				onunmatch: function(){				
				},
				onchange: function(e)
				{
	        var value   = $(this).val(),
	            $parent = $(this).parents('.bulkManagerOptions'),
	            $btn    = $parent.find('.doBulkActionButton'),
	            config  = $btn.data('config'),
	            $icon   = $parent.find('.doBulkActionButton .ui-icon')
							;

					$.each( config, function( configKey, configData )
					{
						if ( configKey != value )
						{
							$icon.removeClass('btn-icon-'+configData['icon']);
						}
					});
					$icon.addClass('btn-icon-'+config[value]['icon']);


					if ( config[value]['isDestructive'] )
					{
						$btn.addClass('ss-ui-action-destructive');
					}
					else{
						$btn.removeClass('ss-ui-action-destructive');
					}
					
				} 
			});
			

			/**
			 * bulk action button behaviours
			 */
			$('.doBulkActionButton').entwine({
				onmatch: function(){
				},
				onunmatch: function(){				
				},
				getActionURL: function(action, url)
				{
					var cacheBuster = new Date().getTime();
					url = url.split('?');

					if ( action )
					{
						action = '/' + action;
					}
					else{
						action = '';
					}

					if ( url[1] )
					{
						url = url[0] + action + '?' + url[1] + '&' + 'cacheBuster=' + cacheBuster;
					}
					else{
						url = url[0] + action + '?' + 'cacheBuster=' + cacheBuster;
					}
					return url;
				},
				onclick: function(e)
				{
          var $parent = $(this).parents('.bulkManagerOptions'),
              action  = $parent.find('select.bulkActionName').val(),
              ids     = $(this).parents('.bulkManagerOptions').find('input.bulkSelectAll:first').getSelectRecordsID()
							;							

					this.doBulkAction(action, ids);					
				},

				doBulkAction: function(action, ids, callbackFunction, callbackContext)
				{					
          var $parent = $(this).parents('.bulkManagerOptions'),
              $btn    = $parent.find('a.doBulkActionButton'),

              config  = $btn.data('config'),
              url     = this.getActionURL(action, $(this).data('url')),
              data    = { records: ids }
							;
							
					if ( ids.length <= 0 )
					{
						alert( ss.i18n._t('GRIDFIELD_BULK_MANAGER.BULKACTION_EMPTY_SELECT') );
						return;
					}

					//if ( $btn.hasClass('ss-ui-action-destructive') )
					if ( config[action]['isDestructive'] )
					{
						if( !confirm(ss.i18n._t('GRIDFIELD_BULK_MANAGER.CONFIRM_DESTRUCTIVE_ACTION')) )
						{
							if ( callbackFunction && callbackContext )
							{
								callbackFunction.call(callbackContext, false);
							}
							return false;
						}					
					}	

					$btn.addClass('loading');				

					if ( config[action]['isAjax'] )
					{
						$.ajax({
							url: url,
							data: data,
							type: "POST",
							context: $(this)
						}).done(function(data, textStatus, jqXHR) {	            
	            $btn.removeClass('loading');
	            if ( callbackFunction && callbackContext )
							{
								callbackFunction.call(callbackContext, data);
							}
							else{
								$(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
							}
						});
					}
					else{
						var records = 'records[]='+ids.join('&records[]=');
						url = url + '&' + records;

						window.location.href = url;
					}
				}
			});

			
		});	
	});
}(jQuery));
