<?php
/**
 *
 */

namespace CyberSource\ThreeDSecure\Controller\Cca;

use CyberSource\SecureAcceptance\Model\Ui\ConfigProvider;
use Magento\Quote\Api\Data\PaymentInterface;

class RequestToken extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\Magento\Checkout\Model\Session
     */
    private $sessionManager;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \CyberSource\Core\Model\LoggerInterface
     */
    private $logger;

    /**
     * RequestToken constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \CyberSource\Core\Model\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \CyberSource\Core\Model\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->commandManager = $commandManager;
        $this->sessionManager = $sessionManager;
        $this->jsonFactory = $jsonFactory;
        $this->cartRepository = $cartRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $resultJson = $this->jsonFactory->create();

        try {
            $quote = $this->sessionManager->getQuote();

            if (!$this->getRequest()->isPost()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Wrong method.'));
            }

            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid formkey.'));
            }

            if (!$quote || !$quote->getId()) {
                throw new \Exception('Quote is not defined');
            }

            $params = ['amount' => $quote->getBaseGrandTotal()];
            $payment = $quote->getPayment();

            $data = [
                PaymentInterface::KEY_METHOD => $payment->getMethod() ?? ConfigProvider::CODE
            ];

            if ($method = $this->getRequest()->getParam('method')) {
                $data[PaymentInterface::KEY_METHOD] = $method;
            };

            if ($additionalData = $this->getRequest()->getParam('additional_data')) {
                unset($additionalData['cvv']);
                $data['additional_data'] = $additionalData;
            };

            $payment->importData($data);

            $tokenResult = $this->commandManager->executeByCode(
                \CyberSource\ThreeDSecure\Gateway\Command\Cca\CreateToken::COMMAND_NAME,
                $quote->getPayment(),
                $params
            );

            $this->cartRepository->save($quote);

            $resultJson->setData(array_merge(['success' => true], $tokenResult->get()));

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $resultJson->setData(['success' => false, 'error_msg' => $e->getMessage()]);
            $this->logger->critical($e->getMessage(), ['exception'=> $e]);
        } catch (\Exception $e) {
            $resultJson->setData(['success' => false, 'error_msg' => __('Unable to handle request')]);
            $this->logger->critical($e->getMessage(), ['exception'=> $e]);
        }

        return $resultJson;
    }
}
