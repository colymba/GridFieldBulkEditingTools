<?php

namespace Colymba\BulkManager\BulkAction;

use Colymba\BulkManager\BulkAction\Handler;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;

/**
 * Bulk action handler for editing records.
 *
 * @author colymba
 */
class EditHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = 'edit';

    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array(
        'index',
        'bulkEditForm',
        'recordEditForm',
    );

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        'bulkEditForm' => 'bulkEditForm',
        'recordEditForm' => 'recordEditForm',
        '' => 'index',
    );

    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
    protected $label = 'Edit';

    /**
     * Front-end icon path for this handler's action.
     * 
     * @var string
     */
    protected $icon = '';

    /**
     * Extra classes to add to the bulk action button for this handler
     * Can also be used to set the button font-icon e.g. font-icon-trash
     * 
     * @var string
     */
    protected $buttonClasses = 'font-icon-edit';
    
    /**
     * Whether this handler should be called via an XHR from the front-end
     * 
     * @var boolean
     */
    protected $xhr = false;
    
    /**
     * Set to true is this handler will destroy any data.
     * A warning and confirmation will be shown on the front-end.
     * 
     * @var boolean
     */
    protected $destructive = false;

    /**
     * Return i18n localized front-end label
     *
     * @return array
     */
    public function getI18nLabel()
    {
        return _t('GRIDFIELD_BULK_MANAGER.EDIT_SELECT_LABEL', $this->getLabel());
    }

    /**
     * Return URL to this RequestHandler.
     *
     * @param string $action Action to append to URL
     *
     * @return string URL
     */
    public function Link($action = null)
    {
        return Controller::join_links(parent::Link(), $this->stat('url_segment'), $action);
    }

    /**
     * Return a form for all the selected DataObjects
     * with their respective editable fields.
     *
     * @return Form Selected DataObjects editable fields
     */
    public function bulkEditForm()
    {
        $crumbs = $this->Breadcrumbs();
        if ($crumbs && $crumbs->count() >= 2) {
            $one_level_up = $crumbs->offsetGet($crumbs->count() - 2);
        }

        $actions = new FieldList();

        $actions->push(
            FormAction::create('doSave', _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.SAVE_BTN_LABEL', 'Save all'))
                ->setAttribute('id', 'bulkEditingSaveBtn')
                ->addExtraClass('btn btn-success')
                ->setAttribute('data-icon', 'accept')
                ->setUseButtonTag(true)
        );

        $actions->push(
            FormAction::create('Cancel', _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.CANCEL_BTN_LABEL', 'Cancel'))
                ->setAttribute('id', 'bulkEditingUpdateCancelBtn')
                ->addExtraClass('btn btn-danger cms-panel-link')
                ->setAttribute('data-icon', 'decline')
                ->setAttribute('href', $one_level_up->Link)
                ->setUseButtonTag(true)
                ->setAttribute('src', '')//changes type to image so isn't hooked by default actions handlers
        );

        $recordList = $this->getRecordIDList();
        $recordsFieldList = new FieldList();
        $config = $this->component->getConfig();

        $editingCount = count($recordList);
        $modelClass = $this->gridField->getModelClass();
        $singleton = singleton($modelClass);
        $titleModelClass = (($editingCount > 1) ? $singleton->i18n_plural_name() : $singleton->i18n_singular_name());

        //some cosmetics
        $headerText = _t(
            'GRIDFIELD_BULKMANAGER_EDIT_HANDLER.HEADER_TEXT',
            'Editing {count} {class}',
            array(
                'count' => $editingCount,
                'class' => $titleModelClass,
            )
        );
        $header = LiteralField::create(
            'bulkEditHeader',
            '<h1 id="bulkEditHeader">'.$headerText.'</h1>'
        );
        $recordsFieldList->push($header);

        $toggle = LiteralField::create(
            'bulkEditToggle',
            '<span id="bulkEditToggle">' . _t('GRIDFIELD_BULKMANAGER_EDIT_HANDLER.TOGGLE_ALL_LINK', 'Show/Hide all') . '</span>'
        );
        $recordsFieldList->push($toggle);

        //fetch fields for each record and push to fieldList
        foreach ($recordList as $id) {
            $record = DataObject::get_by_id($modelClass, $id);
            $recordEditingFields = $this->getRecordEditingFields($record);

            $toggleField = ToggleCompositeField::create(
                'RecordFields_' . $id,
                $record->getTitle(),
                $recordEditingFields
            )
            ->setHeadingLevel(4)
            ->setAttribute('data-id', $id)
            ->addExtraClass('bulkEditingFieldHolder');

            $recordsFieldList->push($toggleField);
        }

        $bulkEditForm = Form::create(
            $this,
            'recordEditForm', //recordEditForm name is here to trick SS to pass all subform request to recordEditForm()
            $recordsFieldList,
            $actions
        );

        if ($crumbs && $crumbs->count() >= 2) {
            $bulkEditForm->Backlink = $one_level_up->Link;
        }

        //override form action URL back to bulkEditForm
        //and add record ids GET var
        $bulkEditForm->setAttribute(
            'action',
            $this->Link('bulkEditForm?records[]='.implode('&', $recordList))
        );

        return $bulkEditForm;
    }

    /**
     * Return's a form with only one record's fields
     * Used for bulkEditForm subForm requests via ajax.
     *
     * @return Form Currently being edited form
     */
    public function recordEditForm()
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

    /**
     * Returns a record's populated form fields
     * with all filtering done ready to be included in the main form.
     *
     * @uses DataObject::getCMSFields()
     *
     * @param DataObject $record The record to get the fields from
     *
     * @return array The record's editable fields
     */
    private function getRecordEditingFields(DataObject $record)
    {
        $tempForm = Form::create(
            $this,
            'TempEditForm',
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
     * See {@link BulkManager} component for filtering config.
     *
     * @param FieldList $fields Record's CMS Fields
     * @param int       $id     Record's ID, used fir unique name
     *
     * @return array Filtered record's fields
     */
    private function filterRecordEditingFields(FieldList $fields, $id)
    {
        $config = $this->component->getConfig();
        $editableFields = $config['editableFields'];

        // get all dataFields or just the ones allowed in config
        if ($editableFields) {
            $dataFields = array();

            foreach ($editableFields as $fieldName) {
                $dataFields[$fieldName] = $fields->dataFieldByName($fieldName);
            }
        } else {
            $dataFields = $fields->dataFields();
        }

        // escape field names with unique prefix
        foreach ($dataFields as $name => $field) {
            $field->Name = $this->escapeFieldName($id, $name);
            $dataFields[$name] = $field;
        }

        return $dataFields;
    }

    /**
     * Escape a fieldName with a unique prefix.
     *
     * @param int    $recordID Record id from who the field belongs
     * @param string $name     Field name
     *
     * @return string Escaped field name
     */
    protected function escapeFieldName($recordID, $name)
    {
        return 'record_' . $recordID . '_' . $name;
    }

    /**
     * Un-escape a previously escaped field name.
     *
     * @param string $fieldName Escaped field name
     *
     * @return array|false Fasle if the fieldName was not escaped. Or Array map with record 'id' and field 'name'
     */
    protected function unEscapeFieldName($fieldName)
    {
        $parts = array();
        $match = preg_match('/record_(\d+)_(\w+)/i', $fieldName, $parts);

        if (!$match) {
            return false;
        } else {
            return array(
                'id' => $parts[1],
                'name' => $parts[2],
            );
        }
    }

    /**
     * Creates and return the bulk editing interface.
     *
     * @return string Form's HTML
     */
    public function index()
    {
        $form = $this->bulkEditForm();
        $form->setTemplate([
            'type' => 'Includes',
            'SilverStripe\\Admin\\LeftAndMain_EditForm',
        ]);
        $form->addExtraClass('center cms-content');
        $form->setAttribute('data-pjax-fragment', 'CurrentForm Content');

        Requirements::javascript('colymba/gridfield-bulk-editing-tools:client/dist/js/main.js');
        Requirements::css('colymba/gridfield-bulk-editing-tools:client/dist/styles/main.css');
        Requirements::add_i18n_javascript('colymba/gridfield-bulk-editing-tools:lang');

        if ($this->request->isAjax()) {
            $response = new HTTPResponse(
                Convert::raw2json(array('Content' => $form->forAjaxTemplate()->getValue()))
            );
            $response->addHeader('X-Pjax', 'Content');
            $response->addHeader('Content-Type', 'text/json');
            $response->addHeader('X-Title', 'SilverStripe - Bulk ' . $this->gridField->list->dataClass . ' Editing');

            return $response;
        } else {
            $controller = $this->getToplevelController();

            return $controller->customise(array('Content' => $form));
        }
    }

    /**
     * Handles bulkEditForm submission
     * and parses and saves each records data.
     *
     * @param array $data Sumitted form data
     * @param Form  $form Form
     */
    public function doSave($data, $form)
    {
        $className = $this->gridField->list->dataClass;
        $singleton = singleton($className);

        $formsData = array();
        $ids = array();
        $done = 0;

        //unescape and sort form data per record ID
        foreach ($data as $fieldName => $value) {
            if ($fieldInfo = $this->unEscapeFieldName($fieldName)) {
                if (!isset($formsData[$fieldInfo['id']])) {
                    $formsData[$fieldInfo['id']] = array();
                }

                $formsData[$fieldInfo['id']][$fieldInfo['name']] = $value;
            }
        }

        //process each record's form data and save
        foreach ($formsData as $recordID => $recordData) {
            $record = DataObject::get_by_id($className, $recordID);
            $recordForm = Form::create(
                $this,
                'RecordForm',
                $record->getCMSFields(),
                FieldList::create()
            );

            $recordForm->loadDataFrom($recordData);
            $recordForm->saveInto($record);
            $id = $record->write();

            array_push($ids, $record->ID);

            if ($id) {
                ++$done;
            }
        }

        //compose form message
        $messageModelClass = (($done > 1) ? $singleton->i18n_plural_name() : $singleton->i18n_singular_name());
        $message = _t(
            'GRIDFIELD_BULKMANAGER_EDIT_HANDLER.SAVE_RESULT_TEXT',
            '{count} {class} saved successfully.',
            array(
                'count' => $done,
                'class' => $messageModelClass,
            )
        );
        $form->sessionMessage($message, 'good');

        //return back to form
        return Controller::curr()->redirect($this->Link('?records[]=' . implode('&records[]=', $ids)));
        //return Controller::curr()->redirect($form->Backlink); //returns to gridField
    }

    /**
     * Traverse up nested requests until we reach the first that's not a GridFieldDetailForm or GridFieldDetailForm_ItemRequest.
     * The opposite of {@link Controller::curr()}, required because
     * Controller::$controller_stack is not directly accessible.
     *
     * @return Controller
     */
    protected function getToplevelController()
    {
        $c = Controller::curr();
        while ($c && ($c instanceof GridFieldDetailForm_ItemRequest || $c instanceof GridFieldDetailForm)) {
            $c = $c->getController();
        }

        return $c;
    }

    /**
     * Edited version of the GridFieldEditForm function
     * adds the 'Bulk Upload' at the end of the crums.
     *
     * CMS-specific functionality: Passes through navigation breadcrumbs
     * to the template, and includes the currently edited record (if any).
     * see {@link LeftAndMain->Breadcrumbs()} for details.
     *
     * @author SilverStripe original Breadcrumbs() method
     *
     * @see GridFieldDetailForm_ItemRequest
     *
     * @param bool $unlinked
     *
     * @return ArrayData
     */
    public function Breadcrumbs($unlinked = false)
    {
        if (!Controller::curr()->hasMethod('Breadcrumbs')) {
            return;
        }

        $items = Controller::curr()->Breadcrumbs($unlinked);
        $items->push(new ArrayData(array(
            'Title' => 'Bulk Editing',
            'Link' => false,
        )));

        return $items;
    }
}
