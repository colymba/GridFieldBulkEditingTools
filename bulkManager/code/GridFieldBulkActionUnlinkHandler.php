<?php
/**
 * Bulk action handler for unlinking records.
 * 
 * @author colymba
 */
class GridFieldBulkActionUnlinkHandler extends GridFieldBulkActionHandler
{
    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('unLink');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        'unLink' => 'unLink',
    );

    /**
     * Unlink the selected records passed from the unlink bulk action.
     * 
     * @param SS_HTTPRequest $request
     *
     * @return SS_HTTPResponse List of affected records ID
     */
    public function unLink(SS_HTTPRequest $request)
    {
        $ids = $this->getRecordIDList();
        $this->gridField->list->removeMany($ids);

        $response = new SS_HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids,
        )));
        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
