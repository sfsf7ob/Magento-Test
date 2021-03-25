<?php

namespace MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout;

use Magento\Sales\Model\Order;

/**
 * @package MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout
 */
class Index extends AbstractAction {

    public $orderId = null;

//---------------------------------------------------------------------------------------------------------------------------------------------------
    /* private function shippingAndCurrencyRate($order) {

      if ($order == null) {////???????????????????not working whaT IS THAT USED FOR??
      $this->getLogger()->addError('Unable to get order from last lodged order id. Possibly related to a failed database call');
      $this->_redirect('checkout/onepage/error', array('_secure' => false));
      }

      $shippingAddress      = $order->getShippingAddress();
      $shippingAddressParts = preg_split('/\r\n|\r|\n/', $shippingAddress->getData('street'));

      $magento_version = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
      $plugin_version  = $this->getGatewayConfig()->getVersion();

      $data = array(
      // 'x_url_cancel' => $this->getDataHelper()->getCancelledUrl($orderId),
      'x_shop_name'  => $this->getDataHelper()->getStoreCode(),
      //            'x_customer_shipping_address1' => $shippingAddressParts[0],
      //            'x_customer_shipping_address2' => count($shippingAddressParts) > 1 ? $shippingAddressParts[1] : '',
      //            'x_customer_shipping_city' => $shippingAddress->getData('city'),
      //            'x_customer_shipping_state' => $shippingAddress->getData('region'),
      //            'x_customer_shipping_zip' => $shippingAddress->getData('postcode'),
      'version_info' => 'MyFatoorah_' . $plugin_version . '_on_magento' . substr($magento_version, 0, 3),
      //            'x_test'                       => 'false',
      );

      $currencyRate = (double) $this->objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore()->getCurrentCurrencyRate();
      $items        = $order->getAllVisibleItems();
      foreach ($items as $item) {
      $product_name = $item->getName();
      // $itemPrice = $item->getPrice() * $currencyRate;
      // print_r($item->getPriceInclTax() ); die;
      $itemPrice    = $item->getPriceInclTax() * $currencyRate;
      $qty          = $item->getQtyOrdered();

      $invoiceItemsArr[] = array('ItemName' => $product_name, 'Quantity' => intval($qty), 'UnitPrice' => $itemPrice);
      }

      $shipping = $order->getShippingAmount() + $order->getShippingTaxAmount();
      if ($shipping != '0') {
      $invoiceItemsArr[] = array('ItemName' => 'Shipping Amount', 'Quantity' => 1, 'UnitPrice' => $shipping);
      //            $amount = $amount + $shipping;
      }
      $discount = $order->getDiscountAmount();
      if ($discount != '0') {
      $invoiceItemsArr[] = array('ItemName' => 'Discount Amount', 'Quantity' => 1, 'UnitPrice' => $discount);
      //            $amount = $amount + $discount;
      }

      // print_r($data); die;
      foreach ($data as $key => $value) {
      $data[$key] = preg_replace('/\r\n|\r|\n/', ' ', $value);
      }
      } */

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @return void
     */
    public function execute() {

        try {
            $order = $this->getOrder();

            $this->order   = $order;
            $this->orderId = $order->getRealOrderId();

            if ($order->getState() === Order::STATE_CANCELED) {
                $errorMessage = $this->getCheckoutSession()->getMyFatoorahErrorMessage(); //set in InitializationRequest
                if ($errorMessage) {
                    $this->getMessageManager()->addWarningMessage($errorMessage);
                    $errorMessage = $this->getCheckoutSession()->unsMyFatoorahErrorMessage();
                }
                $this->getLogger()->addNotice('Order in state: ' . $order->getState());
                $this->getCheckoutHelper()->restoreQuote(); //restore cart

                $this->_redirect('checkout/cart');
            } else {
                if ($order->getState() !== Order::STATE_PENDING_PAYMENT) {
                    $this->getLogger()->addNotice('Order in state: ' . $order->getState());
                }
                $this->postToCheckout($order);
            }
        } catch (Exception $ex) {
            $this->getLogger()->addError('An exception was encountered in myfatoorah/checkout/index: ' . $ex->getMessage());
            $this->getLogger()->addError($ex->getTraceAsString());
            $this->getMessageManager()->addErrorMessage(__('Unable to start myfatoorah Checkout.'));
        } catch (\Exception $ex) {
//            $this->getCheckoutHelper()->restoreQuote(); //restore cart
//            $this->getMessageManager()->addErrorMessage($ex->getMessage());
//            $this->_redirect('checkout/cart');

            $err = $ex->getMessage();

            $url = $this->getDataHelper()->getCancelledUrl($this->orderId, urlencode($err));

            $this->_redirect($url);
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /** @var \Magento\Sales\Model\Order $order */
    private function getPayload($order, $gateway) {

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $billingAddress  = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        if (is_object($shippingAddress)) {
            $addressData = $shippingAddress->getData();
        } else if (is_object($billingAddress)) {
            $addressData = $billingAddress->getData();
        } else {
            throw new \Exception('Billing Address or Shipping address Data Should be set to create the invoice');
        }

        $city     = isset($addressData['city']) ? $addressData['city'] : '';
        $postcode = isset($addressData['postcode']) ? $addressData['postcode'] : '';
        $region   = isset($addressData['region']) ? $addressData['region'] : '';
        $street   = isset($addressData['street']) ? $addressData['street'] : '';
        $phoneNo  = isset($addressData['telephone']) ? $addressData['telephone'] : '';
        $fName    = !empty($billingAddress->getFirstname()) ? $billingAddress->getFirstname() : $shippingAddress->getFirstname();
        $lName    = !empty($billingAddress->getLastname()) ? $billingAddress->getLastname() : $shippingAddress->getLastname();
        $email    = $order->getData('customer_email');


        $getLocale = $this->objectManager->get('Magento\Framework\Locale\Resolver');
        $haystack  = $getLocale->getLocale();
        $lang      = strstr($haystack, '_', true);


        $phone = $this->getPhone($phoneNo);
        $url   = $this->getDataHelper()->getCompleteUrl();



        $shippingMethod = $order->getShippingMethod();
        $isShipping     = ($shippingMethod == 'myfatoorah_shippingDHL_myfatoorah_shippingDHL') ? 1 : (($shippingMethod == 'myfatoorah_shippingAramex_myfatoorah_shippingAramex') ? 2 : null);

        $shippingConsignee = !$isShipping ? '' : array(
            'PersonName'   => "$fName $lName",
            'Mobile'       => $phoneNo,
            'EmailAddress' => $email,
            'LineAddress'  => trim(preg_replace("/[\n]/", ' ', $addressData['street'] . ' ' . $region)),
            'CityName'     => $city,
            'PostalCode'   => $postcode,
            'CountryCode'  => $addressData['country_id']
        );

        $currency = $this->getCurrencyData($gateway);
        //$invoiceItemsArr
//        $invoiceValue    = round($order->getBaseTotalDue() * $currency['rate'], 2);
//        $invoiceItemsArr = [[array('ItemName' => "Total Amount Order #$this->orderId", 'Quantity' => 1, 'UnitPrice' => $invoiceValue)]];


        $invoiceValue    = 0;
        $invoiceItemsArr = $this->getInvoiceItems($order, $currency['rate'], $isShipping, $invoiceValue);


        return [
            'CustomerName'       => $fName . ' ' . $lName,
            'DisplayCurrencyIso' => $currency['code'], //$order->getOrderCurrencyCode(),
            'MobileCountryCode'  => trim($phone[0]),
            'CustomerMobile'     => trim($phone[1]),
            'CustomerEmail'      => $email,
            'InvoiceValue'       => $invoiceValue,
            'CallBackUrl'        => $url,
            'ErrorUrl'           => $url,
            'Language'           => $lang,
            'CustomerReference'  => $this->orderId,
            'CustomerCivilId'    => $this->orderId,
            'UserDefinedField'   => $this->orderId,
            'ExpiryDate'         => '',
            'SourceInfo'         => 'Magento 2 - API Ver 2.0',
            'CustomerAddress'    => [
                'Block'               => '',
                'Street'              => '',
                'HouseBuildingNo'     => '',
                'Address'             => $city . ', ' . $region . ', ' . $postcode,
                'AddressInstructions' => $street
            ],
            'ShippingConsignee'  => $shippingConsignee,
            'ShippingMethod'     => $isShipping,
            'InvoiceItems'       => $invoiceItemsArr
        ];
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /** @var \Magento\Sales\Model\Order $order */
    function getInvoiceItems($order, $currencyRate, $isShipping, &$amount) {

        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $ScopeConfigInterface */
        $ScopeConfigInterface = $this->getObjectManager()->create('\Magento\Framework\App\Config\ScopeConfigInterface');

        $weightUnit = $ScopeConfigInterface->getValue('general/locale/weight_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $weightRate = ($isShipping) ? $this->getWeightRate($weightUnit) : 1;

        /** @var \Magento\Sales\Api\Data\OrderItemInterface[]  $items */
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $itemPrice = round($item->getBasePriceInclTax() * $currencyRate, 2);
            $qty       = intval($item->getQtyOrdered());

            $invoiceItemsArr[] = [
                'ItemName'  => $item->getName(),
                'Quantity'  => $qty,
                'UnitPrice' => $itemPrice,
                'weight'    => $item->getWeight() * $weightRate,
                'Width'     => $item->getProduct()->getData('width'),
                'Height'    => $item->getProduct()->getData('height'),
                'Depth'     => $item->getProduct()->getData('depth'),
            ];
            $amount            += $itemPrice * $qty;
        }


        $shipping = $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();
        if ($shipping != '0' && !$isShipping) {
            $itemPrice         = round($shipping * $currencyRate, 2);
            $invoiceItemsArr[] = ['ItemName' => 'Shipping Amount', 'Quantity' => '1', 'UnitPrice' => $itemPrice, 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];

            $amount += $itemPrice;
        }


        $discount = $order->getBaseDiscountAmount();
        if ($discount != '0') {
            $itemPrice         = round($discount * $currencyRate, 2);
            $invoiceItemsArr[] = ['ItemName' => 'Discount Amount', 'Quantity' => '1', 'UnitPrice' => $itemPrice, 'Weight' => '0', 'Width' => '0', 'Height' => '0', 'Depth' => '0'];

            $amount += $itemPrice;
        }

        //Mageworx
//        $fees = $order->getBaseMageworxFeeAmount();
//        if ($fees != '0') {
//            $itemPrice         = round($fees * $currencyRate, 2);
//            $invoiceItemsArr[] = array('ItemName' => 'Additional Fees', 'Quantity' => 1, 'UnitPrice' => $itemPrice);
//            $amount      += $itemPrice;
//        }
//
//        $fees = $order->getBaseMageworxProductFeeAmount();
//        if ($fees != '0') {
//            $itemPrice         = round($fees * $currencyRate, 2);
//            $invoiceItemsArr[] = array('ItemName' => 'Additional Product Fees', 'Quantity' => 1, 'UnitPrice' => $itemPrice);
//            $amount      += $itemPrice;
//        }
//
        /*
          $this->log->info(print_r('FeeAmount' . $order->getBaseMageworxFeeAmount(),1));
          $this->log->info(print_r('FeeInvoiced' . $order->getBaseMageworxFeeInvoiced(),1));
          $this->log->info(print_r('FeeCancelled' . $order->getBaseMageworxFeeCancelled(),1));
          $this->log->info(print_r('FeeTaxAmount' . $order->getBaseMageworxFeeTaxAmount(),1));
          $this->log->info(print_r('FeeDetails' . $order->getMageworxFeeDetails(),1));
          $this->log->info(print_r('FeeRefunded' . $order->getMageworxFeeRefunded(),1));

          $this->log->info(print_r('ProductFeeAmount' . $order->getBaseMageworxProductFeeAmount(),1));
          $this->log->info(print_r('ProductFeeInvoiced' . $order->getBaseMageworxProductFeeInvoiced(),1));
          $this->log->info(print_r('ProductFeeCancelled' . $order->getBaseMageworxProductFeeCancelled(),1));
          $this->log->info(print_r('ProductFeeTaxAmount' . $order->getBaseMageworxProductFeeTaxAmount(),1));
          $this->log->info(print_r('ProductFeeDetails' . $order->getMageworxProductFeeDetails(),1));
          $this->log->info(print_r('ProductFeeRefunded' . $order->getMageworxProductFeeRefunded(),1));
         */
        return $invoiceItemsArr;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    function getCurrencyData($gateway) {
        /** @var \Magento\Store\Model\StoreManagerInterface  $StoreManagerInterface */
        $store = $this->objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore();


        $KWDcurrencyRate = (double) $store->getBaseCurrency()->getRate('KWD');
        if ($gateway == 'kn' && !empty($KWDcurrencyRate)) {
            $currencyCode = 'KWD';
            $currencyRate = $KWDcurrencyRate;
        } else {
            $currencyCode = $store->getBaseCurrencyCode();
            $currencyRate = 1;
            //(double) $this->objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore()->getCurrentCurrencyRate();
        }
        return ['code' => $currencyCode, 'rate' => $currencyRate];
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------

    /** @var \Magento\Sales\Model\Order $order */
    private function postToCheckout($order) {

        $gateway = $this->getRequest()->get("gateway") ? $this->getRequest()->get("gateway") : 'myfatoorah';

        $this->log->info('-------------------------------------------------------------------------------------------------------------');
        $this->log->info("Order #$this->orderId ----- Gateway - Type: $gateway");


        $payload = $this->getPayload($order, $gateway);


        if ($gateway == 'myfatoorah' || $gateway == null || $gateway == 'undefined') {
            $gateway = 'myfatoorah';
            $data    = $this->sendPayment($payload);
        } else {
            $data = $this->executePayment($payload, $gateway);
        }

        //save the invoice id in myfatoorah_invoice table 
        $mf = $this->objectManager->create('MyFatoorah\MyFatoorahPaymentGateway\Model\MyfatoorahInvoice');
        $mf->addData([
            'order_id'    => $this->orderId,
            'invoice_id'  => $data['InvoiceId'],
            'gateway_id'  => $gateway,
            'invoice_url' => $data['url'],
        ]);
        $mf->save();

        //save the invoice id in sales_order table 
//        $this->order->setMyfatoorahInvoiceId($data['InvoiceId']);
//        $this->order->save();

        $this->_redirect($data['url']);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function sendPayment($curlData) {

        $curlData['NotificationOption'] = 'Lnk';

        $json = $this->callAPI("$this->gatewayUrl/v2/SendPayment", $curlData, $this->orderId, 'Send Payment');
        return array('url' => $json->Data->InvoiceURL, 'InvoiceId' => $json->Data->InvoiceId);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function executePayment($curlData, $gateway) {

        $curlData['PaymentMethodId'] = $this->getPaymentMethodId($curlData, $gateway);

        $json = $this->callAPI("$this->gatewayUrl/v2/ExecutePayment", $curlData, $this->orderId, 'Execute Payment');
        return array('url' => $json->Data->PaymentURL, 'InvoiceId' => $json->Data->InvoiceId);
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function getPaymentMethodId($curlData, $gateway) {
        $postFields = [
            'InvoiceAmount' => $curlData['InvoiceValue'],
            'CurrencyIso'   => $curlData['DisplayCurrencyIso'],
        ];

        // initiate payment
        $json = $this->callAPI("$this->gatewayUrl/v2/InitiatePayment", $postFields, $this->orderId, 'Initiate Payment');

        // execute payment
        ///??????????? why PaymentMethodCode vs PaymentMethodEn
        //    ???? null    The PaymentMethodId field is required.
        //check for null ??????????????
        $PaymentMethodId = null;
        foreach ($json->Data->PaymentMethods as $value) {
            if ($value->PaymentMethodCode == $gateway) {
                $PaymentMethodId = $value->PaymentMethodId;
                break;
            }
        }

        return $PaymentMethodId;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    /*
     * Matching regular expression pattern: ^(?:(\+)|(00)|(\\*)|())[0-9]{3,14}((\\#)|())$
     * if (!preg_match('/^(?:(\+)|(00)|(\\*)|())[0-9]{3,14}((\\#)|())$/iD', $inputString))
     * String length: inclusive between 0 and 11
     */

    public function getPhone($inputString) {

        //remove any arabic digit
        $newNumbers = range(0, 9);

        $persianDecimal = array('&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;'); // 1. Persian HTML decimal
        $arabicDecimal  = array('&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;'); // 2. Arabic HTML decimal
        $arabic         = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'); // 3. Arabic Numeric
        $persian        = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'); // 4. Persian Numeric

        $string0 = str_replace($persianDecimal, $newNumbers, $inputString);
        $string1 = str_replace($arabicDecimal, $newNumbers, $string0);
        $string2 = str_replace($arabic, $newNumbers, $string1);
        $string3 = str_replace($persian, $newNumbers, $string2);

        //Keep Only digits
        $string4 = preg_replace('/[^0-9]/', '', $string3);

        //remove 00 at start
        if (strpos($string4, '00') === 0) {
            $string4 = substr($string4, 2);
        }

        //$this->log->info($string4);
        //check for the allowed length
        $len = strlen($string4);
        if ($len < 3 || $len > 14) {
            throw new \Exception('Phone Number lenght must be between 3 to 14 digits');
        }

        //get the phone arr
        if (strlen(substr($string4, 3)) > 3) {
            return [
                substr($string4, 0, 3),
                substr($string4, 3)
            ];
        } else {
            return [
                '',
                $string4
            ];
        }
        ///end here with return $arr
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given weight unit to MyFatoorah default weight unit.
     * 
     * @param string        $unit It is the weight unit used. Weight must be in kg, g, lbs, or oz. Default is kg.
     * @return real         The conversion rate that will convert the given unit into the kg. 
     * @throws Exception    Throw exception if the input unit is not support. Weight must be in kg, g, lbs, or oz. Default is kg.
     */
    public function getWeightRate($unit) {

        $unit1 = strtolower($unit);
        if ($unit1 == 'kg') {
            $rate = 1; //kg is the default
        } else if ($unit1 == 'g') {
            $rate = 0.001;
        } else if ($unit1 == 'lbs') {
            $rate = 0.453592;
        } else if ($unit1 == 'oz') {
            $rate = 0.0283495;
        } else {
            throw new \Exception('Weight must be in kg, g, lbs, or oz. Default is kg');
        }

        return $rate;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given dimension unit to MyFatoorah default dimension unit.
     * 
     * @param string        $unit It is the dimension unit used in width, hight, or depth. Dimension must be in cm, m, mm, in, or yd. Default is cm.
     * @return real         The conversion rate that will convert the given unit into the cm.
     * @throws Exception    Throw exception if the input unit is not support. Dimension must be in cm, m, mm, in, or yd. Default is cm.
     */
    public function getDimensionRate($unit) {

        $unit1 = strtolower($unit);
        if ($unit1 == 'cm') {
            $rate = 1; //cm is the default
        } elseif ($unit1 == 'm') {
            $rate = 100;
        } else if ($unit1 == 'mm') {
            $rate = 0.1;
        } else if ($unit1 == 'in') {
            $rate = 2.54;
        } else if ($unit1 == 'yd') {
            $rate = 91.44;
        } else {
            throw new \Exception('Dimension must be in cm, m, mm, in, or yd. Default is cm');
        }

        return $rate;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
