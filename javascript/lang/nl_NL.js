if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
	console.error('Class ss.i18n niet gevonden of niet gedefinieerd');
} else {
	ss.i18n.addDictionary('nl_NL', {
		'GridFieldBulkTools.FINISH_CONFIRM': "Er zijn niet-opgeslagen wijzigingen.\n\nDoorgaan zal al deze niet-opgeslagen wijzigingen vergeten.\n\nWeet je zeker dat je de pagina wilt verlaten?",
		'GridFieldBulkTools.EDIT_CHANGED': 'Aangepast',
		'GridFieldBulkTools.EDIT_UPDATED': 'Opgeslagen',
		'GridFieldBulkManager.BULKACTION_EMPTY_SELECT': 'U moet minstens een item te selecteren.',
		'GridFieldBulkManager.CONFIRM_DESTRUCTIVE_ACTION': 'De gegevens zullen permanent verloren. Weet je zeker dat je de pagina wilt verlaten?'
	});
}