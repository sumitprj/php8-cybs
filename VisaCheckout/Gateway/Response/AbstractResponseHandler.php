<?php

namespace CyberSource\VisaCheckout\Gateway\Response;

use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;
use CyberSource\VisaCheckout\Gateway\Validator\ResponseCodeValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractResponseHandler
{
    const REQUEST_ID = "requestID";
    const REASON_CODE = "reasonCode";
    const DECISION = "decision";
    const MERCHANT_REFERENCE_CODE = "merchantReferenceCode";
    const REQUEST_TOKEN = "requestToken";
    const RECONCILIATION_ID = "reconciliationID";
    const CALL_ID = "callID";
    const CURRENCY = "currency";
    const AMOUNT = "amount";

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractResponseHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return \Magento\Payment\Model\InfoInterface
     */
    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }

    protected function handleAuthorizeResponse($payment, $response)
    {
        // Cleanup additional_information
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $callId = $payment->getAdditionalInformation("callId");
        $accountNumber = $payment->getAdditionalInformation("accountNumber");
        $cardType = $payment->getAdditionalInformation("cardType");
        $expMonth = $payment->getAdditionalInformation("expirationMonth");
        $expYear = $payment->getAdditionalInformation("expirationYear");

        $payment->unsAdditionalInformation();

        $payment->setTransactionId($response[self::REQUEST_ID]);
        $payment->setCcTransId($response[self::REQUEST_ID]);
        $payment->setAdditionalInformation(self::REQUEST_ID, $response[self::REQUEST_ID]);
        $payment->setAdditionalInformation(self::CURRENCY, $response['purchaseTotals']->{self::CURRENCY});
        $payment->setAdditionalInformation(self::AMOUNT, $response['ccAuthReply']->{self::AMOUNT});
        $payment->setAdditionalInformation(self::CALL_ID, $callId);
        $payment->setAdditionalInformation("accountNumber", $accountNumber);
        $payment->setAdditionalInformation("cardType", $cardType);
        $payment->setAdditionalInformation("expirationMonth", $expMonth);
        $payment->setAdditionalInformation("expirationYear", $expYear);
        $payment->setAdditionalInformation(self::REQUEST_TOKEN, $response[self::REQUEST_TOKEN]);
        $payment->setAdditionalInformation(self::REASON_CODE, $response[self::REASON_CODE]);
        $payment->setAdditionalInformation(self::DECISION, $response[self::DECISION]);
        $payment->setAdditionalInformation(self::MERCHANT_REFERENCE_CODE, $response[self::MERCHANT_REFERENCE_CODE]);
        $payment->setAdditionalInformation(self::RECONCILIATION_ID, $response['ccAuthReply']->{self::RECONCILIATION_ID});

        return $payment;
    }
}
