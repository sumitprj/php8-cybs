<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Model;

/**
 * Class Checkout
 */
class Checkout
{
    const PARAM_CALL_ID = 'callId';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var SoapClient
     */
    private $soapClient;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var GetDataHelper
     */
    private $getDataHelper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Coreconfig
     */
    private $coreconfig;

     /**
      * @var Quote
      */
    private $quote;

    /**
     * @var \Magento\Checkout\Model\Session
     * @var \CyberSource\VisaCheckout\Helper\RequestDataBuilder
     * @var \CyberSource\VisaCheckout\Gateway\Http\Client\SOAPClient
     * @var \CyberSource\VisaCheckout\Gateway\Http\TransferFactory
     * @var \CyberSource\VisaCheckout\Helper\ParseGetDataHelper
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @var \CyberSource\Core\Model\Config
     * @var \Psr\Log\LoggerInterface
     */

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \CyberSource\VisaCheckout\Helper\RequestDataBuilder $requestDataBuilder,
        \CyberSource\VisaCheckout\Gateway\Http\Client\SOAPClient $soapClient,
        \CyberSource\VisaCheckout\Gateway\Http\TransferFactory $transferFactory,
        \CyberSource\VisaCheckout\Helper\ParseGetDataHelper $getDataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \CyberSource\Core\Model\Config $coreconfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->soapClient = $soapClient;
        $this->transferFactory = $transferFactory;
        $this->getDataHelper = $getDataHelper;
        $this->quoteRepository = $quoteRepository;
        $this->coreconfig = $coreconfig;
        $this->logger = $logger;
        $this->quote = $this->checkoutSession->getQuote();
    }

    /**
     * Save VC tokens
     * @param $callId
     */
    public function saveVcTokens($callId)
    {

        $payment = $this->quote->getPayment();

        if (!$payment->getQuote()) {
            $payment->setQuote($this->quote);
        }

        $this->quote->reserveOrderId();

        $payment->importData(['method' => \CyberSource\VisaCheckout\Model\Ui\ConfigProvider::CODE]);
        $payment->setAdditionalInformation(self::PARAM_CALL_ID, $callId);

        $requestDecryptData = (array) $this->requestDataBuilder->buildVisaDecryptRequestData(
            $callId,
            $this->quote->getReservedOrderId()
        );
        try {
            $responseDecrypted = $this->soapClient->placeRequest(
                $this->transferFactory->create($requestDecryptData)
            );
            if ($responseDecrypted !== null && $responseDecrypted[\CyberSource\VisaCheckout\Gateway\Validator\ResponseCodeValidator::RESULT_CODE] == 100) {
                $billTo = $this->getDataHelper->convertVisaCheckoutAddressToAddress($responseDecrypted['billTo']);
                $billingAddress = $this->getDataHelper->parseVisaAddress($this->quote->getBillingAddress(), $billTo, $responseDecrypted['vcReply']);
                $shippingAddress = $this->getDataHelper->parseVisaAddress($this->quote->getShippingAddress(), $billTo, $responseDecrypted['vcReply']);
                $this->quote->setBillingAddress($billingAddress);
                $this->quote->setShippingAddress($shippingAddress);
                $this->quote->collectTotals();
                $this->quoteRepository->save($this->quote);
                $shippingAddress = $this->quote->getShippingAddress();
                $shippingAddress->setPrefix(null);
                $shippingAddress->setCollectShippingRates(true);
                $shippingAddress->collectShippingRates()->save();
            }

        } catch (\Exception $e) {
            if ($this->coreconfig->getDebugMode()) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Set shipping method to quote, if needed
     *
     * @param string $methodCode
     * @return void
     */
    public function updateShippingMethod($methodCode)
    {
        $shippingAddress = $this->quote->getShippingAddress();
        if (!$this->quote->getIsVirtual() && $shippingAddress) {
            if ($methodCode != $shippingAddress->getShippingMethod()) {
                $shippingAddress->setShippingMethod($methodCode)->setCollectShippingRates(true);
                $cartExtension = $this->quote->getExtensionAttributes();
                if ($cartExtension && $cartExtension->getShippingAssignments()) {
                    $cartExtension->getShippingAssignments()[0]
                        ->getShipping()
                        ->setMethod($methodCode);
                }
                $this->quote->collectTotals();
                $this->quoteRepository->save($this->quote);
            }
        }
    }
}
