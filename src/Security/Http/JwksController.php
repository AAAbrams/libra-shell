<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Http;

use Libra\Shell\Security\Jwk\JwksSerializer;
use Libra\Shell\Security\Jwk\JwkProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class JwksController
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $keys = JwkProvider::getPublicKeys();

        $response->getBody()->write(json_encode(
            JwksSerializer::toJwks($keys),
            JSON_UNESCAPED_SLASHES
        ));

        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'public, max-age=3600');
    }
}