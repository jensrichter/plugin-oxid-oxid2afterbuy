<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 20.11.18
 */

class fco2aartimport extends fco2abase
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
                $this->_fcParseApiProductResponse($oXmlResponse, $sType);
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
    protected function _fcParseApiProductResponse($oXmlResponse, $sType)
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
        $oArticle->fcAddCustomFieldsToObject();
        $sOxid = $this->_fcProductExists($oXmlProduct);
        if ($sOxid) {
            $oArticle->load($sOxid);
        }

        $this->fcWriteLog(
            "DEBUG: Trying to add/update XML Product: \n".
            print_r($oXmlProduct ,true), 4);

        $this->_fcAddProductBasicData($oXmlProduct, $oArticle, $sType);
        $this->_fcAddProductPictures($oXmlProduct, $oArticle, $sType);
        $this->_fcAddProductAttributes($oXmlProduct, $oArticle, $sType);
        $this->_fcAddProductCategories($oXmlProduct, $oArticle, $sType);
    }

    /**
     * Added basic productdata
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     */
    protected function _fcAddProductBasicData($oXmlProduct, &$oArticle, $sType)
    {
        // identification
        $this->_fcAddIdentificationData($oXmlProduct, $oArticle, $sType);

        // description
        $this->_fcAddDescriptionData($oXmlProduct, $oArticle, $sType);

        // productdata
        $this->_fcAddProductAmounts($oXmlProduct, $oArticle, $sType);

        // prices
        $this->_fcAddProductPrices($oXmlProduct, $oArticle, $sType);
    }

    /**
     * Adds identification data to oxid product
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     */
    protected function _fcAddProductPrices($oXmlProduct, &$oArticle, $sType)
    {
        $oArticle->oxarticles__oxprice =
            new oxField((double) $oXmlProduct->SellingPrice);
        $oArticle->oxarticles__oxbprice =
            new oxField((double) $oXmlProduct->BuyingPrice);
        $oArticle->oxarticles__oxpricea =
            new oxField((double) $oXmlProduct->DealerPrice);
        $oArticle->oxarticles__oxvat =
            new oxField((int) $oXmlProduct->TaxRate);

        $aScaledDiscounts = (array) $oXmlProduct->ScaledDiscounts;

        foreach ($aScaledDiscounts as $aScaledDiscount) {
            $this->_fcSetScaledDiscount($oArticle, $aScaledDiscount);
        }
    }

    /**
     * Add a scalediscount into oxid system
     *
     * @param $oArticle
     * @param $aScaledDiscount
     */
    protected function _fcSetScaledDiscount($oArticle, $aScaledDiscount)
    {
        $oConfig = $this->getConfig();
        $sShopId = $oConfig->getShopId();
        $dListPrice = $oArticle->oxarticles__oxprice->value;
        $dScaledPrice = (double) $aScaledDiscount['ScaledPrice'];

        $dAbsDiscount = $dListPrice - $dScaledPrice;
        $aParams = array();
        $aParams['oxprice2article__oxshopid'] = $sShopId;
        $aParams['oxprice2article__oxamount'] = $aScaledDiscount['ScaledQuantity'];
        $aParams['oxprice2article__oxaddabs'] = $dAbsDiscount;

        $oArticlePrice = oxNew("oxbase");
        $oArticlePrice->init("oxprice2article");
        $oArticlePrice->assign($aParams);
    }

    /**
     * Adds identification data to oxid product
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     */
    protected function _fcAddProductAmounts($oXmlProduct, &$oArticle, $sType)
    {
        $oArticle->oxarticles__oxstock =
            new oxField((int) $oXmlProduct->Quantity);
        $oArticle->oxarticles__oxunitname =
            new oxField((string) $oXmlProduct->UnitOfQuantity);
        $oArticle->oxarticles__oxunitquantity =
            new oxField((int) $oXmlProduct->BasepriceFactor);
        $oArticle->oxarticles__oxweight =
            new oxField((int) $oXmlProduct->Weight);

    }

    /**
     * Adds identification data to oxid product
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     */
    protected function _fcAddIdentificationData($oXmlProduct, &$oArticle, $sType)
    {
        $oArticle->setId($oXmlProduct->ProductID);
        $sArtNum = $oXmlProduct->EAN ?: $oXmlProduct->Anr;
        $oArticle->oxarticles__fcafterbuyid = new oxField($oXmlProduct->ProductID);
        $oArticle->oxarticles__oxartnum = new oxField($sArtNum);
    }

    /**
     * Adds identification data to oxid product
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     */
    protected function _fcAddDescriptionData($oXmlProduct, &$oArticle, $sType)
    {
        $oArticle->oxarticles__oxtitle = new oxField($oXmlProduct->Name);
        $oArticle->oxarticles__oxshortdesc = new oxField($oXmlProduct->ShortDescription);
        $oArticle->setArticleLongDesc($oXmlProduct->Description);
    }

    /**
     * Adds AB-Attributes of product into OXID Shop
     *
     * @param $oXmlProduct
     * @param $oArticle
     * @param $sType
     */
    protected function _fcAddProductAttributes($oXmlProduct, $oArticle, $sType)
    {
        $aProductAttributes = (array) $oXmlProduct->Attributes;

        foreach ($aProductAttributes as $aProductAttribute) {
            $sAttributeId = $this->_fcGetAttributeId($aProductAttribute);
            $sArticleId = $oArticle->getId();
            $sAttributeValue = $aProductAttribute['AttributWert'];
            $this->_fcAddAttributeValue($sAttributeId, $sArticleId, $sAttributeValue);
        }
    }

    /**
     * Create or update attribute value
     *
     * @param $sAttributeId
     * @param $sArticleId
     * @param $sAttributeValue
     */
    protected function _fcAddAttributeValue($sAttributeId, $sArticleId, $sAttributeValue)
    {
        $oDb = oxDb::getDb();
        $sOxid = $this->_fcGetAttributeValueId($sAttributeId, $sArticleId);


        if ($sOxid) {
            $sQuery = "
                UPDATE oxobject2attribute
                SET oxvalue=".$oDb->quote($sAttributeValue)."
                WHERE OXID=".$oDb->quote($sOxid);
        } else {
            $oUtilsObject = oxRegistry::get('oxUtilsObject');
            $sNewOxid = $oUtilsObject->generateUId();
            $sQuery = "
                INSERT INTO oxobject2attribute
                (
                  OXID,
                  OXOBJECTID,
                  OXATTRID,
                  OXVALUE
                )
                VALUES
                (
                  ".$oDb->quote($sNewOxid).",
                  ".$oDb->quote($sArticleId).",
                  ".$oDb->quote($sAttributeId).",
                  ".$oDb->quote($sAttributeValue)."
                )
            ";
        }

        $oDb->execute($sQuery);
    }

    /**
     * Returns id of attribute-value-assignment or false if none could
     * be found
     *
     * @param $sAttributeId
     * @param $sArticleId
     * @return mixed string|bool
     */
    protected function _fcGetAttributeValueId($sAttributeId, $sArticleId)
    {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $sQuery = "
            SELECT 
                OXID 
            FROM 
                oxobject2attribute 
            WHERE
                OXOBJECTID=".$oDb->quote($sArticleId)." AND
                OXATTRID=".$oDb->quote($sAttributeId)."
            LIMIT 1
        ";

        $mOxid = $oDb->getOne($sQuery);

        return $mOxid;
    }

    /**
     * Fetches or creates attribute id
     *
     * @param $aProductAttribute
     * @return string
     */
    protected function _fcGetAttributeId($aProductAttribute)
    {
        $sAttributeName = trim($aProductAttribute['AttributName']);
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $sQuery = "
            SELECT 
                OXID 
            FROM 
                oxattribute 
            WHERE 
                OXTITLE =".$oDb->quote($sAttributeName);

        $sOxid = $oDb->getOne($sQuery);

        if ($sOxid) return $sOxid;

        $sOxid = $this->_fcCreateAttribute($aProductAttribute);

        return $sOxid;
    }

    /**
     * Creates a new attribute of AB-Attribute
     *
     * @param $aProductAttribute
     * @return string
     */
    protected function _fcCreateAttribute($aProductAttribute)
    {
        $sAttributeName = trim($aProductAttribute['AttributName']);
        $oAttribute = oxNew('oxattribute');
        $oAttribute->setTitle($sAttributeName);
        $sOxid = $oAttribute->getId();
        $oAttribute->save();

        return $sOxid;
    }

    /**
     * Adds AB-Product categories to OXID Shop
     *
     * @param $oXmlProduct
     * @param $oArticle
     * @param $sType
     */
    protected function _fcAddProductCategories($oXmlProduct, $oArticle, $sType)
    {
        $aCatalogs = (array) $oXmlProduct->Catalogs;
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();

        foreach ($aCatalogs as $aCatalog) {
            $sCatalogId = $aCatalog['CatalogID'];
            $sResponse = $oAfterbuyApi->getShopCatalogsById($sCatalogId);
            $oXmlResponse =
                simplexml_load_string($sResponse);
            $this->_fcParseApiCatalogResponse($oXmlResponse, $oArticle, $sType);
        }
    }

    /**
     * Handles response of GetShopCatalogs (ByID) Call
     *
     * @param $oXmlResponse
     * @param $oArticle
     * @param $sType
     * @return void
     */
    protected function _fcParseApiCatalogResponse($oXmlResponse, $oArticle, $sType)
    {
        /**
         * @todo:
         */
    }

    /**
     * Handles product picture handling
     *
     * @param $oXmlProduct
     * @param $oArticle
     * @param $sType
     */
    protected function _fcAddProductPictures($oXmlProduct, &$oArticle, $sType)
    {
        $aProductPictures = (array) $oXmlProduct->ProductPictures;

        foreach ($aProductPictures as $iIndex=>$aProductPicture) {
            $iPicNr = $iIndex + 1;
            $sImageUrl = (string) $aProductPicture['Url'];
            $sTargetFileName = basename($sImageUrl);
            $this->_fcDownloadImage($sImageUrl, $sTargetFileName, $iPicNr);
            $sField = "oxarticles__oxpic".$iPicNr;
            $oArticle->$sField = new oxField($sTargetFileName);
        }
    }

    /**
     * Downloads and places image into master folder
     *
     * @param $sImageUrl
     * @param $sTargetFileName
     * @param $iPicNr
     */
    protected function _fcDownloadImage($sImageUrl, $sTargetFileName, $iPicNr)
    {
        $oConfig = $this->getConfig();
        $sPicNrFolder = (string) $iPicNr;
        $sMasterPictureFolder = $oConfig->getMasterPicturePath();
        $sTargetFolder = "{$sMasterPictureFolder}/product/{$sPicNrFolder}";
        $sTargetPath = "{$sTargetFolder}/{$sTargetFileName}";
        $oCurl = curl_init($sImageUrl);
        $oFile = fopen($sTargetPath, 'wb');
        curl_setopt($oCurl, CURLOPT_FILE, $oFile);
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_exec($oCurl);
        curl_close($oCurl);
        fclose($oFile);
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