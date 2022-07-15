<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Config;

use CyberSource\VisaCheckout\Gateway\Config\CanVoidHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleNotOrderPayment()
    {
        $paymentDO = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $subject = [
            'payment' => $paymentDO
        ];

        $paymentMock = $this->getMockBuilder(InfoInterface::class);

        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $voidHandler = new CanVoidHandler();

        static::assertFalse($voidHandler->handle($subject));
    }

    public function testHandleSomeAmountWasPaid()
    {
        $paymentDO = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subject = [
            'payment' => $paymentDO
        ];

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects(static::once())
            ->method('getAmountPaid')
            ->willReturn(1.00);

        $voidHandler = new CanVoidHandler();

        static::assertFalse($voidHandler->handle($subject));
    }
}
