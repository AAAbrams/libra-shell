<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Jwk;

final class RsaKey
{
    /**
     * @var string
     */
    public $kid;
    /**
     * @var string
     */
    public $publicKeyPem;
    /**
     * @var string
     */
    public $algorithm;

    public function __construct(
        string $kid,
        string $publicKeyPem,
        string $algorithm = 'RS256'
    ) {
        $this->kid= $kid;
        $this->publicKeyPem = $publicKeyPem;
        $this->algorithm = $algorithm;
    }
}
