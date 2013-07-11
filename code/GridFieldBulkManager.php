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

	/**
	 * @var String
	 */
	protected $bulkEditRequestClass;
	
	
	public function __construct($editableFields = null)
	{				
		if ( $editableFields != null ) $this->setConfig ( 'editableFields', $editableFields );
		$this->config['fieldsClassBlacklist'] = $this->forbiddenFieldsClasses;

		$this->config['actions'] = array(
			'edit' => _t('GridFieldBulkTools.EDIT_SELECT_LABEL', 'Edit'),
			'unlink' => _t('GridFieldBulkTools.UNLINK_SELECT_LABEL', 'UnLink'),
			'delete' => _t('GridFieldBulkTools.DELETE_SELECT_LABEL', 'Delete')
		);
	}
	
	/**
	 * Set a component configuration parameter
	 * 
	 * @param string $reference
	 * @param mixed $value 
	 */
	function setConfig ( $reference, $value )
	{
		if (!key_exists($reference, $this->config) ) {
			user_error("Unknown option reference: $reference", E_USER_ERROR);
		}
		
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

	/**
	 * Add an action to the dropdown
	 *
	 * @param string $className
	 * @return GridFieldBulkManager
	 */
	function addDropdownAction ( $action, $label = '' )
	{
		if(!$label) $label = $action;
		$this->config['actions'][$action] = $label;
		return $this;
	}

	/**
	 * Remove an action from the dropdown
	 *
	 * @param string $className
	 * @return GridFieldBulkManager
	 */
	function removeDropdownAction ( $action )
	{
		if(isset($this->config['actions'][$action]))
			unset($this->config['actions'][$action]);
		return $this;
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
			->setSource($this->config['actions'])
			->setAttribute('class', 'bulkActionName')
			->setAttribute('id', '');

    $templateData = new ArrayData(array(
    	'Menu' => $dropDownActionList->FieldHolder(),
    	'Button' => array(
    		'Label' => _t('GridFieldBulkTools.ACTION_BTN_LABEL', 'Go'),
    		'Link' => $gridField->Link('bulkediting').'/edit',
    		'DataURL' => $gridField->Link('bulkediting')
    	),
    	'Select' => array(
    		'Label' => _t('GridFieldBulkTools.SELECT_ALL_LABEL', 'Select all')
    	)
    ));
		
		$args = array('Colspan' => count($gridField->getColumns())-1);

		return array(
			'header' => $templateData->renderWith('BulkManagerButtons', $args)
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
		$class = $this->getBulkEditRequestClass();
		$handler = Object::create($class, $gridField, $this, $controller);
		
		return $handler->handleRequest($request, DataModel::inst());		
	}

	/**
	 * @param String
	 */
	public function setBulkEditRequestClass($class) {
		$this->bulkEditRequestClass = $class;
		return $this;
	}

	/**
	 * @return String
	 */
	public function getBulkEditRequestClass() {
		if($this->bulkEditRequestClass) {
			return $this->bulkEditRequestClass;
		} else if(ClassInfo::exists(get_class($this) . "_ItemRequest")) {
			return get_class($this) . "_ItemRequest";
		} else {
			return 'GridFieldBulkManager_Request';
		}
	}

	/**
	 * Allow the manager to use actions applicable to versioned dataobjects
	 */
	public function applyVersioned() {
		$this->addDropdownAction('publish', _t('GridFieldBulkTools.PUBLISH_SELECT_LABEL', 'Publish'));
		$this->addDropdownAction('unpublish', _t('GridFieldBulkTools.UNPUBLISH_SELECT_LABEL', 'Unpublish'));
		$this->setBulkEditRequestClass('VersionedGridFieldBulkManager_Request');
	}
}