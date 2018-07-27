(function ($) {
  $.entwine('colymba', ($) => {
    /**
     * Toggle all accordion forms
     * open or closed
     */
    $('#bulkEditToggle').entwine({
      onmatch() {},
      onunmatch() {},
      onclick(e) {
        let toggleFields = this.parents('form').find('.ss-toggle .ui-accordion-header'),
            state = this.data('state')
            ;

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
      onmatch() {},
      onunmatch() {},
      onchange() {
        this.removeClass('updated');
        if (!this.hasClass('hasUpdate')) {
          this.addClass('hasUpdate');
        }
      }
    });
  });
}(jQuery));
