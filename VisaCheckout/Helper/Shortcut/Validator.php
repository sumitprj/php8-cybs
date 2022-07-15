<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Helper\Shortcut;

class Validator extends \Magento\Paypal\Helper\Shortcut\Validator
{
    /**
     * Checks visibility of context (cart or product page)
     *
     * @param string $paymentCode Payment method code
     * @param bool $isInCatalog
     * @return bool
     */
    public function isContextAvailable($paymentCode, $isInCatalog)
    {
        return true;
    }
}
