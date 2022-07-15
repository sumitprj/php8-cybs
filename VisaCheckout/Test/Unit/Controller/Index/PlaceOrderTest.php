<?php

namespace CyberSource\VisaCheckout\Test\Unit\Controller\Index;

use CyberSource\VisaCheckout\Controller\Index\PlaceOrder;
use Magento\Framework\App\Action\Context;
use CyberSource\VisaCheckout\Helper\RequestDataBuilder;
use CyberSource\VisaCheckout\Service\CyberSourceSoap;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Message\ManagerInterface;
use CyberSource\VisaCheckout\Gateway\Validator\ResponseCodeValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var RequestDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestDataBuilderMock;

    /**
     * @var CyberSourceSoap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cyberSourceSOAPMock;

    /**
     * @var QuoteManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteManagementMock;

    /**
     * @var PlaceOrder
     */
    private $placeOrder;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var Session\SuccessValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $successValidatorMock;
    
    private $quoteRepositoryMock;
    
    private $transferFactoryMock;
    
    private $getDataHelperMock;
    
    private $agreementsValidator;
    
    private $quoteAddressMock;
    
    private $resultControllerFactoryMock;

    public function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock */
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock = $this
            ->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->checkoutSessionMock = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this
            ->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->resultControllerFactoryMock = $this
            ->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'setPath'])
            ->getMock();

        $this->requestDataBuilderMock = $this
            ->getMockBuilder(RequestDataBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'buildVisaDecryptRequestData'])
            ->getMock();

        $this->requestDataBuilderMock->method('create')
            ->willReturn($this->_buildRequestDataBuilder());

        $this->requestDataBuilderMock->method('buildVisaDecryptRequestData')
            ->willReturn($this->_buildRequestDataBuilder());

        $this->cyberSourceSOAPMock = $this
            ->getMockBuilder(\CyberSource\VisaCheckout\Gateway\Http\Client\SOAPClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cyberSourceSOAPMock->method('placeRequest')
            ->willReturn([ResponseCodeValidator::RESULT_CODE => '101', 'billTo' => [], 'vcReply' => 'vcReply']);

        $this->quoteManagementMock = $this
            ->getMockBuilder(QuoteManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress', 'submit'])
            ->getMock();

        $this->quoteRepositoryMock = $this
            ->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transferFactoryMock = $this
            ->getMockBuilder(\CyberSource\VisaCheckout\Gateway\Http\TransferFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->transferInterfaceMock = $this
            ->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transferFactoryMock->method('create')
            ->willReturn($this->transferInterfaceMock);

        $this->getDataHelperMock = $this
            ->getMockBuilder(\CyberSource\VisaCheckout\Helper\ParseGetDataHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->setMethods(['setShouldIgnoreValidation'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->getDataHelperMock->method('parseVisaAddress')
            ->willReturn($this->quoteAddressMock);

        $this->agreementsValidator = $this
            ->getMockBuilder(\Magento\Checkout\Api\AgreementsValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->getMockForAbstractClass();

        $contextMock->expects(self::any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->agreementsValidator->method('isValid')
            ->willReturn(true);

        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $helper = new ObjectManager($this);
        $this->placeOrder = $helper->getObject(
            PlaceOrder::class,
            [
                'checkoutSession' => $this->checkoutSessionMock,
                'quoteManagement' => $this->quoteManagementMock,
                'resultPageFactory' => $this->resultFactoryMock,
                'requestDataBuilder' => $this->requestDataBuilderMock,
                'soapClient' => $this->cyberSourceSOAPMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'transferFactory' => $this->transferFactoryMock,
                'getDataHelper' => $this->getDataHelperMock,
                'agreementsValidator' => $this->agreementsValidator,
                'logger' => $logger,
                '_objectManager' => $this->objectManagerMock,
                'resultFactory' => $this->resultControllerFactoryMock
            ]
        );
    }

    /**
     * @dataProvider placeOrderScenarios
     */
    public function testExecute($status)
    {
        $quoteMock = $this->getQuoteMock();

        $this->successValidatorMock = $this->getMockBuilder(Session\SuccessValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultMock = $this->getResultMock();

        switch ($status) {
            case 'invalid':
                $this->successValidatorMock->expects(self::once())
                    ->method('isValid')
                    ->willReturn(false);
                $url = 'checkout/cart';
                break;
            case 'exception':
                $this->successValidatorMock->expects(self::once())
                    ->method('isValid')
                    ->willThrowException(new \Exception());
                $url = 'checkout/cart';
                break;
            case 'success':
                $this->successValidatorMock->expects(self::once())
                    ->method('isValid')
                    ->willReturn(true);
                $url = 'checkout/onepage/success';
                break;
            default:
                $this->successValidatorMock->expects(self::once())
                    ->method('isValid')
                    ->willReturn(true);
                $url = 'checkout/onepage/success';
                break;
        }

        $resultMock->expects(self::once())
            ->method('setPath')
            ->with($url)
            ->willReturnSelf();

        $this->successValidatorMock
            ->method('isValid')
            ->willReturn(true);
        $this->objectManagerMock
            ->method('get')
            ->with('Magento\Checkout\Model\Session\SuccessValidator')
            ->willReturn($this->successValidatorMock);

        $paymentMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->setMethods(['setShouldIgnoreValidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock->method('setShouldIgnoreValidation');
        
        $quoteMock->expects(self::any())
            ->method('getBillingAddress')
            ->willReturn($addressMock);

        $quoteMock->expects(self::any())
            ->method('getIsVirtual')
            ->willReturn(true);

        $quoteMock->expects(self::exactly(2))
            ->method('getPayment')
            ->willReturn($paymentMock);

        $quoteMock->method('getBillingAddress')
            ->willReturn($this->quoteAddressMock);

        $this->resultControllerFactoryMock->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultMock);

        $this->checkoutSessionMock->expects(self::once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $orderMock = $this->getOrderMock();

        $this->quoteManagementMock->expects(self::once())
            ->method('submit')
            ->with($quoteMock)
            ->willReturn($orderMock);

        self::assertEquals($this->placeOrder->execute(), $resultMock);
    }

    public function placeOrderScenarios()
    {
        return [
            ['invalid'],
            ['exception'],
            ['success']
        ];
    }

    /**
     * @return ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getResultMock()
    {
        return $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setPath'])
            ->getMockForAbstractClass();
    }

    /**
     * @return Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteMock()
    {
        return $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getOrderMock()
    {
        return $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function _buildRequestDataBuilder()
    {
        $request = new \stdClass();

        $request->merchantID = 'merchanID';
        $request->partnerSolutionID = 'partnerSolutionID';
        $request->developerId = 'developerId';
        $request->merchantReferenceCode = 'merchantReferenceCode';

        $getVisaCheckoutDataService = new \stdClass();
        $getVisaCheckoutDataService->run = "true";
        $request->getVisaCheckoutDataService =  [];

        $request->paymentSolution = 'visacheckout';

        $vc = new \stdClass();
        $vc->orderID = '123456789';

        $request->vc = $vc;

        return $request;
    }
}
