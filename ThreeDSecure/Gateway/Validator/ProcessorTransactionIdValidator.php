<?php
/**
 *
 */

namespace CyberSource\ThreeDSecure\Gateway\Validator;

class ProcessorTransactionIdValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{


    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * Validates that received JWT matches previously initialized session
     *
     * @param array $validationSubject
     *
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {

        /** @var \Lcobucci\JWT\Token $response */
        $response = $validationSubject['response'] ?? null;

        if (!$response) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        $payload = (array) $response->claims()->get('Payload');
        if (!$payload) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        $jwtPayment = (array) $payload['Payment'] ?? null;

        if (!$jwtPayment || !($jwtPayment['ProcessorTransactionId'] ?? null)) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        $paymentDo = $this->subjectReader->readPayment($validationSubject);
        $payment = $paymentDo->getPayment();

        $transactionId = $payment->getAdditionalInformation(\CyberSource\ThreeDSecure\Gateway\Validator\PaEnrolledValidator::KEY_PAYER_AUTH_ENROLL_TRANSACTION_ID);

        if ($transactionId !== $jwtPayment['ProcessorTransactionId']) {
            return $this->createResult(false, [__('Invalid CCA response')]);
        }

        return $this->createResult(true, []);
    }
}
