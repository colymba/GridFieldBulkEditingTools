<?php
/**
 * Handles request from the GridFieldBulkImageUpload component 
 * 
 * Handles:
 * * Form creation
 * * file upload
 * * editing and cancelling records
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
		'$Action!' => '$Action'/*,
		'' => 'uploadForm'*/
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
		$fieldName = $this->component->getRecordImageField();
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
		$fields = $this->component->getRecordEditableFields();
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
	 * Return a list of the GridField managed DataObject's editable fields: (HTML)Text, (HTML)Varchar and Enum fields
	 * 
	 * @return array 
	 */
	function getDefaultRecordEditableFields()
	{
		$recordClass = $this->gridField->list->dataClass;
		$recordDbFields = Config::inst()->get($recordClass, 'db', Config::UNINHERITED);
		
		$editableFields = array();
		foreach ( $recordDbFields as $field => $type )
		{
			if ( preg_match( '/(Text|Varchar|Enum)/i', $type ) > 0 ) {
				array_push($editableFields, $field);
			}
		}
		
		return $editableFields;
	}
	
	/**
	 * Return the CMS edit field for a given name. As set in the GridField managed DataObject getCMSFields method
	 * 
	 * @param string $fieldName
	 * @return FormField 
	 */
	function getFieldEditForm($fieldName)
	{		
		if ( !$this->recordCMSFieldList ) {
			$recordClass = $this->gridField->list->dataClass;
			$this->recordCMSFieldList = singleton($recordClass)->getCMSFields();
		}		
		
		$field = $this->recordCMSFieldList->fieldByName($fieldName);
				
		if ( !$field ) {
			$fields = $this->recordCMSFieldList->dataFields();
			$field = $fields[$fieldName];
		}
		
		return $field;
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
		
		$html = "
		<a id=\"bulkImageUploadUpdateBtn\" class=\"cms-panel-link action ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary\" data-icon=\"accept\" data-url=\"".$this->Link('update')."\" href=\"#\">
			Save All
		</a>";
		$actions->push(new LiteralField('savebutton', $html));
		
		
		if($crumbs && $crumbs->count()>=2)
		{			
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
		$form->addExtraClass('center cms-edit-form cms-content');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm Content');
		
		if($crumbs && $crumbs->count()>=2){
			$form->Backlink = $one_level_up->Link;
		}
		
		// this actually fixes the JS Requirements issue.
		// Calling forTemplate() before other requirements forces SS to add the Form's X-Include-JS before
		$formHTML = $form->forTemplate();
				
		Requirements::javascript('GridFieldBulkImageUpload/javascript/GridFieldBulkImageUpload.js');	
		Requirements::css('GridFieldBulkImageUpload/css/GridFieldBulkImageUpload.css');
		Requirements::javascript('GridFieldBulkImageUpload/javascript/GridFieldBulkImageUpload_downloadtemplate.js');		
		
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
					$record->setField($this->getRecordImageField(), $file->ID);					
					$record->write();
					
					
					// collect all editable fields forms					
					$recordEditableFieldsForms = array();
					foreach ( $this->getRecordEditableFields() as $editableFieldName )
					{
						$formField = $this->getFieldEditForm($editableFieldName);
						if ( $formField ) {
							array_push($recordEditableFieldsForms, $this->parseFieldHTMLWithRecordID($formField->FieldHolder(), $record->ID) );
						}						
					}
					
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
							'fields' => $recordEditableFieldsForms
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
		$data = $this->getParsedPostData($request->requestVars());
		
		$recordClass = $this->gridField->list->dataClass;
		$record = DataObject::get_by_id($recordClass, $data['ID']);
				
		foreach($data as $field => $value)
		{
			$record->setField($field, $value);
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
		$data = $this->getParsedPostData($request->requestVars());
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
	 * Simple function that replace the 'record_XX_' off of the ID field name
	 * prefix needed since it was taken for a pageID if sent as is as well as fixing other things
	 * 
	 * @param array $data
	 * @return array 
	 */
	function getParsedPostData(array $data)
	{
		$return = array();
		
		foreach( $data as $key => $val)
		{			
			$return[ preg_replace( '/record_(\d+)_(\w+)/i', '$2', $key) ] = $val;
		}
		
		return $return;
	}
	
	/**
	 * Add a unique prefix to sensitive HTML attributes (ID, FOR, NAME)
	 * Fixes rendering issue (i.e. dropdown fields) and IDs being mistaken for page IDs
	 * 
	 * @param string $html
	 * @param string/int $id
	 * @return string 
	 */
	function parseFieldHTMLWithRecordID($html, $id)
	{
		$prefix = 'record_'.$id.'_';
		return str_ireplace ( array('id="', 'for="', 'name="'),
													array('id="'.$prefix, 'for="'.$prefix, 'name="'.$prefix), 
													$html);
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