<?php

namespace CyberSource\Recaptcha\Model\Provider;

interface ResponseProviderInterface
{
    /**
     * Handle recaptcha failure
     * @return string
     */
    public function execute();
}
