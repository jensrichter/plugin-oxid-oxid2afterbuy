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
ToDO

## Requirements


## Description
Interface to Afterbuy API

## Extend
void

## Installation
The update-process is exactly the same!

1. Extract the module-package.
2. Copy the content of the folder `copy_this` into your shop root-folder (where `config.inc.php` is located).
3. Add FCAFTERBUYACTIVE to $this->aMultishopArticleFields array in your config.inc.php
4. 
    - OXID CE/PE: Execute/Upload under Service->Tools install_ce.sql
    - OXID EE: Execute/Upload under Service->Tools install_ee.sql
5. Go to Extensions->Modules, select the "FATCHIP OXID 2 Afterbuy Connector" extension and press the "Activate" button in the "Overview" tab.
6. Empty "tmp" folder.
7. Go to Extensions->Modules, select the "FATCHIP OXID 2 Afterbuy Connector" extension and configure the module in the "Settings" tab.


## De-installation
1. Go to Extensions->Modules, select the "FATCHIP OXID 2 Afterbuy Connector" extension and press the "Deactivate" Button in the "Overview" tab.
2. Delete the plugin dir.
3. Delete the database columns added by the plugin-installer.

## How to contribute
If you want to contribute to this plugin ask FATCHIP GmbH for access to the private repository, fork it and make a pull request with your changes.