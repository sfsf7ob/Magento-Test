<?php

namespace Vendor\Module\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{

    protected function _initSelect()
    {
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('customer_id', 'main_table.customer_id');

        parent::_initSelect();
    }

    protected function _renderFiltersBefore()
    {
    $joinTable = $this->getTable('sales_order_address');
    $this->getSelect()->joinLeft($joinTable, 'main_table.entity_id = 
    sales_order_address.parent_id AND sales_order_address.address_type = "billing"', 
      ['telephone']);
       parent::_renderFiltersBefore();
   }

}
