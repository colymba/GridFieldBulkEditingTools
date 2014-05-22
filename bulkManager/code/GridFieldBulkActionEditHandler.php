<?php
/**
 * Bulk action handler for editing records.
 * 
 * @author colymba
 * @package GridFieldBulkEditingTools
 * @subpackage BulkManager
 */
class GridFieldBulkActionEditHandler extends GridFieldBulkActionHandler
{	
	/**
	 * RequestHandler allowed actions
	 * @var array
	 */
	private static $allowed_actions = array('edit', 'update');


	/**
	 * RequestHandler url => action map
	 * @var array
	 */
	private static $url_handlers = array(
		'bulkedit/update' => 'update',
		'bulkedit' => 'edit'
	);


	/**
	 * Return a form for all the selected DataObjects
	 * with their respective editable fields.
	 * 
	 * @return Form Selected DataObjects editable fields
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
			FormAction::create('SaveAll', _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.SAVE_BTN_LABEL', 'Save all'))
				->setAttribute('id', 'bulkEditingUpdateBtn')
				->addExtraClass('ss-ui-action-constructive cms-panel-link')
				->setAttribute('data-icon', 'accept')
				->setAttribute('data-url', $this->gridField->Link('bulkaction/bulkedit/update'))
				->setUseButtonTag(true)
				->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
		);
		
		$actions->push(
			FormAction::create('Cancel', _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.CANCEL_BTN_LABEL', 'Cancel'))
				->setAttribute('id', 'bulkEditingUpdateCancelBtn')
				->addExtraClass('ss-ui-action-destructive cms-panel-link')
				->setAttribute('data-icon', 'decline')
				->setAttribute('href', $one_level_up->Link)
				->setUseButtonTag(true)
				->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
		);
		
    $recordList       = $this->getRecordIDList();
    $recordsFieldList = new FieldList();
    $config           = $this->component->getConfig();

    $editingCount     = count($recordList);
    $modelClass       = $this->gridField->getModelClass();
    $singleton        = singleton($modelClass);
    $titleModelClass  = (($editingCount > 1) ? $singleton->i18n_plural_name() : $singleton->i18n_singular_name());

    $headerText = _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.HEADER_TEXT',
    	'Editing {count} {class}',
			array(
				'count' => $editingCount,
				'class' => $titleModelClass
			)
		);
		$header = LiteralField::create(
			'bulkEditHeader',
			'<h1 id="bulkEditHeader">' . $headerText . '</h1>'
		);
		$recordsFieldList->push($header);

		$toggle = LiteralField::create('bulkEditToggle', '<span id="bulkEditToggle">' . _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.TOGGLE_ALL_LINK', 'Show/Hide all') . '</span>');
		$recordsFieldList->push($toggle);
				
		foreach ( $recordList as $id )
		{						
      $record              = DataObject::get_by_id($modelClass, $id);
      $recordEditingFields = $this->getRecordEditingFields($record);

			$toggleField = ToggleCompositeField::create(
				'RecordFields_'.$id,
				$record->getTitle(),
				$recordEditingFields
			)
			->setHeadingLevel(4)
			->setAttribute('data-id', $id)
			->addExtraClass('bulkEditingFieldHolder');

			$recordsFieldList->push($toggleField);
		}
		
		$form = new Form(
			$this,
			'BulkEditingForm',
			$recordsFieldList,
			$actions
		);		
		
		if($crumbs && $crumbs->count()>=2){
			$form->Backlink = $one_level_up->Link;
		}

		return $form;
	}


	/**
	 * Returns a record's populated form fields
	 * with all filtering done ready to be included in the main form
	 *
	 * @uses DataObject::getCMSFields()
	 * 
	 * @param  DataObject $record The record to get the fields from
	 * @return array              The record's editable fields
	 */
	private function getRecordEditingFields(DataObject $record)
	{
		$tempForm = Form::create(
			$this, "TempEditForm",
			$record->getCMSFields(),
			FieldList::create()
		);

		$tempForm->loadDataFrom($record);
		$fields = $tempForm->Fields();

		$fields = $this->filterRecordEditingFields($fields, $record->ID);

		return $fields;
	}


	/**
	 * Filters a records editable fields
	 * based on component's config
	 * and escape each field with unique name.
	 *
	 * See {@link GridFieldBulkManager} component for filtering config.
	 * 
	 * @param  FieldList $fields Record's CMS Fields
	 * @param  integer   $id     Record's ID, used fir unique name
	 * @return array             Filtered record's fields
	 */
	private function filterRecordEditingFields(FieldList $fields, $id)
	{
    $config              = $this->component->getConfig();
    $editableFields      = $config['editableFields'];
    $fieldsNameBlacklist = $config['fieldsNameBlacklist'];
    $readOnlyClasses     = $config['readOnlyFieldClasses'];

    // get all dataFields or just the ones allowed in config
		if ( $editableFields )
		{
			$dataFields = array();

			foreach ($editableFields as $fieldName)
			{
				array_push(
					$dataFields,
					$fields->dataFieldByName($fieldName)
				);
			}
		}
		else{
			$dataFields = $fields->dataFields();
		}

		// remove and/or set readonly fields in blacklists
		foreach ($dataFields as $name => $field)
		{
			if ( in_array($name, $fieldsNameBlacklist) )
			{
				unset( $dataFields[$name] );
			}
			else if ( in_array(get_class($field), $readOnlyClasses) )
			{
				$newField = $field->performReadonlyTransformation();
				$dataFields[$name] = $newField;
			}
		}

		// escape field names with unique prefix
		foreach ( $dataFields as $name => $field )
		{
      $field->Name       = 'record_' . $id . '_' . $name;
      $dataFields[$name] = $field;
		}
		
		return $dataFields;
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
				
		Requirements::javascript(BULKEDITTOOLS_MANAGER_PATH . '/javascript/GridFieldBulkEditingForm.js');	
		Requirements::css(BULKEDITTOOLS_MANAGER_PATH . '/css/GridFieldBulkEditingForm.css');	
		Requirements::add_i18n_javascript(BULKEDITTOOLS_PATH . '/lang/js');	
		
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
		$data = $this->request->requestVars();
		$return = array();
		$className = $this->gridField->list->dataClass;

		if ( isset($data['url']) ) unset($data['url']);
		if ( isset($data['cacheBuster']) ) unset($data['cacheBuster']);
		if ( isset($data['locale']) ) unset($data['locale']);

		foreach ($data as $recordID => $recordDataSet)
		{
			$record = DataObject::get_by_id($className, $recordID);
			foreach($recordDataSet as $recordData)
			{
				$field = preg_replace('/record_(\d+)_(\w+)/i', '$2', $recordData['name']);
				$value = $recordData['value'];

				if ( $record->hasMethod($field) )
				{				
					$list = $record->$field();
					$list->setByIDList($value);
				}
				else{
					$record->setCastedField($field, $value);
				}
			}
			$done = $record->write();
			array_push($return, array(
        'id'    => $done,
        'title' => $record->getTitle()
			));
		}

		return json_encode(array(
      'done'    => 1,
      'records' => $return
		), JSON_NUMERIC_CHECK);
	}
}
