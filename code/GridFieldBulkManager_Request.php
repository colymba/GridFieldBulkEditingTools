<?php
/**
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkManager_Request extends RequestHandler {
	
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
		return Controller::join_links($this->gridField->Link(), 'bulkediting', $action);
	}
	
	
	public function edit(SS_HTTPRequest $request)
	{
		$recordList = $this->getPOSTRecordList($request);
		
		$crumbs = $this->Breadcrumbs();
		if($crumbs && $crumbs->count()>=2) $one_level_up = $crumbs->offsetGet($crumbs->count()-2);
		
		$actions = new FieldList();		
		
		$actions->push(
			FormAction::create('SaveAll', 'Save All')
				->setAttribute('id', 'bulkEditingUpdateBtn')
				->addExtraClass('ss-ui-action-constructive cms-panel-link')
				->setAttribute('data-icon', 'accept')
				->setAttribute('data-url', $this->Link('update'))
				->setUseButtonTag(true)
		);
		
		if($crumbs && $crumbs->count()>=2)
		{			
			$actions->push(
				FormAction::create('SaveAndFinish', 'Save All & Finish')
					->setAttribute('id', 'bulkEditingUpdateFinishBtn')
					->addExtraClass('ss-ui-action-constructive cms-panel-link')
					->setAttribute('data-icon', 'accept')
					->setAttribute('data-url', $this->Link('update'))
					->setAttribute('data-return-url', $one_level_up->Link)
					->setUseButtonTag(true)
			);
		}	
		
		$actions->push(
			FormAction::create('Cancel', 'Cancel & Delete All')
				->setAttribute('id', 'bulkEditingUpdateCancelBtn')
				->addExtraClass('ss-ui-action-destructive cms-panel-link')
				->setAttribute('data-icon', 'decline')
				->setAttribute('data-url', $this->Link('cancel'))
				->setUseButtonTag(true)
		);
		
		/*
		 * ********************************************************************
		 */
		
		$editedRecordList = new FieldList();
		$config = $this->component->getConfig();
				
		foreach ( $recordList as $id )
		{						
			$recordCMSDataFields = GridFieldBulkEditingHelper::getModelCMSDataFields( $config, $this->gridField->list->dataClass );
			$recordCMSDataFields = GridFieldBulkEditingHelper::getModelFilteredDataFields($config, $recordCMSDataFields);
			$recordCMSDataFields = GridFieldBulkEditingHelper::populateCMSDataFields( $recordCMSDataFields, $this->gridField->list->dataClass, $id );
			
			$recordCMSDataFields['ID'] = new HiddenField('ID', '', $id);			
			$recordCMSDataFields = GridFieldBulkEditingHelper::escapeFormFieldsName( $recordCMSDataFields, $id );
			
			$editedRecordList->push(
				ToggleCompositeField::create(
					'GFBM_'.$id,
					'#'.$id.': '.DataObject::get_by_id($this->gridField->list->dataClass, $id)->getTitle(),					
					array_values($recordCMSDataFields)
				)->setHeadingLevel(4)
				->addExtraClass('bulkEditingFieldHolder')
			);
		}
		
		/*
		 * ********************************************************************
		 */
		
		$form = new Form(
			$this,
			'bulkEditingForm',
			$editedRecordList,
			$actions
		);
		
		$form->setTemplate('LeftAndMain_EditForm');
		$form->addExtraClass('center cms-content');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm Content');
		
		if($crumbs && $crumbs->count()>=2){
			$form->Backlink = $one_level_up->Link;
		}
		
		$formHTML = $form->forTemplate();
				
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkManager.js');	
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');		
		
		$response = new SS_HTTPResponse($formHTML);
		$response->addHeader('Content-Type', 'text/plain');
		$response->addHeader('X-Title', 'SilverStripe - Bulk '.$this->gridField->list->dataClass.' Editing');
		return $response;
	}
	
	/**
	 * Saves the changes made in the bulk edit into the dataObject
	 * 
	 * @param SS_HTTPRequest $request
	 * @return JSON 
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
	 *
	 * @param SS_HTTPRequest $request
	 * @return \SS_HTTPResponse 
	 */
	public function unlink(SS_HTTPRequest $request)
	{
		$recordList = $this->getPOSTRecordList($request);
		$recordClass = $this->gridField->list->dataClass;
		$recordForeignKey = $this->gridField->list->foreignKey;
		//$recordForeignID = $this->gridField->list->foreignID;
		$result = array();
		
		foreach ( $recordList as $id )
		{			
			$record = DataObject::get_by_id($recordClass, $id);
			$res = $record->setField($recordForeignKey, 0);
			$record->write();
			array_push($result, array($id => $res));
		}
		
		$response = new SS_HTTPResponse(Convert::raw2json(array($result)));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;
	}
	
	/**
	 *
	 * @param SS_HTTPRequest $request
	 * @return \SS_HTTPResponse 
	 */
	public function delete(SS_HTTPRequest $request)
	{
		$recordList = $this->getPOSTRecordList($request);
		$recordClass = $this->gridField->list->dataClass;
		$result = array();
		
		foreach ( $recordList as $id )
		{			
			$res = DataObject::delete_by_id($recordClass, $id);
			array_push($result, array($id => $res));
		}
		
		$response = new SS_HTTPResponse(Convert::raw2json(array($result)));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;	
	}
	
	
	public function getPOSTRecordList(SS_HTTPRequest $request)
	{
		$recordList = $request->requestVars();
		return $recordList['records'];		 
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
				'Title' => 'Bulk Editing',
				'Link' => false
			)));
		return $items;
	}
}