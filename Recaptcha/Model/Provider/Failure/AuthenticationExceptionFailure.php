<?php
namespace CyberSource\ReCaptcha\Model\Provider\Failure;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\Plugin\AuthenticationException;
use CyberSource\ReCaptcha\Model\Config;
use CyberSource\ReCaptcha\Model\Provider\FailureProviderInterface;

class AuthenticationExceptionFailure implements FailureProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * AuthenticationExceptionFailure constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Handle reCaptcha failure
     * @param ResponseInterface $response
     * @return void
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(ResponseInterface $response = null)
    {
        throw new AuthenticationException($this->config->getErrorDescription());
    }
}
