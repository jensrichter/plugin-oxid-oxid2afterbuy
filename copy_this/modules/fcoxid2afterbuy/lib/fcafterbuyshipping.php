<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 12:22
 */
class fcafterbuyshipping
{
    /**
     * Representation of possible values of an afterbuy article
     * @var array
     */
    protected $_aShippingAttributes = array(
        'ShippingMethod' => null,
        'ShippingCost' => null,
        'ShippingAdditionalCost' => null,
        'ShippingTotalCost' => null,
        'ShippingTaxRate' => null,
        'DeliveryDate' => null,
    );

    /**
     * Magic setter
     *
     * @param string $sName
     * @param mixed $mValue
     * @return void
     */
    public function __set($sName, $mValue) {
        $this->_aShippingAttributes[$sName] = $mValue;
    }

    /**
     * Magic getter
     *
     * @param $sName
     * @return mixed
     */
    public function __get($sName) {
        return $this->_aShippingAttributes[$sName];
    }

    /**
     * Creates shipping info part of order
     *
     * @param $oXmlOrder
     * @return object
     */
    public function createShippingInfo($oXmlOrder) {
        $oXmlShippingInfo = $oXmlOrder->ShippingInfo;
        $this->ShippingMethod = (string) $oXmlShippingInfo->ShippingMethod;
        $this->ShippingCost = (string) $oXmlShippingInfo->ShippingCost;
        $this->ShippingAdditionalCost = (string) $oXmlShippingInfo->ShippingAdditionalCost;
        $this->ShippingTotalCost = (string) $oXmlShippingInfo->ShippingTotalCost;
        $this->ShippingTaxRate = (string) $oXmlShippingInfo->ShippingTaxRate;
        $this->DeliveryDate = (string) $oXmlShippingInfo->DeliveryDate;

        return $this;
    }
}