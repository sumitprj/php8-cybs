<?php

namespace CyberSource\VisaCheckout\Helper;

use CyberSource\VisaCheckout\Gateway\Validator\ResponseCodeValidator;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObjectFactory;

class ParseGetDataHelper extends AbstractHelper
{
    const CARD_ACCOUNT_NUMBER = "accountNumber";
    const CARD_TYPE = "cardType";
    const CARD_EXP_MONTH = "expirationMonth";
    const CARD_EXP_YEAR = "expirationYear";
    const VISA_CUSTOMER_FIRSTNAME = 'vcAccountFirstName';
    const VISA_CUSTOMER_LASTNAME = 'vcAccountLastName';
    const VISA_CUSTOMER_EMAIL = 'vcAccountEmail';

    const CC_MASK = '****';

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var array
     */
    private $cardTypes = [
        "AMEX" => "003",
        "DISCOVER" => "004",
        "MASTERCARD" => "002",
        "VISA" => "001",
    ];

    /**
     * Map for billing address import/export
     *
     * @var array
     */
    protected $visaAddressMap = [
        'country' => 'country_id', // iso-3166 two-character code
        'state' => 'region',
        'city' => 'city',
        'street1' => 'street',
        'postalCode' => 'postcode'
    ];

    /**
     * ParseGetDataHelper constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        DataObjectFactory $dataObjectFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        parent::__construct($context);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param array $visaAddress
     * @return \Magento\Framework\DataObject
     */
    public function convertVisaCheckoutAddressToAddress($visaAddress)
    {
        $address = $this->dataObjectFactory->create();
        \Magento\Framework\DataObject\Mapper::accumulateByMap((array) $visaAddress, $address, $this->visaAddressMap);
        $address->setExportedKeys(array_values($this->visaAddressMap));

        // attempt to fetch region_id from directory
        if ($address->getCountryId() && $address->getRegion()) {
            $regions = $this->countryFactory->create()->loadByCode(
                $address->getCountryId()
            )->getRegionCollection()->addRegionCodeOrNameFilter(
                $address->getRegion()
            )->setPageSize(
                1
            );
            foreach ($regions as $region) {
                $address->setRegionId($region->getId());
                $address->setExportedKeys(array_merge($address->getExportedKeys(), ['region_id']));
                break;
            }
        }

        return $address;
    }

    public function parseVisaAddress(\Magento\Quote\Model\Quote\Address $quoteAddress, $exportedAddress, $additionalInfo)
    {
        $billingAddress = clone $quoteAddress;
        $billingAddress->unsAddressId()->setCustomerAddressId(null);
        $billingAddress->setSaveInAddressBook(0);
        $this->_setExportedAddressData($billingAddress, $exportedAddress);
        $billingAddress->setPrefix(null);
        $billingAddress->setFirstname($additionalInfo->{self::VISA_CUSTOMER_FIRSTNAME});
        $billingAddress->setLastname($additionalInfo->{self::VISA_CUSTOMER_LASTNAME});
        $billingAddress->setEmail($additionalInfo->{self::VISA_CUSTOMER_EMAIL});

        return $billingAddress;
    }

    /**
     * Sets address data from exported address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $exportedAddress
     * @return void
     */
    protected function _setExportedAddressData(\Magento\Quote\Model\Quote\Address $address, $exportedAddress)
    {
        foreach ($exportedAddress->getExportedKeys() as $key) {
            $data = $exportedAddress->getData($key);
            if (!empty($data)) {
                $address->setDataUsingMethod($key, $data);
            }
        }
    }


    /**
     * Save information from decrypted VC data into payment additional information
     *
     * @param $responseDecrypted decrypted response array
     * @param \Magento\Quote\Model\Quote\Payment $payment payment model
     * @throws LocalizedException
     */
    public function setCreditCardData($responseDecrypted, \Magento\Quote\Model\Quote\Payment $payment)
    {
        $payment->setAdditionalInformation(
            self::CARD_ACCOUNT_NUMBER,
            self::CC_MASK . '-' . substr($responseDecrypted['card']->{self::CARD_ACCOUNT_NUMBER}, -4)
        );
        $payment->setAdditionalInformation(self::CARD_EXP_MONTH, $responseDecrypted['card']->{self::CARD_EXP_MONTH});
        $payment->setAdditionalInformation(self::CARD_EXP_YEAR, $responseDecrypted['card']->{self::CARD_EXP_YEAR});

        foreach ($responseDecrypted['vcReply'] as $key => $value) {
            $payment->setAdditionalInformation($key, $value);
        }

        $payment->setAdditionalInformation(self::CARD_TYPE, $this->getCardTypeCodeByName($responseDecrypted['vcReply']->{self::CARD_TYPE}));
    }

    private function getCardTypeCodeByName($name)
    {
        return $this->cardTypes[$name];
    }
}
