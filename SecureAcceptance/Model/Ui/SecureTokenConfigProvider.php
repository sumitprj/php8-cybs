<?php

namespace CyberSource\SecureAcceptance\Model\Ui;

/**
 * Class SecureTokenConfigProvider
 * @package CyberSource\SecureAcceptance\Model\Ui
 */
class SecureTokenConfigProvider
{
    /**
     * @var \CyberSource\SecureAcceptance\Model\SecureToken\Generator
     */
    private $generator;

    /**
     * SecureTokenConfigProvider constructor.
     *
     * @param \CyberSource\SecureAcceptance\Model\SecureToken\Generator $generator
     */
    public function __construct(\CyberSource\SecureAcceptance\Model\SecureToken\Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                \CyberSource\SecureAcceptance\Model\Ui\ConfigProvider::CODE => [
                    'secure_token' => $this->generator->get(),
                ],
            ]
        ];
    }
}
