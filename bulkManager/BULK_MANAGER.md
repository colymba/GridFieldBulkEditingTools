# Bulk Manager
Perform actions on multiple records straight from the GridField. Comes with unlink, delete and bulk editing but you can easily create/add your own.

## Usage
Simply add GridFieldBulkManager to your GridFieldConfig
		
		$config->addComponent(new GridFieldBulkManager());
		
## Configuration
The component's option can be configurated individually or in bulk through the 'config' functions like this:

    $config->getComponentByType('GridFieldBulkManager')->setConfig( $reference, $value );
		
### $config overview
The available configuration options are:
* 'editableFields' : array of string referencing specific CMS fields available for editing
* 'fieldsClassBlacklist' : array of string referencing types (ClassName) of fields that wont be available for editing
* 'fieldsNameBlacklist' : array of string referencing the names of fields that wont be available for editing

## Custom actions
You can remove or add individual action or replace them all via `addBulkAction()` and `removeBulkAction()`

### Adding a custom action
To add a custom bulk action to the list use:

    $config->getComponentByType('GridFieldBulkManager')->addBulkAction('actionName', 'Dropdown label', 'ActionHandlerClassName', $frontEndConfig)

You can omit the handler's class name and the front-end config array, those will default to:
* `'GridFieldBulkAction'.ucfirst($name).'Handler'`
* `$config = array( 'isAjax' => true, 'icon' => 'accept', 'isDestructive' => false )`

#### Custom action handler
When creating your awn bulk action RequestHandler, you should extend `GridFieldBulkActionHandler` which will expose 2 usefull functions `getRecordIDList()` and `getRecords()` returning either and array with the selected records IDs or a DataList of the selected records.

Make sue to the define your `$allowed_actions` and `$url_handlers`. See `GridFieldBulkActionEditHandler`, `GridFieldBulkActionDeleteHandler` and `GridFieldBulkActionUnlinkHandler` for examples.

#### Front-end config
The last component's parameter lets you pass an array with configuration options for the UI/UX:
* `isAjax`: if true the action will be called via XHR request otherwise the broser will be redirected to the action's URL
* `icon`: lets you define which icon to use when the action is selected (SilverStripe button icon name only)
* `isDestructive`: if true a confirmation dialog will be shown before the action is processed