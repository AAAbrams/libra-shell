<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Jwk;

final class JwksSerializer
{
    /**
     * @param RsaKey[] $keys
     * @return array
     */
    public static function toJwks(array $keys): array
    {
        return [
            'keys' => array_map(
                function (RsaKey $key) {
                    return self::toJwk($key);
                },
                $keys
            )
        ];
    }

    private static function toJwk(RsaKey $key): array
    {
        $details = openssl_pkey_get_details(
            openssl_pkey_get_public($key->publicKeyPem)
        );

        if (!$details || !isset($details['rsa'])) {
            throw new \RuntimeException('Invalid RSA key');
        }

        return [
            'kty' => 'RSA',
            'use' => 'sig',
            'kid' => $key->kid,
            'alg' => $key->algorithm,
            'n'   => self::base64UrlEncode($details['rsa']['n']),
            'e'   => self::base64UrlEncode($details['rsa']['e']),
        ];
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
