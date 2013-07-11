<?php
/**
 * Milkyway Multimedia
 * VersionedGridFieldBulkManager_Request.php
 *
 * Adding extra bulk edit actions specifically for versioned objects,
 * just like the Multi-selection action dropdown in the Pages section
 *
 * @todo Extend the bulk edit action to allow publish and unpublish records from that interface
 *
 * @package GridFieldBulkEditingTools
 * @author Mellisa Hankins
 * @author colymba
 */

class VersionedGridFieldBulkManager_Request extends GridFieldBulkManager_Request {
	private static $allowed_actions = array(
		'edit',
		'update',
		'unlink',
		'delete',
		'publish',
		'unpublish',
	);

	public function publish(SS_HTTPRequest $request) {
		$recordList = $this->getPOSTRecordList($request);
		$records = $this->gridField->List->byIDs($recordList);

		foreach($records as $record) {
			if(!$this->doPublish($record))
				unset($recordList[$record->ID]);
		}

		$this->completeAction($recordList);
	}

	public function unpublish(SS_HTTPRequest $request) {
		$recordList = $this->getPOSTRecordList($request);
		$records = $this->gridField->List->byIDs($recordList);

		foreach($records as $record) {
			if(!$this->doUnpublish($record))
				unset($recordList[$record->ID]);
		}

		$this->completeAction($recordList);
	}

	protected function doPublish($record)	{
		if(!$record || !$record->hasExtension('Versioned') || ($record->hasMethod('canPublish') && !$record->canPublish())) {
			return false;
		}

		if($record->hasMethod('doPublish'))
			$record->doPublish();
		else
			$record->publish("Stage", "Live");

		return true;
	}

	protected function doUnpublish($record)	{
		if(!$record || !$record->hasExtension('Versioned') || ($record->hasMethod('canDeleteFromLive') && !$record->canDeleteFromLive())) {
			return false;
		}

		if($record->hasMethod('doPublish'))
			$record->doUnpublish();
		else {
			$origStage = Versioned::current_stage();
			Versioned::reading_stage('Live');

			// This way our ID won't be unset
			$clone = clone $record;
			$clone->delete();

			Versioned::reading_stage($origStage);
		}

		return true;
	}

	private function completeAction($recordList = null) {
		$response = new SS_HTTPResponse(Convert::raw2json(array($recordList)));
		$response->addHeader('Content-Type', 'text/plain');
		return $response;
	}
}