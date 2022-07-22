<?php

namespace CyberSource\Recaptcha\Model;


class Validate implements \CyberSource\ReCaptcha\Api\ValidateInterface
{

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function validate($reCaptchaResponse, $remoteIp)
    {
        if (!$reCaptchaResponse) {
            return false;
        }

        $secret = $this->config->getPrivateKey();
        $reCaptcha = new \ReCaptcha\ReCaptcha($secret);
        $res = $reCaptcha->verify($reCaptchaResponse, $remoteIp);

        return $res->isSuccess();
    }
}
