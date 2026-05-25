#!/usr/bin/env bash
set -euo pipefail
cd ~/invitatie-nunta-docker

git fetch --all
git reset --hard origin/main

docker compose -f docker-compose.yml build
docker compose -f docker-compose.yml up -d

docker compose -f docker-compose.yml exec -T app npm run build

docker compose -f docker-compose.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.yml exec -T app php artisan optimize
docker compose -f docker-compose.yml exec -T app php artisan queue:restart
