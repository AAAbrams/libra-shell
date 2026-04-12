<?php

declare(strict_types=1);

namespace Libra\Shell\Security\Http;

use CUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class TestLoginController
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {

        if (!$this->isTestEnvironment()) {
            return $this->forbidden($response);
        }

        $data = json_decode($request->getBody()->getContents(), true);

        if (
            empty($data['login']) ||
            empty($data['password'])
        ) {
            return $this->badRequest($response);
        }

        global $USER;
        if (!$USER instanceof CUser) {
            $USER = new CUser();
        }

        $authResult = $USER->Login(
            $data['login'],
            $data['password'],
            'Y'
        );

        if ($authResult !== true) {
            return $this->unauthorized($response, (string)$authResult);
        }

        return $response
            ->withStatus(204);
    }

    private function isTestEnvironment(): bool
    {
        return true;/*defined('LIBRA_API_TEST_MODE')
            && LIBRA_API_TEST_MODE === true;*/
    }

    private function forbidden(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'error' => 'forbidden'
        ]));

        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }

    private function unauthorized(
        ResponseInterface $response,
        string $message
    ): ResponseInterface {
        $response->getBody()->write(json_encode([
            'error' => 'unauthorized',
            'message' => $message,
        ]));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    private function badRequest(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode([
            'error' => 'bad_request'
        ]));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }
}