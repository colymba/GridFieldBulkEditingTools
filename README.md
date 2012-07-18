GridFieldBulkImageUpload
========================

SilverStripe 3 GridField component for uploading images in bulk into the managed DataObject relation, with option to edit fields on the fly.
This component takes bit and pieces around from CMSFileAddController, GridFieldDetailForm_ItemRequest, UploadField, and it overrides and adds some behaviors, templates and styles.

## Requirments
* SilverStripe 3.0

## Installation
* Download and copy module in SilverStripe root directory under 'GridFieldBulkImageUpload'
* Run dev/build?flush=all to regenerate the manifest
* run ?flush=all in CMS to force the templates to regenerate

## Usage 1
Simplest usage, add the component to your GridField as below. The component will find the first Image has_one relation on the managed object and it's editable db fields
		
		:::php
		$config->addComponent(new GridFieldBulkImageUpload());

## Usage 2
Same as 1 but you can specify which Image field to use and which fields are editable
$imageField: string: The name of the image field to use (should have 'ID' at the end: If your relation is set has 'MyImage' => 'Image', the parameter should be 'MyImageID')
$editableFields: array: list of db fields name as string that will be editable like: array('myTextField', 'myVarcharField', 'myEnumField')
		
		:::php
		$config->addComponent(new GridFieldBulkImageUpload( $imageField, $editableFields ));

## Notes
* The HTML form fields for each editable fields are taken from the DataObject's getCMSFields() method
* Only (HTML)Text/Varchar and Enum fields are picked up by the automatic config

## @TODO
* Add option to specify upload folder
* Styles: fade back progress to blue once updated
* Handle and display errors better for: creation, update, cancel
* Make it work not only for images -> might need to rename this component then?