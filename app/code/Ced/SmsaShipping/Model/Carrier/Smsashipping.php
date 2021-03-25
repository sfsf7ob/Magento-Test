<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_SmsaShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\SmsaShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Ups\Helper\Config;
use Magento\Framework\Xml\Security;

class Smsashipping extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    protected $_code = 'smsashipping';

    protected $_rateResultFactory;

    protected $_quote;

    protected $_request;
    protected $_result;
    protected $_baseCurrencyRate;
    protected $_xmlAccessRequest;
    protected $_localeFormat;
    protected $_logger;
    protected $configHelper;
    protected $_errors = [];
    protected $_isFixed = true;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Config $configHelper,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        array $data = []
    ) {
        $this->_localeFormat = $localeFormat;
        $this->configHelper = $configHelper;
        $this->_scopeConfig =  $scopeConfig;
        $this->_scopeConfig = $scopeConfig;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        if(!$this->_scopeConfig->getValue('carriers/smsashipping/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return false;
        }
        $weight = 0;
        $rate = 0;
        $price = 0;
        $additional_price = 0;
        $result = $this->_rateResultFactory->create();
        $price = $this->_scopeConfig->getValue('carriers/smsashipping/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $additional_price = $this->_scopeConfig->getValue('carriers/smsashipping/price_additional', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $weight = $request->getPackageWeight();
        if($request->getFreeShipping()){
            $rate = 0;
        }else{
            if($weight <= 15)
            {  
                $rate = $price;
            }
            else
            {
              $remaining_weight = $weight-15;
              $rate += $price;

              while($remaining_weight > 0)
              {
                $rate += $additional_price;
                $remaining_weight = $remaining_weight-1;
              }

            }
        }
        $title = $this->_scopeConfig->getValue('carriers/smsashipping/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name = $this->_scopeConfig->getValue('carriers/smsashipping/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $error_msg = $this->_scopeConfig->getValue('carriers/advflatrate/specificerrmsg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($title);
        $method->setMethod($this->_code);
        $method->setMethodTitle($name);
        $method->setCost($rate);
        $method->setPrice($rate);
        $result->append($method);
        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code=> $this->getConfigData('name')];
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
      
    }

    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getXmlTrackingInfo($trackings);
        return $this->_result;
    }

    public function _getXmlTrackingInfo($trackings){
        $result = $this->_trackFactory->create();
        $title = $this->_scopeConfig->getValue('carriers/smsashipping/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $track_url = $this->_scopeConfig->getValue('carriers/smsashipping/track_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($title);
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("{$track_url}={$tracking}");
            $result->append($status);
        }
        $this->_result = $result;
        return $result;
    }

    /*protected function _getXmlTrackingInfo($trackings)
    {
        foreach ($trackings as $tracking) {
            $this->_parseXmlTrackingResponse($tracking);
        }
    }*/

    protected function _parseXmlTrackingResponse($trackingvalue)
    {

        $pass_key = 'Testing0';
        $results = $this->_trackFactory->create();
        $defaults = $this->getDefaults();
        //var_dump($result);die;
        $arguments = array('passkey' => $pass_key);
        $arguments['awbNo'] = $trackingvalue/*$track['number']*/;  
        $client = new \SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl',array('exceptions' => false));
        $result = $client->getTracking($arguments);
        $xml = json_decode(json_encode($result),True);
         /*if($result->getTrackingResult){
            //print_r(json_decode(json_encode($result->getTrackingResult),True)['any']);
        }*/
     
        $title = $this->_scopeConfig->getValue('carriers/smsashipping/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$resultTable = $objectManager->get('Magento\Framework\Escaper')->escapeHtml($resultTable);
        if (!empty($xml)) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier($this->_code);
            $tracking->setCarrierTitle($title);
            $tracking->setTracking(__($trackingvalue));
            if (!empty($xml)) {//print_r($this->getTrackingInfoTable($result));die;
                $value = str_replace(array("&lt;", "&gt;"), array("<", ">"), htmlspecialchars($this->getTrackingInfoTable($result), ENT_COMPAT, "UTF-8", false));
                $tracking->setTrackSummary($value);
            } else {
                $tracking->setTrackSummary(
                    'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.'
                );
            }
            $results->append($tracking);
        } else {
            $errorTitle = '';
            foreach ($response->Notifications as $notification) {
                $errorTitle .= '<b>' . $notification->Code . '</b>' . $notification->Message;
            }
            $error = $this->_trackErrorFactory->create();
            $error->setCarrier('smsa');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $result->append($error);
        }
        $this->_result = $results;
    }

    public function getTrackingInfoTable($result)
    {
        $xml = json_decode(json_encode($result),True);
        $xml = json_decode(json_encode($result->getTrackingResult),True)['any'];
        $xml = simplexml_load_string($xml);
        $xml = json_decode(json_encode((array)$xml), TRUE);
        $tracks = $xml['NewDataSet']['Tracking'];
        $resultTable = "<table summary='Item Tracking'  class='data-table order tracking'>";
        $resultTable .= '<col width="1">
                        <col width="1">
                        <col width="1">
                        <col width="1">
                        <thead>
                        <tr class="first last">
                        <th>Date/Time</th>
                        <th>Activity</th>
                        <th class="a-right">Details</th>
                        <th class="a-center">Location</th>
                        </tr>
                        </thead>
                        <tbody>';
        $resultTable .= '<tr><td>' . $tracks["Date"] . '</td><td>' . $tracks["Activity"] . '</td><td>' . $tracks["Details"] . '</td><td>' . $tracks["Location"] . '</td></tr>';

        /*foreach ($tracks as $track) {
            $resultTable .= '<tr>
                <td>' . $HAWBUpdate->UpdateLocation . '</td>
                <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                <td>' . $HAWBUpdate->UpdateDescription . '</td>
                <td>' . $HAWBUpdate->Comments . '</td>
                </tr>';
        }*/
        $resultTable .= '</tbody></table>';
        //$resultTable .= "</table>";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$resultTable = $objectManager->get('Magento\Framework\Escaper')->escapeHtml($resultTable);
//print_r($resultTable);die;
        //$resultTable = '<table></table>';
        //$resultTable = html_entity_decode($resultTable);
        return $resultTable;
    }

    public function processAdditionalValidation(\Magento\Framework\DataObject $request) {
        return $this;
    }
}


