<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Test\Unit\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use CyberSource\VisaCheckout\Gateway\Validator\ResponseCodeValidator;
use Psr\Log\LoggerInterface;

class ResponseCodeValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    public function setUp()
    {
        $this->resultFactory = $this->getMockBuilder(
            'Magento\Payment\Gateway\Validator\ResultInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(ResultInterface::class);
    }

    /**
     * @param array $response
     * @param array $expectationToResultCreation
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $response, array $expectationToResultCreation)
    {
        $this->resultFactory->expects(static::once())
            ->method('create')
            ->with(
                $expectationToResultCreation
            )
            ->willReturn($this->resultMock);

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validator = new ResponseCodeValidator($this->resultFactory, $logger);
        try {
            $validator->validate(['response' => $response]);
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Response does not exist', $e->getMessage());
        }
    }

    public function validateDataProvider()
    {
        return [
            'fail_1' => [
                'response' => [ResponseCodeValidator::RESULT_CODE => 0],
                'expectationToResultCreation' => [
                    'isValid' => false,
                    'failsDescription' => [__('Gateway rejected the transaction.')]
                ]
            ],
            'success' => [
                'response' => [ResponseCodeValidator::RESULT_CODE => 100],
                'expectationToResultCreation' => [
                    'isValid' => true,
                    'failsDescription' => []
                ]
            ]
        ];
    }
}
