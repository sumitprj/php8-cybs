<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */
namespace CyberSource\VisaCheckout\Controller\Index;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\Controller\ResultFactory;
use CyberSource\VisaCheckout\Controller\Index\SaveTokens;

/**
 * Visacheckout Review Controller
 */
class Review extends SaveTokens
{

    /**
     * @var \CyberSource\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    private $paymentFailureRouteProvider;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \CyberSource\VisaCheckout\Model\Checkout $checkout
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \CyberSource\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider
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
        \CyberSource\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $urlHelper,
            $customerUrl,
            $checkout,
            $checkoutHelper,
            $httpContext
        );
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
    }

    /**
     * Review order after returning from Visacheckout
     *
     * @return void|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_initCheckout();
            if (!$this->_checkoutHelper->isAllowedGuestCheckout($this->_getQuote())) {
                if (!$this->_httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
                    $this->redirectLogin();
                    return;
                }
            }
            $this->_view->loadLayout();
            $reviewBlock = $this->_view->getLayout()->getBlock('visacheckout.review');
            $reviewBlock->setQuote($this->_getQuote());
            $reviewBlock->getChildBlock('details')->setQuote($this->_getQuote());
            if ($reviewBlock->getChildBlock('shipping_method')) {
                $reviewBlock->getChildBlock('shipping_method')->setQuote($this->_getQuote());
            }
            $this->_view->renderLayout();
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t initialize Visa Checkout review.')
            );
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->paymentFailureRouteProvider->getFailureRoutePath());
    }

    /**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initCheckout()
    {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t initialize Visa Checkout.'));
        }
    }

    /**
     * Returns login url parameter for redirect
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Redirect to login page
     *
     * @return void
     */
    public function redirectLogin()
    {
        $this->_actionFlag->set('', 'no-dispatch', true);
        $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('checkout/cart'));
        $this->getResponse()->setRedirect(
            $this->_urlHelper->addRequestParam($this->_customerUrl->getLoginUrl(), ['context' => 'checkout'])
        );
    }
}
