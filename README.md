# Libra Shell

`libra/shell` — это микрофреймворк-слой для Inertia-приложений на Slim, Twig, Vite и React SSR.

## Что предоставляет пакет

- PHP bootstrap для создания Slim-приложения из хост-проекта
- Inertia service provider с интеграцией Twig, Vite и SSR
- client bootstrap helper для Inertia-приложений
- server bootstrap helper для React SSR
- демонстрационный starter внутри репозитория
- CLI-установщик для генерации стартовых файлов в хост-проекте

## Установка

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/AAAbrams/inertia-psr15.git"
        },
        {
            "type": "vcs",
            "url": "https://github.com/AAAbrams/libra-shell.git"
        }
    ],
    "require": {
        "libra/shell": "^1.0",
        "libra/inertia-psr15": "^1.0"
    }
}
```

## Публичный PHP API

- `Libra\Shell\Bootstrap\ShellBootstrap`
- `Libra\Shell\ServiceProvider\InertiaServiceProvider`

`InertiaServiceProvider` поддерживает внешнюю конфигурацию через container definitions:

- `InertiaServiceProvider::ROOT_VIEW_KEY`
- `InertiaServiceProvider::VIEW_PATHS_KEY`

## Публичный JS API

Client runtime:

- `createLibraInertiaClient`
- `resolveInitialPage`
- `resolvePageComponent`

Server runtime:

- `createLibraInertiaServer`

Импорты:

```tsx
import { createLibraInertiaClient } from '@libra-shell'
import { createLibraInertiaServer } from '@libra-shell/server'
```

## CLI Starter

Сгенерировать стартовые файлы в текущем проекте:

```bash
vendor/bin/libra-shell-init
```

Сгенерировать стартовые файлы в конкретной директории:

```bash
vendor/bin/libra-shell-init /path/to/project
```

Перезаписать существующие стартовые файлы:

```bash
vendor/bin/libra-shell-init --force
```

Генерируемые файлы:

- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/app.twig`
- `resources/css/app.css`
- `resources/js/entries/client.tsx`
- `resources/js/entries/server.tsx`
- `resources/js/pages/Home.tsx`

## Пример PHP Bootstrap

```php
<?php

use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Libra\Shell\Bootstrap\ShellBootstrap;
use Libra\Shell\ServiceProvider\InertiaServiceProvider;

$projectRoot = dirname(__DIR__);

return (new ShellBootstrap($projectRoot))
    ->registerDefinitions([
        InertiaServiceProvider::ROOT_VIEW_KEY => 'app.twig',
        InertiaServiceProvider::VIEW_PATHS_KEY => [
            $projectRoot . '/resources/views',
        ],
    ])
    ->registerServiceProviders([
        new InertiaServiceProvider(),
    ])
    ->registerMiddlewares([
        InertiaMiddleware::class,
    ])
    ->registerRouteFiles([
        $projectRoot . '/routes/web.php',
    ])
    ->create();
```

## Пример Route

```php
<?php

use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Cherif\InertiaPsr15\Service\InertiaInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        /** @var InertiaInterface $inertia */
        $inertia = $request->getAttribute(InertiaMiddleware::INERTIA_ATTRIBUTE);

        return $inertia->render('Home');
    });
};
```

## Пример Client Entry

```tsx
import '@shell-css-entry'

import { MantineProvider } from '@mantine/core'
import { createLibraInertiaClient } from '@libra-shell'

const appName = import.meta.env.VITE_APP_NAME || 'My App'
const pages = import.meta.glob('../pages/**/*.tsx')

void createLibraInertiaClient({
    appName,
    pageLookupPaths: ['resources/js/pages'],
    pages,
    wrap: (app) => (
        <MantineProvider defaultColorScheme="light" forceColorScheme="light">
            {app}
        </MantineProvider>
    ),
})
```

## Пример Server Entry

```tsx
import '@shell-css-entry'

import { MantineProvider } from '@mantine/core'
import { createLibraInertiaServer } from '@libra-shell/server'

const appName = import.meta.env.VITE_APP_NAME || 'My App'
const pages = import.meta.glob('../pages/**/*.tsx')

void createLibraInertiaServer({
    appName,
    pageLookupPaths: ['resources/js/pages'],
    pages,
    wrap: (app) => (
        <MantineProvider defaultColorScheme="light" forceColorScheme="light">
            {app}
        </MantineProvider>
    ),
})
```

## Пример Twig Root View

```twig
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libra Shell Starter</title>
    {{ inertia_head(ssrHead) }}
    {{ vite_react_refresh() }}
    {{ vite('resources/js/entries/client.tsx') }}
</head>
<body>
{{ inertia(page, ssrBody) }}
</body>
</html>
```

## Переменные окружения

Основные переменные:

```dotenv
VITE_INPUT=resources/js/entries/client.tsx
VITE_CSS_ENTRY=resources/css/app.css
VITE_OUT_DIR=public/build
VITE_SSR_OUT_DIR=bootstrap/ssr
VITE_SSR_PORT=13714
VITE_DEV_SERVER=http://127.0.0.1:5173
VITE_SHELL_PAGE_PATHS=resources/js/pages
```

Опциональные:

```dotenv
VITE_APP_ROOT=/absolute/path/to/host-project
INERTIA_SSR_ENABLED=true
INERTIA_SSR_URL=http://127.0.0.1:13714/render
INERTIA_SSR_TIMEOUT=2
```

## Demo-файлы в репозитории

- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/app.twig`
- `resources/js/demo/entries/client.tsx`
- `resources/js/demo/entries/server.tsx`
- `resources/js/pages/Home.tsx`

## NPM scripts

```bash
npm run build
npm run build:ssr
npm run build:all
npm run serve:ssr
npm run dev
npm run typecheck
```

`serve:ssr` запускает собранный SSR bundle через `scripts/serve-ssr.mjs`.

## Docker

Запуск demo-приложения:

```bash
docker compose -f docker/compose.yml up --build
```

После старта:

- приложение доступно на `http://localhost:8080`
- SSR service слушает `http://localhost:13714`

Схема запуска:

- `app` — PHP 7.2 + Apache
- `ssr` — Node.js service для `npm ci`, сборки assets и SSR runtime
