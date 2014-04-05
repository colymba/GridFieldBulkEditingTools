<?php
/**
 * GridField component for uploading images in bulk
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkUpload implements GridField_HTMLProvider, GridField_URLHandler {
		
	/**
	 * component configuration
	 * 
	 * 'fileRelationName' => field name of the $has_one File/Image relation
	 * 'editableFields' => fields editable on the Model
	 * 'fieldsClassBlacklist' => field types that will be removed from the automatic form generation
	 * 'fieldsNameBlacklist' => fields that will be removed from the automatic form generation
	 * 
	 * @var array 
	 */
	protected $config = array(
		'fileRelationName' => null,
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
	 * @param string $fileRelationName
	 * @param string/array $editableFields
	 */
	public function __construct($fileRelationName = null, $editableFields = null)
	{		
		if ( $fileRelationName != null ) $this->setConfig ( 'fileRelationName', $fileRelationName );
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

	/* ******************************************************************************** */

	/**
	 * Get the first has_one Image/File relation from the GridField managed DataObject
	 * i.e. 'MyImage' => 'Image' will return 'MyImage'
	 * 
	 * @return string Name of the $has_one relation
	 */
	public function getDefaultFileRelationName($gridField)
	{
		$recordClass = $gridField->list->dataClass;
		$hasOneFields = Config::inst()->get($recordClass, 'has_one', Config::INHERITED);
		
		$imageField = null;
		foreach( $hasOneFields as $field => $type )
		{
			if( $type === 'Image' ||  $type === 'File' || is_subclass_of($type, 'File') )
			{
				$imageField = $field;
				break;
			}
		}
		
		return $imageField;
	}	

	/**
	 * Returns the name of the Image/File field name from the managed record
	 * Either as set in the component config or the default one
	 * 
	 * @return string 
	 */	
	public function getFileRelationName($gridField)
	{
		$configFileRelationName = $this->getConfig('fileRelationName');
		return $configFileRelationName ? $configFileRelationName : $this->getDefaultFileRelationName($gridField);
	}

	/**
	 * Return the ClassName of the fileRelation
	 * i.e. 'MyImage' => 'Image' will return 'Image'
	 * i.e. 'MyImage' => 'File' will return 'File'
	 *
	 * @return string file relation className
	 */
	public function getFileRelationClassName($gridField)
	{
		$recordClass  = $gridField->list->dataClass;
		$hasOneFields = Config::inst()->get($recordClass, 'has_one', Config::INHERITED);

		$fieldName = $this->getFileRelationName($gridField);
		if($fieldName)
		{
			return $hasOneFields[$fieldName];
		}
		else{
			return 'File';
		}		
	}


	/* ******************************************************************************** */

	public function bulkUploadField($gridField)
	{
		$fileRelationName = $this->getFileRelationName($gridField);
		$uploadField = UploadField::create($fileRelationName, '')
			->setForm($gridField->getForm())

			->setConfig('previewMaxWidth', 20)
			->setConfig('previewMaxHeight', 20)
			->setConfig('changeDetection', false)

			->setTemplate('GridFieldBulkUploadField')
			->setDownloadTemplateName('colymba-bulkuploaddownloadtemplate')
			
			->setConfig('url', $gridField->Link('bulkupload/upload'))
			->setConfig('urlSelectDialog', $gridField->Link('bulkupload/select'))
			->setConfig('urlAttach', $gridField->Link('bulkupload/attach'))
			->setConfig('urlFileExists', $gridField->Link('bulkupload/fileexists'))
			;

		//max file size
		$maxFileSize = $this->getConfig('maxFileSize');
		if ( $maxFileSize !== null ) 
		{
			$uploadField->getValidator()->setAllowedMaxFileSize( $maxFileSize );
		}

		//upload dir
		$uploadDir = $this->getConfig('folderName');
		if ( $uploadDir !== null )
		{
			$uploadField->setFolderName($uploadDir);
		}	

		//sequential upload
		$uploadField->setConfig('sequentialUploads', $this->getConfig('sequentialUploads'));
		
		return $uploadField;
	}
		
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getHTMLFragments($gridField)
	{				
		// permission check
		if( !singleton($gridField->getModelClass())->canEdit() )
		{
			return array();
		}

		// upload management buttons
		$finishButton = FormAction::create('Finish', _t('GridFieldBulkTools.FINISH_BTN_LABEL', 'Finish'))
			->addExtraClass('bulkUploadFinishButton')
			->setAttribute('data-icon', 'accept')
			->setUseButtonTag(true)
			->setAttribute('src', '');//changes type to image so isn't hooked by default actions handlers

		$cancelButton = FormAction::create('Cancel', _t('GridFieldBulkTools.CANCEL_BTN_LABEL', 'Cancel & delete all'))
			->addExtraClass('bulkUploadCancelButton ss-ui-action-destructive')
			->setAttribute('data-icon', 'decline')
			->setAttribute('data-url', $gridField->Link('bulkupload/cancel'))
			->setUseButtonTag(true)
			->setAttribute('src', '');

		if ( $gridField->getConfig()->getComponentsByType('GridFieldBulkManager') )
		{
			$editAllButton = FormAction::create('EditAll', _t('GridFieldBulkTools.EDIT_ALL_BTN_LABEL', 'Edit all'))
				->addExtraClass('bulkUploadEditButton')
				->setAttribute('data-icon', 'pencil')
				->setAttribute('data-url', $gridField->Link('bulkupload/edit'))
				->setUseButtonTag(true)
				->setAttribute('src', '');
		}else{
			$editAllButton = '';
		}

		// get uploadField + inject extra buttons
		$uploadField = $this->bulkUploadField($gridField);
    $uploadField->FinishButton  = $finishButton;
    $uploadField->CancelButton  = $cancelButton;
    $uploadField->EditAllButton = $editAllButton;

		$data = ArrayData::create(array(
      'Colspan'     => count($gridField->getColumns()),
      'UploadField' => $uploadField->Field() // call ->Field() to get requirements in right order
		));

		Requirements::css(BULKEDITTOOLS_UPLOAD_PATH . '/css/GridFieldBulkUpload.css');
		Requirements::javascript(BULKEDITTOOLS_UPLOAD_PATH . '/javascript/GridFieldBulkUpload.js');
		Requirements::javascript(BULKEDITTOOLS_UPLOAD_PATH . '/javascript/GridFieldBulkUpload_downloadtemplate.js');
		Requirements::add_i18n_javascript(BULKEDITTOOLS_UPLOAD_PATH . '/javascript/lang');
		
		return array(
			'header' => $data->renderWith('GridFieldBulkUpload')
		);
	}
	
	/**
	 *
	 * @param GridField $gridField
	 * @return array 
	 */
	public function getURLHandlers($gridField) {
			return array(
				'bulkupload' => 'handleBulkUpload'
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
		$handler = new GridFieldBulkUpload_Request($gridField, $this, $controller);
		
		return $handler->handleRequest($request, DataModel::inst());		
	}
}

