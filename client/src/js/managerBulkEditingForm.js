/* global window */
import jQuery from 'jquery';

jQuery.entwine('colymba', ($) => {
  /**
   * Toggle all accordion forms
   * open or closed
   */
  $('#bulkEditToggle').entwine({
    onclick() {
      const toggleFields = this.parents('form').find('.ss-toggle .ui-accordion-header');
      let state = this.data('state');

      if (!state || state === 'close') {
        state = 'open';
      } else {
        state = 'close';
      }

      toggleFields.each(function () {
        const $this = $(this);

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


  /**
   * Contains each rocrds editing fields,
   * tracks changes and updates...
   */
  $('.bulkEditingFieldHolder').entwine({
    onchange() {
      this.removeClass('updated');
      if (!this.hasClass('hasUpdate')) {
        this.addClass('hasUpdate');
      }
    }
  });
});
