<?php

namespace MyFatoorah\MyFatoorahPaymentGateway\Observer;

class ClickOnBack implements \Magento\Framework\Event\ObserverInterface {

    private $checkoutSession;

    public function __construct(\Magento\Checkout\Model\Session\Proxy $checkoutSession) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $lastRealOrder = $this->checkoutSession->getLastRealOrder();
        if ($lastRealOrder->getPayment()) {

            if ($lastRealOrder->getData('state') === 'pending_payment' && $lastRealOrder->getData('status') === 'pending_payment') {
                $this->checkoutSession->restoreQuote();
            }
        }
        return true;
    }

}
