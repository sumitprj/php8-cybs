<?php

namespace CyberSource\ReCaptcha\Model\Provider;

interface ResponseProviderInterface
{
    /**
     * Handle reCaptcha failure
     * @return string
     */
    public function execute();
}
