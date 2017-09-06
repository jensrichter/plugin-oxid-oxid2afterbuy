<?php
class fco2aartexport extends oxI18n {

    /**
     * Dictionary of value translations
     * @var array
     */
    protected $_aAfterbuy2OxidDictionary = array(
        'UserProductID' => 'oxarticles__fcafterbuyid',
        'Anr' => 'oxarticles__oxartnum',
        'EAN' => 'oxarticles__oxean',
        'ProductID' => 'oxarticles__oxid',
        'Name' => 'oxarticles__oxtitle',
        'ManufacturerPartNumber' => 'oxarticles__oxmpn',
        'Keywords' => 'oxarticles__oxkeywords',
        'Quantity' => 'oxarticles__oxstock|oxarticles__oxvarstock',
        'AuctionQuantity' => 'oxarticles__oxstock|oxarticles__oxvarstock',
        'UnitOfQuantity' => 'oxarticles__oxunitname',
        'BuyingPrice' => 'oxarticles__oxbprice',
        'ProductBrand' => null,
        'Weight' => null,
        'ItemSize' => null,
        'CanonicalUrl' => null,
        'ProductPicture_Nr_1' => null,
        'ProductPicture_Nr_2' => null,
        'ProductPicture_Nr_3' => null,
        'ProductPicture_Nr_4' => null,
        'ProductPicture_Nr_5' => null,
        'ProductPicture_Nr_6' => null,
        'ProductPicture_Nr_7' => null,
        'ProductPicture_Nr_8' => null,
        'ProductPicture_Nr_9' => null,
        'ProductPicture_Nr_10' => null,
        'ProductPicture_Nr_11' => null,
        'ProductPicture_Nr_12' => null,
        'ProductPicture_Url_1' => null,
        'ProductPicture_Url_2' => null,
        'ProductPicture_Url_3' => null,
        'ProductPicture_Url_4' => null,
        'ProductPicture_Url_5' => null,
        'ProductPicture_Url_6' => null,
        'ProductPicture_Url_7' => null,
        'ProductPicture_Url_8' => null,
        'ProductPicture_Url_9' => null,
        'ProductPicture_Url_10' => null,
        'ProductPicture_Url_11' => null,
        'ProductPicture_Url_12' => null,
        'ProductPicture_AltText_1' => null,
        'ProductPicture_AltText_2' => null,
        'ProductPicture_AltText_3' => null,
        'ProductPicture_AltText_4' => null,
        'ProductPicture_AltText_5' => null,
        'ProductPicture_AltText_6' => null,
        'ProductPicture_AltText_7' => null,
        'ProductPicture_AltText_8' => null,
        'ProductPicture_AltText_9' => null,
        'ProductPicture_AltText_10' => null,
        'ProductPicture_AltText_11' => null,
        'ProductPicture_AltText_12' => null,
    );

    /**
     * Executes upload of selected afterbuy articles
     *
     * @param void
     * @return void
     */
    public function execute()
    {
        $aConfig = $this->_fcGetAfterbuyConfigArray();
        $oAfterbuyApi = $this->_fcGetAfterbuyApi($aConfig);

        $aArticleIds = $this->_fcGetAffectedArticleIds();

        foreach ($aArticleIds as $sArticleOxid) {
            $oArt = $this->_fcGetAfterbuyArticleByOxid($sArticleOxid);
            $sResponse = $oAfterbuyApi->updateArticleToAfterbuy($oArt);
            $this->_fcAddAfterbuyIdToArticle($sArticleOxid, $sResponse);
        }
    }

    /**
     * Takes an oxid of an article and creates an afterbuy article object of it
     *
     * @param $sArticleOxid
     * @return object
     */
    protected function _fcGetAfterbuyArticleByOxid($sArticleOxid) {
        $oAfterbuyArticle = $this->_fcGetAfterbuyArticle();
        $oArticle = oxNew('oxarticle');
        $oArticle->load($sArticleOxid);

        $oAfterbuyArticle->Description = $oArticle->getLongDesc();
        $oAfterbuyArticle->SellingPrice = $oArticle->getPrice()->getBruttoPrice();
        $oAfterbuyArticle->TaxRate = $oArticle->getArticleVat();

        // standard values will be iterated through translation array
        foreach ($this->_aAfterbuy2OxidDictionary as $sAfterbuyName=>$sOxidName) {
            $oAfterbuyArticle->$sAfterbuyName = $oArticle->$sOxidName->value;
        }
    }

    /**
     * Returns an afterbuy article object
     *
     * @param void
     * @return object fcafterbuyart
     */
    protected function _fcGetAfterbuyArticle() {
        $oViewConfig = oxRegistry::get('oxViewConfig');
        $sPathToModule = $oViewConfig->getModulePath('fcoxid2afterbuy');
        $sPathToAfterbuyLib = $sPathToModule.'lib/fcaferbuyapi.php';
        include_once($sPathToAfterbuyLib);
        $oAfterbuyArticle = new fcafterbuyart();

        return $oAfterbuyArticle;
    }

    /**
     * Returns an array of article ids which have been flagged to be an afterbuy article
     *
     * @param void
     * @return array
     */
    protected function _fcGetAffectedArticleIds() {
        $aArticleIds = array();
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sQuery = "SELECT OXID FROM ".getViewName('oxarticles')." WHERE FCAFTERBUYACTIVE='1' AND OXPARENTID=''";
        $aRows = $oDb->getAll($sQuery);
        foreach ($aRows as $aRow) {
            $aArticleIds[] = $aRow['OXID'];
        }

        return $aArticleIds;
    }

    /**
     * Returns needed configuration for instantiate afterbuy api object
     *
     * @param void
     * @return array
     */
    protected function _fcGetAfterbuyConfigArray() {
        $oConfig = $this->getConfig();
        $aConfig = array(
            'sFcAfterbuyShopInterfaceBaseUrl' => $oConfig->getConfigParam('sFcAfterbuyShopInterfaceBaseUrl'),
            'sFcAfterbuyAbiUrl' => $oConfig->getConfigParam('sFcAfterbuyAbiUrl'),
            'sFcAfterbuyPartnerId' => $oConfig->getConfigParam('sFcAfterbuyPartnerId'),
            'sFcAfterbuyPartnerPassword' => $oConfig->getConfigParam('sFcAfterbuyPartnerPassword'),
            'sFcAfterbuyUsername' => $oConfig->getConfigParam('sFcAfterbuyUsername'),
            'sFcAfterbuyUserPassword' => $oConfig->getConfigParam('sFcAfterbuyUserPassword'),
            'iFcLogLevel' => $oConfig->getConfigParam('iFcLogLevel'),
        );

        return $aConfig;
    }

    /**
     * Returns afterbuy api object
     *
     * @param $aConfig
     * @return object
     */
    protected function _fcGetAfterbuyApi($aConfig) {
        $oViewConfig = oxRegistry::get('oxViewConfig');
        $sPathToModule = $oViewConfig->getModulePath('fcoxid2afterbuy');
        $sPathToAfterbuyLib = $sPathToModule.'lib/fcaferbuyapi.php';
        include_once($sPathToAfterbuyLib);
        $oAfterbuyApi = new fcafterbuyapi($aConfig);

        return $oAfterbuyApi;
    }
}
