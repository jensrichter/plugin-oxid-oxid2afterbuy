<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 20.11.18
 */

class fco2aartimport extends  fco2abase
{
    protected $_iMaxPages = 500;

    /**
     * Central entry point for triggering product import
     *
     * @param void
     * @return void
     */
    public function execute() {
        $this->_fcProcessProducts('variationsets');
        $this->_fcProcessProducts('nonsets');
    }

    /**
     * Process variation sets
     *
     * @param void
     * @return void
     */
    protected function _fcProcessProducts($sType)
    {
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();
        $iPage = 1;
        while($iPage > 0 && $iPage <= $this->_iMaxPages) {
            $sResponse =
                $oAfterbuyApi->getShopProductsFromAfterbuy($iPage, $sType);
            $oXmlResponse =
                simplexml_load_string($sResponse);
            $iPage =
                $this->_fcParseApiResponse($oXmlResponse, $sType);
        }
    }

    /**
     * Processing get shop products api response
     *
     * @param object $oXmlResponse
     * @param object $oAfterbuyApi
     * @param string $sType
     * @return int
     */
    protected function _fcParseApiResponse($oXmlResponse, $sType)
    {
        $iPage = $this->_fcGetNextPage($oXmlResponse);

        foreach ($oXmlResponse->Result->Products->Product as $oXmlProduct) {
            $this->_fcAddProductToOxid($oXmlProduct, $sType);
        }

        return $iPage;
    }

    /**
     * Adds/Updates afterbuy product into oxid
     *
     * @param $oXmlProduct
     */
    protected function _fcAddProductToOxid($oXmlProduct, $sType)
    {
        $oArticle = oxNew('oxarticle');
        $sOxid = $this->_fcProductExists($oXmlProduct);
        if ($sOxid) {
            $oArticle->load($sOxid);
        }

        $this->_fcAddDefaultProductData($oXmlProduct, $oArticle, $sType);
    }

    /**
     * Added basic productdata like
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     */
    protected function _fcAddDefaultProductData($oXmlProduct, &$oArticle, $sType)
    {
        $this->fcWriteLog(
            "DEBUG: Trying to add XML Product: \n".
            print_r($oXmlProduct ,true), 4);
        die("Test is ending here");
    }

    /**
     * Returns next page. If no next page available return zero
     *
     * @param $oXmlResponse
     * @return int
     */
    protected function _fcGetNextPage($oXmlResponse)
    {
        $iAvailablePages =
            (int) $oXmlResponse->Result->PaginationResult->TotalNumberOfPages;

        $iCurrentPage =
            (int) $oXmlResponse->Result->PaginationResult->PageNumber;

        $iNextPage =
            ($iCurrentPage<$iAvailablePages) ? ++$iCurrentPage : 0;

        return $iNextPage;
    }


    /**
     * Returns oxid of product if exists or false if not
     *
     * @param $oXmlProduct
     * @return mixed
     */
    protected function _fcProductExists($oXmlProduct)
    {
        $sProductId = (int) $oXmlProduct->ProductID;

        $oDb = oxDb::getDb();

        $sQuery = "
            SELECT 
                OXID 
            FROM 
                oxarticles_afterbuy
            WHERE 
                fcafterbuyid = ".$oDb->quote($sProductId);

        $mOxid = $oDb->getOne($sQuery);

        return $mOxid;
    }
}