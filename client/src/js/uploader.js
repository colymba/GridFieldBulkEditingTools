/**
 * A quick hack to catch the uploadfield add file event
 * and send the file ID to the bulkUploader component
 */
import Injector from 'lib/Injector';


const bulkUploadFieldAttach = (payload) => {
	const schema = jQuery('#'+payload.fieldId).data('schema');
	jQuery.ajax(schema.data.attachFileEndpoint.url, {
	  method: schema.data.attachFileEndpoint.method, //doesn't seem to change anything
	  data: {
	  	fileID: payload.file.id
	  }
	}).done(function( data, textStatus, jqXHR ) {
	  //do something?
	});
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

    default: {
      return originalReducer(state, { type, payload });
    }
  }
}

Injector.transform('bulkUploaderCustom', (updater) => {
  updater.reducer('assetAdmin', bulkUploadFieldReducer);
});