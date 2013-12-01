if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
	console.error('Class ss.i18n not defined');
} else {
	ss.i18n.addDictionary('fr_FR', {
		'GridFieldBulkTools.FINISH_CONFIRM': "Vous avez des changements non enregistrés. En continuant vous allez perdre toutes vos données non enregistrées.\n\nVoulez-vous vraiment continuer?",
		'GridFieldBulkTools.EDIT_CHANGED': 'Changé',
		'GridFieldBulkTools.EDIT_UPDATED': 'Enregisté',
		'GridFieldBulkTools.BULKACTION_EMPTY_SELECT': 'Vous devez séléctionner au moins un élément.'
	});
}
