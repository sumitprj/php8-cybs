<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Request;

use CyberSource\VisaCheckout\Gateway\Config\Config;
use CyberSource\VisaCheckout\Helper\RequestDataBuilder;
use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;
use CyberSource\VisaCheckout\Gateway\Request\CaptureRequest;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CaptureRequestTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaptureRequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CaptureRequest
     */
    private $request;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDO;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * @var RequestDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestDataBuilderMock;

    protected function setUp()
    {
        $this->paymentDO = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestDataBuilderMock = $this
            ->getMockBuilder(RequestDataBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new CaptureRequest($this->configMock, $this->requestDataBuilderMock, $this->subjectReaderMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildReadPaymentException()
    {
        $buildSubject = [];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willThrowException(new \InvalidArgumentException());

        $this->request->build($buildSubject);
    }

    public function testBuild()
    {
        $buildSubject = [
            'payment' => $this->paymentDO,
            'amount' => 10.00
        ];

        $expectedResult = new \stdClass();
        $expectedResult->test = "test1";

        $this->requestDataBuilderMock->expects(static::once())
            ->method('buildCaptureRequestData')
            ->willReturn($expectedResult);

        $this->paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDO);

        static::assertEquals(
            (array) $expectedResult,
            $this->request->build($buildSubject)
        );
    }
}
