<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 20.11.18
 */

class fco2aartimport extends fco2abase
{
    /**
     * Number ox maximum pages that will be processed
     * @var int
     */
    protected $_iMaxPages = 500;

    /**
     * List of variation ids and their correspending parentid
     * @var array
     */
    protected $_aVariations = array();

    /**
     * Central entry point for triggering product import
     *
     * @param void
     * @return void
     */
    public function execute() {
        $blAllowed = $this->fcJobExecutionAllowed('artimport');
        if (!$blAllowed) {
            echo "Execution of artimport is not allowed by configuration\n";
            exit(1);
        }

        $this->_fcProcessCategoryTree();
        $this->_fcProcessProducts('variationsets');
        $this->_fcProcessProducts('nonsets');
        $this->_fcProcessParentCategoryAssignment();
        $this->_fcUpdateCategoryIndex();
    }

    public function categoryImport() {
        $blAllowed = $this->fcJobExecutionAllowed('artimport');
        if (!$blAllowed) {
            echo "Execution of artimport is not allowed by configuration\n";
            exit(1);
        }

        $this->_fcProcessCategoryTree();
        $this->_fcUpdateCategoryIndex();
    }

    public function productImport($isUpdate = false) {
        $blAllowed = $this->fcJobExecutionAllowed('artimport');
        if (!$blAllowed) {
            echo "Execution of artimport is not allowed by configuration\n";
            exit(1);
        }

        $updateFrom = $this->getUpdateFromTimestamp($isUpdate);

        $this->_fcProcessProducts('variationsets', $updateFrom);
        $this->_fcProcessProducts('nonsets', $updateFrom);
        $this->_fcProcessParentCategoryAssignment();
    }

    /**
     * Rebuilding nested sets information
     *
     * @param void
     * @return void
     */
    protected function _fcUpdateCategoryIndex()
    {
        $oCatList = oxNew('oxCategoryList');
        $oCatList->updateCategoryTree();
    }

    /**
     * Due to there is no product assignments for variattionsets
     * we need to determine variant assignments and also have to
     * assign parent products to categories
     *
     * @param void
     * @return void
     */
    protected function _fcProcessParentCategoryAssignment()
    {
        $oAfterbuyDb = oxNew('fco2adatabase');
        $aMissingAssignments = $oAfterbuyDb->fcGetMissingParentAssignments();

        $blValid = (
            is_array($aMissingAssignments) &&
            count($aMissingAssignments)
        );

        if (!$blValid) return;

        foreach ($aMissingAssignments as $aMissingAssignment) {
            $sArticleId = $aMissingAssignment['sArticleId'];
            $sCategoryId = $aMissingAssignment['sCategoryId'];
            $this->_fcAssignCategoryProducts($sCategoryId, $sArticleId);
        }
    }

    /**
     * Fetching category information from AB and create
     * category structure in OXID
     *
     * @param void
     * @return void
     */
    protected function _fcProcessCategoryTree()
    {
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();
        $sResponse = $oAfterbuyApi->getShopCatalogs();
        $this->_fcParseCatalogStructure($sResponse);
    }

    /**
     * Parses response from afterbuy, create object and iterate
     * through it
     *
     * @param $sResponse
     * @return array
     */
    protected function _fcParseCatalogStructure($sResponse) {
        if (empty($sResponse)) return array();
        $oXmlResponse = simplexml_load_string($sResponse);

        $aCatalogs = $oXmlResponse->xpath('Result/Catalogs/Catalog');
        foreach ($aCatalogs as $oCatalog) {
            $this->_fcCreateOxidCategory($oCatalog);
        }
    }

    /**
     * Recursively create oxid categories and
     * product assignments
     *
     * @param $oCatalog
     * @return void
     */
    protected function _fcCreateOxidCategory($oCatalog)
    {
        $aCatalog = (array) $oCatalog;
        $this->_fcCreateCategory($aCatalog);

        // assigned products
        $sCatalogId = (string) $oCatalog->CatalogID;

        $aCatalogProducts = $oCatalog->xpath('CatalogProducts/ProductID');
        $this->_fcAssignCategoryProducts($sCatalogId, $aCatalogProducts);

        if (!isset($oCatalog->Catalog)) return;

        foreach ($oCatalog->Catalog as $oSubCatalog) {
            $this->_fcCreateOxidCategory($oSubCatalog);
        }
    }

    /**
     * Process variation sets
     *
     * @param void
     * @return void
     */
    protected function _fcProcessProducts($sType, $updateFrom = '')
    {
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();
        $iPage = 1;
        while($iPage > 0 && $iPage <= $this->_iMaxPages) {
            $sResponse =
                $oAfterbuyApi->getShopProductsFromAfterbuy($iPage, $sType, $updateFrom);
            $oXmlResponse =
                simplexml_load_string($sResponse, null, LIBXML_NOCDATA);
            $iPage =
                $this->_fcParseApiProductResponse($oXmlResponse, $sType);
        }
    }

    /**
     * Processing get shop products api response
     *
     * @param object $oXmlResponse
     * @param string $sType
     * @return int
     */
    protected function _fcParseApiProductResponse($oXmlResponse, $sType)
    {
        $iPage = $this->_fcGetNextPage($oXmlResponse);

        $aProducts = $oXmlResponse->xpath('Result/Products/Product');

        foreach ($aProducts as $oXmlProduct) {
            if($this->_fcCheckIfArticleNumberIsValid($oXmlProduct) == false) {
                continue;
            }
            $this->_fcAddProductToOxid($oXmlProduct, $sType);
        }

        return $iPage;
    }

    /**
     * Adds/Updates afterbuy product into oxid
     *
     * @param $oXmlProduct
     * @return void
     */
    protected function _fcAddProductToOxid($oXmlProduct, $sType)
    {
        $oArticle = oxNew('oxarticle');
        $oArticle->fcAddCustomFieldsToObject();
        $sOxid = $this->_fcProductExists($oXmlProduct);
        if ($sOxid) {
            $oArticle->load($sOxid);
        }
        $this->addProductPostLoad($oArticle, $oXmlProduct);

        $this->oApiLogger->fcWriteLog(
            "DEBUG: Trying to add/update XML Product: \n".
            print_r($oXmlProduct ,true), 4);

        $this->_fcAddProductBasicData($oXmlProduct, $oArticle, $sType);
        $this->_fcAddProductPictures($oXmlProduct, $oArticle, $sType);
        $this->_fcAddProductAttributes($oXmlProduct, $oArticle, $sType);

        $this->addProductPreSave($oArticle, $oXmlProduct);
        $oArticle->save();
    }

    /**
     * Added basic productdata
     *
     * @param object $oXmlProduct
     * @param object $oArticle
     * @param string $sType
     * @return void
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
        $sProductId = (string) $oXmlProduct->ProductID;
        $oArticle->setId($sProductId);

        $sArtNum = $this->_fcGetArticleNumber($oXmlProduct);
        $oArticle->oxarticles__fcafterbuyid = new oxField($sProductId);
        $oArticle->oxarticles__oxartnum = new oxField($sArtNum);
        if ($sType == 'variationsets') {
            $this->_fcSaveVariations($oXmlProduct);
        } else {
            $sParentId = $this->_fcFetchParentId($sProductId);
            $oArticle->oxarticles__oxparentid = new oxField($sParentId);
        }
    }

    /**
     * declare articles as invalid if article number does not match the configurated conditions
     *
     * @param $oXmlProduct
     * @return bool
     */
    protected function _fcCheckIfArticleNumberIsValid($oXmlProduct) {
        $blDiscard = $this->getConfig()->getConfigParam('blFcAfterbuyIgnoreArticlesWithoutNr');

        if($blDiscard != true) {
            return true;
        }

        $sArtNum = $this->_fcGetArticleNumber($oXmlProduct);

        if(empty($sArtNum) || $sArtNum == 0) {
            $this->oDefaultLogger->fcWriteLog(
                "INFO: Product has been discarded because of missing article number \n".
                print_r($oXmlProduct ,true), 2);
            return false;
        }

        return true;
    }

    /**
     * Assign article number based on given config
     *
     * @param $oXmlProduct
     * @return string
     */
    protected function _fcGetArticleNumber($oXmlProduct) {
        $sSource = $this->getConfig()->getConfigParam('sFcAfterbuyImportArticleNumber');

        switch($sSource) {
            case '0': $sArtNum = $oXmlProduct->EAN ?: $oXmlProduct->Anr;
                break;
            case '1': $sArtNum = $oXmlProduct->EAN;
                break;
            case '2': $sArtNum = $oXmlProduct->ProductID;
                break;
            case '3': $sArtNum = $oXmlProduct->Anr;
                break;
            default: $sArtNum = $oXmlProduct->EAN ?: $oXmlProduct->Anr;
        }

        return $sArtNum;
    }

    /**
     * Fetching parent id from
     *
     * @param $sProductId
     * @return string
     */
    protected function _fcFetchParentId($sProductId) {
        $sParentId =
            isset($this->_aVariations[$sProductId]) ?
                $this->_aVariations[$sProductId] :
                '';

        return $sParentId;
    }

    /**
     * Save related childproduct ids for later fetching parentids
     *
     * @param $oXmlProduct
     * @return string
     */
    protected function _fcSaveVariations($oXmlProduct)
    {
        if (!isset($oXmlProduct->BaseProducts)) return '';
        $sProductId = (string) $oXmlProduct->ProductID;

        foreach ($oXmlProduct->BaseProducts as $aBaseProducts) {
            foreach ($aBaseProducts as $aBaseProduct) {
                $aBaseProduct = (array) $aBaseProduct;
                $sBaseProductId = (string) $aBaseProduct['BaseProductID'];
                if (empty($sBaseProductId)) continue;
                $this->_aVariations[$sBaseProductId] = $sProductId;
            }
        }
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
        $oArticle->oxarticles__oxtitle = new oxField((string)$oXmlProduct->Name);
        $oArticle->oxarticles__oxshortdesc = new oxField((string)$oXmlProduct->ShortDescription);
        $oArticle->setArticleLongDesc((string) $oXmlProduct->Description);
        if ($sType=='nonsets') {
            $sVarselect = $this->_fcFetchVarselect($oXmlProduct);
            $oArticle->oxarticles__oxvarselect = new oxField($sVarselect);
        }
    }

    /**
     * Fetches varselect by subtracting parent product title
     *
     * @param object $oXmlProduct
     * @return string
     * @throws
     */
    protected function _fcFetchVarselect($oXmlProduct) {
        $sProductId = (string) $oXmlProduct->ProductID;
        $sParentId = $this->_fcFetchParentId($sProductId);
        if (!$sParentId) return '';

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $sQuery = "
            SELECT 
                OXTITLE 
            FROM 
                oxarticles 
            WHERE 
                OXID=".$oDb->quote($sParentId);

        $sParentTitle = (string) $oDb->getOne($sQuery);
        $sChildTitle = (string) $oXmlProduct->Name;

        $sVarSelect =
            trim(str_replace($sParentTitle, '', $sChildTitle));

        return $sVarSelect;
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
        $blValidNode = (
            is_array($oXmlProduct->Attributes) ||
            is_object($oXmlProduct->Attributes)
        );
        if (!$blValidNode) return;

        foreach ($oXmlProduct->Attributes as $aProductAttributes) {
            foreach ($aProductAttributes as $aProductAttribute) {
                $aProductAttribute = (array) $aProductAttribute;
                $sAttributeId = $this->_fcGetAttributeId($aProductAttribute);
                $sArticleId = $oArticle->getId();
                $sAttributeValue = $aProductAttribute['AttributValue'];
                $this->_fcAddAttributeValue($sAttributeId, $sArticleId, $sAttributeValue);
            }
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
        $oAttribute->oxattribute__oxtitle = new oxField($sAttributeName);
        $oAttribute->save();

        return $oAttribute->getId();
    }


    /**
     * Assigns category with article
     *
     * @param $sCategoryId
     * @param $sArticleId
     */
    protected function _fcAssignCategoryProducts($sCategoryId, $aCatalogProducts)
    {
        $oUtilsObject = oxRegistry::get('oxUtilsObject');
        $oDb = oxDb::getDb();
        $values = [];
        foreach ($aCatalogProducts as $sArticleId) {
            $sNewId = $oUtilsObject->generateUId();
            $values[] = join(',', array_map([$oDb,'quote'], [$sNewId, $sArticleId, $sCategoryId]));
        }

        if (0 === count($values)) return;

        $sQuery = "
            INSERT IGNORE INTO oxobject2category
            (
                OXID,
                OXOBJECTID,
                OXCATNID
            )
            VALUES
            (" . join('),(', $values) . ")
        ";

        $oDb->execute($sQuery);
    }

    /**
     * Create category entry
     *
     * @param array $aCatalog
     * @return string
     */
    protected function _fcCreateCategory($aCatalog) {
        $sCategoryId = (string) $aCatalog['CatalogID'];
        $sParentId = ($aCatalog['ParentID']) ?
            (string) $aCatalog['ParentID'] :
            'oxrootid';
        $sRootId = $aCatalog['ParentID'] ? '' : $sCategoryId;
        $sDescription = isset($aCatalog['Description']) ? (string) $aCatalog['Description'] : '';

        $oDb = oxDb::getDb();

        $sQuery = "
            INSERT INTO oxcategories
            (
                OXID,
                OXACTIVE,
                OXTITLE,
                OXLONGDESC,
                OXPARENTID,
                OXROOTID
            )
            VALUES
            (
                ".$oDb->quote($sCategoryId).",
                ".$oDb->quote((int) $aCatalog['Show']).",
                ".$oDb->quote((string) htmlspecialchars_decode($aCatalog['Name'])).",
                ".$oDb->quote($sDescription).",
                ".$oDb->quote($sParentId).",
                ".$oDb->quote($sRootId)."
            ) ON DUPLICATE KEY UPDATE 
                OXACTIVE = VALUES(OXACTIVE),
                OXTITLE = VALUES(OXTITLE),
                OXLONGDESC = VALUES(OXLONGDESC),
                OXPARENTID = VALUES(OXPARENTID),
                OXROOTID = VALUES(OXROOTID)
        ";

        $oDb->execute($sQuery);

        return $sCategoryId;
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
        $blValidNode = (
            is_array($oXmlProduct->ProductPictures) ||
            is_object($oXmlProduct->ProductPictures)
        );
        if (!$blValidNode) return;

        $iPicCounter = 1;

        foreach ($oXmlProduct->ProductPictures as $aProductPictures) {
            foreach ($aProductPictures as $aProductPicture) {
                $aProductPicture = (array) $aProductPicture;
                $sImageUrl = (string) $aProductPicture['Url'];
                if (empty($sImageUrl)) continue;

                $sTargetFileName = basename($sImageUrl);
                $this->_fcDownloadImage($sImageUrl, $sTargetFileName, $iPicCounter);
                $sField = "oxarticles__oxpic".$iPicCounter;
                $oArticle->$sField = new oxField($sTargetFileName);
                $iPicCounter++;
            }
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
        $sMasterPictureFolder = $oConfig->getMasterPicturePath('');
        $sTargetFolder = "{$sMasterPictureFolder}product/{$sPicNrFolder}";
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

    protected function getUpdateFromTimestamp($isUpdate) {
        if (!$isUpdate) return '';

        $updateInterval = (int) $this->getConfig()->getConfigParam('sFcAfterbuyImportDeltaInterval');
        // calculate timestamp and fallback to begin of current day
        return $updateInterval ? date('d.m.Y H:m:s', strtotime("now - $updateInterval minutes")): date('d.m.Y 00:00:00');
    }

    protected function addProductPostLoad(&$oArticle, &$oXmlProduct) {
        return;
    }

    protected function addProductPreSave(&$oArticle, $oXmlProduct) {
        return;
    }
}