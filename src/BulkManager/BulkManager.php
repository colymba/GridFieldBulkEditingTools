<?php

namespace Colymba\BulkManager;

use ReflectionClass;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

/**
 * GridField component for editing attached models in bulk.
 *
 * @author colymba
 */
class BulkManager implements GridField_HTMLProvider, GridField_ColumnProvider, GridField_URLHandler
{
    use Injectable;

    /**
     * component configuration.
     *
     * 'editableFields' => fields editable on the Model
     * 'actions' => maps of action URL and Handler Class
     *
     * @var array
     */
    protected $config = array(
        'editableFields' => null,
        'actions' => array(),
    );

    /**
     * BulkManager component constructor.
     *
     * @param array $editableFields List of editable fields
     * @param bool  $defaultActions Use default actions list. False to start fresh.
     */
    public function __construct($editableFields = null, $defaultActions = true, $defaultVersionedActions = false)
    {
        if ($editableFields != null) {
            $this->setConfig('editableFields', $editableFields);
        }

        if ($defaultActions) {
            $this
                ->addBulkAction(BulkAction\EditHandler::class)
                ->addBulkAction(BulkAction\UnlinkHandler::class)
                ->addBulkAction(BulkAction\DeleteHandler::class);
        } elseif ($defaultVersionedActions) {
            $this
                ->addBulkAction(BulkAction\EditHandler::class)
                ->addBulkAction(BulkAction\UnlinkHandler::class)
                ->addBulkAction(BulkAction\ArchiveHandler::class)
                ->addBulkAction(BulkAction\UnPublishHandler::class)
                ->addBulkAction(BulkAction\PublishHandler::class);
        }
    }

    /* **********************************************************************
     * Components settings and custom methodes
     * */

    /**
     * Sets the component configuration parameter.
     *
     * @param string $reference
     * @param mixed  $value
     */
    public function setConfig($reference, $value)
    {
        if (!array_key_exists($reference, $this->config)) {
            user_error("Unknown option reference: $reference", E_USER_ERROR);
        }

        if ($reference == 'actions') {
            user_error('Bulk actions must be edited via addBulkAction() and removeBulkAction()', E_USER_ERROR);
        }

        if (($reference == 'editableFields') && !is_array($value)) {
            $value = array($value);
        }

        $this->config[$reference] = $value;

        return $this;
    }

    /**
     * Returns one $config parameter of the full $config.
     *
     * @param string $reference $congif parameter to return
     *
     * @return mixed
     */
    public function getConfig($reference = false)
    {
        if ($reference) {
            return $this->config[$reference];
        } else {
            return $this->config;
        }
    }

    /**
     * Lets you add custom bulk actions to the bulk manager interface.
     * Exisiting handler will be replaced
     *
     * @param string $handlerClassName RequestHandler class name for this action.
     * @param string $action Specific RequestHandler action to be called.
     *
     * @return $this Current BulkManager instance
     */
    public function addBulkAction($handlerClassName, $action = null)
    {
        if (!class_exists($handlerClassName)) {
            user_error("Bulk action handler not found: $handlerClassName", E_USER_ERROR);
        }

        $handler = Injector::inst()->get($handlerClassName);
        $urlSegment = $handler->config()->get('url_segment');
        if (!$urlSegment)
        {
            $rc = new ReflectionClass($handlerClassName);
            $urlSegment = $rc->getShortName();
        }

        $this->config['actions'][$urlSegment] = $handlerClassName;

        return $this;
    }

    /**
     * Removes a bulk actions from the bulk manager interface.
     *
     * @param string $handlerClassName RequestHandler class name of the action to remove.
     * @param string $urlSegment URL segment of the action to remove.
     *
     * @return $this Current BulkManager instance
     */
    public function removeBulkAction($handlerClassName = null, $urlSegment = null)
    {
        if (!$handlerClassName && !$urlSegment) {
            user_error("Provide either a class name or URL segment", E_USER_ERROR);
        }

        foreach ($this->config['actions'] as $url => $class)
        {
            if ($handlerClassName === $class || $urlSegment === $url)
            {
                unset($this->config['actions'][$url]);
                return $this;
            }
        }

        user_error("Bulk action '$handlerClassName' or '$urlSegment' doesn't exists.", E_USER_ERROR);
    }

    /**
     * Return the list of bulk actions already provided
     *
     * @return RequestHandler[]
     */
    public function getBulkActions()
    {
        return $this->config['actions'];
    }

    /* **********************************************************************
     * GridField_ColumnProvider
     * */

    /**
     * Add bulk select column.
     *
     * @param GridField $gridField Current GridField instance
     * @param array     $columns   Columns list
     */
    public function augmentColumns($gridField, &$columns)
    {
        if (!in_array('BulkSelect', $columns)) {
            $columns[] = 'BulkSelect';
        }
    }

    /**
     * Which columns are handled by the component.
     *
     * @param GridField $gridField Current GridField instance
     *
     * @return array List of handled column names
     */
    public function getColumnsHandled($gridField)
    {
        return array('BulkSelect');
    }

    /**
     * Sets the column's content.
     *
     * @param GridField  $gridField  Current GridField instance
     * @param DataObject $record     Record intance for this row
     * @param string     $columnName Column's name for which we need content
     *
     * @return mixed Column's field content
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        $cb = CheckboxField::create('bulkSelect_' . $record->ID)
            ->addExtraClass('bulkSelect no-change-track')
            ->setAttribute('data-record', $record->ID);

        return $cb->Field();
    }

    /**
     * Set the column's HTML attributes.
     *
     * @param GridField  $gridField  Current GridField instance
     * @param DataObject $record     Record instance for this row
     * @param string     $columnName Column's name for which we need attributes
     *
     * @return array List of HTML attributes
     */
    public function getColumnAttributes($gridField, $record, $columnName)
    {
        return array('class' => 'col-bulkSelect');
    }

    /**
     * Set the column's meta data.
     *
     * @param GridField $gridField  Current GridField instance
     * @param string    $columnName Column's name for which we need meta data
     *
     * @return array List of meta data
     */
    public function getColumnMetadata($gridField, $columnName)
    {
        if ($columnName == 'BulkSelect') {
            return array('title' => 'Select');
        }
    }

    /* **********************************************************************
     * GridField_HTMLProvider
     * */

    /**
     * @param GridField $gridField
     *
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        Requirements::javascript('colymba/gridfield-bulk-editing-tools:client/dist/js/main.js');
        Requirements::css('colymba/gridfield-bulk-editing-tools:client/dist/styles/main.css');
        Requirements::add_i18n_javascript('colymba/gridfield-bulk-editing-tools:client/lang');

        if (!count($this->config['actions'])) {
            user_error('Trying to use BulkManager without any bulk action.', E_USER_ERROR);
        }

        $actionsListSource = array();
        $actionsConfig = array();

        foreach ($this->config['actions'] as $urlSegment => $handlerClassName) {
            $handler = Injector::inst()->get($handlerClassName);
            $handlerConfig = $handler->getConfig();

            $actionsListSource[$urlSegment] = $handlerConfig['label'];
            $actionsConfig[$urlSegment] = $handlerConfig;
        }

        $dropDownActionsList = DropdownField::create(
            'bulkActionName',
            false
        )
            ->setSource($actionsListSource)
            ->addExtraClass('bulkActionName no-change-track form-group--no-label')
            ->setAttribute('id', '')
            ->setEmptyString(_t('SilverStripe\Admin\LeftAndMain.DropdownBatchActionsDefault', 'Choose an action...'));
            

        $templateData = array(
            'Menu' => $dropDownActionsList->FieldHolder(),
            'Button' => array(
                'Label' => _t('GRIDFIELD_BULK_MANAGER.ACTION_BTN_LABEL', 'Go'),
                'DataURL' => $gridField->Link('bulkAction'),
                'DataConfig' => json_encode($actionsConfig)
            ),
            'Select' => array(
                'Label' => _t('GRIDFIELD_BULK_MANAGER.SELECT_ALL_LABEL', 'Select all'),
            ),
            'Colspan' => (count($gridField->getColumns()) - 1),
        );

        $templateData = new ArrayData($templateData);

        return array(
            'header' => $templateData->renderWith('Colymba\\BulkManager\\BulkManagerButtons'),
        );
    }

    /* **********************************************************************
     * GridField_URLHandler
     * */

    /**
     * Returns an action => handler list.
     *
     * @param GridField $gridField
     *
     * @return array
     */
    public function getURLHandlers($gridField)
    {
        return array(
            'bulkAction' => 'handleBulkAction',
        );
    }

    /**
     * Pass control over to the RequestHandler
     * loop through the handlers provided in config['actions']
     * and find matching url_handlers.
     *
     * $url_handlers rule should not use wildcards like '$Action' => '$Action'
     * but have more specific path defined
     *
     * @param GridField      $gridField
     * @param HTTPRequest $request
     *
     * @return mixed
     */
    public function handleBulkAction($gridField, $request)
    {
        $controller = $gridField->getForm()->getController();

        $actionUrlSegment = $request->shift();
        $handlerClass = $this->config['actions'][$actionUrlSegment];

        $controller->pushCurrent();
        $handler = Injector::inst()->create($handlerClass, $gridField, $this);
        if ($handler)
        {
            return $handler->handleRequest($request);
        }

        user_error('Unable to find matching bulk action handler for ' . $actionUrlSegment . ' URL segment.', E_USER_ERROR);
    }
}
