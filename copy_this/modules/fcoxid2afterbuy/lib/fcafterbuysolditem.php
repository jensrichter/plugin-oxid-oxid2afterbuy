<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 12:08
 */
class fcafterbuysolditem
{
    /**
     * Representation of possible values of an afterbuy article
     * @var array
     */
    protected $_aItemAttributes = array(
        'ItemDetailsDone' => null,
        'ItemID' => null,
        'Anr' => null,
        'AmazonAccount' => null,
        'IsAmazonPrime' => null,
        'FulfillmentServiceLevel' => null,
        'eBayTransactionID' => null,
        'AlternativeItemNumber1' => null,
        'AlternativeItemNumber' => null,
        'InternalItemType' => null,
        'UserDefinedFlag' => null,
        'ItemTitle' => null,
        'ItemQuantity' => null,
        'ItemPrice' => null,
        'ItemEndDate' => null,
        'TaxRate' => null,
        'ItemWeight' => null,
        'ItemXmlDate' => null,
        'ItemModDate' => null,
        'ItemPlatformName' => null,
        'ItemLink' => null,
        'eBayFeedbackCompleted' => null,
        'eBayFeedbackReceived' => null,
        'eBayFeedbackCommentType' => null,
        'ShopProductDetails' => null,
        'SoldItemAttributes' => null,
    );

    /**
     * Magic setter
     *
     * @param string $sName
     * @param mixed $mValue
     * @return void
     */
    public function __set($sName, $mValue) {
        $this->_aItemAttributes[$sName] = $mValue;
    }

    /**
     * Magic getter
     *
     * @param $sName
     * @return mixed
     */
    public function __get($sName) {
        return $this->_aItemAttributes[$sName];
    }

    /**
     * Creates sold item
     *
     * @param simplexml object
     * @return void
     */
    public function createSoldItemFromXmlSoldItem($oXmlSoldItem) {
        $this->ItemDetailsDone = (string) $oXmlSoldItem->ItemDetailsDone;
        $this->ItemID = (string) $oXmlSoldItem->ItemID;
        $this->Anr = (string) $oXmlSoldItem->Anr;
        $this->IsAmazonBusiness = (string) $oXmlSoldItem->IsAmazonBusiness;
        $this->IsAmazonPrime = (string) $oXmlSoldItem->IsAmazonPrime;
        $this->FulfillmentServiceLevel = (string) $oXmlSoldItem->FulfillmentServiceLevel;
        $this->eBayTransactionID = (string) $oXmlSoldItem->eBayTransactionID;
        $this->AlternativeItemNumber1 = (string) $oXmlSoldItem->AlternativeItemNumber1;
        $this->AlternativeItemNumber = (string) $oXmlSoldItem->AlternativeItemNumber;
        $this->InternalItemType = (string) $oXmlSoldItem->InternalItemType;
        $this->UserDefinedFlag = (string) $oXmlSoldItem->UserDefinedFlag;
        $this->ItemTitle = (string) $oXmlSoldItem->ItemTitle;
        $this->ItemQuantity = (string) $oXmlSoldItem->ItemQuantity;
        $this->ItemPrice = (string) $oXmlSoldItem->ItemPrice;
        $this->ItemEndDate = (string) $oXmlSoldItem->ItemEndDate;
        $this->TaxRate = (string) $oXmlSoldItem->TaxRate;
        $this->ItemWeight = (string) $oXmlSoldItem->ItemWeight;
        $this->ItemXmlDate = (string) $oXmlSoldItem->ItemXmlDate;
        $this->ItemModDate = (string) $oXmlSoldItem->ItemModDate;
        $this->ItemPlatformName = (string) $oXmlSoldItem->ItemPlatformName;
        $this->ItemLink = (string) $oXmlSoldItem->ItemLink;
        $this->eBayFeedbackCompleted = (string) $oXmlSoldItem->eBayFeedbackCompleted;
        $this->eBayFeedbackReceived = (string) $oXmlSoldItem->eBayFeedbackReceived;
        $this->eBayFeedbackCommentType = (string) $oXmlSoldItem->eBayFeedbackCommentType;
        $this->ShopProductDetails = $this->_fcGetShopProductDetails($oXmlSoldItem);
        $this->SoldItemAttributes = $this->_fcGetItemAttributes($oXmlSoldItem);
    }

    /**
     * Creates needed product details data
     *
     * @param object $oXmlSoldItem
     * @return object
     */
    protected function _fcGetShopProductDetails($oXmlSoldItem) {
        $oShopProductDetails = new stdClass();

        $oShopProductDetails->ProductID = (string) $oXmlSoldItem->ShopProductDetails->ProductID;
        $oShopProductDetails->EAN = (string) $oXmlSoldItem->ShopProductDetails->EAN;
        $oShopProductDetails->Anr = (string) $oXmlSoldItem->ShopProductDetails->Anr;
        $oShopProductDetails->UnitOfQuantity = (string) $oXmlSoldItem->ShopProductDetails->UnitOfQuantity;
        $oShopProductDetails->BasepriceFactor = (string) $oXmlSoldItem->ShopProductDetails->BasepriceFactor;
        $oShopProductDetails->BaseProductDetails = $this->_fcGetBaseProductData($oXmlSoldItem);

        return $oShopProductDetails;
    }

    /**
     * Creates subset of product base data
     *
     * @param $oXmlSoldItem
     * @return object
     */
    protected function _fcGetBaseProductData($oXmlSoldItem) {
        $oBaseProductData = new stdClass();
        $oChildProduct = new stdClass();
        $oBaseProductData->BaseProductType = (string) $oXmlSoldItem->ShopProductDetails->BaseProductData->BaseProductType;

        if (isset($oXmlSoldItem->ShopProductDetails->BaseProductData->ChildProduct)) {
            $oXmlChildProduct = $oXmlSoldItem->ShopProductDetails->BaseProductData->ChildProduct;
            $oChildProduct->ProductID = (string) $oXmlChildProduct->ProductID;
            $oChildProduct->ProductEAN = (string) $oXmlChildProduct->ProductEAN;
            $oChildProduct->ProductANr = (string) $oXmlChildProduct->ProductANr;
            $oChildProduct->ProductName = (string) $oXmlChildProduct->ProductName;
            $oChildProduct->ProductQuantity = (string) $oXmlChildProduct->ProductQuantity;
            $oChildProduct->ProductVAT = (string) $oXmlChildProduct->ProductVAT;
            $oChildProduct->ProductWeight = (string) $oXmlChildProduct->ProductWeight;
            $oChildProduct->ProductUnitPrice = (string) $oXmlChildProduct->ProductUnitPrice;
        }

        $oBaseProductData->ChildProduct = $oChildProduct;

        return $oBaseProductData;
    }

    /**
     * Creates subset of article attributes
     *
     * @param object $oXmlSoldItem
     * @return array
     */
    protected function _fcGetItemAttributes($oXmlSoldItem) {
        $oXmlItemAttributes = $oXmlSoldItem->SoldItemAttributes;
        $aItemAttributes = array();

        foreach ($oXmlItemAttributes as $oXmlItemAttribute) {
            $oItemAttribute = new stdClass();
            $oItemAttribute->AttributeName = (string) $oXmlItemAttribute->AttributeName;
            $oItemAttribute->AttributeValue = (string) $oXmlItemAttribute->AttributeValue;
            $oItemAttribute->AttributePosition = (string) $oXmlItemAttribute->AttributePosition;

            $aItemAttributes[] = $oItemAttribute;
        }

        return $aItemAttributes;
    }

}