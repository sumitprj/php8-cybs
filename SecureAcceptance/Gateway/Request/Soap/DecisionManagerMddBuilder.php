<?php

namespace CyberSource\SecureAcceptance\Gateway\Request\Soap;

class DecisionManagerMddBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $checkoutSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $giftMessageHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory
     */
    private $orderGridCollectionFactory;

    /**
     * DecisionManagerMddBuilder constructor.
     *
     * @param \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Session\SessionManagerInterface $checkoutSession
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollectionFactory
     * @param \Magento\GiftMessage\Helper\Message $giftMessageHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Backend\Model\Auth $auth
     */
    public function __construct(
        \CyberSource\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderGridCollectionFactory,
        \Magento\GiftMessage\Helper\Message $giftMessageHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Backend\Model\Auth $auth
    ) {
        $this->subjectReader = $subjectReader;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->giftMessageHelper = $giftMessageHelper;
        $this->cartRepository = $cartRepository;
        $this->auth = $auth;
        $this->orderGridCollectionFactory = $orderGridCollectionFactory;
    }

    /**
     * Builds DecisionManager MDD fields
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $quote = $this->getQuote($buildSubject);

        $request = [];
        $result = [];

        $result['1'] = (int)$this->customerSession->isLoggedIn();// Registered or Guest Account

        if ($this->customerSession->isLoggedIn()) {

            $orders = $this->getOrders($buildSubject);

            $result['2'] = $this->getAccountCreationDate(); // Account Creation Date

            $result['3'] = $orders->getSize(); // Purchase History Count

            if ($orders->getSize() > 0) {
                $result['4'] = $orders->getFirstItem()->getCreatedAt(); // Last Order Date
            }

            $result['5'] = $this->getAccountAge();// Member Account Age (Days)
        }

        $result['6'] = $this->isRepeatCustomer($order->getBillingAddress()->getEmail()); // Repeat Customer
        $result['20'] = $quote->getCouponCode(); //Coupon Code

        $result['21'] = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount(); // Discount

        $result['22'] = $this->getGiftMessage($buildSubject); // Gift Message

        $result['23'] = ($this->auth->isLoggedIn()) ? 'call center' : 'web'; //order source

        if (!$quote->getIsVirtual()) {
            if ($shippingAddress = $quote->getShippingAddress()) {
                $result['31'] = $quote->getShippingAddress()->getShippingMethod();
                $result['32'] = $quote->getShippingAddress()->getShippingDescription();
            }
        }

        foreach ($result as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $request['merchantDefinedData']['mddField'][] = [
                '_' => $value,
                'id' => $key,
            ];
        }

        if ($fingerPrintId = $this->checkoutSession->getFingerprintId()) {
            $request['deviceFingerprintID'] = $fingerPrintId;
            $request['deviceFingerprintRaw'] = true;
        }

        $request['billTo']['customerID'] = $order->getCustomerId();
        $request['billTo']['ipAddress'] = $order->getRemoteIp();

        return $request;
    }

    private function getQuote($buildSubject)
    {
        if ($quote = $buildSubject['quote'] ?? null) {
            return $quote;
        }

        // $this->checkoutSession->getQuote() method has a side effect of setting customerId from a session
        // to the quote that breaks a compatibility with persistent shopping cart feature
        return $this->cartRepository->get($this->checkoutSession->getQuoteId());
    }

    private function getOrders($buildSubject)
    {
        $field = 'customer_email';
        $value = $this->getQuote($buildSubject)->getCustomerEmail();
        if ($this->customerSession->isLoggedIn()) {
            $field = 'customer_id';
            $value = $this->customerSession->getCustomerId();
        }
        return $this->orderCollectionFactory->create()
            ->addFieldToFilter($field, $value)
            ->setOrder('created_at', 'desc');
    }

    private function isRepeatCustomer($customerEmail)
    {
        $orders = $this->orderGridCollectionFactory->create()
            ->addFieldToFilter('customer_email', $customerEmail)
        ;

        $orders->getSelect()->limit(1);

        return (int)($orders->getSize() > 0);
    }

    private function getAccountCreationDate()
    {
        return $this->customerSession->getCustomerData()->getCreatedAt();
    }

    private function getAccountAge()
    {
        return round((time() - strtotime($this->customerSession->getCustomerData()->getCreatedAt())) / (3600 * 24));
    }

    private function getGiftMessage($buildSubject)
    {
        $message = $this->giftMessageHelper->getGiftMessage($this->getQuote($buildSubject)->getGiftMessageId());
        return $message->getMessage() ? $message->getMessage() : '';
    }
}
