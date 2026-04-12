<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Jwk;

final class JwkProvider
{
    /**
     * @return RsaKey[]
     */
    public static function getPublicKeys(): array
    {
        return [
            new RsaKey(
                $_ENV['LIBRA_API_JWT_KEY_ID'],
                file_get_contents(
                    $_ENV['LIBRA_API_JWT_PUBLIC_KEY_PATH']
                )
            ),
        ];
    }
}