<?php

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
	 *	
	 * @var Array 
	 */
	protected $createdRecords;
	
	
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
		$this->createdRecords = array();
		parent::__construct();
	}

	/**
	 * Returns the URL for this RequestHandler
	 * @param String $action
	 * @return String 
	 */
	public function Link($action = null) {
		return Controller::join_links($this->gridField->Link(), 'bulkimageupload', $action);
	}
			
	/**
	 * Default and main action that returns the upload form etc...
	 * @param SS_HTTPRequest $request
	 * @return String Form HTML ???
	 */
	public function index(SS_HTTPRequest $request)
	{				
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/AssetUploadField.js');
		Requirements::css(FRAMEWORK_DIR . '/css/AssetUploadField.css');
				
		Requirements::javascript('GridFieldBulkImageUpload/javascript/GridFieldBulkImageUpload_downloadtemplate.js');
		Requirements::javascript('GridFieldBulkImageUpload/javascript/GridFieldBulkImageUpload.js');	
		Requirements::css('GridFieldBulkImageUpload/css/GridFieldBulkImageUpload.css');
		
		
		$actions = new FieldList();		
		/*
		$actions->push(FormAction::create('update', 'Finish')
				->setUseButtonTag(true)->addExtraClass('ss-ui-action-constructive')->addExtraClass('bulkImageUploadUpdateBtn')->setAttribute('data-icon', 'accept'));
		*/
		
		
		$html = "
		<a id=\"bulkImageUploadUpdateBtn\" class=\"cms-panel-link action ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary\" data-icon=\"accept\" data-url=\"".$this->Link('update')."\" href=\"#\">
			Save All
		</a>";
		$actions->push(new LiteralField('savebutton', $html));
		
		$crumbs = $this->Breadcrumbs();
		if($crumbs && $crumbs->count()>=2){
			$one_level_up = $crumbs->offsetGet($crumbs->count()-2);
			
			$html = "
			<a id=\"bulkImageUploadUpdateFinishBtn\" class=\"cms-panel-link action ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary\" data-icon=\"accept\" data-url=\"".$this->Link('update')."\" href=\"".$one_level_up->Link."\">
				Save All &amp; Finish
			</a>";
			$actions->push(new LiteralField('finishbutton', $html));
			
			$html = "                                       
			<a id=\"bulkImageUploadUpdateCancelBtn\" class=\"cms-panel-link delete ss-ui-action-destructive ss-ui-button ui-button ui-widget ui-state-default ui-button-text-icon-primary\" data-icon=\"decline\" data-url=\"".$this->Link('cancel')."\" href=\"#\">
				Cancel &amp; Delete All
			</a>";
			$actions->push(new LiteralField('cancelbutton', $html));
		}			
		

		$uploadField = UploadField::create('BulkImageUploadField', '');
		$uploadField->setConfig('previewMaxWidth', 40);
		$uploadField->setConfig('previewMaxHeight', 30);
		
		$uploadField->addExtraClass('ss-assetuploadfield');
		$uploadField->removeExtraClass('ss-uploadfield');
		
		$uploadField->setTemplate('AssetUploadField');
		$uploadField->setConfig('downloadTemplateName','GridFieldBulkImageUpload_downloadtemplate');
				
		
		$uploadField->setConfig('url', $this->Link('upload'));
		
		$uploadField->setFolderName(ASSETS_DIR);
		
		
		$form = new Form(
			$this,
			'bulkImageUploadForm',
			new FieldList(
				$uploadField
			),
			$actions
		);
		
		$form->setTemplate('LeftAndMain_EditForm');
		$form->addExtraClass('center cms-edit-form');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm Content');
		
		if($crumbs && $crumbs->count()>=2){
			$form->Backlink = $one_level_up->Link;
		}
		
		$response = new SS_HTTPResponse(Convert::raw2json(array('Content' => $form->forTemplate())));
		$response->addHeader('Content-Type', 'text/json');
		$response->addHeader('X-Title', 'SilverStripe - Bulk '.$this->gridField->list->dataClass.' Image Upload');
		return $response;		
	}
	
	
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
			/*
			$record->setField($this->component->getLabelFieldName(), $tmpfile['name']);
			$record->write();
			 */
		}
		
		// Process the uploaded file
		if (!$return['error']) {
			$fileObject = Object::create('Image');

			// Get the uploaded file into a new file object.
			try {
				$upload->loadIntoFile($tmpfile, $fileObject, 'Uploads/bulk');
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
					$record->setField($this->component->getRecordImageField(), $file->ID);
					$record->write();
					
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
							'fields' => $this->component->getRecordEditableFields()
						)
					));
				}
			}
		}
		
		array_push($this->createdRecords, $record->ID);
		
		$response = new SS_HTTPResponse(Convert::raw2json(array($return)));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;		
	}
	
	/**
	 * Update a record with the newly edited fields
	 * 
	 * @param SS_HTTPRequest $request
	 * @return String 
	 */
	public function update(SS_HTTPRequest $request)
	{		
		$data = $request->requestVars();
		$recordID = false;
		$recordFields = array();
		
		foreach( $data as $key => $val)
		{			
			if ( stripos($key, 'record_') !== false )
			{
				if ( $key == 'record_ID' )
				{
					$recordID = $val;
				}else{
					$recordFields[str_ireplace('record_', '', $key)] = $val;
				}
			}						
		}
		
		$recordClass = $this->gridField->list->dataClass;
		$record = DataObject::get_by_id($recordClass, $recordID);
				
		foreach($recordFields as $field => $value)
		{
			$record->setField($field, $value);
		}
		
		$record->write();
		
		return '{done:1,recordID:'.$recordID.'}';
	}
	
	/**
	 *
	 * @param SS_HTTPRequest $request
	 * @return String JSON 
	 */
	public function cancel(SS_HTTPRequest $request)
	{
		$data = $this->getParsedPostData($request->requestVars());
		$return = array();
		
		$recordClass = $this->gridField->list->dataClass;
		$record = DataObject::get_by_id($recordClass, $data['ID']);	
		$imageField = $this->component->getRecordImageField();
		
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
	 *
	 * @param array $data
	 * @return array 
	 */
	public function getParsedPostData(array $data)
	{
		$return = array();
		$fields = array();
		
		foreach( $data as $key => $val)
		{			
			if ( stripos($key, 'record_') !== false )
			{
				if ( $key == 'record_ID' )
				{
					$return['ID'] = $val;
				}else{
					$fields[str_ireplace('record_', '', $key)] = $val;
				}
			}						
		}
		
		$return['fields'] = $fields;
		
		return $return;
	}
	
	/**
	 * Traverse up nested requests until we reach the first that's not a GridFieldBulkImageUpload_Request.
	 * The opposite of {@link Controller::curr()}, required because
	 * Controller::$controller_stack is not directly accessible.
	 * 
	 * @return Controller
	 */
	/*
	protected function getToplevelController() {
		$c = $this->controller;
		while($c && $c instanceof GridFieldBulkImageUpload_Request) {
			$c = $c->getController();
		}
		return $c;
	}
	*/
	
	/**
	 * CMS-specific functionality: Passes through navigation breadcrumbs
	 * to the template, and includes the currently edited record (if any).
	 * see {@link LeftAndMain->Breadcrumbs()} for details.
	 * 
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