<?php
//define global path to Components' root folder
if (!defined('BULKEDITTOOLS_PATH')) {
    $folder = rtrim(basename(dirname(__FILE__)));
    define('BULKEDITTOOLS_PATH', $folder);
    define('BULKEDITTOOLS_UPLOAD_PATH', $folder.'/bulkUpload');
    define('BULKEDITTOOLS_MANAGER_PATH', $folder.'/bulkManager');
}
