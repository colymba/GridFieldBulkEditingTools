<?php

namespace Colymba\BulkManager\BulkAction;

use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\ORM\DataList;
use SilverStripe\View\ArrayData;

/**
 * Base class to extend for all custom bulk action handlers
 * Gives access to the GridField, Component and Controller
 * and implements useful functions like {@link getRecordIDList()} and {@link getRecords()}.
 *
 * @author colymba
 */
class Handler extends RequestHandler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = null;

    /**
     * Related GridField instance.
     *
     * @var GridField
     */
    protected $gridField;

    /**
     * GridFieldBulkManager instance.
     *
     * @var GridFieldBulkManager
     */
    protected $component;

    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
    protected $label = 'Action';

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
    protected $buttonClasses = '';
    
    /**
     * Whether this handler should be called via an XHR from the front-end
     * 
     * @var boolean
     */
    protected $xhr = true;
    
    /**
     * Set to true is this handler will destroy any data.
     * A warning and confirmation will be shown on the front-end.
     * 
     * @var boolean
     */
    protected $destructive = false;

    /**
     * @param GridField            $gridField
     * @param GridField_URLHandler $component
     */
    public function __construct($gridField = null, $component = null)
    {
        $this->gridField = $gridField;
        $this->component = $component;

        parent::__construct();
    }

    /**
     * Return front-end configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = array(
            'label' => $this->getI18nLabel(),
            'icon' => $this->getIcon(),
            'buttonClasses' => $this->getButtonClasses(),
            'xhr' => $this->getXhr(),
            'destructive' => $this->getDestructive()
        );
        return $config;
    }

    /**
     * Set if hanlder performs destructive actions
     * 
     * @param boolean destructive If true, a warning will be shown on the front-end
     * @return RequestHandler
     */
    public function setDestructive($destructive)
    {
        $this->destructive = $destructive;
        return $this;
    }
    
    /**
     * True if the  hanlder performs destructive actions
     * 
     * @return boolean
     */
    public function getDestructive()
    {
        return $this->destructive;
    }

    /**
     * Set if handler is called via XHR
     * 
     * @param boolean xhr If true the handler will be called via an XHR from front-end
     * @return RequestHandler
     */
    
    public function setXhr($xhr)
    {
        $this->xhr = $xhr;
        return $this;
    }

    /**
     * True if handler is called via XHR
     *
     * @return boolean
     */
    public function getXhr()
    {
        return $this->xhr;
    }

    /**
     * Set front-end buttonClasses
     *
     * @return RequestHandler
     */
    public function setButtonClasses($buttonClasses)
    {
        $this->buttonClasses = $buttonClasses;
        return $this;
    }

    /**
     * Return front-end buttonClasses
     *
     * @return string
     */
    public function getButtonClasses()
    {
        return $this->buttonClasses;
    }

    /**
     * Set front-end icon
     *
     * @return RequestHandler
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Return front-end icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set front-end label
     *
     * @return RequestHandler
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Return front-end label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Return i18n localized front-end label
     *
     * @return array
     */
    public function getI18nLabel()
    {
        return _t('GRIDFIELD_BULK_MANAGER.HANDLER_LABEL', $this->getLabel());
    }

    /**
     * Returns the URL for this RequestHandler.
     *
     * @author SilverStripe
     *
     * @see GridFieldDetailForm_ItemRequest
     *
     * @param string $action
     *
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::join_links($this->gridField->Link(), 'bulkAction', $action);
    }

    /**
     * Returns the list of record IDs selected in the front-end.
     *
     * @return array List of IDs
     */
    public function getRecordIDList()
    {
        $vars = $this->request->requestVars();

        return $vars['records'];
    }

    /**
     * Returns a DataList of the records selected in the front-end.
     *
     * @return DataList List of records
     */
    public function getRecords()
    {
        $ids = $this->getRecordIDList();

        if ($ids) {
            $class = $this->gridField->list->dataClass;

            return DataList::create($class)->byIDs($ids);
        } else {
            return false;
        }
    }
}
