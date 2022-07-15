<?php
/**
 * Copyright © 2018 CollinsHarper. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Gateway\Config;

use CyberSource\Core\Model\AbstractGatewayConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @codeCoverageIgnore
 */
class Config extends AbstractGatewayConfig
{
    const CODE = 'chcybersourcevisa';

    const KEY_ACTIVE = "active";
    const KEY_TITLE = "title";
    const KEY_CCTYPES = "cctypes";
    const KEY_PAYMENT_ACTION = "payment_action";
    const KEY_ALLOWSPECIFIC = "allowspecific";
    const KEY_SPECIFICCOUNTRY = "specificcountry";
    const KEY_API_KEY = "api_key";

    /**
     * @var null|string
     */
    private $methodCode;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $pathPattern;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode,
        $pathPattern
    ) {
        $this->methodCode = $methodCode;
        $this->scopeConfig = $scopeConfig;
        $this->pathPattern = $pathPattern;
        parent::__construct($scopeConfig, self::CODE, $pathPattern);
    }

    public function isActive()
    {
        return $this->getValue(self::KEY_ACTIVE);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    public function isTest($storeId = null)
    {
        $currentMethod = $this->methodCode;
        $this->setMethodCode('chcybersource');
        $testMode = parent::isTestMode($storeId);
        $this->setMethodCode($currentMethod);
        return $testMode;
    }

    public function getTransactionKey()
    {
        return $this->getValue(self::KEY_TRANSACTION_KEY);
    }

    public function getCCTypes()
    {
        return $this->getValue(self::KEY_CCTYPES);
    }

    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    public function getSpecificCountry()
    {
        return $this->getValue(self::KEY_SPECIFICCOUNTRY);
    }

    public function getApiKey()
    {
        return $this->getValue(self::KEY_API_KEY);
    }

    public function getValue($field, $storeId = null)
    {
        $value = $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null) {
            $this->setMethodCode(\CyberSource\Core\Model\Config::CODE);
            $value = parent::getValue($field, $storeId);
        }

        return $value;
    }

    public function isAuthMode()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION) ==
            \CyberSource\VisaCheckout\Model\Adminhtml\Source\PaymentAction::ACTION_AUTHORIZE;
    }

    public function isDeveloperMode()
    {
        return ($this->isTest() == 1) ? "true" : "false";
    }
}
