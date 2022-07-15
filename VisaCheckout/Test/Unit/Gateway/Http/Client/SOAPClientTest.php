<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Http\Client;

use CyberSource\VisaCheckout\Gateway\Http\Client\SOAPClient;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransactionSaleTest
 */
class SOAPClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SOAPClient
     */
    private $client;

    /**
     * @var SoapClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $soapClientMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $wsdlFile = dirname(__FILE__) .
            DIRECTORY_SEPARATOR .
            'wsdl' .
            DIRECTORY_SEPARATOR .
            'CyberSourceTransaction_1.134.wsdl';

        $scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        
        $loggerMock = $this->getMockBuilder(
            'Psr\Log\LoggerInterface',
            ['error', 'info'],
            [],
            '',
            false
        )->getMock();
        
        $this->soapClientMock = $this->getMockBuilder('\SoapClient')
            ->disableOriginalConstructor()
            ->getMock();

        // Set to isTestMode under AbstractConnection::handleWsdlEnvironment
        $scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::IS_TEST_MODE_CONFIG_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn(true);

        $scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::TEST_WSDL_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($wsdlFile);
        // Setup credentials AbstractConnection::setupCredentials
        $scopeConfigMock->expects($this->at(2))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::MERCHANT_ID_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn('collinsharper');

        $scopeConfigMock->expects($this->at(3))
            ->method('getValue')
            ->with(
                \CyberSource\Core\Service\AbstractConnection::TRANSACTION_KEY_PATH,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn('123456');

        $this->soapClientMock->expects($this->any())
            ->method('SoapClient')
            ->withAnyParameters()
            ->willReturnSelf();

        $this->client = new SOAPClient(
            $scopeConfigMock,
            $loggerMock,
            $this->soapClientMock
        );
    }

    /**
     * Run test placeRequest method
     *
     * @return void
     */
    public function testPlaceRequestSuccess()
    {
        $actualResult = $this->client->placeRequest($this->getTransferObjectMock());

        $this->assertTrue(is_array($actualResult));
        $this->assertEquals([], $actualResult);
    }

    /**
     * Run test placeRequest method
     *
     * @return void
     */
    public function testPlaceRequestException()
    {
        $this->client->placeRequest($this->getTransferObjectMock());
    }

    /**
     * @return TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransferObjectMock()
    {
        $transferObjectMock = $this->getMockBuilder(TransferInterface::class)->getMock();
        $transferObjectMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getTransferData());

        return $transferObjectMock;
    }

    /**
     * @return array
     */
    private function getTransferData()
    {
        return [
            'test-data-key' => 'test-data-value'
        ];
    }
}
