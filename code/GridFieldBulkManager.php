<?php
/**
 * GridField component for editing attached models in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkManager implements GridField_HTMLProvider, GridField_ColumnProvider, GridField_URLHandler {
	
	/**
	 * component configuration
	 * 
	 * 'imageFieldName' => field name of the $has_one Model Image relation
	 * 'editableFields' => fields editable on the Model
	 * 'fieldsClassBlacklist' => field types that will be removed from the automatic form generation
	 * 'fieldsNameBlacklist' => fields that will be removed from the automatic form generation
	 * 
	 * @var array 
	 */
	protected $config = array(
		'editableFields' => null,
		'fieldsClassBlacklist' => array(),
		'fieldsNameBlacklist' => array()
	);
	
	/**
	 * Holds any class that should not be used as they break the component
	 * These cannot be removed from the blacklist
	 */
	protected $forbiddenFieldsClasses = array( 'GridField', 'UploadField' );
	
	
	public function __construct($editableFields = null)
	{				
		if ( $editableFields != null ) $this->setConfig ( 'editableFields', $editableFields );
		$this->config['fieldsClassBlacklist'] = $this->forbiddenFieldsClasses;
	}
	
	/**
	 * Set a component configuration parameter
	 * 
	 * @param string $reference
	 * @param mixed $value 
	 */
	function setConfig ( $reference, $value )
	{
		if ( isset( $this->config[$reference] ) )
		{
			if ( ($reference == 'fieldsClassBlacklist' || $reference == 'fieldsClassBlacklist' || $reference == 'editableFields') && !is_array($value) )
			{
				$value = array($value);
			}
			
			//makes sure $forbiddenFieldsClasses are in no matter what
			if ( $reference == 'fieldsClassBlacklist' )
			{
				$value = array_unique( array_merge($value, $this->forbiddenFieldsClasses) );
			}
			
			$this->config[$reference] = $value;
		}
	}
	
	/**
	 * Returns one $config parameter of the full $config
	 * 
	 * @param string $reference $congif parameter to return
	 * @return mixed 
	 */
	function getConfig ( $reference = false )
	{
		if ( $reference ) return $this->config[$reference];
		else return $this->config;
	}
	
	/**
	 * Add a field to the editable fields blacklist
	 * 
	 * @param string $fieldName
	 * @return boolean 
	 */
	function addFieldNameToBlacklist ( $fieldName )
	{
		return array_push( $this->config['fieldsNameBlacklist'], $fieldName);
	}
	
	/**
	 * Add a class to the editable fields blacklist
	 * 
	 * @param string $className
	 * @return boolean 
	 */
	function addClassToBlacklist ( $className )
	{
		return array_push( $this->config['fieldsClassBlacklist'], $className);
	}
	
	/**
	 * Remove a field to the editable fields blacklist
	 * 
	 * @param string $fieldName
	 * @return boolean 
	 */
	function removeFieldNameFromBlacklist ( $fieldName )
	{
		if (key_exists($fieldName, $this->config['fieldsNameBlacklist'])) {
			return delete( $this->config['fieldsNameBlacklist'][$fieldName] );
		}else{
			return false;
		}
	}
	
	/**
	 * Remove a class to the editable fields blacklist
	 * 
	 * @param string $className
	 * @return boolean 
	 */
	function removeClassFromBlacklist ( $className )
	{
		if (key_exists($className, $this->config['fieldsNameBlacklist']) && !in_array($className, $this->forbiddenFieldsClasses)) {
			return delete( $this->config['fieldsNameBlacklist'][$className] );
		}else{
			return false;
		}
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
		if($columnName == 'BulkSelect') {
			return array('title' => 'Select');
		}
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
			->setSource( array('edit' => 'Edit','unlink' => 'UnLink','delete' => 'Delete') );
		/*
		$actionButton = FormAction::create('doBulkAction', 'GO')
				->setAttribute('id', 'doBulkActionButton')
				//->addExtraClass('cms-panel-link')
				->setAttribute('data-icon', 'pencil')
				->setAttribute('data-url', $gridField->Link('bulkEdit'))
				->setAttribute('href', $gridField->Link('bulkEdit').'/edit')
				->setUseButtonTag(true);
		*/
		$actionButtonHTML = '
		<a id="doBulkActionButton" href="'.$gridField->Link('bulkediting').'/edit'.'" data-url="'.$gridField->Link('bulkediting').'"  class="action ss-ui-button cms-panel-link" data-icon="pencil">
			GO
		</a>';
    
    $toggleSelectAllHTML = '
      <span>Select all <input id="toggleSelectAll" type="checkbox" title="select all" name="toggleSelectAll" /></span>
    ';
		
		$html = '<div id="bulkManagerOptions">'.
								$dropDownActionList->FieldHolder().
								//$actionButton->Field().
								$actionButtonHTML.
                $toggleSelectAllHTML.
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
				'bulkediting' => 'handlebulkEdit'
			);
	}
	
	/**
	 * Pass control over to the RequestHandler
	 * 
	 * @param GridField $gridField
	 * @param SS_HTTPRequest $request
	 * @return mixed 
	 */
	public function handlebulkEdit($gridField, $request)
	{				
		$controller = $gridField->getForm()->Controller();
		$handler = new GridFieldBulkManager_Request($gridField, $this, $controller);
		
		return $handler->handleRequest($request, DataModel::inst());		
	}
}