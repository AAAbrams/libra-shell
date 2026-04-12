<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Token;

use Firebase\JWT\JWT;

final class JwtIssuer
{
    /**
     * @var string
     */
    private $privateKey;
    /**
     * @var string
     */
    private $keyId;

    public function __construct(
        string $privateKey,
        string $keyId
    ) {
        $this->privateKey = $privateKey;
        $this->keyId = $keyId;
    }

    public function issueAccessToken(array $claims): string
    {
        return $this->encode(
            array_merge($claims, [
                'iss' => 'bitrix',
                'aud' => 'laravel-api',
                'iat' => time(),
                'exp' => time() + 900, // 15 мин
                'typ' => 'access',
            ])
        );
    }

    public function issueRefreshToken(array $claims): string
    {
        return $this->encode(
            array_merge($claims, [
                'iat' => time(),
                'exp' => time() + 86400 * 30, // 30d
                'typ' => 'refresh',
            ])
        );
    }

    private function encode(array $payload): string
    {
        return JWT::encode($payload, $this->privateKey, 'RS256', $this->keyId);
    }
}
