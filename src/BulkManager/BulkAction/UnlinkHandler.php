<?php

namespace Colymba\BulkManager\BulkAction;

use Colymba\BulkManager\BulkAction\Handler;
use Colymba\BulkTools\HTTPBulkToolsResponse;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use Exception;

/**
 * Bulk action handler for unlinking records.
 *
 * @author colymba
 */
class UnlinkHandler extends Handler
{
    /**
     * URL segment used to call this handler
     * If none given, @BulkManager will fallback to the Unqualified class name
     * 
     * @var string
     */
    private static $url_segment = 'unlink';

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
        '' => 'unLink',
    );

    /**
     * Front-end label for this handler's action
     * 
     * @var string
     */
    protected $label = 'Unlink';

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
    protected $buttonClasses = 'font-icon-link-broken';
    
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
        return _t('GRIDFIELD_BULK_MANAGER.UNLINK_SELECT_LABEL', $this->getLabel());
    }

    /**
     * Unlink the selected records passed from the unlink bulk action.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPBulkToolsResponse
     */
    public function unLink(HTTPRequest $request)
    {
        $ids = $this->getRecordIDList();
        $response = new HTTPBulkToolsResponse(true, $this->gridField);

        try {
            //@todo fix this. seems no ids are returned!
            $response->addSuccessRecords($this->getRecords());
            $this->gridField->list->removeMany($ids);
            $response->setMessage('UnLinked records.');
        } catch (Exception $ex) {
            $response->setStatusCode(500);
            $response->setMessage($ex->getMessage());
        }

        return $response;
    }
}
