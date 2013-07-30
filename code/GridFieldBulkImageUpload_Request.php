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
class GridFieldBulkImageUpload_Request extends RequestHandler {
	
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
		'index', 'upload', 'update', 'cancel'
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
	 */
	public function Link($action = null) {
		return Controller::join_links($this->gridField->Link(), 'bulkimageupload', $action);
	}
	
	/**
	 * Get the first has_one Image relation from the GridField managed DataObject
	 * i.e. 'MyImage' => 'Image' will return 'MyImage'
	 * 
	 * @return string Name of the $has_one relation
	 */
	function getDefaultFileRelationName()
	{
		$recordClass = $this->gridField->list->dataClass;
		$recordHasOneFields = Config::inst()->get($recordClass, 'has_one', Config::INHERITED);
		
		$imageField = null;
		foreach( $recordHasOneFields as $field => $type )
		{
			if($type == 'Image' || is_subclass_of($type, 'Image')) {
				$imageField = $field;// . 'ID';
				break;
			}
		}
		
		return $imageField;
	}	

	/**
	 * Returns the name of the Image field name from the managed record
	 * Either as set in the component config or the default one
	 * 
	 * @return string 
	 */
	function getFileRelationName()
	{
		$fieldName = $this->component->getConfig('fileRelationName');
		if ( $fieldName == null ) $fieldName = $this->getDefaultFileRelationName();
		
		return $fieldName;
	}

	/**
	 * Return the ClassName of the fileRelation
	 * i.e. 'MyImage' => 'Image' will return 'Image'
	 * i.e. 'MyImage' => 'File' will return 'File'
	 *
	 * @return string file relation className
	 */
	private function getFileRelationClassName()
	{
		$recordClass        = $this->gridField->list->dataClass;
		$recordHasOneFields = Config::inst()->get($recordClass, 'has_one', Config::INHERITED);

		$fieldName = $this->getFileRelationName();
		if($fieldName != null)
		{
			return $recordHasOneFields[$fieldName];
		}
		else{
			return 'File';
		}		
	}
	
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
		
		if ( $config['fileRelationName'] == null ) $config['fileRelationName'] = $this->getDefaultFileRelationName();
		
		$recordCMSDataFields = GridFieldBulkEditingHelper::getModelFilteredDataFields($config, $recordCMSDataFields);
		$recordCMSDataFields = GridFieldBulkEditingHelper::populateCMSDataFields($recordCMSDataFields, $this->gridField->list->dataClass, $recordID);
		$formFieldsHTML = GridFieldBulkEditingHelper::dataFieldsToHTML($recordCMSDataFields);
		$formFieldsHTML = GridFieldBulkEditingHelper::escapeFormFieldsHTML($formFieldsHTML, $recordID);
		
		return $formFieldsHTML;
	}

	/**
	 * Creates and return the bulk upload form
	 *
	 * @return Form
	 */
	public function uploadForm($id = null, $fields = null)
	{
		$crumbs = $this->Breadcrumbs();
		if($crumbs && $crumbs->count()>=2)
		{
			$one_level_up = $crumbs->offsetGet($crumbs->count()-2);
			$one_level_up = $one_level_up->Link;			
		}

		$actions = new FieldList();

		$actions->push(
			FormAction::create('Finish', _t('GridFieldBulkTools.FINISH_BTN_LABEL', 'Finish'))
				->setAttribute('id', 'bulkImageUploadFinishBtn')				
				->addExtraClass('cms-panel-link')
				->setAttribute('data-icon', 'accept')
				->setAttribute('href', $one_level_up)
				->setUseButtonTag(true)
				->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
		);
		
		$actions->push(
			FormAction::create('SaveAll', _t('GridFieldBulkTools.SAVE_BTN_LABEL', 'Save All'))
				->setAttribute('id', 'bulkImageUploadUpdateBtn')
				->addExtraClass('ss-ui-action-constructive')
				->setAttribute('data-icon', 'accept')
				->setAttribute('data-url', $this->Link('update'))
				->setUseButtonTag(true)
				->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
		);
		
		$actions->push(
			FormAction::create('Cancel', _t('GridFieldBulkTools.CANCEL_BTN_LABEL', 'Cancel & Delete All'))
				->setAttribute('id', 'bulkImageUploadUpdateCancelBtn')
				->addExtraClass('ss-ui-action-destructive')
				->setAttribute('data-icon', 'decline')
				->setAttribute('data-url', $this->Link('cancel'))
				->setUseButtonTag(true)
				->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
		);

		
		/* *
		 * UploadField
		 */
		$fileRelationName = $this->getFileRelationName();
		$uploadField = UploadField::create($fileRelationName, '');
		$uploadField->setConfig('previewMaxWidth', 40);
		$uploadField->setConfig('previewMaxHeight', 30);		
		$uploadField->addExtraClass('ss-assetuploadfield');
		$uploadField->removeExtraClass('ss-uploadfield');		
		$uploadField->setTemplate('AssetUploadField');

		$uploadField->setDownloadTemplateName('colymba-gfbiu-uploadfield-downloadtemplate');		

		//always overwrite
		$uploadField->setOverwriteWarning(false);

		/* *
		 * UploadField configs
		 */
		//custom upload url
		$uploadField->setConfig('url', $this->Link('upload'));

		//max file size
		$maxFileSize = $this->component->getConfig('maxFileSize');
		if ( $maxFileSize !== null ) 
		{
			$uploadField->getValidator()->setAllowedMaxFileSize( $maxFileSize );
		}

		//upload dir
		$uploadDir = $this->component->getConfig('folderName');
		if ( $uploadDir !== null )
		{
			$uploadField->setFolderName($uploadDir);
		}	

		//sequential upload
		$uploadField->setConfig('sequentialUploads', $this->component->getConfig('sequentialUploads'));
		
		//Create form
		$form = new Form(
			$this,
			'uploadForm',
			new FieldList(
				$uploadField
			),
			$actions
		);
		
		if($one_level_up)
		{
			$form->Backlink = $one_level_up;
		}

		return $form;
	}
	
	/**
	 * Default and main action that returns the upload form etc...
	 * 
	 * @return string Form's HTML
	 */
	public function index($request)
	{	
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/AssetUploadField.js');
		Requirements::css(FRAMEWORK_DIR . '/css/AssetUploadField.css');

		$form = $this->uploadForm();
		$form->setTemplate('LeftAndMain_EditForm');
		$form->addExtraClass('cms-content center LeftAndMain'); //not using cms-edit-form to avoid btn being hooked with default handlers
		$form->setAttribute('data-pjax-fragment', 'Content');

		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js'); 

		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkImageUpload.js');	
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkImageUpload.css');
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkImageUpload_downloadtemplate.js');
		Requirements::add_i18n_javascript(BULK_EDIT_TOOLS_PATH . '/javascript/lang');

		if($request->isAjax())
		{			
			$response = new SS_HTTPResponse(
				Convert::raw2json(array( 'Content' => $form->forAjaxTemplate()->getValue() ))
			);
			$response->addHeader('X-Pjax', 'Content');
			$response->addHeader('Content-Type', 'text/json');
			$response->addHeader('X-Title', 'SilverStripe - Bulk '.$this->gridField->list->dataClass.' Image Upload');
			return $response;
		}
		else {
			$controller = $this->getToplevelController();
			return $controller->customise(array( 'Content' => $form ));
		}
	}
	
	/**
	 * Traverse up nested requests until we reach the first that's not a GridFieldDetailForm or GridFieldDetailForm_ItemRequest.
	 * The opposite of {@link Controller::curr()}, required because
	 * Controller::$controller_stack is not directly accessible.
	 * 
	 * @return Controller
	 */
	protected function getToplevelController() {
		$c = $this->controller;
		while($c && ($c instanceof GridFieldDetailForm_ItemRequest || $c instanceof GridFieldDetailForm)) {
			$c = $c->getController();
		}
		return $c;
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
		$record->extend("onBulkImageUpload", $this->gridField);

		//get uploadField and process upload
		$fileRelationName = $this->getFileRelationName();
		$uploadField = $this->uploadForm()->Fields()->fieldByName($fileRelationName);
		$uploadField->setRecord($record);
		$uploadResponse = $uploadField->upload( $request );

		//get uploaded File
		$uploadResponse = Convert::json2array( $uploadResponse->getBody() );
		$uploadResponse = array_shift( $uploadResponse );
		$uploadedFile = DataObject::get_by_id( $this->getFileRelationClassName(), $uploadResponse['id'] );
		$preview_url = $uploadedFile->setHeight(55)->Link();
		
		// Attach the file to record.				
		$record->{"{$fileRelationName}ID"} = $uploadedFile->ID;					
		$record->write();

		// attached record to gridField relation
		$this->gridField->list->add($record->ID);

		//get record's CMS Fields
		$recordEditableFormFields = $this->getRecordHTMLFormFields( $record->ID );
		
		// Collect all output data.
		$return = array_merge($uploadResponse, array(
			'preview_url' => $preview_url,
			'record' => array(
				'ID' => $record->ID,
				'fields' => $recordEditableFormFields
			)
		));
				
		$response = new SS_HTTPResponse(Convert::raw2json(array($return)));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;		
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
		
	/**
	 * Edited version of the GridFieldEditForm function
	 * adds the 'Bulk Upload' at the end of the crums
	 * 
	 * CMS-specific functionality: Passes through navigation breadcrumbs
	 * to the template, and includes the currently edited record (if any).
	 * see {@link LeftAndMain->Breadcrumbs()} for details.
	 * 
	 * @author SilverStripe original Breadcrumbs() method
	 * @see GridFieldDetailForm_ItemRequest
	 * @param boolean $unlinked
	 * @return ArrayData
	 */
	function Breadcrumbs($unlinked = false) {
		if(!$this->controller->hasMethod('Breadcrumbs')) return;

		$items = $this->controller->Breadcrumbs($unlinked);
		$items->push(new ArrayData(array(
				'Title' => 'Bulk Upload',
				'Link' => false
			)));
		return $items;
	}
}
