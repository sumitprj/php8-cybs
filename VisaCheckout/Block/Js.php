<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Block;

use Magento\Framework\View\Element\Template;

class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var $config
     */
    protected $config;

    /**
     * Js Constructor
     *
     * @param Template\Context $context
     * @param \CyberSource\VisaCheckout\Gateway\Config\Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \CyberSource\VisaCheckout\Gateway\Config\Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }
    
    /**
     * Check Is sandbox enable or not
     *
     * @return boolean
     */
    public function isSandbox()
    {
        return $this->config->isTest();
    }
}
