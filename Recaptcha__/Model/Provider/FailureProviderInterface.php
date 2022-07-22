<?php

namespace CyberSource\Recaptcha\Model\Provider;

use Magento\Framework\App\ResponseInterface;

interface FailureProviderInterface
{
    /**
     * Handle reCaptcha failure
     * @param ResponseInterface $response
     * @return void
     */
    public function execute(ResponseInterface $response = null);
}
