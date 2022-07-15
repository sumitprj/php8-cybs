<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Controller\Index;

class SaveShippingMethod extends \Magento\Paypal\Controller\Express\AbstractExpress\SaveShippingMethod
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \CyberSource\VisaCheckout\Gateway\Config\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \CyberSource\VisaCheckout\Gateway\Config\Config::CODE;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \CyberSource\VisaCheckout\Model\Checkout::class;
}
