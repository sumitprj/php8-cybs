<?php

namespace CyberSource\Recaptcha\Api;

interface ValidateInterface
{
    const PARAM_RECAPTCHA_RESPONSE = 'g-recaptcha-response';

    /**
     * Return true if reCaptcha validation has passed
     * @param string $reCaptchaResponse
     * @param string $remoteIp
     * @return bool
     */
    public function validate($reCaptchaResponse, $remoteIp);
}
