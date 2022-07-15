<?php
/**
 *
 */

namespace CyberSource\ThreeDSecure\Gateway\Request\Jwt;

class TokenBuilder implements TokenBuilderInterface
{

    const JWT_LIFETIME = 3600;

    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256
     */
    private $sha256;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var \DateTimeImmutableFactory
     */
    private $dateTimeImmutableFactory;

    /**
     * @var \CyberSource\SecureAcceptance\Model\Jwt\JwtHelper
     */
    private $jwtHelper;

    public function __construct(
        \Lcobucci\JWT\Signer\Hmac\Sha256 $sha256,
        \Magento\Framework\Math\Random $random,
        \CyberSource\SecureAcceptance\Model\Jwt\JwtHelper $jwtHelper,
        \DateTimeImmutableFactory $dateTimeImmutableFactory
    ) {
        $this->sha256 = $sha256;
        $this->random = $random;
        $this->jwtHelper = $jwtHelper;
        $this->dateTimeImmutableFactory = $dateTimeImmutableFactory;
    }

    public function buildToken($referenceId, $payload, $orgUnitId, $apiId, $apiKey)
    {
        /** @var \Lcobucci\JWT\Builder $tokenBuilder */
        $tokenBuilder = $this->jwtHelper->getTokenBuilder();

        $jwtId = $this->random->getUniqueHash('jwt_');
        $currentTime = $this->getTime();

        $jwt = $tokenBuilder
            ->identifiedBy($jwtId)
            ->issuedBy($apiId)
            ->issuedAt($this->dateTimeImmutableFactory->create()->setTimestamp($currentTime))
            ->expiresAt($this->dateTimeImmutableFactory->create()->setTimestamp($currentTime + self::JWT_LIFETIME))
            ->withClaim('OrgUnitId', $orgUnitId)
            ->withClaim('ReferenceId', $referenceId)
            ->withClaim('Payload', $payload)
            ->withClaim('ObjectifyPayload', true)
            ->getToken($this->sha256, $this->jwtHelper->getJwtKeyObj($apiKey));

        return $jwt->toString();
    }

    protected function getTime()
    {
        return time(); //TODO: should we replace this with \Magento\Framework\Stdlib\DateTime\DateTime::gmtTimestamp ?
    }
}
