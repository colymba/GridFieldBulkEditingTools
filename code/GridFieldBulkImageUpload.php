<?php
/**
 *  GridField component for uploading images in bulk
 */
class GridFieldBulkImageUpload implements GridField_HTMLProvider, GridField_URLHandler {
		
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
		'imageFieldName' => null,
		'editableFields' => null,
		'fieldsClassBlacklist' => array( 'GridField', 'UploadField' ),
		'fieldsNameBlacklist' => array(),
		'folderName' => 'bulkUpload'
	);

	/**
	 * 
	 * @param string $imageField
	 * @param string/array $editableFields 
	 */
	public function __construct($imageField = null, $editableFields = null)
	{
		if ( $imageField != null ) $this->setConfig ( 'imageFieldName', $imageField );
		if ( $editableFields != null ) $this->setConfig ( 'editableFields', $editableFields );
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
		if (key_exists($className, $this->config['fieldsNameBlacklist'])) {
			return delete( $this->config['fieldsNameBlacklist'][$className] );
		}else{
			return false;
		}
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