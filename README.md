# Plugin Oxid2Afterbuy
Export Articles from OXID eShop to Afterbuy

## License
see LICENSE

## Prefix
fc

## Version
%%VERSION%%
%%TODAY%%

## Link
https://www.fatchip.de/Plugins/OXID-eShop/OXID-2-Afterbuy.html

## Requirements


## Description
Interface to Afterbuy API

## Extend
oxorder
oxcounter

## Installation
The update-process is exactly the same!

1. Extract the module-package.
2. Copy the content of the folder `copy_this` into your shop root-folder (where `config.inc.php` is located).
3. Add FCAFTERBUYACTIVE to $this->aMultishopArticleFields array in your config.inc.php
4. Install database file corresponding to your shop edition:
    - OXID CE/PE: Execute/Upload under Service->Tools install_ce.sql
    - OXID EE: Execute/Upload under Service->Tools install_ee.sql
5. Generate database views in admin under Service->Tools    
5. Go to Extensions->Modules, select the "FATCHIP OXID 2 Afterbuy Connector" extension and press the "Activate" button in the "Overview" tab.
6. Empty "tmp" folder.
7. Go to Extensions->Modules, select the "FATCHIP OXID 2 Afterbuy Connector" extension and configure the module in the "Settings" tab.
8. Fire one or more batch scripts manually from your commandline or periodically by using a cronjob (Refer to your hoster for this) pointing on folder `modules/fcoxid2afterbuy/batch/`
   Depending on what you would like to do use:
   - For article catalogue exports from OXID to Afterbuy use `fco2aartexport_batch.php`
   - For importing orders from Afterbuy into OXID use `fco2aorderimport_batch.php`
   - For updating order states from OXID to Afterbuy use `fco2astatusexport_batch.php`

## De-installation
1. Go to Extensions->Modules, select the "FATCHIP OXID 2 Afterbuy Connector" extension and press the "Deactivate" Button in the "Overview" tab.
2. Delete the plugin dir.
3. Delete the database columns added by the plugin-installer.

## How to contribute
If you want to contribute to this plugin ask FATCHIP GmbH for access to the private repository, fork it and make a pull request with your changes.