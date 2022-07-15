<?php
/**
 *
 */

namespace CyberSource\VisaCheckout\Test\Unit\CyberSource\VisaCheckout\Helper;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use CyberSource\VisaCheckout\Helper\ParseGetDataHelper;

class ParseGetDataHelperTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\Helper\Context|MockObject
     */
    protected $contextMock;
    /**
     * @var \Magento\Framework\DataObjectFactory|MockObject
     */
    protected $dataObjectFactoryMock;
    /**
     * @var \Magento\Directory\Model\CountryFactory|MockObject
     */
    protected $countryFactoryMock;

    /**
     * @var ParseGetDataHelper
     */
    protected $helper;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)->disableOriginalConstructor()->getMock();
        $this->dataObjectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)->disableOriginalConstructor()->getMock();
        $this->countryFactoryMock = $this->getMockBuilder(\Magento\Directory\Model\CountryFactory::class)->disableOriginalConstructor()->getMock();

        $this->helper = new ParseGetDataHelper(
            $this->contextMock,
            $this->dataObjectFactoryMock,
            $this->countryFactoryMock
        );
    }

    public function testSetCreditCardData()
    {
        $responseDecrypted = [
            'card' => (object)[
                'accountNumber' => '4111111111111111',
                'expirationMonth' => '11',
                'expirationYear' => '22',
            ],
            'vcReply' => (object)[
                'cardType' => 'VISA',
                'some' => 'Field'
            ]
        ];

        $paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)->disableOriginalConstructor()->getMock();

        $paymentMock->expects(static::exactly(6))->method('setAdditionalInformation')->withConsecutive(
            [ParseGetDataHelper::CARD_ACCOUNT_NUMBER, '1111'],
            [ParseGetDataHelper::CARD_EXP_MONTH, '11'],
            [ParseGetDataHelper::CARD_EXP_YEAR, '22'],
            ['cardType', 'VISA'],
            ['some', 'Field'],
            ['cardType', '001']
        );

        $this->helper->setCreditCardData($responseDecrypted, $paymentMock);
    }
}
