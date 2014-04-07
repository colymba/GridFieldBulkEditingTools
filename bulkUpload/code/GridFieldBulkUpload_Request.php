<?php
/**
 * Handles request from the GridFieldBulkImageUpload component 
 * 
 * Handles:
 * * Form creation
 * * file upload
 * * editing and cancelling records
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkUpload_Request extends RequestHandler {
	
  /**
	 *
	 * @var GridField 
	 */
	protected $gridField;
	
	/**
	 *
	 * @var GridField_URLHandler
	 */
	protected $component;
	
	/**
	 *
	 * @var Controller
	 */
	protected $controller;
	
	/**
	 * Cache the records FieldList from getCMSfields()
	 * 
	 * @var FieldList 
	 */
	protected $recordCMSFieldList;
	
	/**
	 *
	 */
	private static $allowed_actions = array(
		'upload', 'select', 'attach', 'fileexists', 'update', 'cancel'
	);

	/**
	 *
	 */
	private static $url_handlers = array(
		'$Action!' => '$Action'
	);
	
	/**
	 *
	 * @param GridFIeld $gridField
	 * @param GridField_URLHandler $component
	 * @param Controller $controller
	 */
	public function __construct($gridField, $component, $controller) {
		$this->gridField = $gridField;
		$this->component = $component;
		$this->controller = $controller;		
		parent::__construct();
	}

	/**
	 * Returns the URL for this RequestHandler
	 * 
	 * @author SilverStripe
	 * @see GridFieldDetailForm_ItemRequest
	 * @param string $action
	 * @return string 
	 *//*
	public function Link($action = null) {
		return Controller::join_links($this->gridField->Link(), 'bulkimageupload', $action);
	}*/
		
	
	/**
	 * Returns the list of editable fields from the managed record
	 * Either as set in the component config or the default ones
	 * 
	 * @return array 
	 */
	function getRecordEditableFields()
	{
		$fields = $this->component->getConfig('editableFields');
		if ( $fields == null ) $fields = $this->getDefaultRecordEditableFields();
		
		return $fields;
	}

	/**
	 *
	 * @param type $recordID
	 * @return type 
	 */
	function getRecordHTMLFormFields( $recordID = 0 )
	{
		$config = $this->component->getConfig();
		$recordCMSDataFields = GridFieldBulkEditingHelper::getModelCMSDataFields( $config, $this->gridField->list->dataClass );
		
		//@TODO: if editableFields given use them with filterNonEditableRecordsFields()
		// otherwise go through getModelFilteredDataFields
		
		
		$recordCMSDataFields = GridFieldBulkEditingHelper::filterNonEditableRecordsFields($config, $recordCMSDataFields);
		
		$config['fileRelationName'] = $config['fileRelationName'] ? $config['fileRelationName'] : $this->component->getDefaultFileRelationName($this->gridField);
		
		$recordCMSDataFields = GridFieldBulkEditingHelper::getModelFilteredDataFields($config, $recordCMSDataFields);
		$recordCMSDataFields = GridFieldBulkEditingHelper::populateCMSDataFields($recordCMSDataFields, $this->gridField->list->dataClass, $recordID);
		$formFieldsHTML = GridFieldBulkEditingHelper::dataFieldsToHTML($recordCMSDataFields);
		$formFieldsHTML = GridFieldBulkEditingHelper::escapeFormFieldsHTML($formFieldsHTML, $recordID);
		
		return $formFieldsHTML;
	}


	public function getUploadField()
	{
		return $this->component->bulkUploadField($this->gridField);
	}

	
	
	/**
	 * Noop.
	 */
	public function index($request)
	{	
		return;
	}

	
	/**
	 * Process upload through UploadField,
	 * creates new record and link newly uploaded file
	 * adds record to GrifField relation list
	 * and return image/file data and record edit form
	 *
	 * @param SS_HTTPRequest $request
	 * @return string json
	 */
	public function upload(SS_HTTPRequest $request)
	{
		//create record
		$recordClass = $this->gridField->list->dataClass;		
		$record = Object::create($recordClass);
		$record->write();

		// passes the current gridfield-instance to a call-back method on the new object
		//$record->extend("onBulkImageUpload", $this->gridField);
		$record->extend("onBulkFileUpload", $this->gridField);

		//get uploadField and process upload
		$uploadField = $this->getUploadField();
		$uploadField->setRecord($record);

    $fileRelationName = $uploadField->getName();
    $uploadResponse   = $uploadField->upload($request);

		//get uploaded File response datas
		$uploadResponse = Convert::json2array( $uploadResponse->getBody() );
		$uploadResponse = array_shift( $uploadResponse );
		
		// Attach the file to record.				
		$record->{"{$fileRelationName}ID"} = $uploadResponse['id'];					
		$record->write();

		// attached record to gridField relation
		$this->gridField->list->add($record->ID);
		
		// fetch uploadedFile record and sort out previewURL
		// update $uploadResponse datas in case changes happened onAfterWrite()
		$uploadedFile = DataObject::get_by_id( $this->component->getFileRelationClassName($this->gridField), $uploadResponse['id'] );
		if ( $uploadedFile )
		{
			$uploadResponse['name'] = $uploadedFile->Name;
			$uploadResponse['url'] = $uploadedFile->getURL();

			if ( $uploadedFile instanceof Image )
			{
				$uploadResponse['thumbnail_url'] = $uploadedFile->CroppedImage(30,30)->getURL();
			}
			else{
				$uploadResponse['thumbnail_url'] = $uploadedFile->Icon();
			}

			// check if our new record has a Title, if not create one automatically
			$title = $record->getTitle();
			if ( !$title || $title === $record->ID )
			{
				$title = basename($uploadedFile->getFilename());

				if ( $record->hasDatabaseField('Title') )
				{
					$record->Title = $title;
					$record->write();
				}
				else if ($record->hasDatabaseField('Name')){
					$record->Name = $title;
					$record->write();
				}
			}
		}

		// Collect all data for JS template
		$return = array_merge($uploadResponse, array(
			'record' => array(
				'id' => $record->ID
			)
		));
				
		$response = new SS_HTTPResponse(Convert::raw2json(array($return)));
		$response->addHeader('Content-Type', 'text/json');
		return $response;		
	}


	public function select(SS_HTTPRequest $request)
	{
		$uploadField = $this->getUploadField();
		return $uploadField->handleSelect($request);
	}


	public function attach(SS_HTTPRequest $request)
	{
		$uploadField = $this->getUploadField();
		return $uploadField->attach($request);
	}


	public function fileexists(SS_HTTPRequest $request)
	{
		$uploadField = $this->getUploadField();
		return $uploadField->fileexists($request);
	}

	
	/**
	 * Update a record with the newly edited fields
	 * 
	 * @param SS_HTTPRequest $request
	 * @return string 
	 */
	public function update(SS_HTTPRequest $request)
	{
    $data = GridFieldBulkEditingHelper::unescapeFormFieldsPOSTData($request->requestVars());
		$record = DataObject::get_by_id($this->gridField->list->dataClass, $data['ID']);
				
		foreach($data as $field => $value)
		{						
			if ( $record->hasMethod($field) ) {				
				$list = $record->$field();
				$list->setByIDList( $value );
			}else{
				$record->setCastedField($field, $value);
			}
		}		
		$record->write();
		
		return '{done:1,recordID:'.$data['ID'].'}';
	}
	
	/**
	 * Delete the Image Object and File as well as the DataObject
	 * according to the ID sent from the form
	 * 
	 * @param SS_HTTPRequest $request
	 * @return string json 
	 */
	public function cancel(SS_HTTPRequest $request)
	{
		$data = GridFieldBulkEditingHelper::unescapeFormFieldsPOSTData($request->requestVars());
		$return = array();
		
		$recordClass = $this->gridField->list->dataClass;
		$record = DataObject::get_by_id($recordClass, $data['ID']);	
		
		$imageField = $this->getFileRelationName();
		$imageID = $record->$imageField.'ID';
		$image = DataObject::get_by_id('Image', $imageID);
		
		$return[$data['ID']]['imageID'] = $imageID;			
		$return[$data['ID']]['deletedDataObject'] = DataObject::delete_by_id($recordClass, $data['ID']);
			
		$return[$data['ID']]['deletedFormattedImages'] = $image->deleteFormattedImages();
		$return[$data['ID']]['deletedImageFile'] = unlink( Director::getAbsFile($image->getRelativePath()) );
		
		
		$response = new SS_HTTPResponse(Convert::raw2json($return));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;		
	}
}
