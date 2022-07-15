<?php declare(strict_types = 1);

namespace CyberSource\ThreeDSecure\Block;

use PHPUnit\Framework\TestCase;

class SongbirdJsTest extends TestCase
{
    /** @var SongbirdJs */
    private $songbirdJs;

    /** @var \Magento\Framework\View\Element\Template\Context | \PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var \CyberSource\ThreeDSecure\Gateway\Config\Config | \PHPUnit_Framework_MockObject_MockObject */
    private $config;

    /** @var array */
    private $data;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->config = $this->createMock(\CyberSource\ThreeDSecure\Gateway\Config\Config::class);
        $this->data = [];
        $this->songbirdJs = new SongbirdJs(
            $this->context,
            $this->config,
            $this->data
        );
    }


    /**
     * @dataProvider isSandboxTestProvider
     */
    public function testIsSandbox($isTestMode, $isSandbox)
    {
        $this->config->expects(static::once())->method('isTestMode')->willReturn($isSandbox);

        $this->assertEquals($isSandbox, $this->songbirdJs->isSandbox());
    }

    public function isSandboxTestProvider()
    {
        return [
            ['isTestMode' => true, 'isSandbox' => true],
            ['isTestMode' => false, 'isSandbox' => false],
        ];
    }
}
