<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Http;

use Libra\Shell\Security\Cookie\JwtCookieManager;
use Libra\Shell\Security\Token\AccessTokenWithoutVerificationReader;
use Libra\Shell\Security\Token\JwtIssuer;
use Libra\Shell\Security\User\UserContext;
use Libra\Shell\Security\User\UserRepositoryTable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IssueJwtController
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        $redirect = $request->getQueryParams()['redirect'] ?? '';
        // todo: whitelist
        if ($redirect !== '') {
            $response = $response
                ->withHeader('Location', '/' . $redirect)
                ->withStatus(302);
        }

        $user = new UserContext();

        if (!$user->isAuthenticated()) {
            $user = UserContext::guest();
        }

        $cookieManager = new JwtCookieManager();
        $authVersion = UserRepositoryTable::getAuthVersion($user->getId());

        if ($jwt = $cookieManager->extractAccessToken($request->getCookieParams())) {
            $accessTokenReader = new AccessTokenWithoutVerificationReader($jwt);
            if ($accessTokenReader->isAlive($authVersion)) {
                if ($redirect !== '') {
                    return $response;
                }
                return $response->withStatus(204);
            }
        }

        $issuer = new JwtIssuer(
            file_get_contents($_ENV['LIBRA_API_JWT_PRIVATE_KEY_PATH']),
            $_ENV['LIBRA_API_JWT_KEY_ID']
        );
        // 1. Генерируем JWT
        $accessToken = $issuer->issueAccessToken(
            $user->toJwtClaims($authVersion)
        );
        $refreshToken = $issuer->issueRefreshToken(
            $user->toJwtClaims($authVersion)
        );

        // 2. Устанавливаем cookie
        $response = $cookieManager->addAccessTokenCookie(
            $response,
            $accessToken,
        );
        $response = $cookieManager->addRefreshTokenCookie(
            $response,
            $refreshToken,
        );

        if ($redirect !== '') {
            return $response;
        }
        return $response->withStatus(200);
    }

    private function unauthorized(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'error' => 'unauthorized'
        ]));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
}
