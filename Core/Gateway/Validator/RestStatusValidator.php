<?php

namespace CyberSource\Core\Gateway\Validator;

use CyberSource\Core\Model\Logger;

/**
 * Validates the status when HTTP Response Code is 201 for the transaction
 */
class RestStatusValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{

    const REJECT_PAYMENT_STATUS = ['INVALID_REQUEST', 'DECLINED', 'SERVER_ERROR'];
    /**
     * @var \CyberSource\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \CyberSource\Core\Gateway\Helper\SubjectReader $subjectReader,
        Logger $logger
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject)
    {
        $result = $this->subjectReader->readResponse($validationSubject);

        if($result['http_code'] == RestResponseCodeValidator::RESPONSE_CODE_CREATED){
            if(in_array($result['status'], self::REJECT_PAYMENT_STATUS)){
                $error_message = __("Sorry your order could not be processed at this time. Reason is %1", $result['errorInformation']['reason']);
                $log = __("Transaction is declined due to %1", $result['errorInformation']['reason']);
                $this->logger->critical($log);
                return $this->createResult(false, [$error_message]);
            }
        }
        return $this->createResult(true);
    }
}
