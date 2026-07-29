<?php
declare(strict_types=1);

require __DIR__ . '/../config.php';

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $payload, int $status = 200): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$action = (string)($_REQUEST['action'] ?? '');

if ($action === 'me') {
    respond(['ok' => true, 'auth' => !empty($_SESSION['ok'])]);
}

if ($action === 'login') {
    $password = (string)($_POST['password'] ?? '');
    if (!hash_equals((string)APP_PASSWORD, $password)) {
        respond(['ok' => false, 'error' => 'Mot de passe incorrect.'], 401);
    }
    session_regenerate_id(true);
    $_SESSION['ok'] = true;
    respond(['ok' => true, 'auth' => true]);
}

if (empty($_SESSION['ok'])) {
    respond(['ok' => false, 'error' => 'Authentification requise.'], 401);
}

$pdo = db();
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS labo_deo_state (
        id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
        data_json LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

if ($action === 'load') {
    $stmt = $pdo->query('SELECT data_json, updated_at FROM labo_deo_state WHERE id = 1');
    $row = $stmt->fetch();
    $data = null;
    if ($row) {
        $decoded = json_decode((string)$row['data_json'], true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
    respond([
        'ok' => true,
        'data' => $data,
        'updated_at' => $row['updated_at'] ?? null,
    ]);
}

if ($action === 'save') {
    $raw = (string)($_POST['data'] ?? '');
    if ($raw === '' || strlen($raw) > 5 * 1024 * 1024) {
        respond(['ok' => false, 'error' => 'Données absentes ou trop volumineuses.'], 422);
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['edited'], $data['deleted'])
        || !is_array($data['edited']) || !is_array($data['deleted'])) {
        respond(['ok' => false, 'error' => 'Format de données invalide.'], 422);
    }

    $cleanJson = json_encode(
        ['edited' => $data['edited'], 'deleted' => array_values($data['deleted'])],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($cleanJson === false) {
        respond(['ok' => false, 'error' => 'Impossible d’encoder les données.'], 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO labo_deo_state (id, data_json) VALUES (1, ?)
         ON DUPLICATE KEY UPDATE data_json = VALUES(data_json)'
    );
    $stmt->execute([$cleanJson]);
    respond(['ok' => true]);
}

respond(['ok' => false, 'error' => 'Action inconnue.'], 404);
