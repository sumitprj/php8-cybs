<?php
/**
 *
 */

namespace CyberSource\ThreeDSecure\Gateway\Command\Cca;

class CreateToken implements \Magento\Payment\Gateway\CommandInterface
{

    const COMMAND_NAME = 'createToken';
    const KEY_PAYER_AUTH_ENROLL_REFERENCE_ID = 'payer_auth_enroll_reference_id';

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var \Magento\Payment\Gateway\Command\Result\ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var \CyberSource\ThreeDSecure\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \CyberSource\Core\Model\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Payment\Gateway\CommandInterface
     */
    private $subscriptionRetrieveCommand;

    /**
     * @var \CyberSource\ThreeDSecure\Gateway\Request\Jwt\TokenBuilder
     */
    private $tokenBuilder;

    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Request\BuilderInterface $requestBuilder,
        \Magento\Payment\Gateway\Command\Result\ArrayResultFactory $arrayResultFactory,
        \Magento\Framework\Math\Random $random,
        \CyberSource\ThreeDSecure\Gateway\Config\Config $config,
        \Magento\Payment\Gateway\CommandInterface $subscriptionRetrieveCommand,
        \CyberSource\Core\Model\LoggerInterface $logger,
        \CyberSource\ThreeDSecure\Gateway\Request\Jwt\TokenBuilderInterface $tokenBuilder
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->random = $random;
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
        $this->subscriptionRetrieveCommand = $subscriptionRetrieveCommand;
        $this->tokenBuilder = $tokenBuilder;
    }

    /**
     * Executes command basing on business object
     *
     * @param array $commandSubject
     *
     * @return \Magento\Payment\Gateway\Command\Result\ArrayResult
     */
    public function execute(array $commandSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();

        try {
            $result = $this->subscriptionRetrieveCommand->execute($commandSubject)->get();

            $cardNumber = $result['cardAccountNumber'] ?? null;

            if ($cardType = $result['cardType'] ?? null) {
                $payment->setAdditionalInformation('cardType', $cardType);
            }

            if ($cardNumber) {
                $commandSubject['cardBin'] = substr($cardNumber, 0, 6);
            }

        } catch (\Exception $e) {

        }

        $payload = $this->requestBuilder->build($commandSubject);
        $referenceId = $this->random->getUniqueHash('ref_');


        $token = $this->tokenBuilder->buildToken(
            $referenceId,
            $payload,
            $this->config->getOrgUnitId(),
            $this->config->getApiId(),
            $this->config->getApiKey()
        );

        $payment->setAdditionalInformation(self::KEY_PAYER_AUTH_ENROLL_REFERENCE_ID, $referenceId);

        $this->logger->debug(
            'Generated JWT for referenceId '
            . $referenceId
            . ' '
            . $token
        );

        return $this->createArrayResult($token);
    }

    /**
     * @param array $data
     *
     * @return \Magento\Payment\Gateway\Command\Result\ArrayResult
     */
    private function createArrayResult(string $data)
    {
        return $this->arrayResultFactory->create(['array' => ['token' => $data]]);
    }
}
