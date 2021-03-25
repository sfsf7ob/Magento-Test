<?php

namespace Mageplaza\Simpleshipping\Block\Adminhtml;

/**
 * Adminhtml shipment create
 *
 */
class View extends \Magento\Shipping\Block\Adminhtml\View
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $shipid = $this->_coreRegistry->registry('current_shipment')->getIncrementId();
        $oid = $this->_coreRegistry->registry('current_shipment')->getEntityId();
        $shipping_method = $this->_coreRegistry->registry('current_shipment')->getOrder()->getShippingMethod();
        $url = $this->getUrl("simpleshipping/order/view",array('shipment_ids'=>$oid));

        if ($this->getShipment()->getId() ) {
            $this->buttonList->add(
                'simpleshipping_label',
                [
                    'label' => __('Generate Shipping Label'),
                    'class' => 'savgfge',
                    'target'  =>  '_blank',
                    'onclick' => 'window.open(\'' . $url . '\')'
                ]
            );
        }
    }


}
