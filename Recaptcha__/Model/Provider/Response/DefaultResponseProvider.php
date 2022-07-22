<?php


namespace CyberSource\ReCaptcha\Model\Provider\Response;

use Magento\Framework\App\RequestInterface;
use CyberSource\ReCaptcha\Api\ValidateInterface;
use CyberSource\ReCaptcha\Model\Provider\ResponseProviderInterface;

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
     * Handle reCaptcha failure
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute()
    {
        return $this->request->getParam(ValidateInterface::PARAM_RECAPTCHA_RESPONSE);
    }
}
