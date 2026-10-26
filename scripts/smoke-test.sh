#!/usr/bin/env bash
# Tests de fumée : vérifie que le site répond et que les fichiers sensibles sont protégés.
# Usage : scripts/smoke-test.sh https://192-162-69-165.sslip.io
set -euo pipefail

BASE_URL="${1:-http://localhost:8080}"
FAILED=0

check_status() {
    local path="$1" expected="$2" label="$3" method="${4:-GET}"
    local code
    code=$(curl -s -o /dev/null -w '%{http_code}' -X "$method" "$BASE_URL$path")
    if [[ "$code" == "$expected" ]]; then
        echo "OK    $label ($path -> $code)"
    else
        echo "ECHEC $label ($path -> $code, attendu $expected)"
        FAILED=1
    fi
}

check_status "/"                               200 "Page d'accueil"
check_status "/leaderboard.php"                200 "Leaderboard"
check_status "/page2.php"                      302 "Accès direct à une étape redirigé"
check_status "/api/start_game.php"             200 "API démarrage de partie" POST
check_status "/api/submit_score.php"           405 "API score refuse le GET"
check_status "/config/database.php"            403 "Dossier config/ protégé"
check_status "/config/data/leaderboard.sqlite" 403 "Base SQLite protégée"
check_status "/includes/header.php"            403 "Dossier includes/ protégé"
check_status "/schemas.sql"                    403 "Schéma SQL protégé"
check_status "/.htaccess"                      403 ".htaccess protégé"

if curl -s "$BASE_URL/api/get_leaderboard.php" | grep -q '"success":true'; then
    echo "OK    API leaderboard renvoie success:true (base SQLite accessible en écriture)"
else
    echo "ECHEC API leaderboard"
    FAILED=1
fi

exit "$FAILED"
