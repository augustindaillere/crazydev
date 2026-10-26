#!/usr/bin/env bash
# Exécuté SUR LE VPS par GitHub Actions (via SSH).
# Variables fournies par le pipeline : IMAGE_TAG, GHCR_USER, GHCR_TOKEN
set -euo pipefail

APP_DIR=/opt/crazydev
cd "$APP_DIR"

echo "==> Mise à jour de la configuration (compose, Caddyfile)"
git fetch --quiet origin main
git reset --hard --quiet origin/main

echo "==> Version déployée : $IMAGE_TAG"
printf 'IMAGE_TAG=%s\n' "$IMAGE_TAG" > .env

echo "==> Téléchargement de l'image depuis GHCR"
echo "$GHCR_TOKEN" | docker login ghcr.io -u "$GHCR_USER" --password-stdin >/dev/null
docker compose pull app
docker logout ghcr.io >/dev/null

echo "==> Redémarrage des conteneurs (sans coupure de la base : volume persistant)"
docker compose up -d --remove-orphans

echo "==> Nettoyage des anciennes images"
docker image prune -f >/dev/null

docker compose ps
