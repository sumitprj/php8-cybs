<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\ApplePay\Gateway\Config;

use CyberSource\ApplePay\Model\Ui\ConfigProvider;
use CyberSource\Core\Model\AbstractGatewayConfig;
use CyberSource\Core\Model\Config as CoreConfig;

/**
 * Class Config
 */
class Config extends AbstractGatewayConfig
{
    const KEY_APPLE_MERCHANT_ID = "apple_merchant_id";
    const KEY_DISPLAY_NAME = "display_name";
    const KEY_DOMAIN = "domain";
    const KEY_PATH_CERT = "path_cert";
    const KEY_PATH_KEY = "path_key";

    /**
     * Returns apple merchant ID
     *
     * @return string|null
     */
    public function getAppleMerchantId()
    {
        return $this->getValue(self::KEY_APPLE_MERCHANT_ID);
    }

    /**
     * Returns merchant display name
     *
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->getValue(self::KEY_DISPLAY_NAME);
    }

    /**
     * Returns merchant domain
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->getValue(self::KEY_DOMAIN);
    }

    /**
     * Returns path to certificate
     *
     * @return string|null
     */
    public function getPathCert()
    {
        return $this->getValue(self::KEY_PATH_CERT);
    }

    /**
     * Returns path to key
     *
     * @return string|null
     */
    public function getPathKey()
    {
        return $this->getValue(self::KEY_PATH_KEY);
    }

    /**
     * @return array
     */
    public function getCcTypes()
    {
        return explode(',', parent::getCcTypes() ?? '');
    }

    /**
     * Returns apple config value with fallback to core
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        $this->setMethodCode(ConfigProvider::APPLEPAY_CODE);
        $value = parent::getValue($field, $storeId);
        if ($value === null) {
            $this->setMethodCode(CoreConfig::CODE);
            $value = parent::getValue($field, $storeId);
        }

        return $value;
    }
}
