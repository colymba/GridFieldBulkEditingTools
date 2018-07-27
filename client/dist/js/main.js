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
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
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
/******/ 	return __webpack_require__(__webpack_require__.s = "./client/src/bundles/bundle.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/bundles/bundle.js":
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./client/src/js/bulkTools.js");
__webpack_require__("./client/src/js/manager.js");
__webpack_require__("./client/src/js/managerBulkEditingForm.js");
__webpack_require__("./client/src/js/uploader.js");

/***/ }),

/***/ "./client/src/js/bulkTools.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {
window.bulkTools = {

  gridfieldRefresh: function gridfieldRefresh($gridfield, data) {
    if (!data.isError) {
      if (data.isDestructive) {
        this.removeGridFieldRows($gridfield, data.records.success);
      } else {
        this.updateGridFieldRows($gridfield, data.records.success);
      }

      this.failedGridFieldRows($gridfield, data.records.failed);
    }
  },

  getGridFieldRow: function getGridFieldRow($gridfield, record) {
    return $gridfield.find('.ss-gridfield-item[data-id="' + record.id + '"][data-class="' + record.class + '"]');
  },

  cleanGridFieldRow: function cleanGridFieldRow($row) {
    return $row.removeClass('bt-deleted bt-failed bt-updated').removeAttr('bt-error');
  },

  removeGridFieldRows: function removeGridFieldRows($gridfield, records) {
    records.forEach(function (record) {
      var $row = this.getGridFieldRow($gridfield, record);
      $row.addClass('bt-deleted').fadeOut(2000);
    }, this);
    $gridfield.entwine('.').entwine('ss').delay(2000).reload();
  },

  failedGridFieldRows: function failedGridFieldRows($gridfield, records) {
    records.forEach(function (record) {
      var $row = this.getGridFieldRow($gridfield, record);
      $row.addClass('bt-failed').attr('bt-error', record.message);
    }, this);
  },

  updateGridFieldRows: function updateGridFieldRows($gridfield, records) {
    $gridfield.find('.ss-gridfield-item.ss-gridfield-no-items').remove();
    records.forEach(function (record) {
      var $row = this.getGridFieldRow($gridfield, record);
      var $newRow = $(record.row).addClass('bt-updated');

      if ($row.length === 1) {
        $row.replaceWith($newRow);
      } else {
        $gridfield.find('.ss-gridfield-items').prepend($newRow);
      }
    }, this);
  }

};
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(0)))

/***/ }),

/***/ "./client/src/js/manager.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {(function ($) {
	$.entwine('ss', function ($) {

		$.entwine('colymba', function ($) {
			$('.bulkManagerOptions').entwine({
				onmatch: function onmatch() {
					var $parent = this.parents('thead'),
					    $tr = $parent.find('tr'),
					    targets = ['.filter-header', '.sortable-header'],
					    $target = $parent.find(targets.join(',')),
					    index = $tr.index(this),
					    newIndex = $tr.length - 1;

					$target.each(function (index, Element) {
						var idx = $tr.index(Element);
						if (idx < newIndex) {
							newIndex = idx;
						}
					});

					if (index > newIndex) {
						$tr.eq(newIndex).insertAfter($(this));
					}
				},
				onunmatch: function onunmatch() {}
			});

			$('td.col-bulkSelect').entwine({
				onmatch: function onmatch() {},
				onunmatch: function onunmatch() {},
				onmouseover: function onmouseover() {
					$(this).parents('.ss-gridfield-item').find('.edit-link').removeClass('edit-link').addClass('tempDisabledEditLink');
				},
				onmouseout: function onmouseout() {
					$(this).parents('.ss-gridfield-item').find('.tempDisabledEditLink').addClass('edit-link').removeClass('tempDisabledEditLink');
				},
				onclick: function onclick(e) {
					var cb = $(e.target).find('input');
					if (!$(cb).prop('checked')) $(cb).prop('checked', true);else $(cb).prop('checked', false);
				}
			});

			$('td.col-bulkSelect input').entwine({
				onmatch: function onmatch() {},
				onunmatch: function onunmatch() {},
				onclick: function onclick(e) {
					$(this).parents('.grid-field__table').find('input.bulkSelectAll').prop('checked', '');
				}
			});

			$('input.bulkSelectAll').entwine({
				onmatch: function onmatch() {},
				onunmatch: function onunmatch() {},
				onclick: function onclick() {
					var state = $(this).prop('checked');
					$(this).parents('.grid-field__table').find('td.col-bulkSelect input').prop('checked', state).trigger('change');
				},
				getSelectRecordsID: function getSelectRecordsID() {
					return $(this).parents('.grid-field__table').find('td.col-bulkSelect input:checked').map(function () {
						return parseInt($(this).data('record'));
					}).get();
				}
			});

			$('select.bulkActionName').entwine({
				onmatch: function onmatch() {},
				onunmatch: function onunmatch() {},
				onchange: function onchange(e) {
					var value = $(this).val(),
					    $parent = $(this).parents('.bulkManagerOptions'),
					    $btn = $parent.find('.doBulkActionButton'),
					    config = $btn.data('config');

					$.each(config, function (configKey, configData) {
						if (configKey != value) {
							$btn.removeClass(configData['buttonClasses']);
						}
					});

					if (!value) {
						$btn.addClass('disabled');
						return;
					} else {
						$btn.removeClass('disabled');
					}

					$btn.addClass(config[value]['buttonClasses']).addClass('btn-outline-secondary');

					if (config[value]['icon']) {
						var $img = $btn.find('img');

						if ($img.length) {
							$img.attr('src', config[value]['icon']);
						} else {
							$btn.prepend('<img src="' + config[value]['icon'] + '" alt="" />');
						}
					} else {
						$btn.find('img').remove();
					}

					if (config[value]['destructive']) {
						$btn.addClass('btn-outline-danger');
					} else {
						$btn.removeClass('btn-outline-danger');
					}
				}
			});

			$('.doBulkActionButton').entwine({
				onmatch: function onmatch() {},
				onunmatch: function onunmatch() {},
				getActionURL: function getActionURL(action, url) {
					var cacheBuster = new Date().getTime();
					url = url.split('?');

					if (action) {
						action = '/' + action;
					} else {
						action = '';
					}

					if (url[1]) {
						url = url[0] + action + '?' + url[1] + '&' + 'cacheBuster=' + cacheBuster;
					} else {
						url = url[0] + action + '?' + 'cacheBuster=' + cacheBuster;
					}
					return url;
				},
				onclick: function onclick(e) {
					var $parent = $(this).parents('.bulkManagerOptions'),
					    action = $parent.find('select.bulkActionName').val(),
					    ids = $(this).parents('.bulkManagerOptions').find('input.bulkSelectAll:first').getSelectRecordsID();

					this.doBulkAction(action, ids);
				},

				doBulkAction: function doBulkAction(action, ids) {
					var $parent = $(this).parents('.bulkManagerOptions'),
					    $btn = $parent.find('a.doBulkActionButton'),
					    $msg = $parent.find('.message'),
					    config = $btn.data('config'),
					    url = this.getActionURL(action, $(this).data('url')),
					    data = { records: ids };

					if (ids.length <= 0) {
						alert(ss.i18n._t('GRIDFIELD_BULK_MANAGER.BULKACTION_EMPTY_SELECT'));
						return;
					}

					if (config[action]['destructive']) {
						if (!confirm(ss.i18n._t('GRIDFIELD_BULK_MANAGER.CONFIRM_DESTRUCTIVE_ACTION'))) {
							return false;
						}
					}

					$btn.addClass('loading');
					$msg.removeClass('static show error warning');

					if (config[action]['xhr']) {
						$.ajax({
							url: url,
							data: data,
							type: "POST",
							context: $(this)
						}).always(function (data, textStatus, jqXHR) {
							$btn.removeClass('loading');

							if (data.responseText) {
								data = JSON.parse(data.responseText);
							}

							$msg.html(data.message);

							if (data.isError) {
								$msg.addClass('static error');
							} else if (data.isWarning) {
								$msg.addClass('show warning');
							} else {
								$msg.addClass('show');
							}

							bulkTools.gridfieldRefresh($(this).parents('.ss-gridfield'), data);
						});
					} else {
						var records = 'records[]=' + ids.join('&records[]=');
						url = url + '&' + records;

						window.location.href = url;
					}
				}
			});
		});
	});
})(jQuery);
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(0)))

/***/ }),

/***/ "./client/src/js/managerBulkEditingForm.js":
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {(function ($) {
  $.entwine('colymba', function ($) {
    $('#bulkEditToggle').entwine({
      onmatch: function onmatch() {},
      onunmatch: function onunmatch() {},
      onclick: function onclick(e) {
        var toggleFields = this.parents('form').find('.ss-toggle .ui-accordion-header'),
            state = this.data('state');

        if (!state || state === 'close') {
          state = 'open';
        } else {
          state = 'close';
        }

        toggleFields.each(function () {
          var $this = $(this);

          if (state === 'open' && !$this.hasClass('ui-state-active')) {
            $this.click();
          }

          if (state === 'close' && $this.hasClass('ui-state-active')) {
            $this.click();
          }
        });

        this.data('state', state);
      }
    });

    $('.bulkEditingFieldHolder').entwine({
      onmatch: function onmatch() {},
      onunmatch: function onunmatch() {},
      onchange: function onchange() {
        this.removeClass('updated');
        if (!this.hasClass('hasUpdate')) {
          this.addClass('hasUpdate');
        }
      }
    });
  });
})(jQuery);
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(0)))

/***/ }),

/***/ "./client/src/js/uploader.js":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_lib_Injector__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_lib_Injector___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_lib_Injector__);



var bulkUploadFieldAttach = function bulkUploadFieldAttach(payload) {
  var $uploadField = jQuery('#' + payload.fieldId);
  var $gridfield = $uploadField.parents('.ss-gridfield');
  var schema = $uploadField.data('schema');
  jQuery.ajax(schema.data.attachFileEndpoint.url, {
    method: schema.data.attachFileEndpoint.method,
    data: {
      fileID: payload.file.id
    }
  }).done(function (data, textStatus, jqXHR) {
    bulkTools.gridfieldRefresh($gridfield, data);
  });
};

var bulkUploadFieldUpload = function bulkUploadFieldUpload(payload) {
  var $gridfield = jQuery('#' + payload.fieldId).parents('.ss-gridfield');
  bulkTools.gridfieldRefresh($gridfield, payload.json.bulkTools);
};

var bulkUploadFieldReducer = function bulkUploadFieldReducer(originalReducer) {
  return function (globalState) {
    return function (state, _ref) {
      var type = _ref.type,
          payload = _ref.payload;

      switch (type) {
        case 'UPLOADFIELD_ADD_FILE':
          {
            if (payload.fieldId.indexOf('_BU') !== -1 && payload.file.id) {
              bulkUploadFieldAttach(payload);
            }
            return originalReducer(state, { type: type, payload: payload });
          }

        case 'UPLOADFIELD_UPLOAD_SUCCESS':
          {
            if (payload.fieldId.indexOf('_BU') !== -1) {
              bulkUploadFieldUpload(payload);
            }
            return originalReducer(state, { type: type, payload: payload });
          }

        default:
          {
            return originalReducer(state, { type: type, payload: payload });
          }
      }
    };
  };
};

__WEBPACK_IMPORTED_MODULE_0_lib_Injector___default.a.transform('bulkUploaderTransformation', function (updater) {
  updater.reducer('assetAdmin', bulkUploadFieldReducer);
});
/* WEBPACK VAR INJECTION */}.call(__webpack_exports__, __webpack_require__(0)))

/***/ }),

/***/ 0:
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),

/***/ 1:
/***/ (function(module, exports) {

module.exports = Injector;

/***/ })

/******/ });
//# sourceMappingURL=main.js.map