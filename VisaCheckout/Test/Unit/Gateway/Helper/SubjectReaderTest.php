<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Helper;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;

/**
 * Class SubjectReaderTest
 */
class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @covers \CyberSource\VisaCheckout\Gateway\Helper\SubjectReader::readPayment()
     */
    public function testReadPayment()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        static::assertEquals($paymentData, $this->subjectReader->readPayment($subject));
    }

    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
