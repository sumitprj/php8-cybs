<?php

namespace CyberSource\Core\Gateway\Request\Rest;

use Magento\Payment\Helper\Formatter;

class AddressDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds Address Data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $request = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $request['orderInformation']['billTo'] = $this->buildAddress($order->getBillingAddress());

        if ($order->getShippingAddress()) {
            $request['orderInformation']['shipTo'] = $this->buildAddress($order->getShippingAddress());
        }

        return $request;
    }

    private function buildAddress($address)
    {
        $addressFields =
        [
            'firstName' => $address->getFirstname(),
            'lastName' => $address->getLastname(),
            'company' => $address->getCompany(),
            'email' => $address->getEmail(),
            'locality' => $address->getCity(),
            'administrativeArea' => $address->getRegionCode(),
            'country' => $address->getCountryId(),
            'phoneNumber' => $address->getTelephone(),
            'postalCode' => $address->getPostcode(),
        ];

        if($address instanceof \Magento\Payment\Gateway\Data\AddressAdapterInterface)
		{
            $addressFields['address1'] = $address->getStreetLine1();
            $addressFields['address2'] = $address->getStreetLine2();
        }
        else{
            $addressFields['address1'] = $address->getStreetLine(1);
            $addressFields['address2'] = $address->getStreetLine(2);
        }
        return $addressFields;
    }
}
