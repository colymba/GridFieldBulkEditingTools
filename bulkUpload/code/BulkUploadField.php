<?php
/**
 * Custom UploadField used to override Link()
 * and redirect UploadField action properly through the GridField.
 *
 * @author colymba
 */
class BulkUploadField extends UploadField
{
    protected $gridfield;

    public function __construct($gridfield, $parent, $folderName = null) {
        $this->gridfield = $gridfield;
        parent::__construct($parent, $folderName);
    }

    public function Link($action = null)
    {
        return Controller::join_links($this->gridfield->Link(), 'bulkupload/', $action);
    }
}
