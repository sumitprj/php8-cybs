<?php

namespace CyberSource\Recaptcha\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use CyberSource\Recaptcha\Api\ValidateInterface;
use CyberSource\Recaptcha\Model\IsCheckRequiredInterface;
use CyberSource\Recaptcha\Model\Provider\ResponseProviderInterface;

class OrderPlaceFlexafter implements ObserverInterface
{
    
    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;
   /**
   * Recipient email config path
   */
  const PATH_CHECKOUT_FLOW_TYPE = 'payment/chcybersource/secureacceptance_type';

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var ResponseProviderInterface
     */
    private $responseProvider;

    /**
     * @var IsCheckRequiredInterface
     */
    private $isCheckRequired;

    /**
     * @param ResponseProviderInterface $responseProvider
     * @param ValidateInterface $validate
     * @param RemoteAddress $remoteAddress
     * @param IsCheckRequiredInterface $isCheckRequired
     */
    public function __construct(
        ResponseProviderInterface $responseProvider,
        ValidateInterface $validate,
        RemoteAddress $remoteAddress,
        IsCheckRequiredInterface $isCheckRequired,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->responseProvider = $responseProvider;
        $this->validate = $validate;
        $this->remoteAddress = $remoteAddress;
        $this->isCheckRequired = $isCheckRequired;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
       
        $checkoutFlowTypeConfig = $this->getCheckoutFlowtype();
            if ($this->isCheckRequired->execute() && $checkoutFlowTypeConfig == "flex") {
                $reCaptchaResponse = $this->responseProvider->execute();
                $remoteIp = $this->remoteAddress->getRemoteAddress();
                $this->validate->validate($reCaptchaResponse, $remoteIp); 
            }
    }

    /** 
    * function returning Checkout flow type config value
    **/
    public function getCheckoutFlowtype() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $checkoutFlowTypeConfig = $this->scopeConfig->getValue(self::PATH_CHECKOUT_FLOW_TYPE, $storeScope);
    }
    
}
