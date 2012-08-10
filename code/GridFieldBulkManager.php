<?php
/**
 * GridField component for editing attached models in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkManager implements GridField_HTMLProvider, GridField_ColumnProvider, GridField_URLHandler {
	
	public function __construct()
	{				
	}
	
	/* GridField_ColumnProvider */
	
	function augmentColumns($gridField, &$columns)
	{
		if(!in_array('BulkSelect', $columns)) $columns[] = 'BulkSelect';
	}
	
	function getColumnsHandled($gridField)
	{
		return array('BulkSelect');
	}
	
	function getColumnContent($gridField, $record, $columnName)
	{
		$cb = CheckboxField::create('bulkSelect_'.$record->ID)
			->addExtraClass('bulkSelect');
		return $cb->Field();
	}
	
	function getColumnAttributes($gridField, $record, $columnName)
	{
		return array('class' => 'col-bulkSelect');
	}
	
	function getColumnMetadata($gridField, $columnName)
	{
		return array('title' => null);
	}
		
	/* // GridField_ColumnProvider */
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getHTMLFragments($gridField) {		
		
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkManager.js');
		
		$dropDownActionList = DropdownField::create('bulkActionName', '')
			->setSource( array('Edit','UnLink','Delete') );
		
		$actionButton = FormAction::create('doBulkAction', 'GO')
				->setAttribute('id', 'doBulkActionButton')
				->addExtraClass('ss-ui-action-constructive cms-panel-link')
				->setAttribute('data-icon', 'accept')
				->setAttribute('data-url', 'bulkEdit')
				->setUseButtonTag(true);
		
		$html = '<div id="bulkManagerOptions">'.
								$dropDownActionList->FieldHolder().
								$actionButton->Field().
						'</div>';
		
		return array(
			'bulk-edit-tools' => $html
		);
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getURLHandlers($gridField) {
			return array(
				'bulkEdit' => 'handlebulkEdit'
			);
	}
	
	/**
	 * Pass control over to the RequestHandler
	 * 
	 * @param GridField $gridField
	 * @param SS_HTTPRequest $request
	 * @return mixed 
	 */
	public function handleBulkUpload($gridField, $request)
	{				
		$controller = $gridField->getForm()->Controller();
		$handler = new GridFieldBulkImageUpload_Request($gridField, $this, $controller);
		
		return $handler->handleRequest($request, DataModel::inst());		
	}
}