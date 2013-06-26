GridField Bulk Editing Tools
============================
SilverStripe 3 GridField component set to facilitate bulk image upload, bulk record editing, unlinking and deleting.
Included are:
* [Bulk Image Upload](#bulk-image-upload): Bulk images upload and on the fly fields editing
* [Bulk Manager](#bulk-manager): Delete and unlink multiple records at once as well as editing records in bulk

## Requirements
* SilverStripe 3.1 (version master / 1.+)
* Silverstripe 3.0 (version [0.5](https://github.com/colymba/GridFieldBulkEditingTools/tree/0.5))

## Development notes
The master branch will try to be compatible with the latest SilverStripe release/pre-release. Please submit pull request against the master branch. Older branches are kept for compatibility but may not be maintained.

## Preview
![preview](screenshots/preview.png)
[More screenshots here.](screenshots)

## Installation
* Download and copy module in SilverStripe root directory and name it whatever you want
* Run dev/build?flush=all to regenerate the manifest
* run ?flush=all in CMS to force the templates to regenerate

## Bulk Image Upload
A component for uploading images in bulk into the managed Model relation, with option to edit fields on the fly.

### Usage 1
Simplest usage, add the component to your GridField as below. The component will find the first Image has_one relation on the managed Model and the record's editable CMS fields
		
		$config->addComponent(new GridFieldBulkImageUpload());

### Usage 2
You can specify which Image field to use and which fields are editable from the managed Model
$fileRelationName (string): The name of the File/Image field to use (If your relation is set has 'MyImage' => 'Image', the parameter should be 'MyImage')
$editableFields (array): list of db fields name as string that will be editable like: array('myTextField', 'myVarcharField', 'myEnumField')
		
		$config->addComponent(new GridFieldBulkImageUpload( $fileRelationName, $editableFields ));

### Configuration
The component's option can be configurated individually or in bulk through the 'config' functions like this:

    $config->getComponentByType('GridFieldBulkImageUpload')->setConfig( $reference, $value );
		
#### $config overview
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

## Bulk Manager
A component for Editing, deleting and unlinking records on the fly

### Usage
Add GridFieldBulkEditingTools component if not done already and simply add GridFieldBulkImageUpload
		
		$config->addComponent(new GridFieldBulkManager());
		
### Configuration
The component's option can be configurated individually or in bulk through the 'config' functions like this:

    $config->getComponentByType('GridFieldBulkManager')->setConfig( $reference, $value );
		
#### $config overview
The available configuration options are:
* 'editableFields' : array of string referencing specific CMS fields available for editing
* 'fieldsClassBlacklist' : array of string referencing types (ClassName) of fields that wont be available for editing
* 'fieldsNameBlacklist' : array of string referencing the names of fields that wont be available for editing

## Notes
* The Record edit form uses the Model's getCMSFields()

### @TODO

### Known bug
* When editing fields, if the last field of the edit form is a drop down or similar, the drop down menu is cropped off

### Bulk Image Upload
* Add individual actions for each upload (update + cancel)
* Handle and display errors better for: creation, update, cancel
* Make it work not only for images but Files too