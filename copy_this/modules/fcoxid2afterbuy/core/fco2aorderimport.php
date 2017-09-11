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
        }
    }

    /**
     * Creates an oxid order including user and articles
     *
     * @param $oAfterbuyOrder
     * @return void
     */
    protected function _fcCreateOxidOrder($oAfterbuyOrder) {
        // create OXORDER
        // create OXUSER
        // create OXORDERARTICLES
        // send external order number to Afterbuy API ?
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
