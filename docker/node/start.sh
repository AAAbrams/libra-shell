#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -d node_modules ] || [ ! -f node_modules/.package-lock.json ]; then
    npm ci
fi

npm run build:all
exec npm run serve:ssr
