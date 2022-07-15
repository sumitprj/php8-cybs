<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */
namespace CyberSource\Core\Model\Source\Status;

/**
 * Order Statuses source model
 */
class Pending extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
    ];
}
