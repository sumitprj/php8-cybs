<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */
namespace CyberSource\VisaCheckout\Controller\Index;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\Controller\ResultFactory;

/**
 * Visacheckout Save Tokens Controller
 */
class SaveTokens extends AppAction
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Url\Helper
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var Checkout
     */
    protected $checkout;

    /**
     * @var CheckoutHelper
     */
    protected $_checkoutHelper;

    /**
     * @var HttpContext
     */
    protected $_httpContext;

    /**
     * @var Url
     */
    protected $_url;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \CyberSource\VisaCheckout\Model\Checkout $checkout
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \CyberSource\VisaCheckout\Model\Checkout $checkout,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlHelper = $urlHelper;
        $this->_customerUrl = $customerUrl;
        $this->checkout = $checkout;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_httpContext = $httpContext;
        $this->_url = $context->getUrl();
        parent::__construct($context);
    }

    /**
     * Review order after returning from VisaCheckout
     *
     * @return void|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        if (!$this->_checkoutHelper->isAllowedGuestCheckout($this->_getQuote())) {
            if (!$this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
                return $response->setData(['success' => true, 'login_url' => $this->getLoginUrl()]);
            }
        }
        try {
            $request = $this->getRequest();
            $this->checkout->saveVcTokens($request->getParam('callId'));

            if ($this->_getQuote()->getId()) {
                $this->_getCheckoutSession()->setQuoteId($this->_getQuote()->getId());
            }
            
            return $response->setData(['success' => true]);
            
        } catch (\Magento\Payment\Gateway\Command\CommandException $e) {
            $this->handleError($response, $e, $e->getMessage());

        } catch (\Exception $e) {
            $this->handleError($response, $e, __('Unable to process Visa Checkout tokens. Try again later.'));
        }
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Returns login url parameter for redirect
     * @return string
     */
    public function getLoginUrl()
    {
        $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('checkout/cart'));
        return $this->_urlHelper->addRequestParam($this->_customerUrl->getLoginUrl(), ['context' => 'checkout']);
    }
}
