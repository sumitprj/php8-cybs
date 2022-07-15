<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Service;

use CyberSource\Core\Model\LoggerInterface;
use CyberSource\Core\Service\AbstractConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;

//@TODO: find out if we really need this class
class CyberSourceSoap extends AbstractConnection
{
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        \SoapClient $client = null
    ) {
        parent::__construct($scopeConfig, $logger);
        /**
         * Added soap client as parameter to be able to mock in unit tests.
         */
        if ($client !== null) {
            $this->setSoapClient($client);
        }
    }

    public function request(\stdClass $requestBody)
    {
        $result = null;
        try {
            $this->logger->debug([__METHOD__ => (array) $requestBody]);
            $result = $this->client->runTransaction($requestBody);
            $this->logger->debug([__METHOD__ => (array) $result]);
            return $result;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
