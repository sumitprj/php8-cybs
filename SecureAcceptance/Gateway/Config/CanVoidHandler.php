<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CyberSource\SecureAcceptance\Gateway\Config;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandler implements ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        $paymentDO = SubjectReader::readPayment($subject);

        $payment = $paymentDO->getPayment();
        return $payment instanceof Payment && !(bool)$payment->getAmountPaid();
    }
}
