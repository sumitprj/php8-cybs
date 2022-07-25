<?php

namespace CyberSource\Recaptcha\Model;

class FailureResponseProvider implements \CyberSource\Recaptcha\Model\Provider\FailureProviderInterface
{

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonSerializer;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $actionFlag;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\App\ActionFlag $actionFlag
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->actionFlag = $actionFlag;
    }

    public function execute(\Magento\Framework\App\ResponseInterface $response = null)
    {
        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

        $response->representJson(
            $this->jsonSerializer->serialize(
                [
                    'success' => false,
                    'error' => true,
                    'error_messages' => __('Invalid reCAPTCHA'),
                ]
            )
        );
    }
}
