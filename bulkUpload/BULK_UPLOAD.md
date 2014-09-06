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
### Component configuration
The component's option can be configurated through the `setConfig` functions like this:

    $config->getComponentByType('GridFieldBulkUpload')->setConfig($reference, $value);
		
The available configuration options are:
* 'fileRelationName' : sets the name of the `Image` or `File` has_one field to use (i.e. 'MyImage')

### UploadField configuration
The underlying `UploadField` can be configured via a set of APIs:
* `setUfConfig($reference, $value)` is used to set an `UploadField::$ufConfig` settings
* `setUfSetup($function, $param)` is used to pass function calls on to the `UploadField` itself
* `setUfValidatorSetup($function, $param)` is used to pass function calls on to the `UploadField` `Validator` itself

For example, to set the upload folder, which is set by calling `setFolderName` on the `UploadField`, and setting the upload method as sequential, you would use the following:

    $config->getComponentByType('GridFieldBulkUpload')
        ->setUfSetup('setFolderName', 'myFolder')
        ->setUfConfig('sequentialUploads', true);

Please see the [`UploadField` api](http://api.silverstripe.org/master/class-UploadField.html) and the [`Upload` api](http://api.silverstripe.org/master/class-Upload.html) for more info.


## Bulk Editing
To get a quick edit shortcut to all the newly upload files, please also add the `GridFieldBulkManager` component to your `GridFieldConfig`.