/* global window, alert, confirm */
import jQuery from 'jquery';
import i18n from 'i18n';

jQuery.entwine('colymba', ($) => {
  /**
   * Makes sure the component is above the headers
   */
  $('.bulkManagerOptions').entwine({
    onmatch() {
      const $parent = this.parents('thead');
      const $tr = $parent.find('tr');

      const targets = ['.filter-header', '.sortable-header'];
      const $target = $parent.find(targets.join(','));

      const index = $tr.index(this);
      let newIndex = $tr.length - 1;

      $target.each((targetIndex, Element) => {
        const idx = $tr.index(Element);
        if (idx < newIndex) {
          newIndex = idx;
        }
      });

      if (index > newIndex) {
        $tr.eq(newIndex).insertAfter($(this));
      }
    },
  });


  /**
   * Bulkselect table cell behaviours
   */
  $('td.col-bulkSelect').entwine({
    onmouseover() {
      // disable default row click behaviour -> avoid navigation to edit form when
      // clicking the checkbox
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
      if (!$(cb).prop('checked')) {
        $(cb).prop('checked', true);
      } else {
        $(cb).prop('checked', false);
      }
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
    onclick() {
      $(this).parents('.grid-field__table').find('input.bulkSelectAll').prop('checked', '');
    }
  });

  /**
   * Bulkselect checkbox behaviours
   */
  $('input.bulkSelectAll').entwine({
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
          return parseInt($(this).data('record'), 10);
        })
        .get();
    }
  });


  /**
   * Bulk action dropdown behaviours
   */
  $('select.bulkActionName').entwine({
    onchange() {
      const value = $(this).val();
      const $parent = $(this).parents('.bulkManagerOptions');
      const $btn = $parent.find('.doBulkActionButton');
      const config = $btn.data('config');

      $.each(config, (configKey, configData) => {
        if (configKey !== value) {
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
    getActionURL(action, url) {
      const cacheBuster = new Date().getTime();
      let newUrl = url.split('?');

      let newAction = '';
      if (action) {
        newAction = `/${action}`;
      }

      if (newUrl[1]) {
        newUrl = `${newUrl[0] + newAction}?${newUrl[1]}&cacheBuster=${cacheBuster}`;
      } else {
        newUrl = `${newUrl[0] + newAction}?cacheBuster=${cacheBuster}`;
      }
      return newUrl;
    },
    onclick() {
      const $parent = $(this).parents('.bulkManagerOptions');
      const action = $parent.find('select.bulkActionName').val();
      const ids = $(this).parents('.bulkManagerOptions').find('input.bulkSelectAll:first').getSelectRecordsID();

      this.doBulkAction(action, ids);
    },

    doBulkAction(action, ids) {
      const { bulkTools } = window;

      const $parent = $(this).parents('.bulkManagerOptions');
      const $btn = $parent.find('a.doBulkActionButton');
      const $msg = $parent.find('.message');

      const config = $btn.data('config');
      let url = this.getActionURL(action, $(this).data('url'));
      const inputData = { records: ids };

      if (ids.length <= 0) {
        alert(i18n._t('GRIDFIELD_BULK_MANAGER.BULKACTION_EMPTY_SELECT'));
        return false;
      }

      // if ( $btn.hasClass('ss-ui-action-destructive') )
      if (config[action].destructive) {
        if (!confirm(i18n._t('GRIDFIELD_BULK_MANAGER.CONFIRM_DESTRUCTIVE_ACTION'))) {
          return false;
        }
      }

      $btn.addClass('loading');
      $msg.removeClass('static show error warning');

      if (config[action].xhr) {
        $.ajax({
          url,
          data: inputData,
          type: 'POST',
          context: $(this)
        }).always(function (data) {
          let returnData = data;
          $btn.removeClass('loading');

          // if request fail, return a +4xx status code, extract json response
          if (data.responseText) {
            returnData = JSON.parse(data.responseText);
          }

          $msg.html(returnData.message);

          if (returnData.isError) {
            $msg.addClass('static error');
          } else if (returnData.isWarning) {
            $msg.addClass('show warning');
          } else {
            $msg.addClass('show');
          }

          bulkTools.gridfieldRefresh($(this).parents('.ss-gridfield'), returnData);
        });
      } else {
        const records = `records[]=${ids.join('&records[]=')}`;
        url = `${url}&${records}`;

        window.location.href = url;
      }

      return true;
    }
  });
});
