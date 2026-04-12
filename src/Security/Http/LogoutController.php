<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Http;

use Libra\Shell\Security\Cookie\JwtCookieManager;
use Libra\Shell\Security\User\UserRepositoryException;
use Libra\Shell\Security\User\UserRepositoryTable;
use Libra\Shell\Security\Token\AccessTokenWithoutVerificationReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LogoutController
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        $userId = 0;
        $cookieManager = new JwtCookieManager();
        if ($jwt = $cookieManager->extractAccessToken($request->getCookieParams())) {
            $accessTokenReader = new AccessTokenWithoutVerificationReader($jwt);
            $userId = $accessTokenReader->extractUserId();
        }

        try {
            UserRepositoryTable::increaseAuthVersion($userId);
        } catch (UserRepositoryException $e) {
            \CEventLog::Add([
                "SEVERITY" => "INFO",
                "AUDIT_TYPE_ID" => "JWT_AUTH",
                "MODULE_ID" => "libra.shell",
                "ITEM_ID" => $userId,
                "DESCRIPTION" => "JWT logout: " . json_encode([
                        'exeption_msg' => $e->getMessage()
                    ], JSON_UNESCAPED_UNICODE)
            ]);
        }

        $response = $cookieManager->expireAccessTokenCookie($response);
        $response = $cookieManager->expireRefreshTokenCookie($response);

        return $response
            ->withStatus(204);
    }
}
