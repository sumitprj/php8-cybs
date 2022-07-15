<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Block;

use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use CyberSource\VisaCheckout\Gateway\Config\Config as CyberSourceVCConfig;

class Shortcut extends \Magento\Paypal\Block\Express\Shortcut
{
    /**
     * @var CyberSourceVCConfig
     */
    private $gatewayConfig;

    /**
     * Shortcut image path
     */
    const SHORTCUT_IMAGE = "https://secure.checkout.visa.com/wallet-services-web/xo/button.png";

    /**
     * Shortcut constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param ValidatorInterface $shortcutValidator
     * @param CyberSourceVCConfig $gatewayConfig
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $checkoutType
     * @param string $alias
     * @param string $shortcutTemplate
     * @param \Magento\Checkout\Model\Session|null $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        ValidatorInterface $shortcutValidator,
        CyberSourceVCConfig $gatewayConfig,
        $paymentMethodCode,
        $startAction,
        $checkoutType,
        $alias,
        $shortcutTemplate,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $paypalConfigFactory,
            $checkoutFactory,
            $mathRandom,
            $localeResolver,
            $shortcutValidator,
            $paymentMethodCode,
            $startAction,
            $checkoutType,
            $alias,
            $shortcutTemplate,
            $checkoutSession,
            $data
        );

        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return self::SHORTCUT_IMAGE;
    }

    /**
     * @return bool|string
     * @since 100.2.0
     */
    public function getAPIKey()
    {
        return $this->gatewayConfig->getApiKey();
    }


    public function getJsonConfig()
    {
        $config = [
            "isCatalogProduct" => !empty($isInCatalogProduct) ? (bool)$isInCatalogProduct : false,
            "shortcutContainerClass" => "." . $this->escapeHtml($this->getShortcutHtmlId()),
            "apiKey" => $this->getAPIKey(),
            "baseUrl" => $this->getUrl(),
            "reviewUrl" => $this->getUrl('chcybersourcevisa/index/review', ['_secure' => true])
        ];

        return json_encode($config);
    }
}
