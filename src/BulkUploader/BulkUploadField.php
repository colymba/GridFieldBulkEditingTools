<?php

namespace Colymba\BulkUpload;

use SilverStripe\Control\Controller;
//use SilverStripe\Forms\UploadField;
use SilverStripe\AssetAdmin\Forms\UploadField;

/**
 * Custom UploadField used to override Link()
 * and redirect UploadField action properly through the GridField.
 *
 * @author colymba
 */
class BulkUploadField extends UploadField
{
    /**
     * @var GridField
     */
    protected $gridfield;

    /**
     * @param GridField $gridfield
     * @param string    $parent
     * @param string    $folderName
     */
    public function __construct($gridfield, $parent, $folderName = null)
    {
        $this->gridfield = $gridfield;
        parent::__construct($parent, $folderName);
    }

    /**
     * {@inheritDoc}
     */
    public function Link($action = null)
    {
        return Controller::join_links($this->gridfield->Link(), 'bulkupload/', $action);
    }
}
