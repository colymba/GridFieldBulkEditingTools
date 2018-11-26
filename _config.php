<?php
//define global path to Components' root folder
if (!defined('BULKEDITTOOLS_PATH')) {
    $folder = rtrim(basename(dirname(__FILE__)));
    define('BULKEDITTOOLS_PATH', $folder);
    define('BULKEDITTOOLS_UPLOAD_PATH', $folder.'/bulkUpload');
    define('BULKEDITTOOLS_MANAGER_PATH', $folder.'/bulkManager');
}

// Ensure compatibility with PHP 7.2 ("object" is a reserved word),
// with SilverStripe 3.6 (using Object) and SilverStripe 3.7 (using SS_Object)
if (!class_exists('SS_Object')) {
    class_alias('Object', 'SS_Object');
}
