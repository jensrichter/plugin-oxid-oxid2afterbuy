<?php
class fco2astatusexport extends fco2abase {

    /**
     * Central execution method
     *
     * @param void
     * @return void
     */
    public function execute()
    {
        $oAfterbuyApi = $this->_fcGetAfterbuyApi();

        // load order IDs of changed afterbuy orders to export from oxorder/oxorderarticles
        $aUpdateOrderIds = $this->_fcGetUpdatedAfterbuyOrders();

        // foreach order
        foreach ($aUpdateOrderIds as $sOrderOxid) {
            // create afterbuy order status object
            $oAfterbuyOrderStatus = $this->_fcGetAfterbuyStatus();
            $oOrder = oxNew('oxorder');
            $oOrder->load($sOrderOxid);
            $oAfterbuyOrderStatus =
                $this->_fcAssignOrderDataToOrderStatus($oOrder, $oAfterbuyOrderStatus);
            $sResponse =
                $oAfterbuyApi->updateSoldItemsOrderState($oAfterbuyOrderStatus);
            $blApiCallSuccess =
                $this->_fcCheckApiCallSuccess($sResponse);

            // mark orderstatus as fulfilled in OXID database if there is a remarkable event
            $blFulfilled = (
                $oOrder->oxorder__oxpaid->value != '0000-00-00 00:00:00' &&
                $oOrder->oxorder__oxsenddate->value != '0000-00-00 00:00:00' &&
                $blApiCallSuccess
            );
            if ($blFulfilled) {
                $oOrder->oxorder__fcafterbuy_fulfilled = new oxField(1);
            }
            $oOrder->save();
            $this->_fcSetLastCheckedDate($sOrderOxid);
        }
    }

    /**
     * Sets last check date of this order to now
     *
     * @param $sOrderOxid
     * @return void
     */
    protected function _fcSetLastCheckedDate($sOrderOxid) {
        $oDb = oxDb::getDb();
        $sQuery = "UPDATE oxorder SET FCAFTERBUY_LASTCHECKED=NOW() WHERE OXID=".$oDb->quote($sOrderOxid)." LIMIT 1";
        $oDb->Execute($sQuery);
    }

    /**
     * Assign current order data
     *
     * @param $oOrder
     * @param $oAfterbuyOrderStatus
     * @return object
     */
    protected function _fcAssignOrderDataToOrderStatus($oOrder, $oAfterbuyOrderStatus) {
        $oAfterbuyOrderStatus->OrderID = $oOrder->oxorder__fcafterbuy_uid->value;
        $sOrderSendDate = $oOrder->oxorder__oxsenddate->value;
        $sPaidDate = $oOrder->oxorder__oxpaid->value;

        if ($sOrderSendDate != '0000-00-00 00:00:00') {
            $oShippingInfo = new stdClass();
            $oShippingInfo->DeliveryDate = $this->_fcGetGermanDate($sOrderSendDate);
            $oAfterbuyOrderStatus->ShippingInfo = $oShippingInfo;
        }
        if ($sPaidDate != '0000-00-00 00:00:00') {
            $oPaymentInfo = new stdClass();
            $oPaymentInfo->PaymentDate = $this->_fcGetGermanDate($sPaidDate);
            $oAfterbuyOrderStatus->PaymentInfo = $oPaymentInfo;
        }

        return $oAfterbuyOrderStatus;
    }

    /**
     * Method determines changed afterbuy orders and returns a list of ids
     *
     * @param void
     * @return array
     */
    protected function _fcGetUpdatedAfterbuyOrders() {
        $aAffectedOrderIds = array();
        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);

        $sQuery = "
            SELECT OXID 
            FROM oxorder 
            WHERE FCAFTERBUY_UID != '' 
            AND OXTIMESTAMP>FCAFTERBUY_LASTCHECKED 
            AND FCAFTERBUY_FULFILLED != '1'
        ";
        $aRows = $oDb->getAll($sQuery);

        foreach ($aRows as $aRow) {
            $aAffectedOrderIds[] = $aRow['OXID'];
        }

        return $aAffectedOrderIds;
    }

}
