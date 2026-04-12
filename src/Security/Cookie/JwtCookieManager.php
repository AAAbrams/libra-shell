<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Cookie;

use Psr\Http\Message\ResponseInterface;

final class JwtCookieManager
{
    private const ACCESS_COOKIE  = 'AUTH_JWT';
    private const REFRESH_COOKIE = 'REFRESH_TOKEN';

    private const ACCESS_TTL  = 900;               // 15 минут
    private const REFRESH_TTL = 60 * 60 * 24 * 30;  // 30 дней

    public function extractAccessToken(array $cookies): string
    {
        return (string)$cookies[self::ACCESS_COOKIE];
    }

    public function extractRefreshToken(array $cookies): string
    {
        return (string)$cookies[self::REFRESH_COOKIE];
    }

    public function addAccessTokenCookie(
        ResponseInterface $response,
        string $accessToken
    ): ResponseInterface {
        if ($accessToken === '') {
            return $response;
        }

        return $this->addCookieValue(
            $response,
            self::ACCESS_COOKIE,
            $accessToken,
            time() + self::ACCESS_TTL,
            'Lax'
        );
    }

    public function addRefreshTokenCookie(
        ResponseInterface $response,
        string $refreshToken
    ): ResponseInterface {
        if ($refreshToken === '') {
            return $response;
        }

        return $this->addCookieValue(
            $response,
            self::REFRESH_COOKIE,
            $refreshToken,
            time() + self::REFRESH_TTL,
            'Lax',
            '/auth/refresh'
        );
    }

    public function expireAccessTokenCookie(ResponseInterface $response): ResponseInterface
    {
        return $this->addCookieValue(
            $response,
            self::ACCESS_COOKIE,
            '',
            time() - 3600,
            'Lax'
        );
    }

    public function expireRefreshTokenCookie(ResponseInterface $response): ResponseInterface
    {
        return $this->addCookieValue(
            $response,
            self::REFRESH_COOKIE,
            '',
            time() - 3600,
            'Lax',
            '/auth/refresh'
        );
    }

    // -------------------------
    // Internal helpers
    // -------------------------

    private function addCookieValue(
        ResponseInterface $response,
        string $name,
        string $value,
        int $expires,
        string $sameSite,
        string $path = '/'
    ): ResponseInterface {
        $cookie = rawurlencode($name) . '=' . rawurlencode($value);

        $parts = [
            $cookie,
            'Expires=' . gmdate('D, d M Y H:i:s T', $expires),
            'Path=' . $path,
            'HttpOnly',
            'SameSite=' . $sameSite,
        ];

        if ($this->isSecure()) {
            $parts[] = 'Secure';
        }

        return $response->withAddedHeader(
            'Set-Cookie',
            implode('; ', $parts)
        );
    }

    private function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
    }
}