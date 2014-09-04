<?php
/**
 * Handles request from the GridFieldBulkUpload component
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 * @subpackage BulkUpload
 */
class GridFieldBulkUpload_Request extends RequestHandler
{	
  /**
	 * Gridfield instance
	 * @var GridField 
	 */
	protected $gridField;
	

	/**
	 * Bulk upload component
	 * @var GridFieldBulkUpload
	 */
	protected $component;
	

	/**
	 * Gridfield Form controller
	 * @var Controller
	 */
	protected $controller;
	

	/**
	 * RequestHandler allowed actions
	 * @var array
	 */
	private static $allowed_actions = array(
		'upload', 'select', 'attach', 'fileexists'
	);


	/**
	 * RequestHandler url => action map
	 * @var array
	 */
	private static $url_handlers = array(
		'$Action!' => '$Action'
	);
	

	/**
	 * Handler's constructor
	 * 
	 * @param GridFIeld $gridField
	 * @param GridField_URLHandler $component
	 * @param Controller $controller
	 */
	public function __construct($gridField, $component, $controller)
	{
		$this->gridField = $gridField;
		$this->component = $component;
		$this->controller = $controller;		
		parent::__construct();
	}


	/**
	 * Return the original component's UploadField
	 * 
	 * @return UploadField UploadField instance as defined in the component
	 */
	public function getUploadField()
	{
		return $this->component->bulkUploadField($this->gridField);
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
		$record->extend("onBulkUpload", $this->gridField);
		if ( $record->hasMethod('onBulkImageUpload') )
		{
			Deprecation::notice('2.0', '"onBulkImageUpload" callback is deprecated, use "onBulkUpload" instead.');
			$record->extend("onBulkImageUpload", $this->gridField);
		}

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
		
		// JS Template Data
		$responseData = $this->newRecordJSTemplateData($record, $uploadResponse);
				
		$response = new SS_HTTPResponse(Convert::raw2json(array($responseData)));
		$this->contentTypeNegotiation($response);
		
		return $response;
	}


	/**
	 * Updates the Upload/Attach response from the UploadField
	 * with the new DataObject records for the JS template
	 * 
	 * @param  DataObject $record Newly create DataObject record
	 * @param  array $uploadResponse Upload or Attach response from UploadField
	 * @return array                 Updated $uploadResponse with $record data
	 */
	protected function newRecordJSTemplateData(DataObject &$record, &$uploadResponse)
	{
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
				if ( $record->hasDatabaseField('Title') )
				{
					$record->Title = $uploadedFile->Title;
					$record->write();
				}
				else if ($record->hasDatabaseField('Name')){
					$record->Name = $uploadedFile->Title;
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

		return $return;
	}


	/**
	 * Pass select request to UploadField
	 * 
	 * @link UploadField->select()
	 */
	public function select(SS_HTTPRequest $request)
	{
		/*
		$uploadField = $this->getUploadField();
		return $uploadField->handleSelect($request);
		*/
		$uploadField = $this->getUploadField();
		return UploadField_SelectHandler::create($this, $uploadField->getFolderName());
	}


	/**
	 * Pass getRelationAutosetClass request to UploadField
	 * Used by select dialog
	 * 
	 * @link UploadField->getRelationAutosetClass()
	 */
	public function getRelationAutosetClass($default = 'File')
	{
		$uploadField = $this->getUploadField();
		return $uploadField->getRelationAutosetClass($default);
	}
	

	/**
	 * Pass getAllowedMaxFileNumber request to UploadField
	 * Used by select dialog
	 * 
	 * @link UploadField->getAllowedMaxFileNumber()
	 */
	public function getAllowedMaxFileNumber()
	{
		$uploadField = $this->getUploadField();
		return $uploadField->getAllowedMaxFileNumber();
	}


	/**
	 * Retrieve Files to be attached
	 * and generated DataObjects for each one
	 * 
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function attach(SS_HTTPRequest $request)
	{
    $uploadField     = $this->getUploadField();
    $attachResponses = $uploadField->attach($request);
    $attachResponses = json_decode($attachResponses->getBody(), true);

    $fileRelationName = $uploadField->getName();
    $recordClass      = $this->gridField->list->dataClass;
    $return           = array();

		foreach ($attachResponses as $attachResponse)
		{
			// create record
			$record = Object::create($recordClass);
			$record->write();
			$record->extend("onBulkUpload", $this->gridField);

			// attach file
			$record->{"{$fileRelationName}ID"} = $attachResponse['id'];					
			$record->write();

			// attached record to gridField relation
			$this->gridField->list->add($record->ID);

			// JS Template Data
			$responseData = $this->newRecordJSTemplateData($record, $attachResponse);

			// add to returned dataset
			array_push($return, $responseData);
		}

		$response = new SS_HTTPResponse(Convert::raw2json($return));
		$this->contentTypeNegotiation($response);

		return $response;
	}


	/**
	 * Pass fileexists request to UploadField
	 * 
	 * @link UploadField->fileexists()
	 */
	public function fileexists(SS_HTTPRequest $request)
	{
		$uploadField = $this->getUploadField();
		return $uploadField->fileexists($request);
	}


	/**
	 * @param string $action
	 * @return string
	 */
	public function Link($action = null) {
		return Controller::join_links($this->gridField->Link(), '/bulkupload/', $action);
	}

	/**
	 * Sets response 'Content-Type' depending on browser capabilities
	 * e.g. IE needs text/plain for iframe transport
	 * https://github.com/blueimp/jQuery-File-Upload/issues/1795
	 * @param  SS_HTTPResponse $response HTTP Response to set content-type on
	 */
	protected function contentTypeNegotiation(&$response)
	{
		if (isset($_SERVER['HTTP_ACCEPT']) && ((strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || $_SERVER['HTTP_ACCEPT'] === '*/*' )) {
		{
			$response->addHeader('Content-Type', 'application/json');
		}else{
			$response->addHeader('Content-Type', 'text/plain');
		}
	}
}
