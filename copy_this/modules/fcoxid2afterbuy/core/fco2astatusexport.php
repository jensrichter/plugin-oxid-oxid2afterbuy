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
        // load configuration and instanciate fcafterbuyapi class
        $aConfig = $this->_fcGetAfterbuyConfigArray();
        $oAfterbuyApi = $this->_fcGetAfterbuyApi($aConfig);

        // load order IDs of changed afterbuy orders to export from oxorder/oxorderarticles
        $aUpdateOrderIds = $this->_fcGetUpdatedAfterbuyOrders();

        // foreach order
        foreach ($aUpdateOrderIds as $sOrderOxid) {
            // create afterbuy order status object
            $oAfterbuyOrderStatus = $this->_fcGetAfterbuyStatus();
            $oOrder = oxNew('oxorder');
            $oOrder->load($sOrderOxid);
            $oAfterbuyOrderStatus = $this->_fcAssignOrderDataToOrderStatus($oOrder, $oAfterbuyOrderStatus);
            // update orderstatus via API
            $oAfterbuyApi->updateSoldItemsOrderState($oAfterbuyOrderStatus);

            // mark orderstatus as fulfilled in OXID database if there is a remarkable event
            $blFulfilled = (
                isset($oAfterbuyOrderStatus->ShippingInfo->DeliveryDate) &&
                isset($oAfterbuyOrderStatus->PaymentInfo->PaymentDate)
            );
            if ($blFulfilled) {
                $oOrder->oxorder__fcafterbuy_fulfilled = new oxField(1);
                $oOrder->save();
            }
        }
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
            $oAfterbuyOrderStatus->ShippingInfo->DeliveryDate = $this->_fcGetGermanDate($sOrderSendDate);
        }
        if ($sPaidDate != '0000-00-00 00:00:00') {
            $oAfterbuyOrderStatus->PaymentInfo->PaymentDate = $this->_fcGetGermanDate($sPaidDate);
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
            FROM oxorders 
            WHERE FCAFTERBUY_UID!='' 
            AND OXTIMESTAMP>FCAFTERBUY_LASTCHECKED 
            AND FCAFTERBUY_FULFILLED !='0'
        ";
        $aRows = $oDb->getAll($sQuery);

        foreach ($aRows as $aRow) {
            $aAffectedOrderIds[] = $aRow['OXID'];
        }

        return $aAffectedOrderIds;
    }

    /**
     * Returns a new afterbuy order object
     *
     * @param void
     * @return object
     */
    protected function _fcGetAfterbuyStatus() {
        $oViewConfig = oxRegistry::get('oxViewConfig');
        $sPathToModule = $oViewConfig->getModulePath('fcoxid2afterbuy');
        $sPathToAfterbuyLib = $sPathToModule.'lib/fcafterbuyorderstatus.php';
        include_once($sPathToAfterbuyLib);
        $oAfterbuyStatus = new fcafterbuystatus();

        return $oAfterbuyStatus;
    }

}
