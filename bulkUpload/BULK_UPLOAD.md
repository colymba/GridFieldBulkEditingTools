# Bulk Upload
A component for uploading images and/or files in bulk into `DataObject` managed by the `GridField`.

## Usage 1
Simplest usage, add the component to your `GridFieldConfig` as below. The component will find the first `Image` or `File` has_one relation to use on the managed `DataObject`.
		
		$config->addComponent(new GridFieldBulkUpload());

## Usage 2
You can specify which `Image` or `File` field to use.
$fileRelationName (string, optional): The name of the `Image` or `File` has_one field to use (If your relation is set has 'MyImage' => 'Image', the parameter should be 'MyImage')
		
		$config->addComponent(new GridFieldBulkUpload($fileRelationName));

## Configuration
The component's option can be configurated individually or in bulk through the 'config' functions like this:

    $config->getComponentByType('GridFieldBulkUpload')->setConfig($reference, $value);
		
### $config overview
The available configuration options are:
* 'fileRelationName' : sets the name of the `Image` or `File` has_one field to use (i.e. 'MyImage')
* 'folderName' : name of the folder where the images or files should be uploaded
* 'maxFileSize' : integer, maximum upload file size in bytes
* 'sequentialUploads' : boolean, if true files will be uploaded one by one

## Bulk Editing
To get a quick edit shortcut to all the newly upload files, please also add the `GridFieldBulkManager` component to your `GridFieldConfig`.