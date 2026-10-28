#!/usr/bin/env bash
# Exécuté SUR LE VPS par GitHub Actions (via SSH)
set -euo pipefail

cd /opt/crazydev

echo "==> Récupération de la dernière version du code"
git fetch --quiet origin main
git reset --hard --quiet origin/main
git log --oneline -1

echo "==> Reconstruction de l'image et redémarrage du conteneur"
docker compose up -d --build --remove-orphans

echo "==> Nettoyage des anciennes images"
docker image prune -f >/dev/null

docker compose ps