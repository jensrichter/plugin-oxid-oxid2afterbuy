
<?php
 /*
 * @package FATCHIP Oxid2Afterbuy
 * @copyright (C) VIA-Online GmbH
 * @author FATCHIP GmbH
 * 
 * This Software is the property of VIA-Online GmbH
 * and is protected by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be
 * prosecuted by civil and criminal law.
 */
/**
 * Metadata version
 */
$sMetadataVersion = '1.0';
$oViewConf = oxNew("oxViewConfig");
$sImgUrl = $oViewConf->getModuleUrl('fcoxid2afterbuy', 'fatchip.png');


/**
 * Module information
 */
$aModule = array(
    'id'           => 'fcoxid2afterbuy',
    'title'        => '<img src="' . $sImgUrl . '" alt="FC"> FATCHIP OXID 2 Afterbuy Connector',
    'description'  => array(
        'de'=>'Schnittstelle zur Afterbuy API',
        'en'=>'Interface to Afterbuy API ',
    ),
    'thumbnail'    => '',
    'version'      => '1.0.0',
    'author'       => 'Fatchip GmbH',
    'url'          => 'http://www.fatchip.de',
    'email'        => 'support@fatchip.de',
    'extend'       => array(
    ),
    'files' => array(
        'fcafterbuyapi'     => 'fcoxid2afterbuy/lib/fcafterbuyapi.php',
        'fcafterbuyart'     => 'fcoxid2afterbuy/lib/fcafterbuyart.php',
        'fcafterbuystatus'  => 'fcoxid2afterbuy/lib/fcafterbuystatus.php',
        'fco2aartexport'    => 'fcoxid2afterbuy/core/fco2aartexport.php',
        'fco2aorderimport'  => 'fcoxid2afterbuy/core/fco2aorderimport.php',
        'fco2astatusexport' => 'fcoxid2afterbuy/core/fco2astatusexport.php',
    ),
    'templates' => array(
    ),
    'blocks' => array(
    ),
    'settings' => array(
        array( 'group' => 'debug', 'name' => 'iFcLogLevel', 'type' => 'str',  'value' => '3' ),
        array( 'group' => 'connect', 'name' => 'sFcAfterbuyShopInterfaceBaseUrl', 'type' => 'str',  'value' => "https://www.afterbuy.de/afterbuy/ShopInterface.aspx" ),
        array( 'group' => 'connect', 'name' => 'sFcAfterbuyAbiUrl', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'connect', 'name' => 'sFcAfterbuyPartnerId', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'connect', 'name' => 'sFcAfterbuyPartnerPassword', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'connect', 'name' => 'sFcAfterbuyUsername', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'connect', 'name' => 'sFcAfterbuyUserPassword', 'type' => 'str',  'value' => "" ),
    )
);
