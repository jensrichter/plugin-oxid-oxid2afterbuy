<?php

/**
 * Class for triggering orders directly to Afterbuy after finshing order process
 *
 * @author andre
 */
class fcafterbuy_oxorder extends fcafterbuy_oxorder_parent {

    /**
     * List of paymentids which can be set directly to paid status
     * @var array
     */
    protected $_aPaymentsDirectlyPaid = array();

    /**
     * Overloading load method for appending additional table
     *
     * @param $oxID
     * @return mixed
     */
    public function load($oxID) {
        $mReturn = parent::load($oxID);

        if ($mReturn) {
            $this->_fcAddCustomFieldsToObject($oxID);
        }

        return $mReturn;
    }

    /**
     * Adds fields of custom table too current object
     *
     * @param string $sOxid
     * @return void
     */
    protected function _fcAddCustomFieldsToObject($sOxid) {
        $oDb = oxDb::getDb(oxDB::FETCH_MODE_ASSOC);
        $sQuery = "
            SELECT
              `FCAFTERBUY_AID`,
              `FCAFTERBUY_VID`,
              `FCAFTERBUY_UID`,
              `FCAFTERBUY_CUSTOMNR`,
              `FCAFTERBUY_ECUSTOMNR`,
              `FCAFTERBUY_LASTCHECKED`,
              `FCAFTERBUY_FULFILLED`
            FROM
                oxorder_afterbuy
            WHERE OXID = '{$sOxid}'
        ";

        $aRow = $oDb->getRow($sQuery);
        if (is_array($aRow) && count($aRow)>0) {
            foreach ($aRow as $sDbField=>$sValue) {
                $sDbField = strtolower($sDbField);
                $sField = "oxorder__".$sDbField;
                $this->$sField = new oxField($sValue);
            }
        }
    }


    /**
     * Adds triggering to send order to afterbuy if configured
     *
     * @param oxUser $oUser
     * @param oxBasket $oBasket
     * @param oxPayment $oPayment
     *
     * @return int
     */
    protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null) {
        $iRet = parent::_sendOrderByEmail($oUser, $oBasket, $oPayment);
        $oConfig = $this->getConfig();
        $blSendOrdersOnTheFly =
            (bool) $oConfig->getConfigParam('blFcSendOrdersOnTheFly');
        if (!$blSendOrdersOnTheFly) return $iRet;

        try {
            $oFcAfterbuyOrder = oxNew('fco2aorder');
            $sMessage =
                'MESSAGE: Attempting to send order: '.
                $this->oxorder__oxordernr->value.
                ' to Afterbuy...';

            $oFcAfterbuyOrder->fcWriteLog($sMessage, 3);
            $this->_fcMarkOrderPaid($oBasket);
            $oFcAfterbuyOrder->fcSendOrderToAfterbuy($this, $oUser);
        } catch(Exception $e) {
            $sMessage =
                'ERROR: Could not send order with ordernr:'.
                $this->oxorder__oxordernr->value.
                '. Error that was catched:'.
                $e->getMessage();
            $oFcAfterbuyOrder->fcWriteLog($sMessage, 1);
        }

        return $iRet;
    }

    /**
     * Marks certain orders as paid if condition matches
     * 
     * @param oxBasket $oBasket
     * @return void
     */
    protected function _fcMarkOrderPaid($oBasket) {
        $blSetPaid = $this->_fcCheckPaid($oBasket);

        if (!$blSetPaid) return;

        $oUtilsDate = oxRegistry::get("oxUtilsDate");

        $oDb = oxDb::getDb();
        $sDate = date('Y-m-d H:i:s', $oUtilsDate->getTime());

        $sQ = 'UPDATE oxorder SET oxpaid=? WHERE oxid=?';
        $oDb->execute($sQ, array($sDate, $this->getId()));

        //updating order object
        $this->oxorder__oxpaid = new oxField($sDate);
    }

    /**
     * Checks if order should be marked as paid
     * 
     * @param oxBasket $oBasket
     * @return boolean
     */
    protected function _fcCheckPaid($oBasket) {
        $this->_fcSetPaidPayments();

        $blNoBasketObject = ($oBasket === null);
        if ($blNoBasketObject) return false;

        $sPaymentId = $oBasket->getPaymentId();

        $blReturn = in_array(
            $sPaymentId,
            $this->_aPaymentsDirectlyPaid
        );

        return $blReturn;
    }

    /**
     * Sets payments which are configured to be set to paid directly after order
     * 
     * @param void
     * @return void
     */
    protected function _fcSetPaidPayments() {
        $oConfig = $this->getConfig();
        $this->_aPaymentsDirectlyPaid =
            $oConfig->getConfigParam('aFcAfterbuyPaymentsSetPaid');
    }

}
