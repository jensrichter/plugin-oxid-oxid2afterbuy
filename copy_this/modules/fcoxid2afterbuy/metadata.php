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
$sMetadataVersion = '1.1';
$sImgUrl = "https://www.fatchip.de/out/flow/img/favicons/favicon_16x16.png";

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
    'thumbnail'    => 'oxid2afterbuy.png',
    'version'      => '%%VERSION%%',
    'author'       => 'Fatchip GmbH',
    'url'          => 'http://www.fatchip.de',
    'email'        => 'support@fatchip.de',
    'extend'       => array(
        // models
        'oxorder'           => 'fcoxid2afterbuy/extend/application/models/fcafterbuy_oxorder',
        'oxcounter'         => 'fcoxid2afterbuy/extend/application/models/fcafterbuy_oxcounter',
    ),
    'files' => array(
        //core
        'fcafterbuyaddbaseproduct'  => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyaddbaseproduct.php',
        'fcafterbuyaddress'         => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyaddress.php',
        'fcafterbuyapi'             => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyapi.php',
        'fcafterbuyart'             => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyart.php',
        'fcafterbuyorder'           => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyorder.php',
        'fcafterbuyorderstatus'     => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyorderstatus.php',
        'fcafterbuypayment'         => 'fcoxid2afterbuy/core/afterbuy/fcafterbuypayment.php',
        'fcafterbuyshipping'        => 'fcoxid2afterbuy/core/afterbuy/fcafterbuyshipping.php',
        'fcafterbuysolditem'        => 'fcoxid2afterbuy/core/afterbuy/fcafterbuysolditem.php',
        'fco2abase'                 => 'fcoxid2afterbuy/core/fco2abase.php',
        'fco2aorder'                => 'fcoxid2afterbuy/core/fco2aorder.php',
        'fco2aartexport'            => 'fcoxid2afterbuy/core/fco2aartexport.php',
        'fco2aorderimport'          => 'fcoxid2afterbuy/core/fco2aorderimport.php',
        'fco2astatusexport'         => 'fcoxid2afterbuy/core/fco2astatusexport.php',

        //controllers->admin
        'fcafterbuy_article_admin'      => 'fcoxid2afterbuy/application/controllers/admin/fcafterbuy_article_admin.php',
        'fcafterbuy_admin'              => 'fcoxid2afterbuy/application/controllers/admin/fcafterbuy_admin.php',
        'fcafterbuy_list'               => 'fcoxid2afterbuy/application/controllers/admin/fcafterbuy_list.php',
        'fcafterbuy_payments'           => 'fcoxid2afterbuy/application/controllers/admin/fcafterbuy_payments.php',
        'fcafterbuy_orderinfo'          => 'fcoxid2afterbuy/application/controllers/admin/fcafterbuy_orderinfo.php',
    ),
    'templates' => array(
        'fcafterbuy_article_admin.tpl'  => 'fcoxid2afterbuy/application/views/admin/tpl/fcafterbuy_article_admin.tpl',
        'fcafterbuy_admin.tpl'          => 'fcoxid2afterbuy/application/views/admin/tpl/fcafterbuy_admin.tpl',
        'fcafterbuy_list.tpl'           => 'fcoxid2afterbuy/application/views/admin/tpl/fcafterbuy_list.tpl',
        'fcafterbuy_payments.tpl'       => 'fcoxid2afterbuy/application/views/admin/tpl/fcafterbuy_payments.tpl',
        'fcafterbuy_orderinfo.tpl'      => 'fcoxid2afterbuy/application/views/admin/tpl/fcafterbuy_orderinfo.tpl',
    ),
    'blocks' => array(
    ),
    'settings' => array(
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyShopInterfaceBaseUrl', 'type' => 'str',  'value' => "https://api.afterbuy.de/afterbuy/ShopInterface.aspx" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyAbiUrl', 'type' => 'str',  'value' => "https://api.afterbuy.de/afterbuy/ABInterface.aspx" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyPartnerId', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyPartnerPassword', 'type' => 'password',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyUsername', 'type' => 'str',  'value' => "" ),
        array( 'group' => 'fcafterbuy_connect', 'name' => 'sFcAfterbuyUserPassword', 'type' => 'password',  'value' => "" ),

        array( 'group' => 'fcafterbuy_export','name' => 'blFcAfterbuyExportAll', 'type' => 'bool',  'value' => false),

        array( 'group' => 'fcafterbuy_order', 'name' => 'blFcSendOrdersOnTheFly', 'type' => 'bool',  'value' => false ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyDebitPayments', 'type' => 'aarr',  'value' => array() ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyDebitDynBankname', 'type' => 'aarr',  'value' => array() ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyDebitDynBankzip', 'type' => 'aarr',  'value' => array() ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyDebitDynAccountNr', 'type' => 'aarr',  'value' => array() ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyDebitDynAccountOwner', 'type' => 'aarr',  'value' => array() ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'sFcAfterbuyFeedbackType', 'type' => 'select', 'value' => '0', 'constrains' => '0|1|2' ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'sFcAfterbuyDeliveryCalculation', 'type' => 'select', 'value' => '1', 'constrains' => '0|1' ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'sFcAfterbuySendVat', 'type' => 'select', 'value' => '0', 'constrains' => '0|1' ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'blFcAfterbuyUseOwnCustNr', 'type' => 'bool', 'value' => false ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'sFcAfterbuyCustIdent', 'type' => 'select', 'value' => '1', 'constrains' => '0|1|2' ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'sFcAfterbuyOverwriteEbayName', 'type' => 'select', 'value' => '1', 'constrains' => '0|1' ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyPaymentsSetPaid', 'type' => 'arr',  'value' => array() ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'sFcAfterbuyMarkId', 'type' => 'str', 'value' => '' ),
        array( 'group' => 'fcafterbuy_order', 'name' => 'aFcAfterbuyPayments', 'type' => 'aarr',
            'value' => array(
                1=>'Überweisung',
                2=>'Bar/Abholung',
                4=>'Nachnahme',
                5=>'Paypal',
                6=>'Überweisung/Rechnung',
                7=>'Bankeinzug',
                9=>'Click&Buy',
                11=>'Expresskauf/Bonicheck',
                12=>'Sofortüberweisung',
                13=>'Nachnahme/Bonicheck',
                14=>'Ebay Express',
                15=>'Moneybookers',
                16=>'Kreditkarte',
                17=>'Lastschrift',
                18=>'Billsafe',
                19=>'Kreditkartenzahlung',
                20=>'Ideal',
                21=>'Carte Bleue',
                23=>'Onlineüberweisung',
                24=>'Giropay',
                25=>'Dankort',
                26=>'EPS',
                27=>'Przelewy24',
                28=>'Carta Si',
                29=>'Postepay',
                30=>'Nordea Solo Sweden',
                31=>'Nordea Solo Finland',
                34=>'Billsafe Ratenkauf',
            )
        ),
        // array( 'group' => 'fcafterbuy_export', 'name' => 'sFcAfterbuyArticleIdent', 'type' => 'select', 'value' => '3', 'constrains' => '0|1|2|3' ),
        array( 'group' => 'fcafterbuy_debug', 'name' => 'iFcAfterbuyLogLevel', 'type' => 'select',  'value' => '0', 'constraints' => '0|1|2|3|4'),
    )
);
