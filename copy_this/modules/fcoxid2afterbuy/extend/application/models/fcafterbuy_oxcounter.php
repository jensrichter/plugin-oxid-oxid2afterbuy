<?php
/**
 * Created by PhpStorm.
 * User: andre
 * Date: 17.10.17
 * Time: 11:38
 */

class fcafterbuy_oxcounter extends fcafterbuy_oxcounter_parent {

    /**
     * Simply returns counter value
     *
     * @param $sIdent
     * @return int
     */
    public function fcGetCurrent($sIdent) {
        $oDb = oxDb::getDb();
        $oDb->startTransaction();
        $sQ = "SELECT `oxcount` FROM `oxcounters` WHERE `oxident` = " . $oDb->quote($sIdent);
        $iCnt = $oDb->getOne($sQ);
        $oDb->commitTransaction();

        return (int) $iCnt;
    }
}