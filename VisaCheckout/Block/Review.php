<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Block;

class Review extends \Magento\Paypal\Block\Express\Review
{
    /**
     * VisaCheckout controller path
     *
     * @var string
     */
    protected $_controllerPath = 'chcybersourcevisa/index';

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        return \CyberSource\VisaCheckout\Block\Shortcut::SHORTCUT_IMAGE;
    }

    public function canEditShippingMethod()
    {
        return true;
    }
}
