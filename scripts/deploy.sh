#!/usr/bin/env bash
# Exécuté SUR LE VPS par GitHub Actions. Usage : deploy.sh <branche> <dossier>
set -euo pipefail

BRANCH="${1:-main}"
APP_DIR="${2:-/opt/crazydev}"

cd "$APP_DIR"
echo "==> Déploiement de $BRANCH dans $APP_DIR"
git fetch --quiet origin "$BRANCH"
git checkout --quiet -B "$BRANCH" "origin/$BRANCH"
git reset --hard --quiet "origin/$BRANCH"
git log --oneline -1

docker compose up -d --build --remove-orphans
docker image prune -f >/dev/null
docker compose ps