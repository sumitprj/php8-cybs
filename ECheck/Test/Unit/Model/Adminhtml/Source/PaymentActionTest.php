<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\ECheck\Test\Unit\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;
use CyberSource\ECheck\Model\Adminhtml\Source\PaymentAction;

class PaymentActionTest extends \PHPUnit\Framework\TestCase
{
    public function testToOptionArray()
    {
        $sourceModel = new PaymentAction();

        static::assertEquals(
            [
                [
                    'value' => AbstractMethod::ACTION_AUTHORIZE,
                    'label' => __('Authorize Only')
                ]
            ],
            $sourceModel->toOptionArray()
        );
    }
}
