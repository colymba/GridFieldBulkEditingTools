<?php

class GridFieldBulkImageUpload implements GridField_HTMLProvider, GridField_URLHandler { /*GridField_ActionProvider,*/ 
	
	/**
	 * Target record Image foreign key field name
	 * @var String 
	 */
	protected $recordImageFieldName;
	//protected $labelFieldName;
	
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
	public function __construct($imageField, $editableFields)
	{
		$this->imageFieldName = $imageField;
		
		if ( !is_array($editableFields) ) $editableFields = array($editableFields);		
		$this->recordEditableFields = $editableFields;
	}
	
	public function setRecordImageField($field)
	{
		$this->imageFieldName = $field;
	}
	
	public function setRecordEditableFields($fields)
	{
		$this->recordEditableFields = $fields;
	}
	
	public function getRecordImageField()
	{
		return $this->imageFieldName;
	}
	
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