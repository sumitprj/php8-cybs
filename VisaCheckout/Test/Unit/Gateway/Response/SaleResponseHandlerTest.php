<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Response;

use CyberSource\VisaCheckout\Gateway\Http\Client\SOAPClient;
use CyberSource\VisaCheckout\Gateway\Http\TransferFactory;
use CyberSource\VisaCheckout\Gateway\Response\SaleResponseHandler;
use CyberSource\VisaCheckout\Helper\RequestDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Sales\Model\Order\Payment;
use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SaleResponseHandlerTest
 */
class SaleResponseHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TRANSACTION_ID = '432erwwe';

    /**
     * @var SaleResponseHandler
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
     * @var RequestDataBuilder|MockObject
     */
    private $requestDataBuilderMock;

    /**
     * @var SOAPClient|MockObject
     */
    private $soapClientMock;

    protected function setUp()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setTransactionId',
                'setAdditionalInformation',
                'getAdditionalInformation'
            ])
            ->getMock();
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::any())
            ->method('setAdditionalInformation');

        $this->requestDataBuilderMock = $this
            ->getMockBuilder(RequestDataBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TransferInterface|MockObject $transferObjectMock */
        $transferObjectMock = $this->getTransferObjectMock();

        $transferFactoryMock = $this->getMockBuilder(TransferFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        /* $transferFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($transferObjectMock); */

        $this->soapClientMock = $this->getMockBuilder(SOAPClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseHandler = new SaleResponseHandler(
            $this->requestDataBuilderMock,
            $this->soapClientMock,
            $transferFactoryMock,
            $this->subjectReader
        );
    }

    public function testHandle()
    {
        /* $this->soapClientMock->expects(static::once())
            ->method('placeRequest')
            ->willReturn($this->getPaymentCreditCardData()); */

        $this->payment->expects(static::exactly(1))
            ->method('setTransactionId');

        $this->payment->expects(static::exactly(13))
            ->method('setAdditionalInformation');

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

        $this->payment->expects($this->at(0))
            ->method('getAdditionalInformation')
            ->with('callId')
            ->willReturn('callid123');
        
        $this->payment->expects($this->at(1))
            ->method('getAdditionalInformation')
            ->with('accountNumber')
            ->willReturn('accountNumber123');

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->getMockBuilder(TransferInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $transferObjectMock;
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

    private function getPaymentCreditCardData()
    {
        $response = [
            "reasonCode" => 100,
            "card" => (object) [
                "accountNumber" => "1234",
                "expirationMonth" => "08",
                "expirationYear" => "2100"
            ],
            "vcReply" => (object) [
                "cardType" => "VISA"
            ]
        ];

        return $response;
    }
}
