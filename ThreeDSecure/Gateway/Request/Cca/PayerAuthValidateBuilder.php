<?php

namespace CyberSource\ThreeDSecure\Gateway\Request\Cca;

class PayerAuthValidateBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    const COMMAND_PROCESS_TOKEN = 'processToken';

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
    ) {
        $this->subjectReader = $subjectReader;
        $this->commandPool = $commandPool;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $commandResult = $this->commandPool->get(self::COMMAND_PROCESS_TOKEN)->execute($buildSubject);

        $resultArray = $commandResult->get();

        /** @var \Lcobucci\JWT\Token|\Lcobucci\JWT\Token\Plain $parsedToken */
        $parsedToken = $resultArray['parsedToken'];
        $payload = (array) $parsedToken->claims()->get('Payload');
        $payment = (array)$payload['Payment'];
        $processorTransactionId = $payment['ProcessorTransactionId'];
        return [
            'payerAuthValidateService' => [
                'run' => 'true',
                'authenticationTransactionID'=> $processorTransactionId,
            ]
        ];
    }
}
