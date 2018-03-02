GridField Bulk Editing Tools
============================

[![Latest Stable Version](https://poser.pugx.org/colymba/gridfield-bulk-editing-tools/v/stable.svg)](https://github.com/colymba/GridFieldBulkEditingTools/releases)
[![Latest Unstable Version](https://poser.pugx.org/colymba/gridfield-bulk-editing-tools/v/unstable.svg)](https://github.com/colymba/GridFieldBulkEditingTools/tree/master)
[![License](https://poser.pugx.org/colymba/gridfield-bulk-editing-tools/license.svg)](#license-and-copyright)

Set of SilverStripe 4 GridField components to facilitate bulk file upload & record editing.


## Components:
* [Bulk Upload](#bulk-upload): Upload multiple images or files at once into DataObjects
* [Bulk Manager](#bulk-manager): Delete, Unlink, Edit (and more) multiple records at once

## Requirements
* SilverStripe 4.0 (master / 3.+)
* SilverStripe 3.1 (version 2.+ / 1.+)
* Silverstripe 3.0 (version 0.5)

## Installation
`composer require colymba/gridfield-bulk-editing-tools`

## 3.0.0 deprecations
The 3.x versions of this module require SilverStripe 4.x+, and PHP 5.5 or above:

* Namespaces are implemented, and some class names have changed (see `.upgrade.yml` for mapping)

## 2.0.0 deprecations
Major deprections in latest 2.0.0 release:
* The `GridFieldBulkImageUpload` has been renamed to `GridFieldBulkUpload`.
* `onBulkImageUpload` callback has been renamed to `onBulkUpload`

## Bulk Upload
Upload multiple images or files at once into DataObjects. Perfect for galleries and the like.

```php
$config->addComponent(new \Colymba\BulkUpload\BulkUploader());
```

### Versioned
By default `BulkUploader` will write to the current stage (most likely Draft). To auto publish your `DataObject`, use the following param or config:
```php
$config->addComponent(new \Colymba\BulkUpload\BulkUploader(null, null, true));
```
OR
```php
$config->getComponentByType('Colymba\\BulkUpload\\BulkUploader')->setAutoPublishDataObject(true);
```

Your `DataObject` should `own` the `Image`/`File` relation so it is published at the same time: [See SilverStripe DataObject ownership DOC](https://github.com/silverstripe/silverstripe-framework/blob/4.0/docs/en/02_Developer_Guides/00_Model/10_Versioning.md#dataobject-ownership)

See [BULK_UPLOAD.md](docs/en/BULK_UPLOAD.md) for detailed configuration.

## Bulk Manager
Perform actions on multiple records straight from the GridField

```php
$config->addComponent(new \Colymba\BulkManager\BulkManager());
```

See [BULK_MANAGER.md](docs/en//BULK_MANAGER.md) for detailed configuration.

## Tooling
* `npm run watch` for development
* `npm run dist` before pushing to the repo

## Translations

Translations of the natural language strings are managed through a third party translation interface, transifex.com.

Please use [https://www.transifex.com/projects/p/gridfieldbulkeditingtools/](https://www.transifex.com/projects/p/gridfieldbulkeditingtools/) to contribute translations, rather than sending pull requests with YAML/JS files.

## License and Copyright

[BSD 3-clause license](LICENSE)
