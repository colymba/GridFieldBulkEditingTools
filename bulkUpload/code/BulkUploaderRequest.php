<?php

namespace Colymba\BulkUpload;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Object;
use SilverStripe\Control\Controller;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Dev\Deprecation;
use SilverStripe\ORM\DataObject;

use SilverStripe\AssetAdmin\Controller\AssetAdmin;

/**
 * Handles request from the GridFieldBulkUpload component.
 *
 * @author colymba
 */
class BulkUploaderRequest extends RequestHandler
{
    /**
     * Gridfield instance.
     *
     * @var GridField
     */
    protected $gridField;

    /**
     * Bulk upload component.
     *
     * @var BulkUploader
     */
    protected $component;

    /**
     * Gridfield Form controller.
     *
     * @var Controller
     */
    protected $controller;

    /**
     * RequestHandler allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = array(
        'upload', 'attach', 'fileexists', 'select'
    );

    /**
     * RequestHandler url => action map.
     *
     * @var array
     */
    private static $url_handlers = array(
        '$Action!' => '$Action'
    );

    /**
     * Handler's constructor.
     *
     * @param GridField            $gridField
     * @param GridField_URLHandler $component
     * @param Controller           $controller
     */
    public function __construct($gridField, $component, $controller)
    {
        $this->gridField = $gridField;
        $this->component = $component;
        $this->controller = $controller;
        parent::__construct();
    }

    /**
     * Return the original component's UploadField.
     *
     * @return UploadField UploadField instance as defined in the component
     */
    public function getUploadField()
    {
        return $this->component->bulkUploadField($this->gridField);
    }

    /**
     * Process upload through UploadField,
     * creates new record and link newly uploaded file
     * adds record to GrifField relation list
     * and return image/file data and record edit form.
     *
     * @param HTTPRequest $request
     *
     * @return string json
     */
    /*public function upload(HTTPRequest $request)
    {
        //create record
        $recordClass = $this->component->getRecordClassName($this->gridField);
        $record = Object::create($recordClass);
        $record->write();

        // passes the current gridfield-instance to a call-back method on the new object
        $record->extend('onBulkUpload', $this->gridField);

        //get uploadField and process upload
        $uploadField = $this->getUploadField();
        $uploadField->setRecord($record);

        $fileRelationName = $uploadField->getName();
        $uploadResponse = $uploadField->upload($request);

        //get uploaded File response datas
        $uploadResponse = Convert::json2array($uploadResponse->getBody());
        $uploadResponse = array_shift($uploadResponse);

        // Attach the file to record.
        $record->{"{$fileRelationName}ID"} = $uploadResponse['id'];

        // attached record to gridField relation
        $this->gridField->list->add($record);

        // JS Template Data
        $responseData = $this->newRecordJSTemplateData($record, $uploadResponse);

        $response = new HTTPResponse(Convert::raw2json(array($responseData)));
        $this->contentTypeNegotiation($response);

        return $response;
    }*/

    public function upload(HTTPRequest $request)
    {
        // 1. DataObject
        //create record
        $recordClass = $this->component->getRecordClassName($this->gridField);
        $record = $recordClass::create();
        $record->write();

        // passes the current gridfield-instance to a call-back method on the new object
        $record->extend('onBulkUpload', $this->gridField);

        // 2. File Upload
        $assetAdmin = AssetAdmin::singleton();
        $uploadResponse = $assetAdmin->apiCreateFile($request);
        $file = null;
        
        if ($uploadResponse->getStatusCode() == 200)
        {
            $responseData = Convert::json2array($uploadResponse->getBody());
            $responseData = array_shift($responseData);
        }

        // 3. Add File to Record
        $fileRelationName = $this->component->getFileRelationName($this->gridField);
        $record->{"{$fileRelationName}ID"} = $responseData['id'];

        // 4. Add to Gridfield List
        $this->gridField->list->add($record);

        return $uploadResponse;
    }

    /**
     * Updates the Upload/Attach response from the UploadField
     * with the new DataObject records for the JS template.
     *
     * @param DataObject $record         Newly create DataObject record
     * @param array      $uploadResponse Upload or Attach response from UploadField
     *
     * @return array Updated $uploadResponse with $record data
     */
    protected function newRecordJSTemplateData(DataObject &$record, &$uploadResponse)
    {
        // fetch uploadedFile record and sort out previewURL
        // update $uploadResponse datas in case changes happened onAfterWrite()
        $uploadedFile = DataObject::get_by_id(
            $this->component->getFileRelationClassName($this->gridField),
            $uploadResponse['id']
        );

        if ($uploadedFile) {
            $uploadResponse['name'] = $uploadedFile->Name;
            $uploadResponse['url'] = $uploadedFile->getURL();

            if ($uploadedFile instanceof Image) {
                $uploadResponse['thumbnail_url'] = $uploadedFile->Fill(30, 30)->getURL();
            } else {
                $uploadResponse['thumbnail_url'] = $uploadedFile->IconTag();
            }

            // check if our new record has a Title, if not create one automatically
            $title = $record->getTitle();
            if (!$title || $title === $record->ID) {
                if ($record->hasDatabaseField('Title')) {
                    $record->Title = $uploadedFile->Title;
                    $record->write();
                } elseif ($record->hasDatabaseField('Name')) {
                    $record->Name = $uploadedFile->Title;
                    $record->write();
                }
            }
        }

        // Collect all data for JS template
        $return = array_merge($uploadResponse, array(
            'record' => array(
                'id' => $record->ID,
            ),
        ));

        return $return;
    }

    /**
     * Pass getRelationAutosetClass request to UploadField
     * Used by select dialog.
     *
     * @link UploadField->getRelationAutosetClass()
     * @param  string $default
     * @return string
     */
    public function getRelationAutosetClass($default = 'SilverStripe\\Assets\\File')
    {
        $uploadField = $this->getUploadField();

        return $uploadField->getRelationAutosetClass($default);
    }

    /**
     * Pass getAllowedMaxFileNumber request to UploadField
     * Used by select dialog.
     *
     * @link UploadField->getAllowedMaxFileNumber()
     * @return int|null
     */
    public function getAllowedMaxFileNumber()
    {
        $uploadField = $this->getUploadField();

        return $uploadField->getAllowedMaxFileNumber();
    }

    /**
     * Retrieve Files to be attached
     * and generated DataObjects for each one.
     *
     * @param HTTPRequest $request
     *
     * @return HTTPResponse
     */
    public function attach(HTTPRequest $request)
    {
        $uploadField = $this->getUploadField();
        $attachResponses = $uploadField->attach($request);
        $attachResponses = json_decode($attachResponses->getBody(), true);

        $fileRelationName = $uploadField->getName();
        $recordClass = $this->component->getRecordClassName($this->gridField);
        $return = array();

        foreach ($attachResponses as $attachResponse) {
            // create record
            $record = Object::create($recordClass);
            $record->write();
            $record->extend('onBulkUpload', $this->gridField);

            // attach file
            $record->{"{$fileRelationName}ID"} = $attachResponse['id'];

            // attached record to gridField relation
            $this->gridField->list->add($record);

            // JS Template Data
            $responseData = $this->newRecordJSTemplateData($record, $attachResponse);

            // add to returned dataset
            array_push($return, $responseData);
        }

        $response = new HTTPResponse(Convert::raw2json($return));
        $this->contentTypeNegotiation($response);

        return $response;
    }

    /**
     * Pass select request to UploadField.
     *
     * @link UploadField->select()
     */
    public function select(HTTPRequest $request)
    {

        $uploadField = $this->getUploadField();
        $uploadField->setRequest($request);

        return $uploadField->handleSelect($request);
    }

    /**
     * Pass fileexists request to UploadField.
     *
     * @link UploadField->fileexists()
     */
    public function fileexists(HTTPRequest $request)
    {
        $uploadField = $this->getUploadField();

        return $uploadField->fileexists($request);
    }

    /**
     * @param string $action
     *
     * @return string
     */
    public function Link($action = null)
    {
        return Controller::join_links($this->gridField->Link(), '/bulkupload/', $action);
    }

    /**
     * Sets response 'Content-Type' depending on browser capabilities
     * e.g. IE needs text/plain for iframe transport
     * https://github.com/blueimp/jQuery-File-Upload/issues/1795.
     *
     * @param HTTPResponse $response HTTP Response to set content-type on
     */
    protected function contentTypeNegotiation(&$response)
    {
        if (isset($_SERVER['HTTP_ACCEPT'])
            && ((strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                || $_SERVER['HTTP_ACCEPT'] === '*/*'
            )
        ) {
            $response->addHeader('Content-Type', 'application/json');
        } else {
            $response->addHeader('Content-Type', 'text/plain');
        }
    }
}
