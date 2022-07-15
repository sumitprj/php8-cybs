<?php

namespace CyberSource\PayPal\Gateway\Request;

use CyberSource\PayPal\Helper\RequestDataBuilder;
use CyberSource\PayPal\Model\Express\Checkout;
use Magento\Payment\Gateway\Request\BuilderInterface;
use \Magento\Payment\Gateway\Helper\SubjectReader;

class VaultSaleRequestBuilder implements BuilderInterface
{
    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @param RequestDataBuilder $requestDataBuilder
     */
    public function __construct(RequestDataBuilder $requestDataBuilder)
    {
        $this->requestDataBuilder = $requestDataBuilder;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();

        if (!$payment instanceof \Magento\Sales\Model\Order\Payment) {
            return [];
        }

        $vaultPaymentToken = $payment->getExtensionAttributes()->getVaultPaymentToken();

        $amount = SubjectReader::readAmount($buildSubject);

        $request = $this->requestDataBuilder->buildVaultSaleService(
            $vaultPaymentToken->getGatewayToken(),
            $amount,
            $order->getOrderIncrementId(),
            $order->getCurrencyCode()
        );

        if ($billTo = $this->buildAddress($order->getBillingAddress())) {
            $request->billTo = $billTo;
        }

        return (array) $request;
    }

    /**
     * @param $address \Magento\Payment\Gateway\Data\AddressAdapterInterface
     *
     * @return \stdClass|null
     */
    private function buildAddress($address = null)
    {
        if (!$address) {
            return null;
        }

        $addressRequest = new \stdClass();
        $addressRequest->city =  $address->getCity();
        $addressRequest->country = $address->getCountryId();
        $addressRequest->postalCode = $address->getPostcode();
        $addressRequest->state = $address->getRegionCode();
        $addressRequest->street1 = $address->getStreetLine1();
        $addressRequest->email = $address->getEmail();
        $addressRequest->firstName = $address->getFirstname();
        $addressRequest->lastName = $address->getLastname();
        $addressRequest->phoneNumber = $address->getTelephone();

        return $addressRequest;
    }
}
