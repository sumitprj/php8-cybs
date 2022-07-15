<?php

namespace CyberSource\VisaCheckout\Test\Unit\Service;

use CyberSource\VisaCheckout\Service\CyberSourceSoap;
use Magento\Framework\Exception\LocalizedException;

class CyberSourceSoapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Monolog\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var CyberSourceSoap
     */
    private $service;

    /**
     * @var \SoapClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $soapClient;

    public function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerMock = $this->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $this->createSoapClientMock();

        $this->service = new CyberSourceSoap(
            $this->scopeConfigMock,
            $this->loggerMock,
            $this->soapClient
        );
    }

    public function testRequest()
    {
        $requestBody = (object) [
            'test' => 'test'
        ];

        $this->soapClient->expects($this->once())
            ->method('runTransaction')
            ->willReturn((object)['reasonCode' => 100]);

        $response = $this->service->request($requestBody);
        $this->assertInstanceOf(\stdClass::class, $response);
        $this->assertEquals(100, $response->reasonCode);
    }

    public function testRequestException()
    {
        $requestBody = (object) [
            'test' => 'test'
        ];

        $this->soapClient->expects(static::once())
            ->method('runTransaction');

        $this->loggerMock->expects(static::any())
            ->method('error');

        $this->service->request($requestBody);
    }

    private function createSoapClientMock()
    {
        $wsdlFile = dirname(__FILE__) .
            DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
            'Gateway' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Client' .
            DIRECTORY_SEPARATOR .
            'wsdl' .
            DIRECTORY_SEPARATOR .
            'CyberSourceTransaction_1.134.wsdl';

        $this->soapClient = $this->getMockFromWsdl(
            $wsdlFile,
            'CyberSourceTransactionWS'
        );

        // Set to isTestMode under AbstractConnection::handleWsdlEnvironment
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::IS_TEST_MODE_CONFIG_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);

        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::TEST_WSDL_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($wsdlFile);
        // Setup credentials AbstractConnection::setupCredentials
        $this->scopeConfigMock->expects($this->at(2))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::MERCHANT_ID_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn('collinsharper');

        $this->scopeConfigMock->expects($this->at(3))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::TRANSACTION_KEY_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn('123456');


        $this->soapClient->expects($this->any())
            ->method('SoapClient')
            ->withAnyParameters()
            ->willReturnSelf();
    }
}
