<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 05.10.17
 * Time: 19:09
 */

class fcafterbuy_orderinfo extends oxAdminDetails {

    /**
     * Template
     * @var string
     */
    protected $_sThisTemplate = 'fcafterbuy_orderinfo.tpl';


    /**
     * Template getter for returning array of name value combinations
     *
     * @param void
     * @return mixed array|false
     */
    public function fcGetOrderAfterbuyValues() {
        $mReturn = false;
        $oConfig = $this->getConfig();
        $sOxid = $oConfig->getRequestParameter("oxid");
        $oOrder = oxNew("oxorder");
        if ($oOrder->load($sOxid)) {
            $mReturn = array(
                'FCAFTERBUY_AID' => $oOrder->oxorder__fcafterbuy_aid->value,
                'FCAFTERBUY_VID' => $oOrder->oxorder__fcafterbuy_vid->value,
                'FCAFTERBUY_UID' => $oOrder->oxorder__fcafterbuy_uid->value,
                'FCAFTERBUY_CUSTOMNR' => $oOrder->oxorder__fcafterbuy_customnr->value,
                'FCAFTERBUY_ECUSTOMNR' => $oOrder->oxorder__fcafterbuy_ecustomnr->value,
                'FCAFTERBUY_FULFILLED' => $oOrder->oxorder__fcafterbuy_fulfilled->value,
                'FCAFTERBUY_LASTCHECKED' => $oOrder->oxorder__fcafterbuy_lastchecked->value,
            );
        }

        return $mReturn;
    }
}