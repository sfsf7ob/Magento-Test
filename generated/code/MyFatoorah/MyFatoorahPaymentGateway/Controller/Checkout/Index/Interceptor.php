<?php
namespace MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout\Index;

/**
 * Interceptor class for @see \MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout\Index
 */
class Interceptor extends \MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\MyFatoorah\MyFatoorahPaymentGateway\Gateway\Config\Config $gatewayConfig, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\App\Action\Context $context, \Magento\Sales\Model\OrderFactory $orderFactory, \MyFatoorah\MyFatoorahPaymentGateway\Helper\Crypto $cryptoHelper, \MyFatoorah\MyFatoorahPaymentGateway\Helper\Data $dataHelper, \MyFatoorah\MyFatoorahPaymentGateway\Helper\Checkout $checkoutHelper, \MyFatoorah\MyFatoorahPaymentGateway\Model\ResourceModel\MyfatoorahInvoice\CollectionFactory $mfInvoiceFactory, \Psr\Log\LoggerInterface $logger)
    {
        $this->___init();
        parent::__construct($gatewayConfig, $checkoutSession, $context, $orderFactory, $cryptoHelper, $dataHelper, $checkoutHelper, $mfInvoiceFactory, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getInvoiceItems($order, $currencyRate, $isShipping, &$amount)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getInvoiceItems');
        return $pluginInfo ? $this->___callPlugins('getInvoiceItems', func_get_args(), $pluginInfo) : parent::getInvoiceItems($order, $currencyRate, $isShipping, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyData($gateway)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCurrencyData');
        return $pluginInfo ? $this->___callPlugins('getCurrencyData', func_get_args(), $pluginInfo) : parent::getCurrencyData($gateway);
    }

    /**
     * {@inheritdoc}
     */
    public function sendPayment($curlData)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'sendPayment');
        return $pluginInfo ? $this->___callPlugins('sendPayment', func_get_args(), $pluginInfo) : parent::sendPayment($curlData);
    }

    /**
     * {@inheritdoc}
     */
    public function executePayment($curlData, $gateway)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'executePayment');
        return $pluginInfo ? $this->___callPlugins('executePayment', func_get_args(), $pluginInfo) : parent::executePayment($curlData, $gateway);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodId($curlData, $gateway)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getPaymentMethodId');
        return $pluginInfo ? $this->___callPlugins('getPaymentMethodId', func_get_args(), $pluginInfo) : parent::getPaymentMethodId($curlData, $gateway);
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone($inputString)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getPhone');
        return $pluginInfo ? $this->___callPlugins('getPhone', func_get_args(), $pluginInfo) : parent::getPhone($inputString);
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightRate($unit)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getWeightRate');
        return $pluginInfo ? $this->___callPlugins('getWeightRate', func_get_args(), $pluginInfo) : parent::getWeightRate($unit);
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensionRate($unit)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getDimensionRate');
        return $pluginInfo ? $this->___callPlugins('getDimensionRate', func_get_args(), $pluginInfo) : parent::getDimensionRate($unit);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionFlag()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getActionFlag');
        return $pluginInfo ? $this->___callPlugins('getActionFlag', func_get_args(), $pluginInfo) : parent::getActionFlag();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getRequest');
        return $pluginInfo ? $this->___callPlugins('getRequest', func_get_args(), $pluginInfo) : parent::getRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getResponse');
        return $pluginInfo ? $this->___callPlugins('getResponse', func_get_args(), $pluginInfo) : parent::getResponse();
    }
}
