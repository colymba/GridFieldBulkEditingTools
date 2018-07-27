(function ($) {
	$.entwine('ss', ($) => {
		$.entwine('colymba', ($) => {
			/**
       * Makes sure the component is above the headers
       */
      $('.bulkManagerOptions').entwine({
        onmatch() {
          let $parent = this.parents('thead'),
          		$tr = $parent.find('tr'),

          		targets = ['.filter-header', '.sortable-header'],
          		$target = $parent.find(targets.join(',')),

              index = $tr.index(this),
              newIndex = $tr.length - 1
              ;

          $target.each((index, Element) => {
          	const idx = $tr.index(Element);
          	if (idx < newIndex) {
          		newIndex = idx;
          	}
          });

          if (index > newIndex) {
            $tr.eq(newIndex).insertAfter($(this));
          }
        },
        onunmatch() {}
      });


		  /**
		   * Bulkselect table cell behaviours
		   */
			$('td.col-bulkSelect').entwine({
				onmatch() {
				},
				onunmatch() {
				},
				onmouseover() {
					// disable default row click behaviour -> avoid navigation to edit form when clicking the checkbox
	        $(this).parents('.ss-gridfield-item').find('.edit-link').removeClass('edit-link')
.addClass('tempDisabledEditLink');
				},
				onmouseout() {
					// re-enable default row click behaviour
					$(this).parents('.ss-gridfield-item').find('.tempDisabledEditLink').addClass('edit-link')
.removeClass('tempDisabledEditLink');
				},
				onclick(e) {
					// check/uncheck checkbox when clicking cell
					const cb = $(e.target).find('input');
					if (!$(cb).prop('checked')) $(cb).prop('checked', true);
					else $(cb).prop('checked', false);
				}
			});


			/**
			 * Individual select checkbox behaviour
			 */
			$('td.col-bulkSelect input').entwine({
				onmatch() {
				},
				onunmatch() {
				},
				onclick(e) {
					$(this).parents('.grid-field__table').find('input.bulkSelectAll').prop('checked', '');
				}
			});


			/**
			 * Bulkselect checkbox behaviours
			 */
	    $('input.bulkSelectAll').entwine({
	      onmatch() {
				},
				onunmatch() {
				},
	      onclick() {
	        const state = $(this).prop('checked');
	        $(this).parents('.grid-field__table')
	        			 .find('td.col-bulkSelect input')
	        			 .prop('checked', state)
	        			 .trigger('change');
	      },
	      getSelectRecordsID() {
	      	return $(this).parents('.grid-field__table')
					      				.find('td.col-bulkSelect input:checked')
					      				.map(function () {
					      					return parseInt($(this).data('record'));
					      				})
											  .get();
	      }
	    });


	    /**
	     * Bulk action dropdown behaviours
	     */
			$('select.bulkActionName').entwine({
				onmatch() {
				},
				onunmatch() {
				},
				onchange(e) {
					let value = $(this).val(),
						$parent = $(this).parents('.bulkManagerOptions'),
						$btn = $parent.find('.doBulkActionButton'),
						config = $btn.data('config');

					$.each(config, (configKey, configData) => {
						if (configKey != value) {
							$btn.removeClass(configData.buttonClasses);
						}
					});

					if (!value) {
						$btn.addClass('disabled');
						return;
					}

						$btn.removeClass('disabled');


					$btn.addClass(config[value].buttonClasses).addClass('btn-outline-secondary');


					if (config[value].icon) {
						const $img = $btn.find('img');

						if ($img.length) {
							$img.attr('src', config[value].icon);
						} else {
							$btn.prepend(`<img src="${config[value].icon}" alt="" />`);
						}
					} else {
						$btn.find('img').remove();
					}


					if (config[value].destructive) {
						$btn.addClass('btn-outline-danger');
					} else {
						$btn.removeClass('btn-outline-danger');
					}
				}
			});


			/**
			 * bulk action button behaviours
			 */
			$('.doBulkActionButton').entwine({
				onmatch() {
				},
				onunmatch() {
				},
				getActionURL(action, url) {
					const cacheBuster = new Date().getTime();
					url = url.split('?');

					if (action) {
						action = `/${action}`;
					} else {
						action = '';
					}

					if (url[1]) {
						url = `${url[0] + action}?${url[1]}&` + `cacheBuster=${cacheBuster}`;
					} else {
						url = `${url[0] + action}?` + `cacheBuster=${cacheBuster}`;
					}
					return url;
				},
				onclick(e) {
          let $parent = $(this).parents('.bulkManagerOptions'),
              action = $parent.find('select.bulkActionName').val(),
              ids = $(this).parents('.bulkManagerOptions').find('input.bulkSelectAll:first').getSelectRecordsID()
							;

					this.doBulkAction(action, ids);
				},

				doBulkAction(action, ids) {
          let $parent = $(this).parents('.bulkManagerOptions'),
              $btn = $parent.find('a.doBulkActionButton'),
              $msg = $parent.find('.message'),

              config = $btn.data('config'),
              url = this.getActionURL(action, $(this).data('url')),
              data = { records: ids }
							;

					if (ids.length <= 0) {
						alert(ss.i18n._t('GRIDFIELD_BULK_MANAGER.BULKACTION_EMPTY_SELECT'));
						return;
					}

					// if ( $btn.hasClass('ss-ui-action-destructive') )
					if (config[action].destructive) {
						if (!confirm(ss.i18n._t('GRIDFIELD_BULK_MANAGER.CONFIRM_DESTRUCTIVE_ACTION'))) {
							return false;
						}
					}

					$btn.addClass('loading');
					$msg.removeClass('static show error warning');

					if (config[action].xhr) {
						$.ajax({
							url,
							data,
							type: 'POST',
							context: $(this)
						}).always(function (data, textStatus, jqXHR) {
							$btn.removeClass('loading');

							// if request fail, return a +4xx status code, extract json response
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
						const records = `records[]=${ids.join('&records[]=')}`;
						url = `${url}&${records}`;

						window.location.href = url;
					}
				}
			});
		});
	});
}(jQuery));
