# Bulk Image Upload
A component for uploading images in bulk into the managed Model relation, with option to edit fields on the fly.

## Usage 1
Simplest usage, add the component to your GridField as below. The component will find the first Image has_one relation on the managed Model and the record's editable CMS fields
		
		$config->addComponent(new GridFieldBulkImageUpload());

## Usage 2
You can specify which Image field to use and which fields are editable from the managed Model
$fileRelationName (string, optional): The name of the File/Image field to use (If your relation is set has 'MyImage' => 'Image', the parameter should be 'MyImage')
$editableFields (array, optional): list of db fields name as string that will be editable like: array('myTextField', 'myVarcharField', 'myEnumField')
		
		$config->addComponent(new GridFieldBulkImageUpload( $fileRelationName, $editableFields ));

## Configuration
The component's option can be configurated individually or in bulk through the 'config' functions like this:

    $config->getComponentByType('GridFieldBulkImageUpload')->setConfig( $reference, $value );
		
### $config overview
The available configuration options are:
* 'fileRelationName' : sets the name of the File/Image field of the managed Model (i.e. 'MyImage')
* 'editableFields' : array of string referencing specific CMS fields available for editing
* 'fieldsClassBlacklist' : array of string referencing types (ClassName) of fields that wont be available for editing
* 'fieldsNameBlacklist' : array of string referencing the names of fields that wont be available for editing
* 'folderName' : name of the folder where the images should be uploaded
* 'sequentialUploads' : boolean, if true files will be uploaded one by one
* 'maxFileSize' : integer, maximum upload file size in bytes 

Each option can be set through the component's method setConfig( $reference, $value )
In addition, some configuration option can be set more specifically via individual methods:
* addFieldNameToBlacklist( $fieldName )
* addClassToBlacklist( $className )
* removeFieldNameFromBlacklist( $fieldName )
* removeClassFromBlacklist( $className )