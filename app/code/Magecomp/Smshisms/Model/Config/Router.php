<?php
namespace Magecomp\Smshisms\Model\Config;
class Router implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Promotional')],
            ['value' => 4, 'label' => __('Transactional ')],
        ];
    }
}