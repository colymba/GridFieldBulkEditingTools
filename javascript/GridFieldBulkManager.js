(function($) {	
	$.entwine('colymba', function($) {

		    
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
		
		$('td.col-bulkSelect input').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},
			onclick: function(e) {
				$(this).parents('.ss-gridfield-table').find('input.bulkSelectAll').prop('checked', '');
			}
		});
		
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
        			 .prop('checked', state);
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
		
		$('.doBulkActionButton').entwine({
			onmatch: function(){
			},
			onunmatch: function(){				
			},	
			onclick: function(e)
			{
				var $parent = $(this).parents('.bulkManagerOptions'),						
						$btn = $parent.find('a.doBulkActionButton'),

						action = $parent.find('select.bulkActionName').val(),
						config = $btn.data('config'),

						url = $(this).data('url'),	

						ids = $(this).parents('.bulkManagerOptions').find('input.bulkSelectAll:first').getSelectRecordsID(),
						data = { records: ids },

						cacheBuster = new Date().getTime()
						;

				if ( $btn.hasClass('ss-ui-action-destructive') )
				{
					if( !confirm(ss.i18n._t('GridFieldBulkManager.CONFIRM_DESTRUCTIVE_ACTION')) )
					{
						e.preventDefault();
						return false;
					}					
				}

				$btn.addClass('loading');

				if ( ids.length <= 0 )
				{
					alert( ss.i18n._t('GridFieldBulkManager.BULKACTION_EMPTY_SELECT') );
					return;
				}

				if ( config[action]['isAjax'] )
				{
					//if ( url.indexOf('?') !== -1 ) cacheBuster = '&cacheBuster=' + cacheBuster;
					//else cacheBuster = '?cacheBuster=' + cacheBuster;

					$.ajax({
						url: url + '/' + action + '?cacheBuster=' + cacheBuster,
						data: data,
						type: "POST",
						context: $(this)
					}).done(function() {
            $(this).parents('.ss-gridfield').entwine('.').entwine('ss').reload();
            $btn.removeClass('loading');
					});
				}
				else{
					var records = 'records[]='+ids.join('&records[]=');

					if ( window.location.search )
					{
						url = url + '/' + action + window.location.search + '&' + records + '&cacheBuster=' + cacheBuster;
					}
					else{
						url = url + '/' + action + '?' + records + '&cacheBuster=' + cacheBuster;
					}

					window.location.href = url;
				}
				
			} 
		});

		
	});	
}(jQuery));