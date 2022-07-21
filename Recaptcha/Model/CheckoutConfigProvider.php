<?php
namespace CyberSource\Recaptcha\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    private $layoutSettings;

    public function __construct(
        \CyberSource\ReCaptcha\Model\LayoutSettings $layoutSettings
    ) {
        $this->layoutSettings = $layoutSettings;
    }

    public function getConfig()
    {
        return [
            'cybersource_recaptcha' => $this->layoutSettings->getCaptchaSettings()
        ];
    }
}
