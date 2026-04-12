<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Http;

use Libra\Shell\Security\Cookie\JwtCookieManager;
use Libra\Shell\Security\User\UserRepositoryException;
use Libra\Shell\Security\Token\JwtIssuer;
use Libra\Shell\Security\Token\JwtVerifier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class RefreshJwtController
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        $cookieManager = new JwtCookieManager();
        $refreshToken = $cookieManager->extractRefreshToken($request->getCookieParams());

        if ($refreshToken === '') {
            return $response->withStatus(401);
        }

        try {
            $jwtClaims = JwtVerifier::verifyRefreshToken($refreshToken);
        } catch (RuntimeException $e) {
            $response = $cookieManager->expireAccessTokenCookie($response);
            $response = $cookieManager->expireRefreshTokenCookie($response);
            return $response->withStatus(401);
        }

        $issuer = new JwtIssuer(
            file_get_contents($_ENV['LIBRA_API_JWT_PRIVATE_KEY_PATH']),
            $_ENV['LIBRA_API_JWT_KEY_ID']
        );
        // 2. Issue new JWT
        $jwt = $issuer->issueAccessToken($jwtClaims);
        // 3. Set cookies
        $response = $cookieManager->addAccessTokenCookie($response, $jwt);

        return $response->withStatus(204);
    }
}