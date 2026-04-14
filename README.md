# Libra Shell

`libra/shell` теперь позиционируется как микрофреймворк для Inertia-приложений на Slim/Twig/Vite, а не как Bitrix-модуль.

Пакет даёт:

- PHP bootstrap для запуска Slim-приложения из внешнего проекта
- Inertia service provider с SSR/Twig/Vite интеграцией
- минимальный JS runtime для запуска Inertia client и SSR entrypoint-ов извне
- демонстрационный starter внутри репозитория
- CLI-инициализатор стартовой структуры

## Архитектурное решение по auth-sso

`auth-sso` больше не является частью ядра `libra/shell`.

Почему:

- это не инфраструктура Inertia, а прикладной auth-слой
- требования к auth почти всегда зависят от хост-проекта
- прежняя реализация была жёстко связана с Bitrix и таблицей пользователей
- в микрофреймворке такие зависимости только раздувают публичный API и мешают переиспользованию

Если SSO/JWT всё же нужен, его лучше выносить в отдельный адаптер или пакет рядом с доменной моделью проекта. Такой адаптер уже может регистрировать свои роуты, middleware, user resolver, cookie policy и storage-интеграцию.

## Что осталось в ядре

- `Libra\Shell\Bootstrap\ShellBootstrap`
- `Libra\Shell\ServiceProvider\InertiaServiceProvider`
- Twig extensions для Inertia и Vite
- Vite/SSR инфраструктура
- JS helper-ы:
  - `createLibraInertiaClient`
  - `createLibraInertiaServer`
  - `resolveInitialPage`
  - `resolvePageComponent`

JS public surface намеренно сжат. Внешний проект сам владеет своими `entries`, `providers`, страницами и стилями.

## Установка

```json
{
  "require": {
    "libra/shell": "^1.0",
    "libra/inertia-psr15": "^1.0"
  }
}
```

`composer.json` пакета теперь декларирует прямые зависимости на `slim/slim`, `slim/twig-view` и `vlucas/phpdotenv`, чтобы bootstrap был самостоятельным.

## Быстрый старт через CLI

После установки пакета можно сгенерировать стартовые файлы в хост-проект:

```bash
vendor/bin/libra-shell-init
```

Или в конкретную директорию:

```bash
vendor/bin/libra-shell-init /path/to/project
```

Флаг `--force` перезапишет существующие starter-файлы.

CLI создаёт:

- `bootstrap/libra-shell.php`
- `routes/libra-shell.php`
- `resources/views/app.twig`
- `resources/css/app.css`
- `resources/js/entries/client.tsx`
- `resources/js/entries/server.tsx`
- `resources/js/pages/Home.tsx`

## PHP bootstrap

Роутинг и root view теперь инициализируются извне.

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
        $projectRoot . '/routes/libra-shell.php',
    ])
    ->create();
```

`registerDefinitions()` нужен для передачи конфигурации в контейнер до фактического разрешения сервисов.

## Внешние роуты

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

## Внешние JS entrypoint-ы

Client entry:

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

Server entry:

```tsx
import '@shell-css-entry'

import { MantineProvider } from '@mantine/core'
import { createLibraInertiaServer } from '@libra-shell'

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

## Twig root view

Теперь `rootView` не зашит в библиотеку. Его выбирает хост-проект.

Пример:

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

## Vite переменные

Основные переменные:

```dotenv
VITE_INPUT=resources/js/entries/client.tsx
VITE_CSS_ENTRY=resources/css/app.css
VITE_OUT_DIR=local/assets/libra.shell/build
VITE_SSR_OUT_DIR=local/assets/libra.shell/ssr
VITE_SSR_PORT=13714
VITE_DEV_SERVER=http://127.0.0.1:5173
VITE_SHELL_PAGE_PATHS=resources/js/pages
```

Опционально:

```dotenv
VITE_APP_ROOT=/absolute/path/to/host-project
```

`VITE_APP_ROOT` пригодится, если нужно явно переопределить корень хост-проекта. Для стандартных установок в `local/modules` и `vendor/libra/shell` корень теперь вычисляется автоматически.

## Demo внутри репозитория

В репозитории сохранён минимальный demo starter:

- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/app.twig`
- `resources/js/demo/entries/client.tsx`
- `resources/js/demo/entries/server.tsx`
- `resources/js/pages/Home.tsx`

Он нужен как живая референсная сборка и как источник stub-файлов для `libra-shell-init`.

## NPM scripts

```bash
npm run build
npm run build:ssr
npm run build:all
npm run serve:ssr
npm run dev
```

`serve:ssr` теперь запускает SSR bundle через `scripts/serve-ssr.mjs`, который корректно вычисляет путь как для standalone-репозитория, так и для установки в `vendor`.

## Итоговое направление

`libra/shell` теперь отвечает только за transport/runtime слой:

- bootstrap
- routing integration
- Inertia/Twig/Vite glue code
- SSR startup

Всё прикладное:

- auth
- SSO
- user storage
- project providers
- business pages

должно жить снаружи и подключаться как расширение хост-проекта.
