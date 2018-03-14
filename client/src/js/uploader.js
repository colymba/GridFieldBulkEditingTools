/**
 * A quick hack to catch the uploadfield add file event
 * and send the file ID to the bulkUploader component
 * @todo  write actions
 */
import Injector from 'lib/Injector';


const bulkUploadFieldAttach = (payload) => {
  const $uploadField = jQuery('#'+payload.fieldId);
	const $gridfield = $uploadField.parents('.ss-gridfield');
  const schema = $uploadField.data('schema');
	jQuery.ajax(schema.data.attachFileEndpoint.url, {
	  method: schema.data.attachFileEndpoint.method, //doesn't seem to change anything
	  data: {
	  	fileID: payload.file.id
	  }
	}).done(function( data, textStatus, jqXHR ) {
	  bulkTools.gridfieldRefresh($gridfield, data);
    //bulkTools.removeUploadItem($gridfield, null, payload.file.id);//bad! can't dispath in Reducer
	});
}

const bulkUploadFieldUpload = (payload) => {
  const $gridfield = jQuery('#'+payload.fieldId).parents('.ss-gridfield');
  bulkTools.gridfieldRefresh($gridfield, payload.json.bulkTools);
  //bulkTools.removeUploadItem($gridfield, payload.queuedId, null);//bad! can't dispath in Reducer
}

const bulkUploadFieldReducer = (originalReducer) => (globalState) => (state, { type, payload }) => {
  switch (type) {
    case 'UPLOADFIELD_ADD_FILE': {
      // Needs to be a bulk upload field and have a file ID (no file ID = normal)
      if (payload.fieldId.indexOf('_BU') !== -1 && payload.file.id)
      {
      	bulkUploadFieldAttach(payload);
      }
      return originalReducer(state, { type, payload });
    }

    case 'UPLOADFIELD_UPLOAD_SUCCESS': {
      //check if this is a bulk upload
      if (payload.fieldId.indexOf('_BU') !== -1)
      {
        bulkUploadFieldUpload(payload);
      }
      return originalReducer(state, { type, payload });
    }

    default: {
      return originalReducer(state, { type, payload });
    }
  }
}

Injector.transform('bulkUploaderTransformation', (updater) => {
  updater.reducer('assetAdmin', bulkUploadFieldReducer);
});