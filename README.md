GridField Bulk Image Upload
===========================

SilverStripe 3 GridField component for uploading images in bulk into the managed Model relation, with option to edit fields on the fly.
This component takes bit and pieces around from CMSFileAddController, GridFieldDetailForm_ItemRequest, UploadField, and it overrides and adds some behaviors, templates and styles.

## Requirments
* SilverStripe 3.0

## Installation
* Download and copy module in SilverStripe root directory under 'GridFieldBulkImageUpload'
* Run dev/build?flush=all to regenerate the manifest
* run ?flush=all in CMS to force the templates to regenerate

## Usage

### Usage 1
Simplest usage, add the component to your GridField as below. The component will find the first Image has_one relation on the managed Model and it's editable db fields
				
		$config->addComponent(new GridFieldBulkImageUpload());

### Usage 2
You can specify which Image field to use and which fields are editable from the managed Model
$imageField (string): The name of the image field to use (should have 'ID' at the end: If your relation is set has 'MyImage' => 'Image', the parameter should be 'MyImageID')
$editableFields (array): list of db fields name as string that will be editable like: array('myTextField', 'myVarcharField', 'myEnumField')
				
		$config->addComponent(new GridFieldBulkImageUpload( $imageField, $editableFields ));

### Sample Files

#### Page Model

		class Page extends SiteTree {

			public static $db = array(
			);

			public static $has_many = array(
					'Visuals' => 'Visual'
			);

			public function getCMSFields() {
				$fields = parent::getCMSFields();

				$config = GridFieldConfig_RelationEditor::create();	
				$config->addComponent(new GridFieldBulkImageUpload());		
				$f = new GridField('Visuals', 'Case Study Visuals', $this->Visuals(), $config);
				$fields->addFieldToTab('Root.Visuals', $f);

				return $fields;
			}

		}

#### Visual Model
('Image', 'Type', 'Title' and 'Embed' Fields will be picked up automatically by the component)

		class Visual extends DataObject
		{
			public static $db = array(
					'Type' => "Enum('Image,Embed','Image')",
					'Title' => 'Text',
					'Embed' => 'HTMLText'
			);

			public static $has_one = array(
					'Page' => 'Page',
					'Image' => 'Image'
			);

			public function getCMSFields() {
				$fields = new FieldList();

				$fields->push( new DropdownField(
					'Type',
					'Type of visual',
					singleton('Visual')->dbObject('Type')->enumValues()
				));

				$fields->push( new TextField('Title', 'Title and Caption for images (useful for SEO)') );
				$fields->push( new TextareaField('Embed', 'HTML Embed code') );		

				$f = new UploadField('Image', 'Image file');				
				$fields->push($f);

				return $fields;
			}
		}

## Notes
* The HTML form fields for each editable fields are taken from the Model's getCMSFields() method
* Only (HTML)Text/Varchar and Enum fields are picked up by the automatic config for now

## @TODO
* Add option to specify upload folder
* Add individual actions for each upload (update + cancel)
* Styles: fade progress bar back to blue once updated
* Styles: add .loading class to buttons when saving/canceling
* Handle and display errors better for: creation, update, cancel
* Make it work not only for images -> might need to rename this component then?