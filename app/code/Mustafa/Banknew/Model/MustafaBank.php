<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mustafa\Banknew\Model;



/**
 * Pay In Store payment method model
 */
class MustafaBank extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'mustafabank';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;




}
