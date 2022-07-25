<?php


namespace CyberSource\Recaptcha\Model\Provider\Response;

use Magento\Framework\App\RequestInterface;
use CyberSource\Recaptcha\Api\ValidateInterface;
use CyberSource\Recaptcha\Model\Provider\ResponseProviderInterface;

class DefaultResponseProvider implements ResponseProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * DefaultResponseProvider constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Handle recaptcha failure
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute()
    {
        return $this->request->getParam(ValidateInterface::PARAM_RECAPTCHA_RESPONSE);
    }
}
