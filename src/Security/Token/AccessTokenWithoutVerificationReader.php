<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Token;

final class AccessTokenWithoutVerificationReader
{
    /**
     * @var array
     */
    private $payload;

    public function __construct(string $jwt)
    {
        try {
            $this->payload = JwtDecoder::decodeWithoutVerification($jwt);
        } catch (\Throwable $e) {
            $this->payload = [];
        }
    }

    public function isAlive(int $expectedAuthVersion): bool
    {
        if (
            empty($this->payload['exp']) ||
            (int)$this->payload['exp'] <= time()
        ) {
            return false;
        }

        if (
            empty($this->payload['av']) ||
            (int)$this->payload['av'] !== $expectedAuthVersion
        ) {
            return false;
        }

        return true;
    }

    public function extractUserId(): int
    {
        try {
            return isset($this->payload['sub']) ? (int)$this->payload['sub'] : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}