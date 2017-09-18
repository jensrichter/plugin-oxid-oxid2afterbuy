<?php
class fco2astatusexport extends oxI18n {

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

            // update article via API
            // mark orderstatus as transferred in OXID database
        }
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

        $sQuery = "SELECT OXID FROM oxorders WHERE FCAFTERBUY_UID!='' AND OXTIMESTAMP>FCAFTERBUY_LASTCHECKED";
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
        $sPathToAfterbuyLib = $sPathToModule.'lib/fcafterbuystatus.php';
        include_once($sPathToAfterbuyLib);
        $oAfterbuyStatus = new fcafterbuystatus();

        return $oAfterbuyStatus;
    }

}
