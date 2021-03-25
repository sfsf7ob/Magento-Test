<?php
namespace Mageplaza\Simpleshipping\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentAfter implements ObserverInterface
{
     protected $_registry = null;
     protected $_storeManager;
     protected $_moduleReader;

   public function __construct (
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->_objectManager=$objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_moduleReader = $moduleReader;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
            $invoice = $observer->getEvent()->getInvoice();
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $user = $this->_objectManager->get('\Magento\Backend\Model\Auth\Session');
            $userFirstname = $user->getUser()->getFirstname();
            $userLastname = $user->getUser()->getLastname();
            $store_contact = $this->_scopeConfig->getValue('general/store_information/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_country = $this->_scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_region = $this->_scopeConfig->getValue('shipping/origin/region_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_city = $this->_scopeConfig->getValue('shipping/origin/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_street1 = $this->_scopeConfig->getValue('shipping/origin/street_line1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_street2 = $this->_scopeConfig->getValue('shipping/origin/street_line2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $customer_billing_address = $order->getShippingAddress();
            $dest_add  = $customer_billing_address->getStreet();
            $totalItems     = 0;
            $description = "";
            $totalWeight = 0;
            $totalPrice =0;
            $items = $order->getAllItems();
            foreach($items as $item){
                $qty = $item->getQtyOrdered();
                if($item->getWeight() != 0){
                    $weight =  $item->getWeight()* $qty;
                } else {
                    $weight =  0.5*$qty;
                }
                $totalWeight    += $weight;
                $totalItems     += $qty;
                $totalPrice  += $item->getBaseRowTotal();
                $description .= $item->getProduct()->getName()." | ";

            }
            $description = substr($description, 0, 48);
            $shippingMethod = $order->getShippingMethod();
            $s = explode("~",$shippingMethod);
            $shippingMethod=$s[0];
            if($shippingMethod=="simpleshipping_simpleshipping"){

                $pass_key = "https://www.speedlineship.com/partner/api/4fbdb49c001fbd589738135f90719a3efc12640cba11e4f38c8d14703d5f90f0/new/";
                $user_name = $userFirstname;
                $shipment_description = $order->getIncrementId();
                $shipment_fullname	 = $customer_billing_address->getName();
                $shipment_country_code = "SA";
                $shipment_city = $customer_billing_address->getCity();
                $shipment_region=$customer_billing_address->getRegionId();
                $shipment_type="1";
                $shipment_total_packages="1";
                $shipment_mobile= $customer_billing_address->getTelephone();
                $shipment_neighborhood = isset($dest_add[0])?$dest_add[0]:'';
                $shipment_address1 = isset($dest_add[1])?$dest_add[1]:'';
try {
            $ch = curl_init();
                 curl_setopt($ch, CURLOPT_URL, "https://www.speedlineship.com/partner/api/4fbdb49c001fbd589738135f90719a3efc12640cba11e4f38c8d14703d5f90f0/new/");
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 curl_setopt($ch, CURLOPT_HEADER, 0);
                 curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                 curl_setopt($ch, CURLOPT_POST, 1);
                 curl_setopt($ch, CURLOPT_POSTFIELDS, "shipment_fullname=$shipment_fullname&shipment_mobile=$shipment_mobile&shipment_country_code=SA&shipment_region=$shipment_region&shipment_city=$shipment_city&shipment_type=1&shipment_total_packages=1&shipment_address1=$shipment_address1&pack_actual_weight_1=1");
                 if(curl_errno($ch)){
                         throw new Exception(curl_error($ch));
                     }

                $result = curl_exec($ch);

$obj = json_decode($result);
                $awbno = $obj->response_ship_number;
                $shipment = $observer->getEvent()->getShipment();
                $track = $this->_objectManager->create(
                'Magento\Sales\Model\Order\Shipment\Track'
                                            )->setNumber(
                                                $awbno
                                            )->setCarrierCode(
                                                'speedline'
                                            )->setTitle(
                                                'speed line'
                                            );
                $shipment->addTrack($track);}
                catch(\Exception $e)
                {
                    throw new \Exception(__('Something went wrong while saving shipment.'));
                }
       return $result;

    }}
}
