<?php

namespace CyberSource\VisaCheckout\Test\Unit\Helper;

use CyberSource\VisaCheckout\Gateway\Config\Config;
use CyberSource\VisaCheckout\Helper\RequestDataBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Helper\Data as CheckoutHelperData;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Backend\Model\Auth;
use Magento\GiftMessage\Model\Message;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RequestDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var CustomerSession|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var CheckoutHelperData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutHelperMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var RequestDataBuilder
     */
    private $helper;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Auth|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authMock;

    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    private $loggerMock;
    
    private $addressMock;

    public function setUp()
    {
        $this->checkoutSessionMock = $this
            ->getMockBuilder(CheckoutSession::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutHelperMock = $this->getMockBuilder(CheckoutHelperData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAdditionalInformation',
                'getOrder',
                'getQuote'
            ])
            ->getMock();

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseShippingAmount', 'getData', 'getRegionCode'])
            ->getMock();

        $this->addressMock->method('getBaseShippingAmount')
            ->willReturn(10);

        $this->addressMock->method('getRegionCode')
            ->willReturn('RegionCode');

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAddress', 'getAllVisibleItems', 'getReservedOrderId', 'getBillingAddress', 'getShipingAddress'])
            ->getMock();

        $this->quoteMock->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $this->checkoutSessionMock->expects(static::any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'create'])
            ->getMock();

        $this->authMock = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageMock = $this->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getAllItems', 'getBillingAddress', 'getShippingAddress'])
            ->getMock();

        $this->orderMock
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);
            
        $this->orderMock
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);
            
        $this->orderMock
            ->method('getAllItems')
            ->willReturn([]);
        
        $this->payment
            ->method('getOrder')
            ->willReturn($this->orderMock);
            
        $this->loggerMock = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $merchantDefinedData = new \stdClass;
        $this->helper = $helper->getObject(
            RequestDataBuilder::class,
            [
                '_logger' => $this->loggerMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'gatewayConfig' => $this->configMock,
                'merchantDefinedData' => $merchantDefinedData,
                'orderCollectionFactory' => $this->collectionFactoryMock
            ]
        );
    }

    public function testBuildVisaDecryptRequestData()
    {
        $expectedResponse = (object) [
            'merchantID' => null,
            'merchantReferenceCode' => '00000123',
            'getVisaCheckoutDataService' => (object) [
                'run' => 'true'
            ],
            'paymentSolution' => 'visacheckout',
            'vc' => (object) [
                'orderID' => '123callid'
            ],
            'partnerSolutionID' => \CyberSource\Core\Helper\AbstractDataBuilder::PARTNER_SOLUTION_ID,
            'developerId' => null
        ];

        $this->configMock->expects(static::once())
            ->method('getMerchantId');

        $response = $this->helper->buildVisaDecryptRequestData("123callid", "00000123");

        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertEquals($expectedResponse, $response);
        $this->assertObjectHasAttribute('getVisaCheckoutDataService', $response);
    }

    public function testBuildAuthorizationRequestData()
    {
        $this->quoteMock->method('getAllVisibleItems')
            ->willReturn([]);

        $this->quoteMock->method('getReservedOrderId')
            ->willReturn('123');
        
        $this->quoteMock->method('getBillingAddress')
            ->willReturn($this->addressMock);

        $this->quoteMock->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $this->configMock->expects(static::once())
            ->method('getMerchantId');

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->with('callId');

        $this->collectionFactoryMock->method('create')
            ->willReturn($this->collectionFactoryMock);
        
        $this->collectionFactoryMock->method('addFieldToFilter')
            ->willReturn(null);

        $response = $this->helper->buildAuthorizationRequestData($this->payment);
        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertObjectHasAttribute('ccAuthService', $response);
    }

    public function testBuildCaptureRequestData()
    {
        $this->configMock->expects(static::once())
            ->method('getMerchantId');

        $this->payment->expects(static::exactly(6))
            ->method('getAdditionalInformation');
        
        $this->orderMock->method('getBillingAddress')
            ->willReturn([]);

        $response = $this->helper->buildCaptureRequestData($this->payment);
        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertObjectHasAttribute('ccCaptureService', $response);
    }

    public function testBuildSettlementRequestData()
    {
        $response = $this->helper->buildSettlementRequestData();
        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertObjectHasAttribute('ccCaptureService', $response);
    }

    public function testBuildVoidRequestData()
    {
        $this->configMock->expects(static::once())
            ->method('getMerchantId');

        $this->payment->expects(static::exactly(2))
            ->method('getAdditionalInformation');

        $this->loggerMock->method('info');

        $response = $this->helper->buildVoidRequestData($this->payment);
        $this->assertObjectHasAttribute('voidService', $response);
    }

    public function testBuildRefundRequestData()
    {
        $this->configMock->expects(static::once())
            ->method('getMerchantId');

        $this->payment->expects(static::any())
            ->method('getAdditionalInformation');

        $this->payment->expects(static::any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $response = $this->helper->buildRefundRequestData($this->payment, 10.0);
        $this->assertObjectHasAttribute('ccCreditService', $response);
        $this->assertObjectHasAttribute('billTo', $response);
        $this->assertObjectNotHasAttribute('card', $response);
    }
}
