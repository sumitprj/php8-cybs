<?php


namespace CyberSource\SecureAcceptance\Gateway\Request\Soap;

use Magento\Framework\ObjectManager\TMapFactory;

class ServiceRequest implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    const SERVICE_RUN_TRUE = 'true';

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface[]
     */
    private $builders;

    /**
     * @var string
     */
    private $serviceName;

    public function __construct(
        TMapFactory $tmapFactory,
        string $serviceName,
        array $builders = []
    ) {
        $this->serviceName = $serviceName;
        $this->builders = $tmapFactory->create([
            'array' => $builders,
            'type' => \Magento\Payment\Gateway\Request\BuilderInterface::class
        ]);
    }


    /**
     * Builds SOAP Service Request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $result = [];

        foreach ($this->builders as $builder) {
            $result = array_merge($result, $builder->build($buildSubject));
        }

        $result['run'] = self::SERVICE_RUN_TRUE;
        return [$this->serviceName => $result];
    }
}
