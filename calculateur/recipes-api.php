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
    "CREATE TABLE IF NOT EXISTS savon_recipes (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(190) NOT NULL,
        data_json LONGTEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

if ($action === 'list') {
    $rows = $pdo->query('SELECT id, name, data_json, updated_at FROM savon_recipes ORDER BY updated_at DESC, id DESC')->fetchAll();
    $recipes = [];
    foreach ($rows as $row) {
        $data = json_decode((string)$row['data_json'], true);
        if (is_array($data)) {
            $recipes[] = [
                'id' => (int)$row['id'],
                'name' => (string)$row['name'],
                'data' => $data,
                'updated_at' => (string)$row['updated_at'],
            ];
        }
    }
    respond(['ok' => true, 'recipes' => $recipes]);
}

if ($action === 'save') {
    $name = trim((string)($_POST['name'] ?? ''));
    $raw = (string)($_POST['data'] ?? '');
    if ($name === '' || mb_strlen($name) > 190) {
        respond(['ok' => false, 'error' => 'Donnez un nom à la recette.'], 422);
    }
    if ($raw === '' || strlen($raw) > 2 * 1024 * 1024) {
        respond(['ok' => false, 'error' => 'Données absentes ou trop volumineuses.'], 422);
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['rows']) || !is_array($data['rows'])) {
        respond(['ok' => false, 'error' => 'Format de recette invalide.'], 422);
    }
    $clean = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($clean === false) {
        respond(['ok' => false, 'error' => 'Impossible d’enregistrer la recette.'], 422);
    }
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare('UPDATE savon_recipes SET name = ?, data_json = ? WHERE id = ?');
        $stmt->execute([$name, $clean, $id]);
        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM savon_recipes WHERE id = ?');
            $check->execute([$id]);
            if (!$check->fetch()) {
                respond(['ok' => false, 'error' => 'Cette formulation n’existe plus.'], 404);
            }
        }
        respond(['ok' => true, 'id' => (int)$id, 'updated' => true]);
    }
    $stmt = $pdo->prepare('INSERT INTO savon_recipes (name, data_json) VALUES (?, ?)');
    $stmt->execute([$name, $clean]);
    respond(['ok' => true, 'id' => (int)$pdo->lastInsertId(), 'updated' => false]);
}

if ($action === 'delete') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        respond(['ok' => false, 'error' => 'Recette invalide.'], 422);
    }
    $stmt = $pdo->prepare('DELETE FROM savon_recipes WHERE id = ?');
    $stmt->execute([$id]);
    respond(['ok' => true]);
}

respond(['ok' => false, 'error' => 'Action inconnue.'], 404);
