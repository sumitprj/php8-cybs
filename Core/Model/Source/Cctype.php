<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\Core\Model\Source;

/**
 * Class Cctype
 * @package CyberSource\Core\Model\Source
 * @codeCoverageIgnore
 */
class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'OT', 'DN' , 'MI' ];
    }
}
