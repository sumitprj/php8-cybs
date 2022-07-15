<?php

namespace CyberSource\ThreeDSecure\Gateway\Request\Cca;
use CyberSource\ThreeDSecure\Gateway\Validator\PaEnrolledValidator;
use Magento\Vault\Model\Ui\VaultConfigProvider;

class PayerAuthEnrollBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    const TRANSACTION_MODE_ECOMMERCE = 'S';
    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var \CyberSource\SecureAcceptance\Model\PaymentTokenManagement
     */
    private $paymentTokenManagement;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \CyberSource\SecureAcceptance\Model\PaymentTokenManagement $paymentTokenManagement,
        \CyberSource\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->request = $request;
        $this-> config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        if (!$referenceId = $payment->getAdditionalInformation(
            \CyberSource\ThreeDSecure\Gateway\Command\Cca\CreateToken::KEY_PAYER_AUTH_ENROLL_REFERENCE_ID
        )
        ) {
            throw new \InvalidArgumentException('3D Secure initialization is required. Reload the page and try again.');
        }

        $requestArr = [
            'payerAuthEnrollService' => [
                'run' => 'true',
                'mobilePhone' => $order->getBillingAddress()->getTelephone() ?? '',
                'referenceID' => $referenceId,
                'transactionMode' => self::TRANSACTION_MODE_ECOMMERCE,
                'httpAccept' => $this->request->getHeader('accept'),
                'httpUserAgent' => $this->request->getHeader('user-agent')
            ],
        ];

        if($this->isScaRequired($payment)){
            $requestArr['payerAuthEnrollService']['paChallengeCode'] = '04';
        }

        return $requestArr;
    }

    /**
     * Returns whether Strong Customer Authentication is required or not
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return boolean
     */
    private function isScaRequired($payment){
        $result = false;
        $storeId = $payment->getOrder()->getStoreId();
        if($payment->getAdditionalInformation(VaultConfigProvider::IS_ACTIVE_CODE) &&($this->config->isScaEnforcedOnCardSaveSoap($storeId))){
            $result = true;
        }
        
        if($payment->getAdditionalInformation(PaEnrolledValidator::KEY_SCA_REQUIRED)){
            $result = true;
            $payment->unsAdditionalInformation(PaEnrolledValidator::KEY_SCA_REQUIRED);
        }

        return $result;
    }
}