<?php

namespace CyberSource\SecureAcceptance\Gateway\Request\Flex;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class GenerateKeyRequest implements BuilderInterface
{
    const ENCRYPTION_TYPE = 'RsaOaep256';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /***
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $storeFullUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);

        $urlComponents = parse_url($storeFullUrl);

        return [
            'encryptionType' => self::ENCRYPTION_TYPE,
            'targetOrigin' => $urlComponents['scheme'] . '://' . $urlComponents['host'],
        ];
    }
}
