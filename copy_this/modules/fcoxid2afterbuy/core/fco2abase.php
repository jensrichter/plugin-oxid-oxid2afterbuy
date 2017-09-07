<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 07.09.17
 * Time: 12:39
 */
class fco2abase extends oxBase {

    /**
     * Current loglevel
     * @var int
     */
    protected $_iFcLogLevel = null;

    /**
     * Logfile for standard output
     * @var string
     */
    protected $_sLogFile = 'fco2a_default.log';

    /**
     * fco2abase constructor.
     * initialize loglevel
     */
    public function __construct() {
        parent::__construct();
        $oConfig = $this->getConfig();
        $this->_iFcLogLevel = (int)$oConfig->getConfigParam('iFcAfterbuyLogLevel');
    }

    /**
     * Central logging method. Timestamp will be added automatically.
     * Logs only if loglevel matches
     *
     * @param string $sMessage
     * @param int $iLogLevel
     * @return void
     * @access protected
     */
    public function fcWriteLog($sMessage, $iLogLevel = 1) {
        $sTime = date("Y-m-d H:i:s");
        $sFullMessage = "[" . $sTime . "] " . $sMessage . "\n";
        if ($iLogLevel >= $this->_iFcLogLevel) {
            $oUtils = oxRegistry::getUtils();
            $oUtils->writeToLog($sFullMessage, $this->_sLogFile);
        }
    }
}