<?php
// Vérifie que la requête est de type GET, puis récupère le leaderboard depuis la base de données SQLite et renvoie les résultats au format JSON trié par temps final et étape.
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'score_validation.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'error' => 'Méthode non autorisée.'], 405);
}
// Récupère les données JSON de la requête POST
$input = json_decode(file_get_contents('php://input'), true);
$input = is_array($input) ? $input : $_POST;

$result = validateScoreInput($input);
if ($result['error'] !== null) {
    sendJson(['success' => false, 'error' => $result['error']], 422);
}
$score = $result['data'];

// Enregistre le score dans la base de données SQLite
try {
    $database = getDatabase();
    $statement = $database->prepare(
        'INSERT INTO scores (photo, first_name, last_name, final_time, final_time_ms, stopped_at)
         VALUES (:photo, :first_name, :last_name, :final_time, :final_time_ms, :stopped_at)'
    );
     $statement->execute([
        ':photo' => $score['photo'],
        ':first_name' => $score['first_name'],
        ':last_name' => $score['last_name'],
        ':final_time' => $score['final_time'],
        ':final_time_ms' => $score['final_time_ms'],
        ':stopped_at' => $score['stopped_at'],
    ]);

    sendJson(['success' => true, 'id' => (int) $database->lastInsertId()], 201);
} catch (PDOException $exception) {
    sendJson(['success' => false, 'error' => 'Impossible d’enregistrer le score.'], 500);
}