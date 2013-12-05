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
	 * Return a form for all the selected DataObject
	 * with their respective editable fields.
	 * 
	 * @return form Selected DataObject editable fields
	 */
	public function editForm()
	{
		$crumbs = $this->Breadcrumbs();
		if($crumbs && $crumbs->count()>=2)
		{
			$one_level_up = $crumbs->offsetGet($crumbs->count()-2);
		}
		
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
			FormAction::create('Cancel', _t('GridFieldBulkManager.CANCEL_BTN_LABEL', 'Cancel'))
				->setAttribute('id', 'bulkEditingUpdateCancelBtn')
				->addExtraClass('ss-ui-action-destructive cms-panel-link')
				->setAttribute('data-icon', 'decline')
				->setAttribute('href', $one_level_up->Link)
				->setUseButtonTag(true)
				->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
		);
		
		$recordList = $this->getRecordIDList();
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
		
		$form = new Form(
			$this,
			'bulkEditingForm',
			$editedRecordList,
			$actions
		);		
		
		if($crumbs && $crumbs->count()>=2){
			$form->Backlink = $one_level_up->Link;
		}

		return $form;
	}
	
	
	/**
	 * Creates and return the editing interface
	 * 
	 * @return string Form's HTML
	 */
	public function edit()
	{		
		$form = $this->editForm();
		$form->setTemplate('LeftAndMain_EditForm');
		$form->addExtraClass('center cms-content');
		$form->setAttribute('data-pjax-fragment', 'CurrentForm Content');
				
		Requirements::javascript(BULK_EDIT_TOOLS_PATH . '/javascript/GridFieldBulkEditingForm.js');	
		Requirements::css(BULK_EDIT_TOOLS_PATH . '/css/GridFieldBulkManager.css');	
		Requirements::add_i18n_javascript(BULK_EDIT_TOOLS_PATH . '/javascript/lang');	
		
		if($this->request->isAjax())
		{
			$response = new SS_HTTPResponse(
				Convert::raw2json(array( 'Content' => $form->forAjaxTemplate()->getValue() ))
			);
			$response->addHeader('X-Pjax', 'Content');
			$response->addHeader('Content-Type', 'text/json');
			$response->addHeader('X-Title', 'SilverStripe - Bulk '.$this->gridField->list->dataClass.' Editing');
			return $response;
		}
		else {
			$controller = $this->getToplevelController();
			return $controller->customise(array( 'Content' => $form ));
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