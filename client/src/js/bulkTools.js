/**
 * Handles Gridfield updates after a bulk action/upload
 */
window.bulkTools = {
  
  gridfieldRefresh: function ($gridfield, data) {
    if (!data.isError) {
      if (data.isDestructive) {
        this.removeGridFieldRows($gridfield, data.records.success);
      } else {
        this.updateGridFieldRows($gridfield, data.records.success);
      }
      //failed rows
      this.failedGridFieldRows($gridfield, data.records.failed);
    }
  },

  // return the gridfield row node for a specific record
  getGridFieldRow: function ($gridfield, record) {
    return $gridfield.find('.ss-gridfield-item[data-id="'+record.id+'"][data-class="'+record.class+'"]');
  },

  // remove all bulk tools class and attributes from the row
  cleanGridFieldRow: function ($row) {
    return $row.removeClass('bt-deleted bt-failed bt-updated').removeAttr('bt-error');
  },

  // mark all affected gridfield rows as deleted
  removeGridFieldRows: function ($gridfield, records) {
    records.forEach(function(record){
      var $row = this.getGridFieldRow($gridfield, record);
      $row.addClass('bt-deleted').fadeOut(2000);
    }, this);
    $gridfield.entwine('.').entwine('ss').delay(2000).reload();
  },

  // mark all affected gridfield rows as failed
  failedGridFieldRows: function ($gridfield, records) {
    records.forEach(function(record){
      var $row = this.getGridFieldRow($gridfield, record);
      $row.addClass('bt-failed').attr('bt-error', record.message);
      //pseudo element absolute pos don't work in TR, so we'll have to find some JS way to show the bt-error content..
    }, this);
  },

  // update rows with new content
  updateGridFieldRows: function ($gridfield, records) {
    $gridfield.find('.ss-gridfield-item.ss-gridfield-no-items').remove();
    records.forEach(function(record){
      var $row = this.getGridFieldRow($gridfield, record);
      var $newRow = $(record.row).addClass('bt-updated');
      //replace existing or add new?
      if ($row.length === 1) {
        $row.replaceWith($newRow);
      } else {
        $gridfield.find('.ss-gridfield-items').prepend($newRow);
      }      
    }, this);
  },

  //removes an item from the upload item
  //this is bad! can't dispatch within Reducer
  //so it's commentted until actions are written
  /*
  removeUploadItem: function ($gridfield, queueIndex, fileID)
  {
    //we use queueIndex for uploads since the input value will not have been changed yet and we can't watch for change event on hidden input
    if (fileID) {
      $gridfield.find('.uploadfield-item').find('input[value="'+fileID+'"]').siblings('button.uploadfield-item__remove-btn').trigger('click');
    } else if (queueIndex) {
      $gridfield.find('.uploadfield-item').eq(queueIndex-1).find('button.uploadfield-item__remove-btn').trigger('click');
    }
  }*/
}
