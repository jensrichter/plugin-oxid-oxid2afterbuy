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
            $this->_fcNotifyExported($oAfterbuyOrder, $oAfterbuyApi);
        }
    }

    /**
     * Notify to afterbuy that order has been exported
     *
     * @param $oAfterbuyOrder
     * @param $oAfterbuyApi
     * @return void
     */
    protected function _fcNotifyExported($oAfterbuyOrder, $oAfterbuyApi) {

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
     * @param $oUser
     * @param $oAddress
     * @return void
     */
    protected function _fcSetOxidOrderByAfterbuyOrder($oAfterbuyOrder, $oUser, $oAddress) {
        $oOrder = oxNew('oxorder');

        $oOrder = $this->_fcGetOrderGeneralData($oOrder, $oUser, $oAfterbuyOrder);

        // billdata
        $oOrder = $this->_fcGetOrderBillData($oOrder, $oUser);
        // deliveryinfo
        $oOrder = $this->_fcGetOrderDeliveryData($oOrder, $oAddress);
        // paymentinfo
        $oOrder = $this->_fcGetPaymentInfo($oOrder, $oAfterbuyOrder);
        $oOrder = $this->_fcGetPaymentData($oOrder, $oAfterbuyOrder);

        $oOrder->save();

        // set orderarticles
        $this->_fcSetOxidOrderarticlesByAfterbuyOrder($oAfterbuyOrder, $oOrder);
    }


    /**
     * Assign solditems values to orderarticles
     *
     * @todo implementing sets feature (ChildProduct)
     * @param $oAfterbuyOrder
     * @param $oOrder
     */
    protected function _fcSetOxidOrderarticlesByAfterbuyOrder($oAfterbuyOrder, $oOrder) {
        $sOrderId = $oOrder->getId();
        $aSoldItems = $oAfterbuyOrder->SoldItems;
        $oOrderArticleTemplate = oxNew('oxorderarticle');
        foreach ($aSoldItems as $oSoldItem) {
            $oOrderArticle = clone $oOrderArticleTemplate;
            $oProductDetails = $oSoldItem->ShopProductDetails;
            $sArtNum = $oProductDetails->EAN;
            $sProductId = $this->_fcGetProductIdByArtNum($sArtNum);

            $oOrderArticle->oxorderarticles__oxorderid = new oxField($sOrderId);
            $oOrderArticle->oxorderarticles__oxamount = new oxField($oSoldItem->ItemQuantity);
            $oOrderArticle->oxorderarticles__oxartid = new oxField($sProductId);
            $oOrderArticle->oxorderarticles__oxartnum = new oxField($sArtNum);
            $oOrderArticle->oxorderarticles__oxtitle = new oxField($oSoldItem->ItemTitle);
            $oOrderArticle->oxorderarticles__oxprice = new oxField($oSoldItem->ItemPrice);
            $oOrderArticle->save();
        }

    }

    /**
     * Sets additional paymentdata into oxuserpayment and link them to order
     *
     * @param $oOrder
     * @param $oAfterbuyOrder
     * @return oxOrder
     */
    protected function _fcGetPaymentData($oOrder, $oAfterbuyOrder) {
        $oUserPayment = oxNew('oxuserpayment');
        $sUserId = $oOrder->oxorder__oxuserid->value;
        $sPaymentId = $oOrder->oxorder__oxpaymenttype->value;
        $oPaymentData = $oAfterbuyOrder->PaymentInfo->PaymentData;
        $aDynValues = array(
            'BankCode' => $oPaymentData->BankCode,
            'AccountHolder' => $oPaymentData->AccountHolder,
            'BankName' => $oPaymentData->BankName,
            'AccountNumber' => $oPaymentData->AccountNumber,
            'Iban' => $oPaymentData->Iban,
            'Bic' => $oPaymentData->Bic,
            'ReferenceNumber' => $oPaymentData->ReferenceNumber,
        );

        $oUserPayment->oxuserpayments__oxpaymentsid = new oxField($sPaymentId);
        $oUserPayment->oxuserpayments__oxuserid = new oxField($sUserId);
        $oUserPayment->setDynValues($aDynValues);
        $oUserPayment->save();

        $sPaymentsId = $oUserPayment->getId();
        $oOrder->oxorder__oxpaymentid = new oxField($sPaymentsId);

        return $oOrder;
    }

    /**
     * Adds payment information to order
     *
     * @param $oOrder
     * @param $oAfterbuyOrder
     * @return mixed
     */
    protected function _fcGetPaymentInfo($oOrder, $oAfterbuyOrder) {
        $oPaymentInfo = $oAfterbuyOrder->PaymentInfo;
        $sPaymentType = $this->_fcGetPaymentMethod($oPaymentInfo);
        $oOrder->oxorder__oxpaymenttype = new oxField($sPaymentType);
        $oOrder->oxorder__oxtransid = new oxField($oPaymentInfo->PaymentTransactionID);
        if ($oPaymentInfo->AlreadyPaid) {
            $oOrder->oxorder__oxpaid = new oxField($oPaymentInfo->PaymentDate);
        }
        $oOrder->oxorder__oxordertotalsum = new oxField($oPaymentInfo->FullAmount);

        return $oOrder;
    }

    /**
     * Checks if payment method exists, creates payment if needed and returns its paymenttype
     * string for assigning to order
     *
     * @param $oPaymentInfo
     * @return string
     */
    protected function _fcGetPaymentMethod($oPaymentInfo) {
        $sPaymentDescription = $oPaymentInfo->PaymentMethod;
        $sPaymentId = $this->_fcGetOxidEncodedPaymentId($oPaymentInfo->PaymentID);

        $blPaymentTypeExists = $this->_fcPaymentTypeExists($sPaymentId);
        if (!$blPaymentTypeExists) {
            $this->_fcCreateAfterbuyPayment($sPaymentId, $sPaymentDescription);
        }

        return $sPaymentId;
    }

    /**
     * Creates needed payment method
     *
     * @param $sPaymentId
     * @param $sPaymentDescription
     * @return void
     */
    protected function _fcCreateAfterbuyPayment($sPaymentId, $sPaymentDescription) {
        $oPayment = oxNew('oxpayment');
        $oPayment->oxpayments__oxid = new oxField($sPaymentId);
        $oPayment->oxpayments__oxdesc = new oxField($sPaymentDescription);
        $oPayment->oxpayments__oxactive = new oxField(0);
        $oPayment->save();
    }

    /**
     * Checks, if payment with vertain id exists and returns
     *
     * @param $sPaymentId
     * @return bool
     */
    protected function _fcPaymentTypeExists($sPaymentId) {
       $oPayment = oxNew('oxpayment');
       $blPaymentExists = (bool) $oPayment->load($sPaymentId);

       return $blPaymentExists;
    }

    /**
     * Returns oxid paymentid by afterbuy payment id
     *
     * @param $sAfterbuyPaymentId
     * @return string
     */
    protected function _fcGetOxidEncodedPaymentId($sAfterbuyPaymentId) {
        $sOxidPaymentId = "fcab_".strtolower($sAfterbuyPaymentId);

        return $sOxidPaymentId;
    }

    /**
     * Returns origin afterbuy payment id
     *
     * @param $sOxidPaymentId
     * @return string
     */
    protected function _fcGetOxidDecodedPaymentId($sOxidPaymentId) {
        $sAfterbuyPaymentId = strtoupper(substr($sOxidPaymentId,5));

        return $sAfterbuyPaymentId;
    }

    /**
     * Adds general data to order
     *
     * @param $oOrder
     * @param $oUser
     * @param $oAfterbuyOrder
     * @return oxOrder
     */
    protected function _fcGetOrderGeneralData($oOrder, $oUser, $oAfterbuyOrder) {
        $oCounter = oxNew('oxcounter');

        $oOrder->oxorder__fcafterbuy_uid = new oxField($oAfterbuyOrder->OrderID);
        $oOrder->oxorder__oxshopid = $oUser->oxuser__oxshopid;
        $oOrder->oxorder__oxuserid = $oUser->getId();
        $oOrder->oxorder__oxorderdate = new oxField($oAfterbuyOrder->OrderDate);
        $oOrder->oxorder__oxordernr = $oCounter->getNext('oxorder');
        $oOrder->oxorder__oxremark = new oxField($oAfterbuyOrder->UserComment);
        $oOrder->oxorder__oxtrackcode = new oxField($oAfterbuyOrder->TrackingLink);

        return $oOrder;
    }

    /**
     * Adds order delivery data
     *
     * @param $oOrder
     * @param $oAddress
     */
    protected function _fcGetOrderDeliveryData($oOrder, $oAddress) {
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

        return $oOrder;
    }

    /**
     * Adds order billing data to oxorder
     *
     * @param $oOrder
     * @param $oUser
     * @return oxOrder
     */
    protected function _fcGetOrderBillData($oOrder, $oUser) {
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

        return $oOrder;
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
        $oUser->oxuser__fcafterbuy_userid = new oxField($oBillingAddress->AfterbuyUserID);
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
    protected function _fcSetUserAddressData($oShippingAddress, $oUser) {
        $sCompleteStreetInfo = $oShippingAddress->Street." ".$oShippingAddress->Street2;
        $aStreetParts = $this->_fcpoSplitStreetAndStreetNr($sCompleteStreetInfo);
        $sCountryId = $this->_fcpoGetCountryIdByIso2($oShippingAddress->CountryISO);

        $oAddress = oxNew('oxaddress');
        $oAddress->oxaddress__oxuserid = new oxField($oUser->getId());
        $oAddress->oxaddress__oxaddressuserid = new oxField($oUser->getId());
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
