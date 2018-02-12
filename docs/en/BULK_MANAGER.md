# Bulk Manager
Perform actions on multiple records straight from the GridField. Comes with *unlink*, *delete* and bulk *editing*. You can also easily create/add your own.

## Usage
Simply add component to your `GridFieldConfig`

```php
$config->addComponent(new \Colymba\BulkManager\BulkManager());
```

## Configuration
The component's options can be configurated individually or in bulk through the 'config' functions like this:

```php
$config->getComponentByType('Colymba\\BulkManager\\BulkManager')->setConfig($reference, $value);
```

### $config overview
The available configuration options are:
* 'editableFields' : array of string referencing specific CMS fields available for editing

## Custom actions
You can remove or add individual action or replace them all via `addBulkAction()` and `removeBulkAction()`

### Adding a custom action
To add a custom bulk action to the list use:

```php
$config
    ->getComponentByType('Colymba\\BulkManager\\BulkManager')
    ->addBulkAction('Namespace\\ClassName')
```

#### Custom action handler
When creating your own bulk action `RequestHandler`, you should extend `Colymba\BulkManager\BulkAction\Handler` which will expose 2 useful functions `getRecordIDList()` and `getRecords()` returning either an array with the selected records IDs or a `DataList` of the selected records.

Make sure to define the handler's `$url_segment`, from which the handler will be called and its relating `$allowed_actions` and `$url_handlers`. See `Handler`, `DeleteHandler` and `UnlinkHandler` for examples.

#### Front-end config
Bulk action handler's front-end configuration is set via class properties `label`, `icon`, `buttonClasses`, `xhr` and `destructive`. See `Handler`, `DeleteHandler` and `UnlinkHandler` for reference and examples. 
