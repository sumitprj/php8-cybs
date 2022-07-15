<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use CyberSource\VisaCheckout\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 * @codeCoverageIgnore
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'chcybersourcevisa';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * Constructor
     *
     * @param Config $config
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Config $config,
        ResolverInterface $localeResolver,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->url = $url;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $isVisaCheckoutActive = $this->config->isActive();
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $isVisaCheckoutActive,
                    'title' => $this->config->getTitle(),
                    'api_key' => $this->config->getApiKey(),
                    'isDeveloperMode' => $this->config->isDeveloperMode(),
                    'placeOrderUrl' => $this->url->getUrl('chcybersourcevisa/index/placeorder'),
                    'buttonUrl' =>
                        $this->config->isTest()
                            ? 'https://sandbox.secure.checkout.visa.com/wallet-services-web/xo/button.png'
                            : 'https://secure.checkout.visa.com/wallet-services-web/xo/button.png',
                ]
            ]
        ];
    }
}
