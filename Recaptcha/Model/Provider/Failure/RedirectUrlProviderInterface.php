<?php

namespace CyberSource\ReCaptcha\Model\Provider\Failure;

interface RedirectUrlProviderInterface
{
    /**
     * Get redirection URL
     * @return string
     */
    public function execute();
}
