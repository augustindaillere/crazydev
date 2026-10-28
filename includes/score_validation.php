<?php
// Règles de validation d'un score, isolées dans des fonctions pour pouvoir les tester.
declare(strict_types=1);

// Convertit un temps MM:SS:mmm en millisecondes. Renvoie null si le format est invalide.
function parseFinalTime(string $finalTime): ?int
{
    if (!preg_match('/^(\d{1,3}):([0-5]\d):(\d{3})$/', $finalTime, $parts)) {
        return null;
    }
    return ((int) $parts[1] * 60 * 1000) + ((int) $parts[2] * 1000) + (int) $parts[3];
}

// Vérifie qu'une photo base64 est une vraie image de moins de 8 Mo (photo vide acceptée).
function isValidPhoto(string $photo): bool
{
    if ($photo === '') {
        return true;
    }
    if (!preg_match('/^data:image\/(jpeg|png|webp);base64,([A-Za-z0-9+\/=]+)$/', $photo, $parts)) {
        return false;
    }
    $data = base64_decode($parts[2], true);
    return $data !== false
        && strlen($data) <= 8 * 1024 * 1024
        && getimagesizefromstring($data) !== false;
}

// Valide toutes les données. Renvoie ['error' => null, 'data' => [...]] ou ['error' => 'message', 'data' => []].
function validateScoreInput(array $input): array
{
    $firstName = trim((string) ($input['first_name'] ?? ''));
    $lastName = trim((string) ($input['last_name'] ?? ''));
    $finalTime = trim((string) ($input['final_time'] ?? ''));
    $photo = trim((string) ($input['photo'] ?? ''));
    $stoppedAt = filter_var($input['stopped_at'] ?? null, FILTER_VALIDATE_INT);

    if ($firstName === '' || $lastName === '' || $finalTime === '' || $stoppedAt === false) {
        return ['error' => 'Tous les champs sont obligatoires.', 'data' => []];
    }
    if (mb_strlen($firstName) > 80 || mb_strlen($lastName) > 80) {
        return ['error' => 'Le nom ou le prénom est trop long.', 'data' => []];
    }
    if ($stoppedAt < 1 || $stoppedAt > 5) {
        return ['error' => 'L’étape doit être comprise entre 1 et 5.', 'data' => []];
    }
    $finalTimeMs = parseFinalTime($finalTime);
    if ($finalTimeMs === null) {
        return ['error' => 'Le temps doit respecter le format MM:SS:mmm.', 'data' => []];
    }
    if (!isValidPhoto($photo)) {
        return ['error' => 'La photo doit être une image valide de moins de 8 Mo.', 'data' => []];
    }

    return ['error' => null, 'data' => [
        'photo' => $photo,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'final_time' => $finalTime,
        'final_time_ms' => $finalTimeMs,
        'stopped_at' => $stoppedAt,
    ]];
}