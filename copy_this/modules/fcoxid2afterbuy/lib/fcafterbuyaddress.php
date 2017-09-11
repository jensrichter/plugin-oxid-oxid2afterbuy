<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 11:50
 */
class fcafterbuyaddress
{
    /**
     * Representation of possible values of an afterbuy article
     * @var array
     */
    protected $_aAddressAttributes = array(
        'AfterbuyUserID' => null,
        'AfterbuyUserIDAlt' => null,
        'UserIDPlattform' => null,
        'FirstName' => null,
        'LastName' => null,
        'Title' => null,
        'Company' => null,
        'Street' => null,
        'Street2' => null,
        'PostalCode' => null,
        'StateOrProvince' => null,
        'City' => null,
        'Country' => null,
        'CountryISO' => null,
        'Phone' => null,
        'Fax' => null,
        'Mail' => null,
        'IsMerchant' => null,
        'TaxIDNumber' => null,
    );

    /**
     * Magic setter
     *
     * @param string $sName
     * @param mixed $mValue
     * @return void
     */
    public function __set($sName, $mValue) {
        $this->_aAddressAttributes[$sName] = $mValue;
    }

    /**
     * Magic getter
     *
     * @param $sName
     * @return mixed
     */
    public function __get($sName) {
        return $this->_aAddressAttributes[$sName];
    }

    /**
     * Creates billing address from an getsolditems api call
     *
     * @param $oXmlOrder
     * @return void
     */
    public function createBillingAddressFromOrderResponse($oXmlOrder) {
        $oBillingAddress = $oXmlOrder->BuyerInfo->BillingAddress;
        $this->createShippingAddressFromOrderResponse($oXmlOrder);

        $this->AfterbuyUserID = (string) $oBillingAddress->AfterbuyUserID;
        $this->AfterbuyUserIDAlt = (string) $oBillingAddress->AfterbuyUserIDAlt;
        $this->UserIDPlattform = (string) $oBillingAddress->UserIDPlattform;
        $this->Title = (string) $oBillingAddress->Title;
        $this->Phone = (string) $oBillingAddress->Phone;
        $this->Fax = (string) $oBillingAddress->Fax;
        $this->Mail = (string) $oBillingAddress->Mail;
        $this->IsMerchant = (string) $oBillingAddress->IsMerchant;
        $this->TaxIDNumber = (string) $oBillingAddress->TaxIDNumber;
    }

    /**
     * Creates shipping address
     *
     * @param $oXmlOrder
     * @return void
     */
    public function createShippingAddressFromOrderResponse($oXmlOrder) {
        $oShippingAddress = $oXmlOrder->BuyerInfo->ShippingAddress;

        $this->FirstName = (string) $oShippingAddress->FirstName;
        $this->LastName = (string) $oShippingAddress->LastName;
        $this->Company = (string) $oShippingAddress->Company;
        $this->Street = (string) $oShippingAddress->Street;
        $this->Street2 = (string) $oShippingAddress->Street2;
        $this->PostalCode = (string) $oShippingAddress->PostalCode;
        $this->City = (string) $oShippingAddress->City;
        $this->StateOrProvince = (string) $oShippingAddress->StateOrProvince;
        $this->Country = (string) $oShippingAddress->Country;
        $this->CountryISO = (string) $oShippingAddress->CountryISO;
    }
}