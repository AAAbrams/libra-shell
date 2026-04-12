<?php

declare(strict_types=1);

use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Cherif\InertiaPsr15\Service\InertiaInterface;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $app->get('/libra-shell', function (Request $request, Response $response) {
        /**
         * @var InertiaInterface $inertia
         */
        $inertia = $request->getAttribute(InertiaMiddleware::INERTIA_ATTRIBUTE);
        return $inertia->render('Home');
    });

};
