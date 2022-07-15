<?php
/**
 * Copyright © 2018 CollinsHarper. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class RefundRequest extends AbstractRequest implements BuilderInterface
{
    const TRANSACTION_TYPE = "34";

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);
        $request = $this->requestDataBuilder->buildRefundRequestData($payment, $buildSubject['amount']);

        return (array) $request;
    }
}
