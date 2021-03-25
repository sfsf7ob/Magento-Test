<?php
namespace MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout\Cancel;

/**
 * Interceptor class for @see \MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout\Cancel
 */
class Interceptor extends \MyFatoorah\MyFatoorahPaymentGateway\Controller\Checkout\Cancel implements \Magento\Framework\Interception\InterceptorInterface
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
