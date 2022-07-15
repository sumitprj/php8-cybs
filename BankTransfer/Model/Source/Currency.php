<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\BankTransfer\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Currency
 * @package CyberSource\BankTransfer\Model\Source
 * @codeCoverageIgnore
 */
class Currency implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'USD',
                'label' => __('USD'),
            ],
            [
                'value' => 'EUR',
                'label' => __('EUR')
            ]
        ];
    }
}
