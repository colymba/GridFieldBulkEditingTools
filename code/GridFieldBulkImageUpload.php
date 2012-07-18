<?php
/**
 *  GridField component for uploading images in bulk
 */
class GridFieldBulkImageUpload implements GridField_HTMLProvider, GridField_URLHandler {
	
	/**
	 * Target record Image foreign key field name
	 * 
	 * @var string 
	 */
	protected $recordImageFieldName;
	
	/**
	 * Target record editable fields
	 * 
	 * @var array 
	 */
	protected $recordEditableFields;
	
	/**
	 * 
	 * @param string $imageField
	 * @param string/array $editableFields 
	 */
	public function __construct($imageField = null, $editableFields = null)
	{
		$this->setRecordImageField($imageField);
				
		if ( !is_array($editableFields) && $editableFields != null ) $editableFields = array($editableFields);
		$this->setRecordEditableFields($editableFields);
	}
	
	/**
	 *
	 * @param string $field 
	 */
	function setRecordImageField($field)
	{
		$this->recordImageFieldName = $field;
	}
	
	/**
	 *
	 * @param array $fields 
	 */
	function setRecordEditableFields($fields)
	{
		$this->recordEditableFields = $fields;
	}
	
	/**
	 *
	 * @return string 
	 */
	public function getRecordImageField()
	{
		return $this->recordImageFieldName;
	}
	
	/**
	 *
	 * @return string 
	 */
	public function getRecordEditableFields()
	{
		return $this->recordEditableFields;
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
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
	 * @return array 
	 */
	public function getURLHandlers($gridField) {
			return array(
				'bulkimageupload' => 'handleBulkUpload'
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