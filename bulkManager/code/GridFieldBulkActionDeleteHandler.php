<?php

namespace Colymba\BulkManager;

use Colymba\BulkManager\GridFieldBulkActionHandler;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;

/**
 * Bulk action handler for deleting records.
 *
 * @author colymba
 */
class GridFieldBulkActionDeleteHandler extends GridFieldBulkActionHandler
{
    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('delete');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        'delete' => 'delete',
    );

    /**
     * Delete the selected records passed from the delete bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPResponse List of deleted records ID
     */
    public function delete(HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);
            $record->delete();
        }

        $response = new HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids,
        )));
        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
