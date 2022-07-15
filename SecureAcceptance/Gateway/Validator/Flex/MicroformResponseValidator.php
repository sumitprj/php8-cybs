<?php

namespace CyberSource\SecureAcceptance\Gateway\Validator\Flex;

use CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader;
use CyberSource\SecureAcceptance\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class MicroformResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var bool
     */
    private $isAdminHtml;

    /**
     * @var SignatureValidator\ValidatorInterface
     */
    private $signatureValidator;

    /**
     * @var \CyberSource\SecureAcceptance\Model\Jwt\JwtProcessorInterface
     */
    private $jwtProcessor;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param \CyberSource\SecureAcceptance\Gateway\Config\Config $config
     * @param SubjectReader $subjectReader
     * @param bool $isAdminHtml
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        \CyberSource\SecureAcceptance\Gateway\Config\Config $config,
        SubjectReader $subjectReader,
        \CyberSource\SecureAcceptance\Model\Jwt\JwtProcessorInterface $jwtProcessor,
        \CyberSource\SecureAcceptance\Gateway\Validator\Flex\SignatureValidator\ValidatorInterface $signatureValidator,
        $isAdminHtml = false
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->isAdminHtml = $isAdminHtml;
        $this->signatureValidator = $signatureValidator;
        $this->jwtProcessor = $jwtProcessor;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {

        $payment = $validationSubject['payment'] ?? null;

        if ($payment && $payment->getMethod() != \CyberSource\SecureAcceptance\Model\Ui\ConfigProvider::CODE) {
            return $this->createResult(
                true
            );
        }

        if (!$this->config->isMicroform() || $this->isAdminHtml) {
            return $this->createResult(
                true
            );
        }

        if (!$payment) {
            return $this->createResult(
                false,
                ['Payment must be provided.']
            );
        }

        if ($payment instanceof \Magento\Quote\Model\Quote\Payment) {
            //we are validating this only for order
            return $this->createResult(
                true
            );
        }

        $jwt = $payment->getAdditionalInformation('flexJwt');
        $microformPublicKey = $payment->getAdditionalInformation('microformPublicKey');;

        $isValid = $this->jwtProcessor->verifySignature($jwt, $microformPublicKey);

        return $this->createResult(
            $isValid,
            $isValid ? [] : ['Invalid token signature.']
        );
    }
}
