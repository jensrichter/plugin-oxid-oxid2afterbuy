
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
        'en'=>'Interface to Afterbuy API',
    ),
    'thumbnail'    => '',
    'version'      => '%%VERSION%%',
    'author'       => 'Fatchip GmbH',
    'url'          => 'http://www.fatchip.de',
    'email'        => 'support@fatchip.de',
    'extend'       => array(

    ),
    'files' => array(
        //core
        'fcafterbuyapi'     => 'fcoxid2afterbuy/lib/fcafterbuyapi.php',
        'fcafterbuyart'     => 'fcoxid2afterbuy/lib/fcafterbuyart.php',
        'fcafterbuystatus'  => 'fcoxid2afterbuy/lib/fcafterbuystatus.php',
        'fco2aartexport'    => 'fcoxid2afterbuy/core/fco2aartexport.php',
        'fco2aorderimport'  => 'fcoxid2afterbuy/core/fco2aorderimport.php',
        'fco2astatusexport' => 'fcoxid2afterbuy/core/fco2astatusexport.php',

        //controllers
        'fcafterbuy_article_admin' => 'fcoxid2afterbuy/controllers/admin/fcafterbuy_article_admin.php',
    ),
    'templates' => array(
        'fcafterbuy_article_admin.tpl' => 'fcoxid2afterbuy/views/admin/tpl/fcafterbuy_article_admin.tpl',
    ),
    'blocks' => array(
    ),
    'settings' => array(
        array( 'group' => 'fcafterbuy_debug', 'name' => 'iFcAfterbuyLogLevel', 'type' => 'str',  'value' => '3' ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyShopInterfaceBaseUrl', 'type' => 'str',  'value' => "https://www.afterbuy.de/afterbuy/ShopInterface.aspx" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyAbiUrl', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyPartnerId', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyPartnerPassword', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyUsername', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyUserPassword', 'type' => 'str',  'value' => "" ),
    )
);
