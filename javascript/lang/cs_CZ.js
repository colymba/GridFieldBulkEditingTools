if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
	console.error('Class ss.i18n not defined');
} else {
	ss.i18n.addDictionary('cs_CZ', {
		'GridFieldBulkTools.FINISH_CONFIRM': "Máte neuložené změny. Pokud budete pokračovat neuložená data budou nenávratně ztracena.\n\nOpravdu chcete pokračovat?",
		'GridFieldBulkTools.EDIT_CHANGED': 'Změněno',
		'GridFieldBulkTools.EDIT_UPDATED': 'Uloženo',
		'GridFieldBulkManager.BULKACTION_EMPTY_SELECT': 'Musíte vybrat alespoň jednu položku.',
		'GridFieldBulkManager.CONFIRM_DESTRUCTIVE_ACTION': 'Data budou nenávratně ztracena. Opravdu chcete pokračovat?'
	});
}
