<?php

namespace Colymba\BulkManager\BulkAction;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Exception;

/**
 * Bulk action handler for recursive publishing records.
 *
 * @author colymba
 */
class PublishHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = 'publish';

    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array('publish');

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        '' => 'publish',
    );

    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
    protected $label = 'Publish';

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
    protected $buttonClasses = 'font-icon-rocket';
    
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
     * Return i18n localized front-end label
     *
     * @return array
     */
    public function getI18nLabel()
    {
        return _t('GRIDFIELD_BULK_MANAGER.PUBLISH_SELECT_LABEL', $this->getLabel());
    }

    /**
     * Publish the selected records passed from the publish bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPBulkToolsResponse
     */
    public function publish(HTTPRequest $request)
    {
        $records = $this->getRecords();
        $response = new HTTPBulkToolsResponse(false, $this->gridField);

        try {
            foreach ($records as $record)
            {
                $done = $record->publishRecursive();
                if ($done)
                {
                    $response->addSuccessRecord($record);
                }else{
                    $response->addFailedRecord($record, $done);
                }
            }

            $doneCount = count($response->getSuccessRecords());
            $failCount = count($response->getFailedRecords());
            $message = sprintf(
                'Published %1$d of %2$d records.',
                $doneCount,
                $doneCount + $failCount
            );
            $response->setMessage($message);
        } catch (Exception $ex) {
            $response->setStatusCode(500);
            $response->setMessage($ex->getMessage());
        }
        
        return $response;
    }
}
