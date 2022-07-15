<?php

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Http;

use CyberSource\VisaCheckout\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class TransferFactoryTest
 */
class TransferFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var TransferFactory
     */
    private $transferMock;

    /**
     * @var TransferBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transferBuilder;

    protected function setUp()
    {
        $this->transferBuilder = $this->getMockBuilder(TransferBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transferMock = $this->getMockBuilder(TransferInterface::class);

        $this->transferFactory = new TransferFactory(
            $this->transferBuilder
        );
    }

    public function testCreate()
    {
        $request = ['data1', 'data2'];

        $this->transferBuilder->expects($this->once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();

        $this->transferBuilder->expects($this->once())
            ->method('build')
            ->willReturn($this->transferMock);

        $this->assertEquals($this->transferMock, $this->transferFactory->create($request));
    }
}
