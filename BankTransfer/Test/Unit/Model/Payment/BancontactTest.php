<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\BankTransfer\Test\Unit\Model\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PlaceTest
 * @codingStandardsIgnoreStart
 */
class BancontactTest extends \PHPUnit\Framework\TestCase
{
    
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;
    
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;
    
    public function setUp()
    {
        $this->paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();   
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \CyberSource\BankTransfer\Model\Payment\Bancontact::class
        );
    }
    
    public function testCapture()
    {
        $this->assertEquals($this->model, $this->model->capture($this->paymentMock, 11.11));
    }
}