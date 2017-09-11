<?php

/**
 * Class representing an afterbuy order
 * User: andre
 * Date: 11.09.17
 * Time: 11:46
 */
class fcafterbuyorder
{
    /**
     * Representation of possible values of an afterbuy article
     * @var array
     */
    protected $_aOrderAttributes = array(
        'InvoiceNumber' => null,
        'OrderID' => null,
        'EbayAccount' => null,
        'AmazonAccount' => null,
        'Anr' => null,
        'AlternativeItemNumber1' => null,
        'FeedbackDate' => null,
        'UserComment' => null,
        'AdditionalInfo' => null,
        'TrackingLink' => null,
        'Memo' => null,
        'InvoiceMemo' => null,
        'FeedbackLink' => null,
        'OrderDate' => null,
        'OrderIDAlt' => null,
        'PaymentInfo' => null,
        'BuyerInfo' => null,
        'SoldItems' => null,
        'ShippingInfo' => null,
    );

    /**
     * Magic setter
     *
     * @param string $sName
     * @param mixed $mValue
     * @return void
     */
    public function __set($sName, $mValue) {
        $this->_aOrderAttributes[$sName] = $mValue;
    }

    /**
     * Magic getter
     *
     * @param $sName
     * @return mixed
     */
    public function __get($sName) {
        return $this->_aOrderAttributes[$sName];
    }

    /**
     * Method takes care of creating and returning an afterbuy order object
     *
     * @param object $oXmlOrder
     * @return void
     */
    public function createOrderByApiResponse($oXmlOrder) {
        $blValidResponse = $this->_fcValidateResponse($oXmlOrder);
        if (!$blValidResponse) return false;

        $this->_fcSetCommonValues($oXmlOrder);
        $this->_fcSetPaymentInfo($oXmlOrder);
        $this->_fcSetBuyerInfo($oXmlOrder);
        $this->_fcSetSoldItems($oXmlOrder);
        $this->_fcSetShippingInfo($oXmlOrder);
    }

    /**
     * Checks if response really contains the expected orderdata needed to build
     * order object
     *
     * @param $oXmlOrder
     * @return bool
     */
    protected function _fcValidateResponse($oXmlOrder) {
        return true;
    }

    /**
     * Creates information for payment
     *
     * @param $oXmlOrder
     * @return object fcafterbuypayment
     */
    protected function _fcSetPaymentInfo($oXmlOrder) {
        include_once (__DIR__."/fcafterbuypayment.php");
        $oPayment = new fcafterbuypayment();
        $oPayment->createPaymentFromOrderResponse($oXmlOrder);

        $this->PaymentInfo = $oPayment;
    }

    /**
     * Creates information for buyer
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetBuyerInfo($oXmlOrder) {
        include_once (__DIR__."/fcafterbuyaddress.php");
        $oBillingAddress = new fcafterbuyaddress();
        $oShippingAddress = new fcafterbuyaddress();
        $oBillingAddress->createBillingAddressFromOrderResponse($oXmlOrder);
        $oShippingAddress->createShippingAddressFromOrderResponse($oXmlOrder);

        $aBuyerInfo = array(
            'BillingAddress' => $oBillingAddress,
            'ShippingAddress' => $oShippingAddress,
        );

        $this->BuyerInfo = $aBuyerInfo;
    }

    /**
     * Creates sold items (order articles)
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetSoldItems($oXmlOrder) {
        include_once (__DIR__."/fcafterbuysolditem.php");

        $aSoldItems = $oXmlOrder->SoldItems;

        foreach ($aSoldItems as $oXmlSoldItem) {
            $oSoldItem = new fcafterbuysolditem();
            $oSoldItem->createSoldItemFromXmlSoldItem($oXmlSoldItem);
            $this->SoldItems[] = $oSoldItem;
        }
    }

    /**
     * Creates Shipping infos of orderobject
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetShippingInfo($oXmlOrder) {
        include_once (__DIR__."/fcafterbuyshipping.php");
        $oAfterbuyShipping = new fcafterbuyshipping();
        $oShippingInfo = $oAfterbuyShipping->createShippingInfo($oXmlOrder);

        $this->ShippingInfo = $oShippingInfo;
    }

    /**
     * Sets basic values of order
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetCommonValues($oXmlOrder) {
        $this->InvoiceNumber = (string) $oXmlOrder->InvoiceNumber;
        $this->OrderID = (string) $oXmlOrder->OrderID;
        $this->EbayAccount = (string) $oXmlOrder->EbayAccount;
        $this->AmazonAccount = (string) $oXmlOrder->AmazonAccount;
        $this->Anr = (string) $oXmlOrder->Anr;
        $this->AlternativeItemNumber1 = (string) $oXmlOrder->AlternativeItemNumber1;
        $this->FeedbackDate = (string) $oXmlOrder->FeedbackDate;
        $this->UserComment = (string) $oXmlOrder->UserComment;
        $this->AdditionalInfo = (string) $oXmlOrder->AdditionalInfo;
        $this->TrackingLink = (string) $oXmlOrder->TrackingLink;
        $this->UserComment = (string) $oXmlOrder->UserComment;
        $this->Memo = (string) $oXmlOrder->Memo;
        $this->InvoiceMemo = (string) $oXmlOrder->InvoiceMemo;
        $this->FeedbackLink = (string) $oXmlOrder->FeedbackLink;
        $this->OrderDate = (string) $oXmlOrder->OrderDate;
        $this->OrderIDAlt = (string) $oXmlOrder->OrderIDAlt;
    }

}