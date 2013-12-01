<?php
/**
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkActionEditHandler extends GridFieldBulkActionHandler
{	
	/**
	 * List of action handling methods
	 */
	private static $allowed_actions = array('edit', 'update');


	/**
	 * URL handling rules.
	 */
	private static $url_handlers = array(
		//'$Action!' => '$Action'
		'bulkedit/update' => 'update',
		'bulkedit' => 'edit'
	);
	
	
	/**
	 * Creates and return a Form
	 * with a collection of editable fields for each selected records
	 * 
	 * @return Form Edit form with all the selected records
	 */
	public function edit()
	{
		$recordList = $this->getRecordIDList();
		
		$crumbs = $this->Breadcrumbs();
		if($crumbs && $crumbs->count()>=2) $one_level_up = $crumbs->offsetGet($crumbs->count()-2);
		
		$actions = new FieldList();		
		
		$actions->push(
			FormAction::create('SaveAll', _t('GridFieldBulkTools.SAVE_BTN_LABEL', 'Save All'))
				->setAttribute('id', 'bulkEditingUpdateBtn')
				->addExtraClass('ss-ui-action-constructive cms-panel-link')
				->setAttribute('data-icon', 'accept')
				->setAttribute('data-url', $this->gridField->Link('bulkaction/bulkedit/update'))
				->setUseButtonTag(true)
		);
		
		$actions->push(
			FormAction::create('Cancel', _t('GridFieldBulkTools.CANCEL_BTN_LABEL', 'Cancel & Delete All'))
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
		Requirements::add_i18n_javascript(BULK_EDIT_TOOLS_PATH . '/javascript/lang');	
		
		$response = new SS_HTTPResponse($formHTML);
		$response->addHeader('Content-Type', 'text/plain');
		$response->addHeader('X-Title', 'SilverStripe - Bulk '.$this->gridField->list->dataClass.' Editing');
		
		if($this->request->isAjax())
		{
			return $response;
		}
		else {
			$controller = $this->getToplevelController();
			// If not requested by ajax, we need to render it within the controller context+template
			return $controller->customise(array(
				'Content' => $response->getBody(),
			));	
		}
	}

	
	/**
	 * Saves the changes made in the bulk edit into the dataObject
	 * 
	 * @return JSON 
	 */
	public function update()
	{		
		$data = GridFieldBulkEditingHelper::unescapeFormFieldsPOSTData($this->request->requestVars());
		$record = DataObject::get_by_id($this->gridField->list->dataClass, $data['ID']);
				
		foreach($data as $field => $value)
		{						
			if ( $record->hasMethod($field) )
			{				
				$list = $record->$field();
				$list->setByIDList( $value );
			}
			else{
				$record->setCastedField($field, $value);
			}
		}		
		$record->write();
		
		return '{done:1,recordID:'.$data['ID'].'}';
	}
}