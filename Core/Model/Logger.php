<?php

namespace CyberSource\Core\Model;

use Magento\Framework\Logger\MonologFactory;

class Logger implements LoggerInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    private $logger;

    /**
     * @param MonologFactory $loggerFactory
     * @param Config $config
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        MonologFactory $loggerFactory,
        Config $config,
        $name,
        $handlers = [],
        $processors = []
    ) {

        $this->logger = $loggerFactory->create([
            'name' => $name,
            'handlers' => $handlers,
            'processors' => $processors,
        ]);

        $this->config = $config;
    }

    /**
     * @param array|string $data
     * @param array $context
     */
    public function debug($data, array $context = [])
    {
        if (! $this->config->getDebugMode()) {
            return;
        }

        if (is_array($data)) {
            $data = var_export($data, true);
        }

        $this->logger->debug($data, $context);
    }

    /**
     * @inheritdoc
     */
    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        if ($level == \Psr\Log\LogLevel::DEBUG) {
            $this->debug($message, $context);
            return;
        }

        $this->logger->log($level, $message, $context);
    }
}
