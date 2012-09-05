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
	static $url_handlers = array(
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
	 * Returns the name of the Image field name from the managed record
	 * Either as set in the component config or the default one
	 * 
	 * @return string 
	 */
	function getRecordImageField()
	{
		$fieldName = $this->component->getConfig('imageFieldName');
		if ( $fieldName == null ) $fieldName = $this->getDefaultRecordImageField();
		
		return $fieldName;
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
	 * Get the first has_one Image realtion from the GridField managed DataObject
	 * 
	 * @return string 
	 */
	function getDefaultRecordImageField()
	{
		$recordClass = $this->gridField->list->dataClass;
		$recordHasOneFields = Config::inst()->get($recordClass, 'has_one', Config::UNINHERITED);
		
		$imageField = null;
		foreach( $recordHasOneFields as $field => $type )
		{
			if ( $type == 'Image' ) {
				$imageField = $field . 'ID';
				break;
			}
		}
		
		return $imageField;
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
		
		if ( $config['imageFieldName'] == null ) $config['imageFieldName'] = $this->getDefaultRecordImageField();
		
		$recordCMSDataFields = GridFieldBulkEditingHelper::getModelFilteredDataFields($config, $recordCMSDataFields);
		$formFieldsHTML = GridFieldBulkEditingHelper::dataFieldsToHTML($recordCMSDataFields);
		$formFieldsHTML = GridFieldBulkEditingHelper::escapeFormFieldsHTML($formFieldsHTML, $recordID);
		
		return $formFieldsHTML;
	}
	
	/**
	 * Default and main action that returns the upload form etc...
	 * 
	 * @return string Form's HTML
	 */
	public function index()
	{	
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/AssetUploadField.js');
		Requirements::css(FRAMEWORK_DIR . '/css/AssetUploadField.css');				
				
		$crumbs = $this->Breadcrumbs();
		if($crumbs && $crumbs->count()>=2) $one_level_up = $crumbs->offsetGet($crumbs->count()-2);
		
		$actions = new FieldList();		
		
		$actions->push(
			FormAction::create('SaveAll', 'Save All')
				->setAttribute('id', 'bulkImageUploadUpdateBtn')
				->addExtraClass('ss-ui-action-constructive cms-panel-link')
				->setAttribute('data-icon', 'accept')
				->setAttribute('data-url', $this->Link('update'))
				->setUseButtonTag(true)
		);
		
		if($crumbs && $crumbs->count()>=2)
		{			
			$actions->push(
				FormAction::create('SaveAndFinish', 'Save All & Finish')
					->setAttribute('id', 'bulkImageUploadUpdateFinishBtn')
					->addExtraClass('ss-ui-action-constructive cms-panel-link')
					->setAttribute('data-icon', 'accept')
					->setAttribute('data-url', $this->Link('update'))
					->setAttribute('data-return-url', $one_level_up->Link)
					->setUseButtonTag(true)
			);
		}	
		
		$actions->push(
			FormAction::create('Cancel', 'Cancel & Delete All')
				->setAttribute('id', 'bulkImageUploadUpdateCancelBtn')
				->addExtraClass('ss-ui-action-destructive cms-panel-link')
				->setAttribute('data-icon', 'decline')
				->setAttribute('data-url', $this->Link('cancel'))
				->setUseButtonTag(true)
		);
		
		
		$uploadField = UploadField::create('BulkImageUploadField', '');
		$uploadField->setConfig('previewMaxWidth', 40);
		$uploadField->setConfig('previewMaxHeight', 30);
		
		$uploadField->addExtraClass('ss-assetuploadfield');
		$uploadField->removeExtraClass('ss-uploadfield');
		
		$uploadField->setTemplate('AssetUploadField');
		$uploadField->setConfig('downloadTemplateName','GridFieldBulkImageUpload_downloadtemplate');
				
		
		$uploadField->setConfig('url', $this->Link('upload'));
		
		//$uploadField->setFolderName(ASSETS_DIR);
		
		
		$form = new Form(
			$this,
			'bulkImageUploadForm',
			new FieldList(
				$uploadField
			),
			$actions
		);
				
		$form->setTemplate('LeftAndMain_EditForm');
		//$form->addExtraClass('center cms-edit-form cms-content');
		$form->addExtraClass('center cms-content');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm Content');
		
		if($crumbs && $crumbs->count()>=2){
			$form->Backlink = $one_level_up->Link;
		}
		
		// this actually fixes the JS Requirements issue.
		// Calling forTemplate() before other requirements forces SS to add the Form's X-Include-JS before
		$formHTML = $form->forTemplate();
				
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkImageUpload.js');	
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkImageUpload.css');
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkImageUpload_downloadtemplate.js');		
		
		$response = new SS_HTTPResponse($formHTML);
		$response->addHeader('Content-Type', 'text/plain');
		$response->addHeader('X-Title', 'SilverStripe - Bulk '.$this->gridField->list->dataClass.' Image Upload');
		return $response;
	}
	
	/**
	 * Process image upload and Object creation
	 * Create new DataObject and add image relation
	 * returns Image data and editable Fields forms
	 * 
	 * Overides UploadField's upload method by Zauberfisch
	 * Kept original file upload/processing but removed unessesary processing
	 * and adds DataObject creation and editableFields processing
	 * 
	 * @author Zauberfisch original upload() method
	 * @see UploadField->upload()
	 * @param SS_HTTPRequest $request
	 * @return string json
	 */
	public function upload(SS_HTTPRequest $request)
	{
		$recordClass = $this->gridField->list->dataClass;
		$recordForeignKey = $this->gridField->list->foreignKey;
		$recordForeignID = $this->gridField->list->foreignID;
		
		$record = Object::create($recordClass);
		$record->setField($recordForeignKey, $recordForeignID);
		$record->write();
		
		$upload = new Upload();		
		$tmpfile = $request->postVar('BulkImageUploadField');
		
		// Check if the file has been uploaded into the temporary storage.
		if (!$tmpfile) {
			$return = array('error' => _t('UploadField.FIELDNOTSET', 'File information not found'));
		} else {
			$return = array(
				'name' => $tmpfile['name'],
				'size' => $tmpfile['size'],
				'type' => $tmpfile['type'],
				'error' => $tmpfile['error']
			);
		}
		
		// Process the uploaded file
		if (!$return['error']) {
			$fileObject = Object::create('Image');

			// Get the uploaded file into a new file object.
			try {
				$upload->loadIntoFile($tmpfile, $fileObject, $this->component->getConfig('folderName'));
			} catch (Exception $e) {
				// we shouldn't get an error here, but just in case
				$return['error'] = $e->getMessage();
			}

			if (!$return['error']) {
				if ($upload->isError()) {
					$return['error'] = implode(' '.PHP_EOL, $upload->getErrors());
				} else {
					$file = $upload->getFile();

					// Attach the file to the related record.		
					$record->setField($this->getRecordImageField(), $file->ID);					
					$record->write();
					
					//get record's CMS Fields
					$recordEditableFormFields = $this->getRecordHTMLFormFields( $record->ID );
					
					// Collect all output data.
					$return = array_merge($return, array(
						'id' => $file->ID,
						'name' => $file->getTitle() . '.' . $file->getExtension(),
						'url' => $file->getURL(),
						'preview_url' => $file->setHeight(55)->Link(),
						'thumbnail_url' => $file->SetRatioSize(40,30)->getURL(),
						'size' => $file->getAbsoluteSize(),
						//'buttons' => $file->UploadFieldFileButtons,
						'record' => array(
							'ID' => $record->ID,
							'fields' => $recordEditableFormFields
						)
					));
										
				}
			}
		}
				
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
		
		$imageField = $this->getRecordImageField();
		$imageID = $record->$imageField;
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