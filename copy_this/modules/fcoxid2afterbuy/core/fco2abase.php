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
     * Ident for oxid counter
     * @var string
     */
    protected $_sCounterIdent = 'fcAfterbuyLastOrder';

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
        if ($iLogLevel <= $this->_iFcLogLevel) {
            $oUtils = oxRegistry::getUtils();
            $oUtils->writeToLog($sFullMessage, $this->_sLogFile);
        }
    }

    /**
     * Returns needed configuration for instantiate afterbuy api object
     *
     * @param void
     * @return array
     */
    protected function _fcGetAfterbuyConfigArray() {
        $oConfig = $this->getConfig();
        $aConfig = array(
            'afterbuyShopInterfaceBaseUrl' => $oConfig->getConfigParam('sFcAfterbuyShopInterfaceBaseUrl'),
            'afterbuyAbiUrl' => $oConfig->getConfigParam('sFcAfterbuyAbiUrl'),
            'afterbuyPartnerId' => $oConfig->getConfigParam('sFcAfterbuyPartnerId'),
            'afterbuyPartnerPassword' => $oConfig->getConfigParam('sFcAfterbuyPartnerPassword'),
            'afterbuyUsername' => $oConfig->getConfigParam('sFcAfterbuyUsername'),
            'afterbuyUserPassword' => $oConfig->getConfigParam('sFcAfterbuyUserPassword'),
            'logLevel' => $oConfig->getConfigParam('iFcAfterbuyLogLevel'),
            'lastOrderId' => $this->_fcGetLastOrderId(),
        );

        return $aConfig;
    }

    /**
     * Returns current orderid from oxCounter
     *
     * @param void
     * @return string
     */
    protected function _fcGetLastOrderId() {
        $oCounter = oxNew('oxCounter');
        $sLastOrderId = $oCounter-> fcGetCurrent($this->_sCounterIdent);

        return (string) $sLastOrderId;
    }

    /**
     * Returns afterbuy api object
     *
     * @param $aConfig
     * @return object
     */
    protected function _fcGetAfterbuyApi($aConfig) {
        $oViewConfig = oxRegistry::get('oxViewConfig');
        $sPathToModule = $oViewConfig->getModulePath('fcoxid2afterbuy');
        $sPathToAfterbuyLib = $sPathToModule.'lib/fcafterbuyapi.php';
        include_once($sPathToAfterbuyLib);
        $oAfterbuyApi = new fcafterbuyapi($aConfig);

        // directly set oxid logfilepath after instantiation
        $oAfterbuyApi->setLogFilePath(getShopBasePath()."/log/fco2a_api.log");

        return $oAfterbuyApi;
    }

}