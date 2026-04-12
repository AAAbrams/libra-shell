<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtDecoder
{
    public static function decode(string $token): \stdClass
    {
        return JWT::decode($token, new Key(file_get_contents($_ENV['LIBRA_API_JWT_PUBLIC_KEY_PATH']), 'RS256'));
    }

    public static function decodeWithoutVerification(string $jwt): array
    {
        [, $payload] = explode('.', $jwt);

        return json_decode(
            base64_decode(strtr($payload, '-_', '+/')),
            true
        );
    }

}