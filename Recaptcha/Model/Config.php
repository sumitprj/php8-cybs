<?php

namespace CyberSource\Recaptcha\Model;

use Magento\Store\Model\ScopeInterface;

class Config
{

    const XML_PATH_RECAPTCHA_ENABLED = 'payment/chcybersource/recaptcha_enabled';
    const XML_PATH_RECAPTCHA_PUBLIC_KEY = 'payment/chcybersource/recaptcha_website_key';
    const XML_PATH_RECAPTCHA_PRIVATE_KEY = 'payment/chcybersource/recaptcha_secret_key';
    const XML_PATH_RECAPTCHA_TYPE = 'payment/chcybersource/recaptcha_type';
    const XML_PATH_RECAPTCHA_POSITION = 'payment/chcybersource/recaptcha_badge_position';
    const XML_PATH_RECAPTCHA_LANGUAGE = 'payment/chcybersource/recaptcha_language';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            static::XML_PATH_RECAPTCHA_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPublicKey()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_RECAPTCHA_PUBLIC_KEY,
            ScopeInterface::SCOPE_STORE
);
    }

    public function getType()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_RECAPTCHA_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPrivateKey()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_RECAPTCHA_PRIVATE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPosition()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_RECAPTCHA_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getLanguageCode()
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_RECAPTCHA_LANGUAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

}
