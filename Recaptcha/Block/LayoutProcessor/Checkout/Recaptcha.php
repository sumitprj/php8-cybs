<?php

namespace CyberSource\Recaptcha\Block\LayoutProcessor\Checkout;

class Recaptcha implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \CyberSource\Recaptcha\Model\LayoutSettings
     */
    private $layoutSettings;

    public function __construct(
        \CyberSource\Recaptcha\Model\LayoutSettings $layoutSettings
    ) {
        $this->layoutSettings = $layoutSettings;
    }

    public function process($jsLayout)
    {
        $jsLayout['components']
        ['checkout']['children']
        ['steps']['children']
        ['billing-step']['children']
        ['payment']['children']
        ['payments-list']['children']
        ['cybersource-recaptcha']['children']
        ['msp_recaptcha']['settings'] = $this->layoutSettings->getCaptchaSettings();

        return $jsLayout;
    }
}
