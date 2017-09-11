<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 11:51
 */
class fcafterbuypayment
{
    /**
     * Representation of possible values of an afterbuy article
     * @var array
     */
    protected $_aPaymentAttributes = array(
        'PaymentID' => null,
        'PaymentMethod' => null,
        'PaymentFunction' => null,
        'PaymentData' => array(),
        'PaymentTransactionID' => null,
        'PaymentStatus' => null,
        'PaymentDate' => null,
        'AlreadyPaid' => null,
        'FullAmount' => null,
        'PaymentInstruction' => null,
        'InvoiceDate' => null,
    );

    /**
     * Magic setter
     *
     * @param string $sName
     * @param mixed $mValue
     * @return void
     */
    public function __set($sName, $mValue) {
        $this->_aPaymentAttributes[$sName] = $mValue;
    }

    /**
     * Magic getter
     *
     * @param $sName
     * @return mixed
     */
    public function __get($sName) {
        return $this->_aPaymentAttributes[$sName];
    }

    /**
     * Creates payment information node of an afterbuy order resultset
     *
     * @param $sResponse
     * @return void
     */
    public function createPaymentFromOrderResponse($oXmlOrder) {
        $oPaymentInfo = $oXmlOrder->PaymentInfo;

        $this->PaymentID = (string) $oPaymentInfo->PaymentID;
        $this->PaymentMethod = (string) $oPaymentInfo->PaymentMethod;
        $this->PaymentFunction = (string) $oPaymentInfo->PaymentFunction;
        $this->PaymentData['BankCode'] = (string) $oPaymentInfo->PaymentData->BankCode;
        $this->PaymentData['AccountHolder'] = (string) $oPaymentInfo->PaymentData->AccountHolder;
        $this->PaymentData['BankName'] = (string) $oPaymentInfo->PaymentData->BankName;
        $this->PaymentData['AccountNumber'] = (string) $oPaymentInfo->PaymentData->AccountNumber;
        $this->PaymentData['Iban'] = (string) $oPaymentInfo->PaymentData->Iban;
        $this->PaymentData['Bic'] = (string) $oPaymentInfo->PaymentData->Bic;
        $this->PaymentData['ReferenceNumber'] = (string) $oPaymentInfo->PaymentData->ReferenceNumber;
        $this->PaymentTransactionID = (string) $oPaymentInfo->PaymentTransactionID;
        $this->PaymentStatus = (string) $oPaymentInfo->PaymentStatus;
        $this->PaymentDate = (string) $oPaymentInfo->PaymentDate;
        $this->AlreadyPaid = (string) $oPaymentInfo->AlreadyPaid;
        $this->FullAmount = (string) $oPaymentInfo->FullAmount;
        $this->PaymentInstruction = (string) $oPaymentInfo->PaymentInstruction;
        $this->InvoiceDate = (string) $oPaymentInfo->InvoiceDate;
        $this->EFTID = (string) $oPaymentInfo->EFTID;
    }

}