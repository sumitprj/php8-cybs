<?php
/**
 *
 */

namespace CyberSource\ThreeDSecure\Gateway\Request\Jwt;

interface TokenBuilderInterface
{
    /**
     * @param $referenceId
     * @param $payload
     * @param $orgUnitId
     * @param $apiId
     * @param $apiKey
     *
     * @return string
     */
    public function buildToken($referenceId, $payload, $orgUnitId, $apiId, $apiKey);
}
