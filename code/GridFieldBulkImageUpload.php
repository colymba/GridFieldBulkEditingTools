<?php
/**
 * GridField component for uploading images in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
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
		'fieldsClassBlacklist' => array(),
		'fieldsNameBlacklist' => array(),
		'folderName' => 'bulkUpload',
		'maxFileSize' => null,
    'sequentialUploads' => false
	);
	
	/**
	 * Holds any class that should not be used as they break the component
	 * These cannot be removed from the blacklist
	 */
	protected $forbiddenFieldsClasses = array( 'GridField', 'UploadField' );

	/**
	 * 
	 * @param string $imageField
	 * @param string/array $editableFields 
	 */
	public function __construct($imageField = null, $editableFields = null)
	{		
		if ( $imageField != null ) $this->setConfig ( 'imageFieldName', $imageField );
		if ( $editableFields != null ) $this->setConfig ( 'editableFields', $editableFields );
		
		//init classes blacklist with forbidden classes
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

		//makes sure maxFileSize is INT
		if ( $reference == 'maxFileSize' && !is_int($value) )
		{
			user_warning("maxFileSize should be an Integer. Setting it to Auto.", E_USER_ERROR);
			$value = null;
		}
    
    //sequentialUploads true/false
    if ( $reference == 'sequentialUploads' && !is_bool($value) )
		{
      $value = false;
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
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getHTMLFragments($gridField) {	
		
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkImageUpload.css');
		
		$targetFragment = 'before';
		if ( $gridField->getConfig()->getComponentByType('GridFieldButtonRow') )
		{
			$targetFragment = 'buttons-before-right';
		}

		$bulkUploadBtn = new ArrayData(array(
			'Link' => $gridField->Link('bulkimageupload')
		));
		
		return array(
			$targetFragment => $bulkUploadBtn->renderWith('BulkUploadButton')
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