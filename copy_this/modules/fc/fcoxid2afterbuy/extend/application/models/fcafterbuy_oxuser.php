<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 13.11.18
 */

class fcafterbuy_oxuser extends fcafterbuy_oxuser_parent
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
     * Overloading save method to assign afterbuy values to subtable
     *
     * @return mixed
     */
    public function save() {
        $blRet = parent::save();
        $this->fcSaveAfterbuyParams();

        return $blRet;
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
                `FCAFTERBUY_USERID`
            FROM
                oxuser_afterbuy
            WHERE OXID = '{$sOxid}'
        ";

        $aRow = $oDb->getRow($sQuery);
        if (is_array($aRow) && count($aRow)>0) {
            foreach ($aRow as $sDbField=>$sValue) {
                $sDbField = strtolower($sDbField);
                $sField = "oxuser__".$sDbField;
                $this->$sField = new oxField($sValue);
            }
        }
    }

    /**
     * Save afterbuy params
     *
     * @param void
     * @return void
     */
    public function fcSaveAfterbuyParams() {
        $oAfterbuyDb = oxNew('fco2adatabase');
        $sOxid = $this->getId();

        $aAfterbuyParams = array(
            'FCAFTERBUY_USERID' => $this->oxuser__fcafterbuy_userid->value,
        );

        $oAfterbuyDb->fcSaveAfterbuyParams(
            'oxuser_afterbuy',
            'oxuser',
            $sOxid,
            $aAfterbuyParams
        );
    }

}