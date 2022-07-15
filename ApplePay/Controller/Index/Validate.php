<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\ApplePay\Controller\Index;

use CyberSource\ApplePay\Gateway\Config\Config;
use CyberSource\Core\Model\LoggerInterface;
use Magento\Framework\App\Action\Context;

class Validate extends \Magento\Framework\App\Action\Action
{
    const APPLE_PAY_START_SESSION_URL = 'https://apple-pay-gateway-pr-pod2.apple.com/paymentservices/startSession';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * Validate constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Config $gatewayConfig
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->config = $gatewayConfig;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = [
            'merchantIdentifier' => $this->config->getAppleMerchantId(),
            'domainName' => $this->config->getDomain(),
            'displayName' => $this->config->getDisplayName()
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::APPLE_PAY_START_SESSION_URL);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->config->getPathCert());
        curl_setopt($ch, CURLOPT_SSLKEY, $this->config->getPathKey());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (! $result = curl_exec($ch)) {
            $this->logger->critical("Apple Pay merchant validation failed: " . curl_error($ch));
        }

        curl_close($ch);

        return $this->resultJsonFactory->create()->setData(
            ['session' => json_decode($result, 1)]
        );
    }
}
