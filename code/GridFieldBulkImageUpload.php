<?php

class GridFieldBulkImageUpload implements GridField_HTMLProvider, GridField_URLHandler { /*GridField_ActionProvider,*/ 
	
	/**
	 * Target record Image foreign key field name
	 * @var String 
	 */
	protected $recordImageFieldName;
	
	/**
	 * Target record editablez fields
	 * @var Array 
	 */
	protected $recordEditableFields;
	
	/**
	 * 
	 * @param String $imageField
	 * @param String/Array $editableFields 
	 */
	public function __construct($imageField = null, $editableFields = null)
	{
		$this->setRecordImageField($imageField);
				
		if ( !is_array($editableFields) && $editableFields != null ) $editableFields = array($editableFields);
		$this->setRecordEditableFields($editableFields);
	}
	
	/**
	 *
	 * @param String $field 
	 */
	function setRecordImageField($field)
	{
		$this->recordImageFieldName = $field;
	}
	
	/**
	 *
	 * @param Array $fields 
	 */
	function setRecordEditableFields($fields)
	{
		$this->recordEditableFields = $fields;
	}
	
	/**
	 *
	 * @return type 
	 */
	public function getRecordImageField()
	{
		return $this->recordImageFieldName;
	}
	
	/**
	 *
	 * @return type 
	 */
	public function getRecordEditableFields()
	{
		return $this->recordEditableFields;
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return Array 
	 */
	public function getHTMLFragments($gridField) {		
		
		$data = new ArrayData(array(
			'NewLink' => $gridField->Link('bulkimageupload'),
			'ButtonName' => 'Bulk Upload'
		));	
		
		return array(
			'toolbar-header-right' => $data->renderWith('GridFieldAddNewbutton')
		);
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return Array 
	 */
	public function getURLHandlers($gridField) {
			return array(
				'bulkimageupload' => 'handleBulkUpload'
			);
	}
	
	/**
	 *
	 * @param type $gridField
	 * @param type $request
	 * @return type 
	 */
	public function handleBulkUpload($gridField, $request)
	{				
		$controller = $gridField->getForm()->Controller();
		$handler = new GridFieldBulkImageUpload_Request($gridField, $this, $controller);
		
		return $handler->handleRequest($request, DataModel::inst());		
	}
}