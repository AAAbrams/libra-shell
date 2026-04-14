<?php

declare(strict_types=1);

use Slim\App;

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**@var App $app*/
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->run();
