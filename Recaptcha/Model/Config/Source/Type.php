<?php

namespace CyberSource\Recaptcha\Model\Config\Source;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'recaptcha', 'label' => __('reCaptcha v2')],
            ['value' => 'invisible', 'label' => __('Invisible reCaptcha')],
//            ['value' => 'recaptcha3', 'label' => __('reCaptcha v3')], // TODO: add v3 support later??
        ];
    }
}
