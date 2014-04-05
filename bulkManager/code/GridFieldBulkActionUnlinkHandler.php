<?php
/**
 *
 * @author colymba
 * @package GridFieldBulkEditingTools
 */
class GridFieldBulkActionUnlinkHandler extends GridFieldBulkActionHandler
{
	/**
	 * List of action handling methods
	 */
	private static $allowed_actions = array('unlink');

	/**
	 * URL handling rules.
	 */
	private static $url_handlers = array(
		'unlink' => 'unlink'
	);
	
	/**
	 * Unlink the selected records passed from the unlink bulk action
	 * 
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse List of affected records ID
	 */
	public function unlink(SS_HTTPRequest $request)
	{
		$ids = $this->getRecordIDList();
		$this->gridField->list->removeMany($ids);
		
		$response = new SS_HTTPResponse(Convert::raw2json(array(
			'done' => true,
			'records' => $ids
		)));
		$response->addHeader('Content-Type', 'text/json');
		return $response;	
	}
}