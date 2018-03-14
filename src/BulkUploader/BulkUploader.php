<?php

namespace Colymba\BulkUpload;

use Colymba\BulkUpload\BulkUploadHandler;
use Colymba\BulkUpload\BulkUploadField;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\Deprecation;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

/**
 * GridField component for uploading images in bulk.
 *
 * @author colymba
 */
class BulkUploader implements GridField_HTMLProvider, GridField_URLHandler
{
    /**
     * Component configuration.
     *
     * 'fileRelationName' => field name of the $has_one File/Image relation
     * 'recordClassName' => overrides the automatic DataObject class detection from gridfield->dataClass with a custom class name
     *
     * @var array
     */
    protected $config = array(
        'fileRelationName' => null,
        'recordClassName' => null
    );

    /**
     * If true, the component will Publish Versioned DataObject
     * if fasle they will be left as draft.
     * @var boolean
     */
    protected $autoPublishDataObject = false;

    /**
     * UploadField setup function calls.
     * List of setup functions to call on {@link UploadField} with the value to pass.
     *
     * e.g. array('setFolderName' => 'bulkUpload') will result in:
     * $uploadField->setFolderName('bulkUpload')
     *
     * @var array
     */
    protected $ufSetup = array(
        'setFolderName' => 'bulkUpload',
    );

    /**
     * Component constructor.
     *
     * @param string $fileRelationName
     * @param string $recordClassName
     */
    public function __construct($fileRelationName = null, $recordClassName = null, $autoPublish = false)
    {
        if ($fileRelationName != null) {
            $this->setConfig('fileRelationName', $fileRelationName);
        }

        if ($recordClassName != null) {
            $this->setConfig('recordClassName', $recordClassName);
        }

        $this->setAutoPublishDataObject($autoPublish);
    }

    /* **********************************************************************
     * Components settings and custom methodes
     * */

    /**
     * Set a component configuration parameter.
     *
     * @param string $reference
     * @param mixed  $value
     */
    public function setConfig($reference, $value)
    {
        if (!array_key_exists($reference, $this->config)) {
            user_error("Unknown option reference: $reference", E_USER_ERROR);
        }

        $this->config[$reference] = $value;

        return $this;
    }

    /**
     * Set Versioned DataObject auto publish config
     * @param boolean $autoPublish True to auto publish versioned dataobjects
     */
    public function setAutoPublishDataObject($autoPublish)
    {
        $this->autoPublishDataObject = $autoPublish;
        return $this;
    }

    /**
     * Get Versioned DataObject auto publish config
     * @return boolean              auto publish config value
     */
    public function getAutoPublishDataObject()
    {
        return $this->autoPublishDataObject;
    }

    /**
     * Set an UploadField setup function call.
     *
     * @param string $function
     * @param mixed  $param
     */
    public function setUfSetup($function, $param)
    {
        $this->ufSetup[$function] = $param;

        return $this;
    }

    /**
     * Returns one $config reference or the full $config.
     *
     * @param string $reference $congif parameter to return
     *
     * @return mixed
     */
    public function getConfig($reference = false)
    {
        if ($reference) {
            return $this->config[$reference];
        } else {
            return $this->config;
        }
    }

    /**
     * Returns one $ufSetup reference or the full config.
     *
     * @param string $reference $ufSetup parameter to return
     *
     * @return mixed
     */
    public function getUfSetup($reference = false)
    {
        if ($reference) {
            return $this->ufSetup[$reference];
        } else {
            return $this->ufSetup;
        }
    }

    /**
     * Returns the class name of container `DataObject` record.
     * Either as set in the component config or from the `Gridfield` `dataClass`.
     *
     * @return string
     */
    public function getRecordClassName($gridField)
    {
        return $this->getConfig('recordClassName') ? $this->getConfig('recordClassName') : $gridField->list->dataClass;
    }

    /**
     * Get the first has_one Image/File relation from the GridField managed DataObject
     * i.e. 'MyImage' => 'Image' will return 'MyImage'.
     *
     * @param  GridField $gridField
     * @return string Name of the $has_one relation
     */
    public function getDefaultFileRelationName($gridField)
    {
        $recordClass = $this->getRecordClassName($gridField);
        $hasOneFields = Config::inst()->get($recordClass, 'has_one');

        $imageField = null;
        foreach ($hasOneFields as $field => $type) {
            if ($type === 'SilverStripe\\Assets\\Image'
                ||  $type === 'SilverStripe\\Assets\\File'
                || is_subclass_of($type, 'SilverStripe\\Assets\\File')
            ) {
                $imageField = $field;
                break;
            }
        }

        return $imageField;
    }

    /**
     * Returns the name of the Image/File field name from the managed record
     * Either as set in the component config or the default one.
     *
     * @param  GridField $gridField
     * @return string
     */
    public function getFileRelationName($gridField)
    {
        $configFileRelationName = $this->getConfig('fileRelationName');

        return $configFileRelationName ? $configFileRelationName : $this->getDefaultFileRelationName($gridField);
    }

    /**
     * Return the ClassName of the fileRelation
     * i.e. 'MyImage' => 'Image' will return 'Image'
     * i.e. 'MyImage' => 'File' will return 'File'.
     *
     * @param  GridField $gridField
     * @return string file relation className
     */
    public function getFileRelationClassName($gridField)
    {
        $recordClass = $this->getRecordClassName($gridField);
        $hasOneFields = Config::inst()->get($recordClass, 'has_one');

        $fieldName = $this->getFileRelationName($gridField);
        if ($fieldName) {
            return $hasOneFields[$fieldName];
        } else {
            return 'SilverStripe\\Assets\\File';
        }
    }

    /**
     * Returned a configured UploadField instance
     * embedded in the gridfield heard.
     *
     * @param GridField $gridField Current GridField
     *
     * @return UploadField Configured UploadField instance
     */
    public function bulkUploadField($gridField)
    {
        $fileRelationName = $this->getFileRelationName($gridField);
        $fieldName = $fileRelationName . '_' . $this->getRecordClassName($gridField) . '_BU';
        $uploadField = BulkUploadField::create($gridField, $fieldName, '')
            ->setForm($gridField->getForm())
            ->setRecord(DataObject::create()) // avoid UploadField to get auto-config from the Page (e.g fix allowedMaxFileNumber)
            ;

        //UploadField setup
        foreach ($this->ufSetup as $fn => $param) {
            $uploadField->{$fn}($param);
        }

        $schema['data']['createFileEndpoint'] = [
            'url' => $gridField->Link('bulkupload/upload'),
            'method' => 'post',
            'payloadFormat' => 'urlencoded',
        ];

        $schema['data']['attachFileEndpoint'] = [
            'url' => $gridField->Link('bulkupload/attach'),
            'method' => 'post'
        ];

        $uploadField->setSchemaData($schema);

        return $uploadField;
    }

    /* **********************************************************************
     * GridField_HTMLProvider
     * */

    /**
     * HTML to be embedded into the GridField.
     *
     * @param GridField $gridField
     *
     * @return array
     */
    public function getHTMLFragments($gridField)
    {
        // permission check
        if (!singleton($gridField->getModelClass())->canEdit()) {
            return array();
        }

        // get uploadField
        $uploadField = $this->bulkUploadField($gridField);

        $data = ArrayData::create(array(
            'Colspan' => (count($gridField->getColumns())),
            'UploadField' => $uploadField->Field() // call ->Field() to get requirements in right order
        ));

        Requirements::javascript('colymba/gridfield-bulk-editing-tools:client/dist/js/main.js');
        Requirements::css('colymba/gridfield-bulk-editing-tools:client/dist/styles/main.css');
        Requirements::add_i18n_javascript('colymba/gridfield-bulk-editing-tools:client/lang');

        return array(
            'before' => $data->renderWith('Colymba\\BulkUpload\\BulkUploader'),
        );
    }

    /* **********************************************************************
     * GridField_URLHandler
     * */

    /**
     * Component URL handlers.
     *
     * @param GridField $gridField
     *
     * @return array
     */
    public function getURLHandlers($gridField)
    {
        return array(
            'bulkupload' => 'handleBulkUpload'
        );
    }

    /**
     * Pass control over to the RequestHandler.
     *
     * @param GridField      $gridField
     * @param HTTPRequest $request
     *
     * @return mixed
     */
    public function handleBulkUpload($gridField, $request)
    {
        $gridField->getForm()->getController()->pushCurrent();
        $handler = new \Colymba\BulkUpload\BulkUploadHandler($gridField, $this);

        return $handler->handleRequest($request);
    }
}
