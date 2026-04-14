<?php

declare(strict_types=1);

use Slim\App;

require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';

/**@var App $app*/
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->run();
