<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 13.11.18
 */

class fcafterbuy_oxarticle extends fcafterbuy_oxarticle_parent
{
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
              `FCAFTERBUYACTIVE`,
              `FCAFTERBUYID`
            FROM
               oxarticles_afterbuy
            WHERE OXID = '{$sOxid}'
        ";

        $aRow = $oDb->getRow($sQuery);
        if (is_array($aRow) && count($aRow)>0) {
            foreach ($aRow as $sDbField=>$sValue) {
                $sDbField = strtolower($sDbField);
                $sField = "oxarticles__".$sDbField;
                $this->$sField = new oxField($sValue);
            }
        }
    }
}