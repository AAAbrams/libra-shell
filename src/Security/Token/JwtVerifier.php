<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Token;

use Libra\Shell\Security\User\UserContext;
use Libra\Shell\Security\User\UserRepositoryException;
use Libra\Shell\Security\User\UserRepositoryTable;
use RuntimeException;

final class JwtVerifier
{
    /**
     * @param string $jwt
     * @return array
     * @throws RuntimeException
     */
    public static function verifyAccessToken(string $jwt): array
    {
        return self::verify(
            $jwt,
            'access'
        );
    }

    /**
     * @param string $jwt
     * @return array
     * @throws RuntimeException
     */
    public static function verifyRefreshToken(string $jwt): array
    {
        return self::verify(
            $jwt,
            'refresh'
        );
    }

    /**
     * @param string $jwt
     * @param string $expectedType
     * @return array
     * @throws RuntimeException
     */
    private static function verify(string $jwt, string $expectedType): array
    {
        if ($jwt === '') {
            throw new RuntimeException('JWT is empty');
        }
        $payload = JwtDecoder::decode($jwt);

        if (empty($payload->exp) || $payload->exp < time()) {
            throw new RuntimeException('Refresh token expired');
        }

        if (($payload->typ ?? null) !== $expectedType) {
            throw new RuntimeException('Invalid token type');
        }

        $userId = (int)$payload->sub;

        try {
            $authVersion = UserRepositoryTable::getAuthVersion($userId);
        } catch (UserRepositoryException $e) {
            throw new RuntimeException('User not found');
        }

        $user = new UserContext();
        if ($user->getId() !== $userId) {
            throw new RuntimeException('User not match');
        }

        if ((int)$payload->av !== $authVersion) {
            throw new RuntimeException('Token invalidated');
        }


        return $user->toJwtClaims($authVersion);
    }
}