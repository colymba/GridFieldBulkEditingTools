<?php
/**
 * Bulk action handler for deleting records.
 * 
 * @author colymba
 * @package GridFieldBulkEditingTools
 * @subpackage BulkManager
 */
class GridFieldBulkActionDeleteHandler extends GridFieldBulkActionHandler
{	
	/**
	 * List of action handling methods
	 */
	private static $allowed_actions = array('delete');

	/**
	 * URL handling rules.
	 */
	private static $url_handlers = array(
		'delete' => 'delete'
	);

	/**
	 * Delete the selected records passed from the delete bulk action
	 * 
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse List of deleted records ID
	 */
	public function delete(SS_HTTPRequest $request)
	{
		$ids = array();
		
		foreach ( $this->getRecords() as $record )
		{						
			array_push($ids, $record->ID);
			$record->delete();
		}

		$response = new SS_HTTPResponse(Convert::raw2json(array(
			'done' => true,
			'records' => $ids
		)));
		$response->addHeader('Content-Type', 'text/json');
		return $response;	
	}
}