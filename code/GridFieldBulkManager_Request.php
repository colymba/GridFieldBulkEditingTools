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
		return Controller::join_links($this->gridField->Link(), 'bulkimageupload', $action);
	}
	
	
	public function edit(SS_HTTPRequest $request)
	{
		$recordList = $request->requestVars(); 
	}
	
	public function unlink(SS_HTTPRequest $request)
	{
		
	}
	
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
				'Title' => 'Bulk Upload',
				'Link' => false
			)));
		return $items;
	}
}