if(typeof(ss) == 'undefined' || typeof(ss.i18n) == 'undefined') {
  if(typeof(console) != 'undefined') console.error('Class ss.i18n not defined');
} else {
  ss.i18n.addDictionary('ru', {
    "GRIDFIELD_BULK_UPLOAD.PROGRESS_INFO": "Загружается %s файл(ов). %s завершено. %s ошибка(и).",
    "GRIDFIELD_BULK_MANAGER.BULKACTION_EMPTY_SELECT": "Вы должны выбрать как минимум одну запись.",
    "GRIDFIELD_BULK_MANAGER.CONFIRM_DESTRUCTIVE_ACTION": "Ваши данные будут безвозвратно удалены. Вы хотите продолжить?"
});
}