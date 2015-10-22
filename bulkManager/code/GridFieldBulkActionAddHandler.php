<?php
class GridFieldBulkActionAddHandler extends GridFieldBulkActionHandler {
    /**
     * RequestHandler allowed actions
     * @var array
     */
    private static $allowed_actions = array(
        'index',
        'bulkAddForm',
        'recordAddForm'
    );

    /**
     * RequestHandler url => action map
     * @var array
     */
    private static $url_handlers = array(
        'bulkAdd/bulkAddForm'   => 'bulkAddForm',
        'bulkAdd/recordAddForm' => 'recordAddForm',
        'bulkAdd'               => 'index'
    );

    public function Link($action = null)
    {
        return Controller::join_links(parent::Link(), 'bulkAdd', $action);
    }

    public function bulkAddForm()
    {
        $crumbs = $this->Breadcrumbs();

        if ($crumbs && $crumbs->count()>=2) {
            $one_level_up = $crumbs->offsetGet($crumbs->count()-2);
        }

        $actions = new FieldList();

        $actions->push(
            FormAction::create('doSave', _t('GRIDFIELD_BULKMANAGER_ADD_HANDLER.SAVE_BTN_LABEL', 'Save all'))
                ->setAttribute('id', 'bulkEditingSaveBtn')
                ->addExtraClass('ss-ui-action-constructive')
                ->setAttribute('data-icon', 'accept')
                ->setUseButtonTag(true)
        );

        $actions->push(
            FormAction::create('Cancel', _t('GRIDFIELD_BULKMANAGER_ADD_HANDLER.CANCEL_BTN_LABEL', 'Cancel'))
                ->setAttribute('id', 'bulkEditingUpdateCancelBtn')
                ->addExtraClass('ss-ui-action-destructive cms-panel-link')
                ->setAttribute('data-icon', 'decline')
                ->setAttribute('href', $one_level_up->Link)
                ->setUseButtonTag(true)
                ->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
        );

        $recordsFieldList = new FieldList();

        $modelClass       = $this->gridField->getModelClass();
        $singleton        = singleton($modelClass);
        $titleModelClass  = $singleton->i18n_singular_name();

        //some cosmetics
        $headerText = _t('GRIDFIELD_BULKMANAGER_ADD_HANDLER.HEADER_TEXT',
            'Adding {class}',
            array(
                'class' => $titleModelClass
            )
        );

        $header = LiteralField::create(
            'bulkEditHeader',
            '<h1 id="bulkEditHeader">' . $headerText . '</h1>'
        );

        $recordsFieldList->push($header);

        $toggle = LiteralField::create(
            'bulkEditToggle',
            '<span id="bulkEditToggle">' .
            _t('GRIDFIELD_BULKMANAGER_ADD_HANDLER.TOGGLE_ALL_LINK', 'Show/Hide all') . '</span>'
        );

        $recordsFieldList->push($toggle);

        $record = new ListingImage();

        for ($i=0; $i < 10; $i++) {
            $tempForm = Form::create(
                $this, "TempEditForm",
                $record->getCMSFields(),
                FieldList::create()
            );

            $fields = $tempForm->Fields();
            $fields = $this->filterRecordAddFields($fields, $i);

            $toggleField = ToggleCompositeField::create(
                'RecordFields_' . $i,
                $record->getTitle(),
                $fields
            )
            ->setHeadingLevel(4)
            ->setAttribute('data-id', $i)
            ->addExtraClass('bulkEditingFieldHolder');

            $recordsFieldList->push($toggleField);
        }

        //recordEditForm name is here to trick SS to pass all subform request to recordEditForm()
        $bulkEditForm = Form::create(
            $this,
            'recordAddForm',
            $recordsFieldList,
            $actions
        );

        if ($crumbs && $crumbs->count()>=2) {
            $bulkEditForm->Backlink = $one_level_up->Link;
        }

        //override form action URL back to bulkEditForm
        //and add record ids GET var
        $bulkEditForm->setAttribute(
            'action',
            $this->Link('bulkAddForm')
        );

        return $bulkEditForm;
    }

    public function recordAddForm()
    {
        //clone current request : used to figure out what record we are asking
        $request = clone $this->request;
        $recordInfo = $request->shift();

        //shift request till we find the requested field
        while ($recordInfo) {
            if ($unescapedRecordInfo = $this->unEscapeFieldName($recordInfo)) {
                $id = $unescapedRecordInfo['id'];
                $fieldName = $unescapedRecordInfo['name'];
                $action = $request->shift();
                break;
            } else {
                $recordInfo = $request->shift();
            }
        }

        //generate a form with only that requested record's fields
        if ($id) {
            $modelClass = $this->gridField->getModelClass();
            $record = DataObject::get_by_id($modelClass, $id);

            $cmsFields = $record->getCMSFields();
            $recordEditingFields = $this->getRecordEditingFields($record);
            
            return Form::create(
                $this->gridField,
                'recordEditForm',
                FieldList::create($recordEditingFields),
                FieldList::create()
            );
        }
    }

    private function filterRecordAddFields(FieldList $fields, $num)
    {
        $config              = $this->component->getConfig();
        $editableFields      = $config['editableFields'];

        // get all dataFields or just the ones allowed in config
        if ($editableFields){
            $dataFields = array();

            foreach ($editableFields as $fieldName) {
                $dataFields[$fieldName] = $fields->dataFieldByName($fieldName);
            }
        } else {
            $dataFields = $fields->dataFields();
        }

        // escape field names with unique prefix
        foreach ($dataFields as $name => $field) {
            $field->Name       = $this->escapeFieldName($num, $name);
            $dataFields[$name] = $field;
        }
        
        return $dataFields;
    }

    protected function escapeFieldName($num, $name)
    {
        return 'record_' . $num . '_' . $name;
    }

    protected function unEscapeFieldName($fieldName)
    {
        $parts = array();
        $match = preg_match('/record_(\d+)_(\w+)/i', $fieldName, $parts);

        if (!$match) {
             return false;
        } else {
            return array(
                'id'   => $parts[1],
                'name' => $parts[2],
            );
        }
    }

    public function index()
    {
        $form = $this->bulkAddForm();
        $form->setTemplate('LeftAndMain_EditForm');
        $form->addExtraClass('center cms-content');
        $form->setAttribute('data-pjax-fragment', 'CurrentForm Content');

        Requirements::javascript(BULKEDITTOOLS_MANAGER_PATH . '/javascript/GridFieldBulkEditingForm.js');   
        Requirements::css(BULKEDITTOOLS_MANAGER_PATH . '/css/GridFieldBulkEditingForm.css');    
        Requirements::add_i18n_javascript(BULKEDITTOOLS_PATH . '/lang/js');

        $controller = $this->getToplevelController();
        return $controller->customise(array( 'Content' => $form ));
    }

    public function doSave($data, $form)
    {
        $className  = $this->gridField->list->dataClass;
        $singleton  = singleton($className);

        $formsData  = array();
        $done       = 0;

        foreach ($data as $fieldName => $value) {
            if ($fieldInfo = $this->unEscapeFieldName($fieldName)) {
                if (!isset($formsData[$fieldInfo['id']])) {
                    $formsData[$fieldInfo['id']] = array();
                }

                $formsData[$fieldInfo['id']][$fieldInfo['name']] = $value;
            }
        }

        foreach ($formsData as $recordID => $recordData) {
            if (!$this->isEmpty($recordData)) {
                $record = new $className($recordData);
                $id = $record->write();

                if ($id) {
                    $this->gridField->list->add($record);
                    $done++;
                }
            }
        }

        $messageModelClass  = (($done > 1) ? $singleton->i18n_plural_name() : $singleton->i18n_singular_name());

        $message = _t('GRIDFIELD_BULKMANAGER_ADD_HANDLER.SAVE_RESULT_TEXT',
            '{count} {class} created successfully.',
            array(
                'count' => $done,
                'class' => $messageModelClass
            )
        );

        $form->sessionMessage($message, 'good');

        return Controller::curr()->redirect($this->Link());
    }

    private function isEmpty($data)
    {
        $empty = true;
        $ignore_fields = array('SecurityID', 'Image');

        foreach ($data as $name => $value) {
            if (!in_array($name, $ignore_fields) && !empty($value)) {
                $empty = false;
            }
        }

        return $empty;
    }
}

