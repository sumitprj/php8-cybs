<?php
/**
 *
 */

namespace CyberSource\ThreeDSecure\Gateway\Command\Cca;

class ProcessToken implements \Magento\Payment\Gateway\CommandInterface
{

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \CyberSource\SecureAcceptance\Model\Jwt\JwtHelper
     */
    private $jwtHelper;

    /**
     * @var \Magento\Payment\Gateway\Validator\ValidatorInterface
     */
    private $validator;
    /**
     * @var \Magento\Payment\Gateway\Command\Result\ArrayResultFactory
     */
    private $arrayResultFactory;

    /**
     * @var \CyberSource\Core\Model\LoggerInterface
     */
    private $logger;

    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Validator\ValidatorInterface $validator,
        \Magento\Payment\Gateway\Command\Result\ArrayResultFactory $arrayResultFactory,
        \CyberSource\SecureAcceptance\Model\Jwt\JwtHelper $jwtHelper,
        \CyberSource\Core\Model\LoggerInterface $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->jwtHelper = $jwtHelper;
        $this->validator = $validator;
        $this->arrayResultFactory = $arrayResultFactory;
        $this->logger = $logger;
    }

    /**
     * Decodes and validates response jwt
     *
     * @param array $commandSubject
     * @return null|\Magento\Payment\Gateway\Command\ResultInterface
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {

        $paymentDo = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Quote\Api\Data\PaymentInterface $payment */
        $payment = $paymentDo->getPayment();

        if (!$payment->getExtensionAttributes() || !$payment->getExtensionAttributes()->getCcaResponse()) {
            throw new \InvalidArgumentException('Token must be provided');
        }

        $ccaResponse = $payment->getExtensionAttributes()->getCcaResponse();
        $parsedToken = $this->jwtHelper->parse($ccaResponse);

        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $parsedToken])
            );
            if (!$result->isValid()) {
                $this->processErrors($result);
            }
        }

        $this->logger->debug('Received JWT: ' . $ccaResponse);
        $this->logger->debug('JWT Payload:' . var_export((array)$parsedToken->claims()->get('Payload'), true));

        return $this->arrayResultFactory->create([
                'array' => [
                    'token' => $ccaResponse,
                    'parsedToken' => $parsedToken
                ]
            ]);
    }

    /**
     * @param \Magento\Payment\Gateway\Validator\ResultInterface $result
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    private function processErrors(\Magento\Payment\Gateway\Validator\ResultInterface $result)
    {

        $messages = [];

        foreach ($result->getFailsDescription() as $message) {
            $messages[] = $message;
        }

        throw new \Magento\Payment\Gateway\Command\CommandException(
            (empty($messages)) ? __('Invalid CCA response') : $this->formatMessages($messages)
        );
    }

    private function formatMessages(array $messages)
    {
        return __(
            implode(
                \PHP_EOL,
                array_map(
                    function ($text) {
                        return __($text);
                    },
                    $messages
                )
            )
        );
    }
}
