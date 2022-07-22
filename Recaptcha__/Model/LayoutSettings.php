<?php

namespace CyberSource\Recaptcha\Model;


class LayoutSettings
{

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getCaptchaSettings()
    {
        return [
            'siteKey' => $this->config->getPublicKey(),
            'size' => ($this->config->getType() == 'invisible') ? 'invisible' : null,
            'badge' => $this->config->getPosition(),
            'lang' => $this->config->getLanguageCode(),
            'enabled' => [
                'cybersource' => $this->config->isEnabled(),
            ]
        ];
    }
}
