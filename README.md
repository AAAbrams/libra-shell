# Libra Shell

`libra/shell` — это модуль для Bitrix, который предоставляет:

- точки входа на базе Slim для SPA-маршрутов
- интеграцию Inertia для PHP
- React/Vite frontend с поддержкой SSR
- возможность использовать внешние точки входа и внешние директории с TSX-страницами

## Использование С Composer

Рекомендуемая схема подключения из хост-проекта:

```json
{
  "require": {
    "libra/shell": "^1.0",
    "libra/inertia-psr15": "dev-master"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:<org>/inertia-psr15.git"
    },
    {
      "type": "vcs",
      "url": "git@github.com:<org>/libra.shell.git"
    }
  ]
}
```

Важно:

- Composer не наследует `repositories` из зависимостей.
- Если `libra/shell` требует `libra/inertia-psr15`, корневой проект тоже должен знать, откуда получать `libra/inertia-psr15`.

## Подготовка Отдельного Репозитория

Перед публикацией `libra/shell` как отдельного репозитория лучше сохранить
текущую рабочую версию для монорепозитория, а для публикуемого репозитория
подготовить отдельный вариант со следующими изменениями:

1. Переименовать `.env` в `.env.example` и оставить только безопасные значения по умолчанию.
2. Оставить только исходники и не публиковать сгенерированные артефакты и `node_modules`.
3. Убедиться, что в репозитории используется standalone `composer.json` из этого README.

Рекомендуемый standalone `composer.json`:

```json
{
  "name": "libra/shell",
  "description": "Bitrix SPA shell with Inertia, Slim and React SSR",
  "type": "library",
  "autoload": {
    "psr-4": {
      "Libra\\Shell\\": "src/"
    }
  },
  "require": {
    "ext-openssl": "*",
    "firebase/php-jwt": "^6.4",
    "laminas/laminas-diactoros": "2.0.0",
    "libra/inertia-psr15": "dev-master",
    "slim/twig-view": "^2.5"
  }
}
```

Примечания:

- Не стоит держать `"version": "1.0"` в публикуемом пакете. Лучше использовать git-теги.
- Корневой проект всё равно должен объявлять оба VCS-репозитория:
  - `libra/shell`
  - `libra/inertia-psr15`
- Если `libra/inertia-psr15` остаётся на `dev-master`, корневой проект должен разрешать такую stability.

## Переменные Окружения

Модуль читает свой собственный `.env` из корня модуля.

Основные переменные:

```dotenv
VITE_INPUT=resources/js/app/entries/client.tsx
VITE_OUT_DIR=local/assets/libra.shell/build
VITE_SSR_OUT_DIR=local/assets/libra.shell/ssr
VITE_SSR_PORT=13714
VITE_DEV_SERVER=http://node:5173
INERTIA_SSR_URL=http://node:13714/render
VITE_SHELL_PAGE_PATHS=resources/js/pages,../../libra-shell/resources/js/pages
VITE_SHELL_EXTRA_PAGE_DIR=../../libra-shell/resources/js/pages
```

`VITE_SHELL_PAGE_PATHS` задаёт, где frontend и SSR ищут page-компоненты.

Поведение по умолчанию:

- страницы внутри модуля: `resources/js/pages`
- внешние страницы при необходимости: `../../libra-shell/resources/js/pages`

## Сборка Frontend

Внутри контейнера проекта:

```bash
cd /var/www/dev.doctorslon.ru/local/modules/libra.shell
npm ci
npm run build:all
npm run serve:ssr
```

Результат сборки:

- client: `local/assets/libra.shell/build`
- SSR: `local/assets/libra.shell/ssr`

## Внешние Точки Входа

`ShellBootstrap` можно конфигурировать вне модуля.

Пример:

```php
<?php

use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Libra\Shell\Bootstrap\ShellBootstrap;
use Libra\Shell\ServiceProvider\InertiaServiceProvider;

$app = (new ShellBootstrap($_SERVER['DOCUMENT_ROOT'] . '/local/modules/libra.shell'))
    ->registerServiceProviders([
        new InertiaServiceProvider(),
    ])
    ->registerMiddlewares([
        InertiaMiddleware::class,
    ])
    ->registerRouteFiles([
        __DIR__ . '/routes/web.php',
    ])
    ->create();
```

После этого внешний route-файл сможет рендерить страницы, которые лежат вне модуля, если путь к ним включён в `VITE_SHELL_PAGE_PATHS`.

## Переиспользуемый Frontend-Слой

`libra/shell` можно использовать не только как Inertia/SSR-инфраструктуру, но и как источник общих React/TypeScript сущностей для основного проекта.

Рекомендуемое разделение:

- внутри модуля:
  - `resources/js/components/ui` — базовые UI-компоненты
  - `resources/js/components/shared` — переиспользуемые блоки
  - `resources/js/hooks` — общие React hooks
  - `resources/js/lib` — утилиты и Inertia helpers
  - `resources/js/layouts` — общие layout-компоненты
- в основном проекте:
  - бизнес-страницы
  - бизнес-компоненты
  - проектные маршруты

Базовая структура frontend-части модуля:

```text
resources/js/
  app/
    config/
    entries/
  components/
    shared/
    ui/
  hooks/
  layouts/
  lib/
  pages/
  types/
```

Доступные alias внутри страниц, которые собираются через `libra/shell`:

```ts
import { Button } from '@libra-shell/ui'
import { Container, Header } from '@libra-shell/shared'
import { useTypedPage } from '@libra-shell/hooks'
import { resolvePageComponent, cn } from '@libra-shell/lib'
import { CheckoutLayout } from '@libra-shell/layouts'
```

Для внешних страниц это работает без дополнительной настройки, если сами страницы подключаются через `VITE_SHELL_PAGE_PATHS` и собираются Vite-конфигом модуля.

Базовый barrel-export модуля:

```ts
import { Button, CheckoutLayout, useTypedPage } from '@libra-shell'
```

Правило зависимости должно быть односторонним:

- основной проект может зависеть от `libra/shell`
- shared-код внутри `libra/shell` не должен зависеть от бизнес-кода основного проекта

## Замечания По Репозиторию

Этот репозиторий в норме должен содержать только исходные файлы.

Не нужно коммитить:

- `node_modules`
- сгенерированный Vite build output
- сгенерированные SSR-бандлы
- `*.tsbuildinfo`

Практический чеклист для первого коммита:

1. Распаковать архив в чистую директорию будущего репозитория.
2. Оставить `.env.example` и не коммитить реальный `.env`.
3. Проверить, что `composer.json` уже соответствует standalone-варианту из этого README.
4. Проверить, что `.gitignore` по-прежнему исключает `node_modules`, build output и SSR output.
5. Выполнить `git init`, `git add .`, `git commit -m "Initial extract of libra/shell"`.
