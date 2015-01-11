# Silverstripe Client Export
[https://packagist.org/packages/maldicore/silverstripe-clientexport]

1. Exports all files and folders in the assets directory to a ZIP file in the CMS.
2. Exports the database to a ZIP file in the CMS

![Client Export Backup](https://www.diigo.com/file/image/rosqedezceqasdrrbzbpobosrp/SilverStripe+-+Client+Export.jpg)

- Initial Asset Export code by Stan Hutcheon - [Bigfork Ltd](http://bigfork.co.uk) - Silverstripe AssetExport (https://github.com/stnvh/silverstripe-assetexport)
- Database Export added By Maldicore Group Pvt Ltd

## Installation:

### Composer:

```
composer require "maldicore/silverstripe-clientexport" "dev-master"
```

### Download:

Clone this repo into a folder called ```clientexport``` in your silverstripe installation folder.

### Usage:

It adds 2 buttons in the 'Files' section of the CMS. Just click respective buttons to backup and download either the database or asset folder.

After installing via composer, you must */dev/build*
