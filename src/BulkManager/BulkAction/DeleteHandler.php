<?php

namespace Colymba\BulkManager\BulkAction;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use Exception;

/**
 * Bulk action handler for deleting records.
 *
 * @author colymba
 */
class DeleteHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = 'delete';

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
        '' => 'delete',
    );

    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
    protected $label = 'Delete';

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
    protected $buttonClasses = 'font-icon-trash';
    
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
    protected $destructive = true;

    /**
     * Return i18n localized front-end label
     *
     * @return array
     */
    public function getI18nLabel()
    {
        return _t('GRIDFIELD_BULK_MANAGER.DELETE_SELECT_LABEL', $this->getLabel());
    }

    /**
     * Delete the selected records passed from the delete bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPBulkToolsResponse
     */
    public function delete(HTTPRequest $request)
    {
        $response = new HTTPBulkToolsResponse(true, $this->gridField);

        try {
            foreach ($this->getRecords() as $record) {
                $response->addSuccessRecord($record);
                $record->delete();
            }

            $doneCount = count($response->getSuccessRecords());
            $message = sprintf(
                'Deleted %1$d records.',
                $doneCount
            );
            $response->setMessage($message);
        } catch (Exception $ex) {
            $response->setStatusCode(500);
            $response->setMessage($ex->getMessage());
        }

        return $response;
    }
}
