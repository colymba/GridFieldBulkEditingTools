<?php
//define global path to Components' root folder
if(!defined('BULKEDITTOOLS_UPLOAD_PATH'))
{
  $folder = rtrim(basename(dirname(__FILE__)));
	define('BULKEDITTOOLS_UPLOAD_PATH', $folder . '/bulkupload');
  define('BULKEDITTOOLS_MANAGER_PATH', $folder . '/bulkManager');
}