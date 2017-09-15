<?php
class fco2aorderimport extends fco2abase {


    public function execute()
    {
        $aConfig = $this->_fcGetAfterbuyConfigArray();
        $oAfterbuyApi = $this->_fcGetAfterbuyApi($aConfig);

        $sResponse = $oAfterbuyApi->getSoldItemsFromAfterbuy();

        $oXmlResponse = simplexml_load_string($sResponse);

        foreach ($oXmlResponse->Result->Orders as $oXmlOrder) {
            $oAfterbuyOrder = $this->_fcGetAfterbuyOrder();
            $oAfterbuyOrder->createOrderByApiResponse($oXmlOrder);
            $this->_fcCreateOxidOrder($oAfterbuyOrder);
        }
    }

    /**
     * Creates an oxid order including user and articles
     *
     * @param $oAfterbuyOrder
     * @return void
     */
    protected function _fcCreateOxidOrder($oAfterbuyOrder) {
        // create OXUSER
        $aUserData = $this->_fcSetOxidUserByAfterbuyOrder($oAfterbuyOrder);
        $oUser = $aUserData['oxuser'];
        $oAddress = $aUserData['oxaddress'];
        // create OXORDER
        $this->_fcSetOxidOrderByAfterbuyOrder($oAfterbuyOrder, $oUser, $oAddress);
    }

    /**
     * Create oxid order
     *
     * @param $oAfterbuyOrder
     * @param $sUserOxid
     * @return void
     */
    protected function _fcSetOxidOrderByAfterbuyOrder($oAfterbuyOrder, $oUser, $oAddress) {
        $oOrder = oxNew('oxorder');
        $oCounter = oxNew('oxcounter');
        $oOrder->oxorder__oxshopid = $oUser->oxuser__oxshopid;
        $oOrder->oxorder__oxuserid = $oUser->getId();
        $oOrder->oxorder__oxorderdate = new oxField($oAfterbuyOrder->OrderDate);
        $oOrder->oxorder__oxordernr = $oCounter->getNext('oxorder');
        $oOrder->oxorder__oxbillemail = $oUser->oxuser__oxusername;
        $oOrder->oxorder__oxbillfname = $oUser->oxuser__oxfname;
        $oOrder->oxorder__oxbilllname = $oUser->oxuser__oxlname;
        $oOrder->oxorder__oxbillstreet = $oUser->oxuser__oxstreet;
        $oOrder->oxorder__oxbillstreetnr = $oUser->oxuser__oxstreetnr;
        $oOrder->oxorder__oxbillustid = $oUser->oxuser__oxustid;
        $oOrder->oxorder__oxbillcity = $oUser->oxuser__oxcity;
        $oOrder->oxorder__oxbillcountryid = $oUser->oxuser__oxcountryid;
        $oOrder->oxorder__oxbillzip = $oUser->oxuser__oxzip;
        $oOrder->oxorder__oxbillfon = $oUser->oxuser__oxfon;
        $oOrder->oxorder__oxbillfax = $oUser->oxuser__oxfax;
        // deliveryinfo
        $oOrder->oxorder__oxdelcompany = $oAddress->oxaddress__oxcompany;
        $oOrder->oxorder__oxdelfname = $oAddress->oxaddress__oxfname;
        $oOrder->oxorder__oxdellname = $oAddress->oxaddress__oxlname;
        $oOrder->oxorder__oxdelstreet = $oAddress->oxaddress__oxstreet;
        $oOrder->oxorder__oxdelstreetnr = $oAddress->oxaddress__oxstreetnr;
        $oOrder->oxorder__oxdelcity = $oAddress->oxaddress__oxcity;
        $oOrder->oxorder__oxdelcountryid = $oAddress->oxaddress__oxcountryid;
        $oOrder->oxorder__oxdelzip = $oAddress->oxaddress__oxzip;
        $oOrder->oxorder__oxdelfon = $oAddress->oxaddress__oxfon;
        $oOrder->oxorder__oxdelfax = $oAddress->oxaddress__oxfax;
        // paymentinfo
        // ordersum
        // afterbuyids
        // misc

        $oOrder->save();
        $sOrderOxid = $oOrder->getId();
        // create OXORDERARTICLES
        $this->_fcSetOxidOrderarticlesByAfterbuyOrder($oAfterbuyOrder, $sOrderOxid);
    }

    /**
     * Creates user and returns its ID
     *
     * @param $oAfterbuyOrder
     * @return array
     */
    protected function _fcSetOxidUserByAfterbuyOrder($oAfterbuyOrder) {
        $oAfterbuyUser = $oAfterbuyOrder->BuyerInfo;
        $oBillingAddress = $oAfterbuyUser->BillingAddress;
        $oShippingAddress = $oAfterbuyUser->ShippingAddress;
        $oUser = oxNew('oxuser');

        $sUserOxid = $this->_fcCheckUserExists($oBillingAddress->Mail);
        if ($sUserOxid) {
            $oUser->load($sUserOxid);
        }

        $oUser = $this->_fcGetUserData($oBillingAddress, $oUser);
        $oUser->save();
        $sUserId = $oUser->getId();

        $oAddress = $this->_fcSetUserAddressData($oShippingAddress, $oUser);

        $aReturn = array('oxuser'=>$oUser, 'oxaddress'=>$oAddress);

        return $aReturn;
    }

    /**
     * Sets user data from afterbuy order billing address
     *
     * @param $oBillingAddress
     * @param $oUser
     * @return oxUser
     */
    protected function _fcGetUserData($oBillingAddress, $oUser) {
        $oConfig = $this->getConfig();
        $sCompleteStreetInfo = $oBillingAddress->Street." ".$oBillingAddress->Street2;
        $aStreetParts = $this->_fcpoSplitStreetAndStreetNr($sCompleteStreetInfo);
        $sCountryId = $this->_fcpoGetCountryIdByIso2($oBillingAddress->CountryISO);


        $oUser->oxuser__oxshopid = new oxField($oConfig->getShopId());
        $oUser->oxuser__oxusername = new oxField($oBillingAddress->Mail);
        $oUser->oxuser__oxcompany = new oxField($oBillingAddress->Company);
        $oUser->oxuser__oxustid = new oxField($oBillingAddress->TaxIDNumber);
        $oUser->oxuser__oxfname = new oxField($oBillingAddress->FirstName);
        $oUser->oxuser__oxlname = new oxField($oBillingAddress->LastName);
        $oUser->oxuser__oxstreet = new oxField($aStreetParts['street']);
        $oUser->oxuser__oxstreetnr = new oxField($aStreetParts['streetnr']);
        $oUser->oxuser__oxcity = new oxField($oBillingAddress->City);
        $oUser->oxuser__oxcountryid = new oxField($sCountryId);
        $oUser->oxuser__oxzip = new oxField($oBillingAddress->PostalCode);
        $oUser->oxuser__oxfon = new oxField($oBillingAddress->Phone);
        $oUser->oxuser__oxfax = new oxField($oBillingAddress->Fax);
        $oUser->addToGroup('oxidcustomer');

        return $oUser;
    }

    /**
     * Adds or loads matching shipping address
     *
     * @param $oShippingAddress
     * @param $sUserOxid
     * @return oxAddress
     */
    protected function _fcSetUserAddressData($oShippingAddress, $sUserOxid) {
        $sCompleteStreetInfo = $oShippingAddress->Street." ".$oShippingAddress->Street2;
        $aStreetParts = $this->_fcpoSplitStreetAndStreetNr($sCompleteStreetInfo);
        $sCountryId = $this->_fcpoGetCountryIdByIso2($oShippingAddress->CountryISO);

        $oAddress = oxNew('oxaddress');
        $oAddress->oxaddress__oxuserid = new oxField($sUserOxid);
        $oAddress->oxaddress__oxaddressuserid = new oxField($sUserOxid);
        $oAddress->oxaddress__oxfname = new oxField($oShippingAddress->FirstName);
        $oAddress->oxaddress__oxlname = new oxField($oShippingAddress->LastName);
        $oAddress->oxaddress__oxstreet = new oxField($aStreetParts['street']);
        $oAddress->oxaddress__oxstreetnr = new oxField($aStreetParts['streetnr']);
        $oAddress->oxaddress__oxfon = new oxField($oShippingAddress->Phone);
        $oAddress->oxaddress__oxcity = new oxField($oShippingAddress->City);
        $oAddress->oxaddress__oxcountry = new oxField($oShippingAddress->Country);
        $oAddress->oxaddress__oxcountryid = new oxField($sCountryId);
        $oAddress->oxaddress__oxzip = new oxField($oShippingAddress->PostalCode);

        // Check if address exists. Using addresshash as id for recognition
        $sEncodedDeliveryAddress = $oAddress->getEncodedDeliveryAddress();
        $blExists = $this->_fcCheckAddressExists($sEncodedDeliveryAddress);
        if ($blExists) {
            $oAddress->load($sEncodedDeliveryAddress);
        } else {
            $oAddress->setId($sEncodedDeliveryAddress);
            $oAddress->save();
        }

        return $oAddress;
    }

    /**
     * Returns id of a countrycode
     *
     * @param $sIso2Country
     * @return string
     */
    protected function _fcpoGetCountryIdByIso2($sIso2Country) {
        $oCountry = oxNew('oxCountry');
        $sOxid = $oCountry->getIdByCode($sIso2Country);

        return $sOxid;
    }

    /**
     * Method splits street and streetnr from string
     *
     * @param string $sStreetAndStreetNr
     * @return array
     */
    protected function _fcpoSplitStreetAndStreetNr($sStreetAndStreetNr) {
        /**
         * @todo currently very basic by simply splitting ot space
         */
        $aReturn = array();
        $aParts = explode(' ', $sStreetAndStreetNr);
        foreach ($aParts as $iIndex=>$sPart) {
            if ($iIndex==(count($aParts)-1)) {
                $aReturn['streetnr'] = $sPart;
            } else {
                $aStreetNames[] = $sPart;
            }
        }
        $aReturn['street'] = implode(' ', $aStreetNames);

        return $aReturn;
    }


    /**
     * Checks if user exists
     *
     * @param $sEmailAddress
     * @return mixed string|false
     */
    protected function _fcCheckUserExists($sEmailAddress) {
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        $sQuery = "SELECT OXID FROM oxuser WHERE OXUSERNAME=".$oDb->quote($sEmailAddress);
        $mOxid = $oDb->getOne($sQuery);

        return $mOxid;
    }

    /**
     * Returns a new afterbuy order object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAfterbuyOrder() {
        $oViewConfig = oxRegistry::get('oxViewConfig');
        $sPathToModule = $oViewConfig->getModulePath('fcoxid2afterbuy');
        $sPathToAfterbuyLib = $sPathToModule.'lib/fcafterbuyorder.php';
        include_once($sPathToAfterbuyLib);
        $oAfterbuyOrder = new fcafterbuyorder();

        return $oAfterbuyOrder;
    }
}
