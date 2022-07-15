<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Response;

use CyberSource\VisaCheckout\Gateway\Response\CaptureResponseHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Sales\Model\Order;

/**
 * Class CaptureResponseHandlerTest
 */
class CaptureResponseHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureResponseHandler
     */
    private $responseHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    private $payment;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'unsAdditionalInformation',
                'setTransactionId',
                'setAdditionalInformation',
                'getOrder'
            ])
            ->getMock();
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::exactly(3))
            ->method('setAdditionalInformation');

        $this->payment->expects(static::exactly(1))
            ->method('setTransactionId');

        $this->payment->expects(static::never())
            ->method('unsAdditionalInformation');

        $this->responseHandler = new CaptureResponseHandler(
            $this->subjectReader
        );

        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $response = $this->getCyberSourceTransaction();

        $subject = ['payment' => $paymentData];

        $this->subjectReader->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);
        
        $this->payment->method('getOrder')
            ->willReturn($this->order);

        $this->responseHandler->handle($subject, $response);
    }

    /**
     * Create mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    private function getCyberSourceTransaction()
    {
        $response = [
            "requestID" => "1234",
            "purchaseTotals" => (object) [
                "currency" => "USD"
            ],
            "ccAuthReply" => (object) [
                "amount" => "10.0",
                "reconciliationID" => "12341234"
            ],
            "requestToken" => "abc123",
            "reasonCode" => 100,
            "decision" => "ACCEPT",
            "merchantReferenceCode" => "00000123"
        ];

        return $response;
    }
}
