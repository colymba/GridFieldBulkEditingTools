<?php

namespace Colymba\BulkTools;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\HTML;

/**
 * Custom HTTPResponse for all bulk tools to use
 * for a unified response format and facilitate forn-end handling
 *
 * Add custom methods and tool to create a common json output format:
 * {
 *   isDestructive: false,
 *   isError: false,
 *   isWarning: false,
 *   message: "General response error or not message for the cms user",
 *   successClasses: ['list', 'of-new', 'classes', 'to-add', 'bt-done'],
 *   failedClasses: ['list', 'of-new', 'classes', 'to-add', 'bt-done']
 *   records: {
 *     success: [{
 *       id: 1,
 *       class: 'ObjectClass',
 *       row: 'tr .ss-gridfield-item html markup for this record'
 *     }],
 *     failed: [{
 *       id: 2,
 *       class: 'AnotherClass',
 *       message: 'Erro message for that object.'
 *     }]
 *   }
 * }
 *
 * @author colymba
 */
class HTTPBulkToolsResponse extends HTTPResponse
{
    /**
     * We always return JSON
     * @var array
     */
    protected $headers = array(
        "content-type" => "application/json; charset=utf-8",
    );

    /**
     * Does the bulk action removes rows?
     *
     * @var boolean
     */
    protected $removesRows;

    /**
     * Bulk action result message
     *
     * @var string
     */
    protected $message = '';

    /**
     * Gridfield instance.
     *
     * @var GridField
     */
    protected $gridField;

    /**
     * List of DataObject that has been modified successfully by the bulk action
     *
     * @var array
     */
    protected $successRecords = [];

    /**
     * List of css classes to add to gridfield row modified successfully
     *
     * @var array
     */
    protected $successClasses = [];

    /**
     * List of DataObject IDs that failed to be modified by the bulk action
     *
     * @var array
     */
    protected $failedRecords = [];

    /**
     * List of css classes to add to gridfield row with errors
     *
     * @var array
     */
    protected $failedClasses = [];

    /**
     * Create a new bulk tools HTTP response
     *
     * @param boolean $removesRows Does the action removes rows?
     * @param gridfield $gridfield gridfield instance that holds the records list
     * @param int $statusCode The numeric status code - 200, 404, etc
     * @param string $statusDescription The text to be given alongside the status code.
     *  See {@link setStatusCode()} for more information.
     */
    public function __construct($removesRows, $gridfield, $statusCode = null)
    {
        $this->removesRows = $removesRows;
        $this->gridfield = $gridfield;

        register_shutdown_function(array($this, 'shutdown'));

        parent::__construct(null, $statusCode);
    }

    /**
     * Overriden here so content-type cannot be changed
     * Add a HTTP header to the response, replacing any header of the same name.
     *
     * @param string $header Example: "content-type"
     * @param string $value Example: "text/xml"
     * @return $this
     */
    public function addHeader($header, $value)
    {
        if($header === "content-type") {
            return $this;
        }
        return parent::addHeader($header, $value);
    }

    /**
     * Overriden here so content-type cannot be changed
     * Remove an existing HTTP header by its name,
     * e.g. "Content-Type".
     *
     * @param string $header
     * @return $this
     */
    public function removeHeader($header)
    {
        if($header === "content-type") {
            return $this;
        }
        return parent::removeHeader($header);
    }

    /**
     * Overriden here so the response body cannot be set manually
     * 
     * @return $this
     */
    public function setBody($body)
    {
        return $this;
    }

    /**
     * Makes sure body is created before being returned
     * @return string
     */
    public function getBody()
    {
        $this->createBody();
        return $this->body;
    }

    /**
     * Set the general response message
     * 
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Add a record to the successfully modified list
     * @param DataObject $record the newly modified dataObject
     * @return $this
     */
    public function addSuccessRecord($record)
    {
        $this->successRecords[] = $record;
        return $this;
    }

    /**
     * Add a list of records to the successfully modified list
     * @param SS_List $records newly modified dataObjects list
     * @return $this
     */
    public function addSuccessRecords(SS_List $records)
    {
        array_push($this->successRecords, $records->toArray());
        return $this;
    }

    /**
     * Add an ID to the successfully modified list
     * @param interger $id the newly modified ID
     * @param string $className object class name (default to gridfield getModelClass())
     * @return $this
     */
    /*public function addSuccessID($id, $className = null)
    {
        if (!$className) {
            $className = $this->gridfield->getModelClass();
        }
        //we use all caps ID since DO have their ID ion all caps, so createBody finds it...
        $this->successRecords[] = (object) ['ID' => (int) $id, 'ClassName' => $className];
        return $this;
    }*/

    /**
     * Add an array of ID to the successfully modified list
     * @param array $ids of the newly modified objects
     * @return $this
     */
    /*public function addSuccessIDs($ids, $className = null)
    {
         //we use all caps ID since DO have their ID ion all caps, so createBody finds it...
        foreach ($ids as $id) {
            $this->successRecords[] = (object) ['ID' => (int) $id];
        }
        return $this;
    }*/

    /**
     * Return the list of succesful records
     * @return array
     */
    public function getSuccessRecords()
    {
        return $this->successRecords;
    }

    /**
     * Add a record to the failed to modified list with its error message
     * @param DataObject $record the failed dataObject
     * @param string $message error message
     * @return $this
     */
    public function addFailedRecord($record, $message)
    {
        $this->failedRecords[] = array('id' => $record->ID, 'class' => $record->ClassName, 'message' => $message);
        return $this;
    }

    /**
     * Add a list of records to the failed to modified list with a common error message
     * @param SS_List $records  the failed dataObject list
     * @param string $message error message
     * @return $this
     */
    public function addFailedRecords(SS_List $records, $message)
    {
        foreach ($records as $record) {
            $this->failedRecords[] = array('id' => $record->ID, 'class' => $record->ClassName, 'message' => $message);
        }
        return $this;
    }

    /**
     * Return the list of failed records
     * @return array
     */
    public function getFailedRecords()
    {
        return $this->failedRecords;
    }

    /**
     * Creates a gridfield table row for a given record
     * @param  DataObject $record the record to create the row for
     * @return string         the html TR tag
     */
    protected function getRecordGridfieldRow($record)
    {
        $total = count($this->gridfield->getList()) + count($this->successRecords);
        $index = 0;
        $this->gridfield->setList(new ArrayList(array($record)));
        $rowContent = '';

        foreach ($this->gridfield->getColumns() as $column) {
            $colContent = $this->gridfield->getColumnContent($record, $column);
            // Null means this columns should be skipped altogether.
            if ($colContent === null) {
                continue;
            }

            $colAttributes = $this->gridfield->getColumnAttributes($record, $column);
            $rowContent .= HTML::createTag(
                'td',
                $colAttributes,
                $colContent
            );
        }

        $rowAttributes = array(
            'class' => 'ss-gridfield-item ' . implode(' ', $this->successClasses),
            'data-id' => $record->ID,
            'data-class' => $record->ClassName,
        );
        $row = HTML::createTag(
            'tr',
            $rowAttributes,
            $rowContent
        );
        return $row;
    }

    /**
     * Creates the response JSON body
     */
    public function createBody()
    {
        $body = array(
            'isDestructive' => $this->removesRows,
            'isError' => $this->isError(),
            'isWarning' => false,
            'message' => $this->message,
            'successClasses' => $this->successClasses,
            'failedClasses' => $this->failedClasses
        );

        if (!$this->isError()) {
            $body['records'] = array(
                'success' => array(),
                'failed' => array()
            );

            foreach ($this->successRecords as $record) {
                $data = array('id' => $record->ID, 'class' => $record->ClassName);
                if (!$this->removesRows) {
                    $data['row'] = $this->getRecordGridfieldRow($record);
                }
                $body['records']['success'][] = $data;
            }

            $body['records']['failed'] = $this->failedRecords;
        }

        if (count($body['records']['success']) === 0) {
            $body['isWarning'] = true;
        }

        $this->body = Convert::raw2json($body);
    }

    /**
     * Make sure the body has been created before output
     * Output body of this response to the browser
     */
    protected function outputBody()
    {
        $this->createBody();
        parent::outputBody();
    }

    /**
     * Catches fatal PHP eror and output something useful for the front end
     */
    public function shutdown()
    {
        $error = error_get_last();
        if ($error !== null ) {
            $this->setMessage($error['message']);
            $this->setStatusCode(500, $error['message']);
            $this->outputBody();
            exit();
        }
    }
}
