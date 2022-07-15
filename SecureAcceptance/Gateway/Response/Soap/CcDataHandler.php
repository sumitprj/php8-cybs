<?php
/**
 *
 */

namespace CyberSource\SecureAcceptance\Gateway\Response\Soap;


class CcDataHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \CyberSource\SecureAcceptance\Helper\RequestDataBuilder
     */
    private $requestDataBuilder;

    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \CyberSource\SecureAcceptance\Gateway\Config\Config $config,
        \CyberSource\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->requestDataBuilder = $requestDataBuilder;
    }


    /**
     * Handles Cc data for flex microform
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!$this->config->isMicroform()) {
            return;
        }

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        if ($payment->getMethod() !== \CyberSource\SecureAcceptance\Model\Ui\ConfigProvider::CODE) {
            return;
        }

        $cardType = $response['card']->cardType ?? '';
        $maskedPan = $payment->getAdditionalInformation('maskedPan');
        $cardNumber = substr($maskedPan, 0, 6) . str_repeat('X', strlen($maskedPan) - 10) . substr($maskedPan, -4);
        $payment->setAdditionalInformation('cardNumber', $cardNumber);
        $payment->setAdditionalInformation('cardType', $cardType);

        if (!$payment instanceof \Magento\Sales\Model\Order\Payment) {
            return;
        }

        $payment->setCcType($this->requestDataBuilder->getCardType($cardType, true));
        $payment->setCcLast4(substr($maskedPan, -4));

        list($expMonth, $expYear) = explode('-', $payment->getAdditionalInformation(\CyberSource\SecureAcceptance\Observer\DataAssignObserver::KEY_EXP_DATE));

        $payment->setCcExpMonth($expMonth ?? null);
        $payment->setCcExpYear($expYear ?? null);


    }
}
