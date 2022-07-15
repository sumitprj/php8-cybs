<?php
/**
 * Copyright © 2021 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\WeChatPay\Gateway\Request;

class MerchantDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \CyberSource\WeChatPay\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \CyberSource\WeChatPay\Gateway\Config\Config
     */
    private $config;

    /**
     * @param \CyberSource\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
     * @param \CyberSource\WeChatPay\Gateway\Config\Config $config
     */
    public function __construct(
        \CyberSource\WeChatPay\Gateway\Helper\SubjectReader $subjectReader,
        \CyberSource\WeChatPay\Gateway\Config\Config $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $request = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $storeId = $paymentDO->getOrder()->getStoreId();

        $request['partnerSolutionID'] = \CyberSource\Core\Helper\AbstractDataBuilder::PARTNER_SOLUTION_ID;
        $request['storeId'] = $storeId;

        if ($developerId = $this->config->getDeveloperId()) {
            $request['developerId'] = $developerId;
        }

        return $request;
    }
}
