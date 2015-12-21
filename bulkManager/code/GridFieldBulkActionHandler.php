<?php
/**
 * Base class to extend for all custom bulk action handlers
 * Gives access to the GridField, Component and Controller
 * and implements useful functions like {@link getRecordIDList()} and {@link getRecords()}.
 * 
 * @author colymba
 */
class GridFieldBulkActionHandler extends RequestHandler
{
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
     * Current controller instance.
     *
     * @var Controller
     */
    protected $controller;

    /**
     * @param GridFIeld            $gridField
     * @param GridField_URLHandler $component
     * @param Controller           $controller
     */
    public function __construct($gridField, $component, $controller)
    {
        $this->gridField = $gridField;
        $this->component = $component;
        $this->controller = $controller;
        parent::__construct();
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
     * Traverse up nested requests until we reach the first that's not a GridFieldDetailForm or GridFieldDetailForm_ItemRequest.
     * The opposite of {@link Controller::curr()}, required because
     * Controller::$controller_stack is not directly accessible.
     * 
     * @return Controller
     */
    protected function getToplevelController()
    {
        $c = $this->controller;
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
        if (!$this->controller->hasMethod('Breadcrumbs')) {
            return;
        }

        $items = $this->controller->Breadcrumbs($unlinked);
        $items->push(new ArrayData(array(
                'Title' => 'Bulk Editing',
                'Link' => false,
            )));

        return $items;
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
