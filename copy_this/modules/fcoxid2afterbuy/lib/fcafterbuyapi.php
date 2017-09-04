<?php

/**
 * @see Afterbuy API documentation http://xmldoku.afterbuy.de/dokued/
 */

/**
 * fcafterbuy core class
 *
 * @author andre
 */
class fcafterbuyapi {

    /**
     * Error log level 1=Only errors, 2= Errors and warnings, 3=Output all
     * @var int
     */
    protected $iFcLogLevel;

    /**
     * Filename for logfile
     * @var string
     */
    protected $sFcAfterbuyLogFilename = "fcafterbuyapi.log";

    /**
     * ShopInterface Base URL of Afterbuy
     * https://www.afterbuy.de/afterbuy/ShopInterface.aspx
     * @var string
     */
    protected $sFcAfterbuyShopInterfaceBaseUrl = "";

    /**
     * ABI Url of Afterbuy
     * http://api.afterbuy.de/afterbuy/ABInterface.aspx
     * @var string
     */
    protected $sFcAfterbuyAbiUrl = "";

    /**
     * Partner ID of Afterbuy
     * @var string
     */
    protected $sFcAfterbuyPartnerId = "";

    /**
     * Partner Password for Afterbuy
     * @var string
     */
    protected $sFcAfterbuyPartnerPassword = "";

    /**
     * Username for Afterbuy
     * @var string
     */
    protected $sFcAfterbuyUsername = "";

    /**
     * User password for Afterbuy
     * @var string
     */
    protected $sFcAfterbuyUserPassword = "";


    function __construct() {

       /**
        * set variables
        */
    }

    /**
     * Central logging method. Timestamp will be added automatically.
     * Logs only if logging is set to true
     *
     * @param string $sMessage
     * @param int $iLogLevel
     * @return void
     * @access protected
     */
    public function fcWriteLog($sMessage, $iLogLevel = 1) {
        $sTime = date("Y-m-d H:i:s");
        $sFullMessage = "[" . $sTime . "] " . $sMessage . "\n";
        if ($iLogLevel > 0) {
            $sLogFile = $this->sFcAfterbuyLogFilename;
            //file_put_contents($sLogFile, $sFullMessage, FILE_APPEND);
            echo $sFullMessage;
        }
    }


    /**
     * Request Afterbuy API with given XML Request
     *
     * @param string $sXmlData
     * @return string API answer
     * @access protected
     */
    protected function fcRequestAPI($sXmlData) {
        $ch = curl_init($this->sFcAfterbuyAbiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$sXmlData");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $sOutput = curl_exec($ch);
        curl_close($ch);

        return $sOutput;
    }

    /**
     * Request Afterbuy shop interface with REST URL
     *
     * @param string $sRequest
     * @return string API answer
     * @access protected
     */
    protected function fcRequestShopInterfaceAPI($sRequest) {
        // prepare parameters for post call
        $aRequest = explode("?", $sRequest);
        $sParamString = $aRequest[1];
        $aParamsWithValues = explode("&", $sParamString);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->sFcAfterbuyShopInterfaceBaseUrl);
        curl_setopt($ch, CURLOPT_POST, count($aParamsWithValues));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sParamString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $sOutput = curl_exec($ch);
        curl_close($ch);

        return $sOutput;
    }

    public function updateArticleToAfterbuy($oArt) {
        $xml_data = $this->fcGetArtAddXml($oArt);
        //var_dump($xml_data);
        $aOutput = $this->_fcRequestAPI($xml_data);
        //var_dump($aOutput);
        return $aOutput;
    }

    public function getSoldItemsFromAfterbuy() {
        $xml_data = $this->fcGetXmlHead('GetSoldItems', 0);
        $xml_data .= "<MaxSoldItems>99</MaxSoldItems>";
        $xml_data .= "<OrderDirection>1</OrderDirection>";
        /*
        $xml_data .= "<DataFilter>
                        <Filter>
                         <FilterName>DefaultFilter</FilterName>
                         <FilterValues>
                           <FilterValue>not_CompletedAuctions</FilterValue>
                         </FilterValues>
                        </Filter>
                       </DataFilter>";
*/
        $xml_data .= $this->fcGetXmlFoot();
        //var_dump($xml_data);

        $aOutput = $this->fcRequestAPI($xml_data);
        //var_dump($aOutput);
        return $aOutput;
    }

    protected function fcGetArtAddXml($oArt) {
        $sOaAbId = 'oxarticles__fcafterbuyid';
        $sTitle = trim($oArt->oxarticles__oxtitle->value . ' ' . $oArt->oxarticles__oxvarselect->value);
        $xml_data = $this->fcGetXmlHead('UpdateShopProducts', 0);
        $xml_data .= '<Products>
                        <Product>
                            <ProductIdent>';
        if ($oArt->$sOaAbId->value == "" || $oArt->$sOaAbId->value == 0) {
            $xml_data .= '<ProductInsert>1</ProductInsert>
                                <BaseProductType>0</BaseProductType>
                                <UserProductID><![CDATA[' . $oArt->oxarticles__oxid->value . ']]></UserProductID>
                                <Anr>' . $oArt->oxarticles__oxean->value . '</Anr>
                                <EAN>MI-' . $oArt->oxarticles__oxid->value . '</EAN>';
        } else {
            $xml_data .= '<ProductID>' . $oArt->$sOaAbId->value . '</ProductID>';
        }
        $xml_data .= '      </ProductIdent>
                            <UserProductID>' . $oArt->oxarticles__oxid->value . '</UserProductID>
                            <Anr>' . $oArt->oxarticles__oxean->value . '</Anr>
                            <EAN>MI-' . $oArt->oxarticles__oxid->value . '</EAN>
                            <Name><![CDATA[' . $sTitle . ']]></Name>
                            <ShortDescription><![CDATA[' . $oArt->oxarticles__oxshortdesc->value . ']]></ShortDescription>
                            <Description><![CDATA[' . $oArt->getLongDesc() . ']]></Description>
                            <Quantity>' . $oArt->oxarticles__oxstock->value . '</Quantity>
                            <Stock>1</Stock>
                            <Discontinued>1</Discontinued>
                            <MergeStock>1</MergeStock>
                            <SellingPrice>' . $oArt->oxarticles__oxprice->value . '</SellingPrice>
                            <FreeValue2><![CDATA[Neu und unbenutzt, OVP kann geöffnet sein. Mit allem original Zubehör.]]></FreeValue2>
                            <FreeValue5><![CDATA[' . $oArt->oxarticles__oxartnum->value . ']]></FreeValue5>
                            <ImageSmallURL>'.$oArt->getThumbnailUrl().'</ImageSmallURL>
                            <ImageLargeURL>'.$oArt->getPictureUrl().'</ImageLargeURL>

        ';
        if ($oArt->oxarticles__oxean->value != "") {
            $xml_data .= '<ManufacturerStandardProductIDType><![CDATA[EAN]]></ManufacturerStandardProductIDType>
                            <ManufacturerStandardProductIDValue><![CDATA[' . $oArt->oxarticles__oxean->value . ']]></ManufacturerStandardProductIDValue>';
        }
        $xml_data .= '<ProductPictures>
                                <ProductPicture>
                                    <Nr>1</Nr>
                                    <Url>https://www.smarterphonestore.com/out/pictures/master/product/1/' . $oArt->oxarticles__oxpic1->value . '</Url>
                                    <AltText><![CDATA[' . $sTitle . ']]></AltText>
                                </ProductPicture>
                            </ProductPictures>
                        </Product>
                    </Products>';
        $xml_data .= $this->fcGetXmlFoot();
        return $xml_data;
    }

    protected function fcGetXmlHead($sCallName, $iDetailLevel = 0) {
        $sPartnerId = $this->sFcAfterbuyPartnerId;
        $sPassword = $this->sFcAfterbuyPartnerPassword;
        $sUserName = $this->sFcAfterbuyUsername;
        $sUserPassword = $this->sFcAfterbuyUserPassword;
        $sXml = '<?xml version="1.0" encoding="utf-8"?>
                <Request>
                    <AfterbuyGlobal>
                        <PartnerID>' . $sPartnerId . '</PartnerID>
                        <PartnerPassword><![CDATA[' . $sPassword . ']]></PartnerPassword>
                        <UserID><![CDATA[' . $sUserName . ']]></UserID>
                        <UserPassword><![CDATA[' . $sUserPassword . ']]></UserPassword>
                        <CallName>' . $sCallName . '</CallName>
                        <DetailLevel>' . $iDetailLevel . '</DetailLevel>
                        <ErrorLanguage>DE</ErrorLanguage>
                    </AfterbuyGlobal>';
        return $sXml;
    }

    protected function fcGetXmlFoot() {
        $sXml = '</Request>';
        return $sXml;
    }

}
