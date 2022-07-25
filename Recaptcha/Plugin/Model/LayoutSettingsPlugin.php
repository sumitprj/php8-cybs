<?php

namespace CyberSource\Recaptcha\Plugin\Model;


class LayoutSettingsPlugin
{

    /**
     * @var \CyberSource\Recaptcha\Model\Config
     */
    private $config;

    public function __construct(
        \CyberSource\Recaptcha\Model\Config $config
    ) {
        $this->config = $config;
    }

    public function afterGetCaptchaSettings(
        \CyberSource\Recaptcha\Model\LayoutSettings $subject,
        array $result
    ) {
        $result['enabled']['cybersource'] = $this->config->isEnabled();

        return $result;
    }
}
