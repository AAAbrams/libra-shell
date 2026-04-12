<?php

declare(strict_types=1);

use Libra\Shell\Security\Http\JwksController;
use Libra\Shell\Security\Http\IssueJwtController;
use Libra\Shell\Security\Http\LogoutController;
use Libra\Shell\Security\Http\RefreshJwtController;
use Libra\Shell\Security\Http\TestLoginController;
use Slim\App;

/**
 * @var App $app
 */
return function (App $app) {
    $app->group('/auth', function(App $group) {
        $group->get('/jwt/', IssueJwtController::class);
        $group->post('/refresh/', RefreshJwtController::class);
        $group->post('/logout/', LogoutController::class);
        $group->post('/test/login/', TestLoginController::class);
    });
    $app->get('/.well-known/jwks.json', JwksController::class);
};
