<?php
/**
 * Copyright © 2018 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\ECheck\Cron;

use CyberSource\Core\Model\LoggerInterface;
use Magento\Sales\Model\Order;

class Status
{
    const EVENT_TYPE_COLUMN = 4;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \CyberSource\ECheck\Gateway\Config\Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory
     */
    protected $paymentCollectionFactory;

   /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    private $scopeConfig;
    
    /**
     *
     * @var \Magento\Framework\DataObject
     */
    private $postObject;
    
    /**
     * @var  \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;
    
   /**
    * @var \Magento\Framework\HTTP\Client\Curl
    */
    private $curl;

    /**
     * @var \Magento\Payment\Gateway\CommandInterface
     */
    private $command;

    /**
     * Status constructor.
     * @param LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \CyberSource\ECheck\Gateway\Config\Config $config,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DataObject $postObject,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Payment\Gateway\CommandInterface $command,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->orderRepository = $orderRepository;
        $this->config = $config;
        $this->logger = $logger;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->postObject = $postObject;
        $this->transportBuilder = $transportBuilder;
        $this->curl = $curl;
        $this->command = $command;
    }

    public function execute()
    {
        if (!(bool)(int)$this->scopeConfig->getValue(
            "payment/cybersourceecheck/active",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return $this;
        }

        try {
            $reportData = $this->getReportData();
            $paymentCollection = $this->paymentCollectionFactory->create();
            $paymentCollection->getSelect()->joinleft(
                ['order_table' => $paymentCollection->getTable('sales_order')],
                'main_table.parent_id = order_table.entity_id',
                ['status', 'quote_id']
            );

            $paymentCollection->addFieldToFilter('order_table.status', 'payment_review');
            $paymentCollection->addFieldToFilter('main_table.method', 'cybersourceecheck');
            $paymentCollection->addFieldToFilter('main_table.last_trans_id', ['in' => array_keys($reportData)]);
            $paymentCollection->load();
            foreach ($paymentCollection as $payment) {
                $this->updateOrder($reportData[$payment->getLastTransId()][self::EVENT_TYPE_COLUMN], $payment->getOrder());

            }
        } catch (\Exception $e) {
            $this->logger->error("ECheck: " . $e->getMessage());
        }

        return $this;
    }
    
    private function updateOrder($eventType, $order)
    {
        /** @var \Magento\Sales\Model\Order $order */

        $updateStatus = true;
        if (!in_array($eventType, $this->config->getAcceptEventType())
            && !in_array($eventType, $this->config->getRejectEventType())
            && !in_array($eventType, $this->config->getPendingEventType())
        ) {

            $this->sendEmail($order, $eventType, 'cybersource_echeck_unknown');
            $updateStatus = false;
        }
        $inCounter = 0;
        if ($updateStatus && in_array($eventType, $this->config->getAcceptEventType())) {
            $inCounter++;
        }
        if ($updateStatus && in_array($eventType, $this->config->getRejectEventType())) {
            $inCounter++;
        }
        if ($updateStatus && $inCounter > 1) {

            $this->sendEmail($order, $eventType, 'cybersource_echeck_multi');
            $updateStatus = false;
        }
        if ($updateStatus && in_array($eventType, $this->config->getAcceptEventType())) {
            $order->getPayment()->accept();
            $this->orderRepository->save($order);
        }
        if ($updateStatus && in_array($eventType, $this->config->getRejectEventType())) {
            $order->getPayment()->deny();
            $this->orderRepository->save($order);
        }
    }
    
    public function sendEmail($order, $eventType, $templateId = 'cybersource_echeck_unknown')
    {
        $emailTemplateVariables = [];
        $emailTempVariables = ['order' => $order, 'event_type' => $eventType];
        $sender = $this->scopeConfig->getValue(
            "payment/chcybersource/dm_fail_sender",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $senderName = $this->scopeConfig->getValue(
            "trans_email/ident_".$sender."/name",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $senderEmail = $this->scopeConfig->getValue(
            "trans_email/ident_".$sender."/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $email = $this->scopeConfig->getValue(
            "trans_email/ident_general/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->postObject->setData($emailTempVariables);
        $sender = [
            'name' => $senderName,
            'email' => $senderEmail,
        ];
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
        ->setTemplateOptions([
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ])
        ->setTemplateVars(['data' => $this->postObject])
        ->setFrom($sender)
        ->addTo($email)
        ->setReplyTo($senderEmail)
        ->getTransport();
        $transport->sendMessage();
    }
    
    private function getReportData()
    {
        $data = [];
        if ($this->config->isTestMode()) {

            $paymentCollection = $this->paymentCollectionFactory->create();
            $paymentCollection->addFieldToFilter('main_table.method', 'cybersourceecheck');
            $paymentCollection->addFieldToFilter('order_table.status', 'payment_review');
            $paymentCollection->getSelect()->joinleft(
                ['order_table' => $paymentCollection->getTable('sales_order')],
                'main_table.parent_id = order_table.entity_id',
                ['status', 'quote_id']
            );
            $paymentCollection->load();
            foreach ($paymentCollection as $payment) {
                $data[$payment->getLastTransId()] = [
                    0 => $payment->getLastTransId(),
                    self::EVENT_TYPE_COLUMN => $this->scopeConfig->getValue(
                        "payment/cybersourceecheck/test_event_type",
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                ];
            }

            return $data;
        }

        $reportUrl = $this->scopeConfig->getValue(
            "payment/cybersourceecheck/service_url",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $merchantId = $this->scopeConfig->getValue(
            "payment/chcybersource/merchant_id",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $username = $this->scopeConfig->getValue(
            "payment/cybersourceecheck/merchant_username",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $password = $this->scopeConfig->getValue(
            "payment/cybersourceecheck/merchant_password",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $period = (int)$this->scopeConfig->getValue(
            "payment/cybersourceecheck/report_check_period",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->curl->setCredentials($username, $password);
        for ($i = 1; $i <= $period; $i++) {
            $reportDate = date('Y/m/d', strtotime('-' . $i . ' day'));
            $this->curl->get($reportUrl . '/DownloadReport/' . $reportDate . '/' . $merchantId . '/PaymentEventsReport.csv');
            $data = $this->parseCsvFile($this->curl->getBody(), $data);
        }

        return $data;
    }
    
    /**
     * Expected CSV format
     * 0 request_id,
     * 1 merchant_id,
     * 2 merchant_ref_number,
     * 3 payment_type,event_type,
     * 4 event_date,
     * 5 trans_ref_no,
     * 6 merchant_currency_code,
     * 7 merchant_amount,
     * 8 consumer_currency_code,
     * 9 consumer_amount,
     * 10 fee_currency_code,
     * 11 fee_amount,processor_message
     *
     * @param string $response
     * @param array $data
     * @return array
     */
    private function parseCsvFile($response, $data)
    {
        if (preg_match('/^Payment Events Report/', $response)) {

            $lines = explode("\n", $response);
            for ($j = 2; $j < count($lines); $j++) {
                if (!empty($lines[$j])) {
                    $fileData = explode(",", $lines[$j]);
                    $data[$fileData[0]] = $fileData;
                }
            }
        }
        return $data;
    }
}
