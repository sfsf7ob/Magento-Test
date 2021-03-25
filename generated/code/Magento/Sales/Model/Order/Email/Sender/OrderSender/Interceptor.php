<?php
namespace Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Interceptor class for @see \Magento\Sales\Model\Order\Email\Sender\OrderSender
 */
class Interceptor extends \Magento\Sales\Model\Order\Email\Sender\OrderSender implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Sales\Model\Order\Email\Container\Template $templateContainer, \Magento\Sales\Model\Order\Email\Container\OrderIdentity $identityContainer, \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory, \Psr\Log\LoggerInterface $logger, \Magento\Sales\Model\Order\Address\Renderer $addressRenderer, \Magento\Payment\Helper\Data $paymentHelper, \Magento\Sales\Model\ResourceModel\Order $orderResource, \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig, \Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->___init();
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $orderResource, $globalConfig, $eventManager);
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Magento\Sales\Model\Order $order, $forceSyncMode = false)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'send');
        return $pluginInfo ? $this->___callPlugins('send', func_get_args(), $pluginInfo) : parent::send($order, $forceSyncMode);
    }
}
