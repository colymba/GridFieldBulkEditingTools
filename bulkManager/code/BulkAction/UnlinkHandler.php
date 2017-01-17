<?php

namespace Colymba\BulkManager\BulkAction;

use Colymba\BulkManager\BulkAction\Handler;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;

/**
 * Bulk action handler for unlinking records.
 *
 * @author colymba
 */
class UnlinkHandler extends Handler
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
     * @param HTTPRequest $request
     *
     * @return HTTPResponse List of affected records ID
     */
    public function unLink(HTTPRequest $request)
    {
        $ids = $this->getRecordIDList();
        $this->gridField->list->removeMany($ids);

        $response = new HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids,
        )));
        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
