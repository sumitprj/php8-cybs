<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\PayPal\Model;

use CyberSource\Core\Model\AbstractGatewayConfig;
use CyberSource\PayPal\Model\Source\RedirectionType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @codeCoverageIgnore
 */
class Config extends AbstractGatewayConfig
{
    const KEY_PAYPAL_REDIRECT_TYPE = 'paypal_redirection_type';
    const KEY_TITLE = 'title';
    const KEY_CREDIT_TITLE = 'paypal_credit_title';
    const KEY_ACTIVE = 'active';
    const KEY_MERCHANT_ID = 'paypal_merchant_id';
    const KEY_MERCHANT_NAME = 'paypal_merchant_name';
    const KEY_PAYMENT_ACTION = 'paypal_payment_action';
    const KEY_PAYPAL_CREDIT = 'paypal_credit_active';
    const CODE = 'cybersourcepaypal';
    const CODE_CREDIT = 'cybersourcepaypal_credit';
    const CODE_VAULT = 'cybersourcepaypal_vault';

    private $methodCode;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    private $pathPattern;

    private $storeId;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode,
        $pathPattern,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->methodCode = $methodCode;
        $this->scopeConfig = $scopeConfig;
        $this->pathPattern = $pathPattern;
        $this->storeManager = $storeManager;
        parent::__construct($scopeConfig, self::CODE, $pathPattern);
    }

    public function getPayPalRedirectType()
    {
        return $this->getValue(self::KEY_PAYPAL_REDIRECT_TYPE);
    }

    public function isInContext()
    {
        return $this->getPayPalRedirectType() == RedirectionType::IN_CONTEXT;
    }

    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    public function getCreditTitle()
    {
        return $this->getValue(self::KEY_CREDIT_TITLE);
    }

    public function isTestMode($storeId = null)
    {
        $currentMethod = $this->methodCode;
        $this->setMethodCode('chcybersource');
        $testMode = parent::isTestMode($storeId);
        $this->setMethodCode($currentMethod);
        return $testMode;
    }

    public function getMerchantName()
    {
        return $this->getValue(self::KEY_MERCHANT_NAME);
    }

    public function getPayPalMerchantId()
    {
        return $this->getValue(self::KEY_MERCHANT_ID);
    }

    public function getEnvironment()
    {
        $isTestMode = (bool) $this->getValue(self::KEY_TEST_MODE);

        if ($isTestMode) {
            return 'sandbox';
        }

        return 'prod';
    }

    public function isPayPalCreditEnabled()
    {
        return (bool) $this->getValue(self::KEY_PAYPAL_CREDIT);
    }

    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    public function setMethod($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * Get "What Is PayPal" localized URL
     * Supposed to be used with "mark" as popup window
     *
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @return string
     */
    public function getPaymentMarkWhatIsPaypalUrl(\Magento\Framework\Locale\ResolverInterface $localeResolver = null)
    {
        $countryCode = 'US';
        if (null !== $localeResolver) {
            $shouldEmulate = null !== $this->storeId && $this->storeManager->getStore()->getId() != $this->storeId;
            if ($shouldEmulate) {
                $localeResolver->emulate($this->storeId);
            }
            $countryCode = \Locale::getRegion($localeResolver->getLocale());
            if ($shouldEmulate) {
                $localeResolver->revert();
            }
        }
        return sprintf(
            'https://www.paypal.com/%s/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside',
            strtolower($countryCode)
        );
    }

    /**
     * Get PayPal "mark" image URL
     * @return string
     */
    public function getPaymentMarkImageUrl()
    {
        return 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png';
    }

    /**
     * Get url for dispatching customer to express checkout start
     *
     * @param string $token
     * @return string
     */
    public function getExpressCheckoutStartUrl($token)
    {
        return sprintf(
            'https://www.%spaypal.com/checkoutnow%s',
            $this->getEnvironment() == 'sandbox' ? 'sandbox.' : '',
            '?token=' . urlencode($token)
        );
    }

    /**
     * Return start url for PayPal Basic
     *
     * @param string $token
     * @return string
     */
    public function getPayPalBasicStartUrl($token)
    {
        $params = [
            'cmd'   => '_express-checkout',
            'token' => $token,
        ];

        return sprintf(
            'https://www.%spaypal.com/cgi-bin/webscr%s',
            $this->getEnvironment() == 'sandbox' ? 'sandbox.' : '',
            $params ? '?' . http_build_query($params) : ''
        );
    }

    /**
     * Get url that allows to edit checkout details on paypal side
     *
     * @param \Magento\Paypal\Controller\Express|string $token
     * @return string
     */
    public function getExpressCheckoutEditUrl($token)
    {
        return $this->getPaypalUrl(['cmd' => '_express-checkout', 'useraction' => 'continue', 'token' => $token]);
    }

    /**
     * PayPal web URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getPaypalUrl(array $params = [])
    {
        return sprintf(
            'https://www.%spaypal.com/cgi-bin/webscr%s',
            $this->getEnvironment() == 'sandbox' ? 'sandbox.' : '',
            $params ? '?' . http_build_query($params) : ''
        );
    }

    /**
     * @return bool
     */
    public function isVaultEnabled()
    {
        $this->setMethodCode(self::CODE_VAULT);
        return (bool) parent::getValue(self::KEY_ACTIVE);
    }

    /**
     * Returns config value with fallback to core
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        $this->setMethodCode(self::CODE);
        $value = parent::getValue($field, $storeId);
        if ($value === null) {
            $this->setMethodCode(\CyberSource\Core\Model\Config::CODE);
            $value = parent::getValue($field, $storeId);
        }

        return $value;
    }
}
