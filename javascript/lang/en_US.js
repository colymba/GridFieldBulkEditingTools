if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
	console.error('Class ss.i18n not defined');
} else {
	ss.i18n.addDictionary('en_US', {
		'GridFieldBulkTools.FINISH_CONFIRM': "You have unsaved changes. Continuing will loose all unsaved data.\n\nDo your really want to continue?",
		'GridFieldBulkTools.EDIT_CHANGED': 'Modified',
		'GridFieldBulkTools.EDIT_UPDATED': 'Saved'
	});
}
