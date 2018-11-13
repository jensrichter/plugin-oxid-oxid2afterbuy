<?php
/**
 * Created by PhpStorm.
 * User: andrefatchip
 * Date: 13.11.18
 */

class fco2adatabase extends oxBase
{
    /**
     * Saving afterbuy params into own subtable
     *
     * @param string $sTable
     * @param string $sTablePrefix
     * @param string $sOxid
     * @param array $aAfterbuyParams
     * @return void
     * @throws
     */
    public function fcSaveAfterbuyParams($sTable, $sTablePrefix, $sOxid=null, $aAfterbuyParams=null)
    {
        $this->fcCreateAfterbuyDataRow($sTable, $sOxid);

        $blUseRequestValues = (
            $sOxid===null &&
            $aAfterbuyParams === null
        );

        if ($blUseRequestValues) {
            $oConfig = $this->getConfig();
            $sOxid = $oConfig->getRequestParameter('oxid');
            $aAfterbuyParams = $oConfig->getRequestParameter('editvalafterbuy');
        }

        if (!is_array($aAfterbuyParams)) return;

        $sTablePrefix = $sTablePrefix."__";

        foreach ($aAfterbuyParams as $sTableField=>$sValue) {
            $sField = str_replace($sTablePrefix, '', $sTableField);
            $this->fcUpdateFieldOfTable($sTable, $sOxid, $sField, $sValue);
        }
    }

    /**
     * Checks if a certain row exists in given table and id
     *
     * @param $sTable
     * @param $sOxid
     * @return bool
     * @throws exception
     */
    public function fcRowExists($sTable, $sOxid) {
        $oDb = oxDb::getDb();
        $sQuery = "SELECT OXID FROM {$sTable} WHERE OXID = '{$sOxid}'";
        $sDbOxid = $oDb->getOne($sQuery);
        $blExists = (bool) $sDbOxid;

        return $blExists;
    }

    /**
     * Insert a new row in given table with given id
     *
     * @param $sTable
     * @param $sOxid
     * @return void
     * @throws exception
     */
    public function fcInsertRow($sTable, $sOxid) {
        $oDb = oxDb::getDb();
        $sQuery = "INSERT INTO {$sTable} (OXID) VALUES ('{$sOxid}')";
        $oDb->execute($sQuery);
    }

    /**
     * Updating given field with value of given table and id
     *
     * @param string $sTable
     * @param string $sOxid
     * @param string $sField
     * @param string $sValue
     * @return void
     * @throws exception
     */
    public function fcUpdateFieldOfTable($sTable, $sOxid, $sField, $sValue) {
        $oDb = oxDb::getDb();
        $sQuery = "
                UPDATE
                  {$sTable} 
                SET ".strtoupper($sField)."=".$oDb->quote($sValue)."
                WHERE OXID=".$oDb->quote($sOxid);
        $oDb->execute($sQuery);
    }

    /**
     * Creating a database row entry of this id
     *
     * @param string $sTable
     * @param mixed string|null
     * @return void
     * @throws
     */
    public function fcCreateAfterbuyDataRow($sTable, $sOxid) {
        $blExists = $this->fcAfterbuyDataRowExists($sTable, $sOxid);
        if ($blExists) return;

        if ($sOxid === null) {
            $oConfig = $this->getConfig();
            $sOxid   = $oConfig->getRequestParameter("oxid");
        }

        $this->fcInsertRow($sTable, $sOxid);
    }

    /**
     * Returns row of request oxattribute external 1:1 Table
     *
     * @param string $sTable
     * @param mixed string|null
     * @return bool
     * @throws
     */
    public function fcAfterbuyDataRowExists($sTable, $sOxid) {
        if ($sOxid === null) {
            $oConfig = $this->getConfig();
            $sOxid = $oConfig->getRequestParameter("oxid");
        }
        $blExists = $this->fcRowExists($sTable, $sOxid);

        return $blExists;
    }
}