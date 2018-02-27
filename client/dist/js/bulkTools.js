/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(1);
__webpack_require__(2);
module.exports = __webpack_require__(3);


/***/ }),
/* 1 */
/***/ (function(module, exports) {

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
					$(this).parents('.grid-field__table').find('input.bulkSelectAll').prop('checked', '');
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
	        $(this).parents('.grid-field__table')
	        			 .find('td.col-bulkSelect input')
	        			 .prop('checked', state)
	        			 .trigger('change');
	      },
	      getSelectRecordsID: function()
	      {
	      	return $(this).parents('.grid-field__table')
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
						config  = $btn.data('config');

					$.each( config, function( configKey, configData )
					{
						if ( configKey != value )
						{
							$btn.removeClass(configData['buttonClasses']);
						}
					});

					if(!value)
					{
						$btn.addClass('disabled');
						return;
					}
					else {
						$btn.removeClass('disabled');
					}
					
					$btn.addClass(config[value]['buttonClasses']).addClass('btn-outline-secondary');


					if ( config[value]['icon'] )
					{
						var $img = $btn.find('img');

						if ($img.length)
						{
							$img.attr('src', config[value]['icon']);
						}
						else{
							$btn.prepend('<img src="'+config[value]['icon']+'" alt="" />');
						}
					}
					else{
						$btn.find('img').remove();
					}


					if ( config[value]['destructive'] )
					{
						$btn.addClass('btn-outline-danger');
					}
					else{
						$btn.removeClass('btn-outline-danger');
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
					if ( config[action]['destructive'] )
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

					if ( config[action]['xhr'] )
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


/***/ }),
/* 2 */
/***/ (function(module, exports) {

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
        var toggleFields = this.parents('form').find('.ss-toggle .ui-accordion-header'),
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
    
  });
}(jQuery));

/***/ }),
/* 3 */
/***/ (function(module, exports) {

/*
(function($) {	
	$.entwine('ss', function($) {
		$.entwine('colymba', function($) {


		}); // colymba namespace
	}); // ss namespace
}(jQuery));
*/

/***/ })
/******/ ]);
//# sourceMappingURL=bulkTools.js.map