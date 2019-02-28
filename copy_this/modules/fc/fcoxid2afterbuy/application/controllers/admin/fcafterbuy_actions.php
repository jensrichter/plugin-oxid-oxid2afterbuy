<?php
/**
 * FATCHIP oxid2afterbuy
 * @author FATCHIP GmbH
 */
class fcafterbuy_actions extends oxAdminDetails
{
	/**
	 * Template
	 * @var string
	 */
	protected $_sThisTemplate = 'fcafterbuy_actions.tpl';


    /**
     * Triggers a datareset and deletes certain tables
     *
     * @param void
     * @return void
     */
    public function fcResetTransactionData() {
        $oLang = oxRegistry::getLang();
        $oUtilsView = oxRegistry::get('oxUtilsView');
        $oAfterbuyDatabase = oxNew('fco2adatabase');
        $oAfterbuyDatabase->fcResetTransactionData();
        $sMessage = $oLang->translateString('SHOP_MODULE_AFTERBUY_TRANSACTIONDATA_RESET');
        $oUtilsView->addErrorToDisplay(oxNew('oxException', $sMessage));
    }
    
}