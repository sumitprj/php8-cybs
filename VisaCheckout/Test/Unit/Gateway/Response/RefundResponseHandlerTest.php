<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Response;

use CyberSource\VisaCheckout\Gateway\Response\RefundResponseHandler;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class RefundResponseHandlerTest
 */
class RefundResponseHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RefundResponseHandler
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


    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'unsAdditionalInformation',
                'setTransactionId',
                'setAdditionalInformation'
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

        $this->responseHandler = new RefundResponseHandler(
            $this->subjectReader
        );
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
