<?php

/**
 * Load OXID framework
 */
function getShopBasePath()
{
    return dirname(__FILE__).'/../../../../';
}

require_once getShopBasePath() . "/bootstrap.php";

/**
 * Start the job
 */
$oJob = oxNew('fco2aartimport');
$oJob->categoryImport();
