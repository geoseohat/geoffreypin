<?php
/* ============================================================
   INSTALLATION â€” Ã€ OUVRIR UNE SEULE FOIS DANS TON NAVIGATEUR
============================================================ */
require __DIR__.'/config.php';
header('Content-Type: text/html; charset=utf-8');
echo "<style>body{font-family:system-ui;max-width:640px;margin:40px auto;padding:0 20px;line-height:1.6;color:#1a1f2b}code{background:#f3efe7;padding:2px 6px;border-radius:4px}.ok{color:#1f9d55}.err{color:#9a3b1b}</style>";
echo "<h1>Installation de l'outil</h1>";
try {
    $pdo = db();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            nom VARCHAR(255) DEFAULT '',
            categorie VARCHAR(255) DEFAULT '(sans categorie)',
            statut VARCHAR(100) DEFAULT 'a contacter',
            date_contact DATETIME NULL,
            notes TEXT DEFAULT NULL,
            cree_le DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='ok'>OK Table contacts prete.</p>";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS envois (
            id INT AUTO_INCREMENT PRIMARY KEY,
            contact_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            objet VARCHAR(500) DEFAULT '',
            expediteur VARCHAR(255) DEFAULT '',
            statut_envoi VARCHAR(50) DEFAULT 'envoye',
            erreur TEXT DEFAULT NULL,
            envoye_le DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX(contact_id), INDEX(email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='ok'>OK Table envois (historique) prete.</p>";
    // Expediteurs gerables depuis l'outil
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS expediteurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '',
            email VARCHAR(255) NOT NULL,
            nom VARCHAR(255) DEFAULT '',
            connecte TINYINT DEFAULT 0,
            cree_le DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='ok'>OK Table expediteurs prete.</p>";
    // Modeles d'email memorises
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS modeles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) DEFAULT '',
            objet VARCHAR(500) DEFAULT '',
            corps TEXT DEFAULT NULL,
            maj_le DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='ok'>OK Table modeles prete.</p>";

    // Jetons OAuth Gmail (un par adresse connectee)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS oauth_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            access_token TEXT,
            refresh_token TEXT,
            expires_at DATETIME NULL,
            maj_le DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p class='ok'>OK Table oauth_tokens prete.</p>";

    // Colonnes campagne (ignore l'erreur si elles existent deja)
    foreach ([
        "ALTER TABLE envois ADD COLUMN campagne VARCHAR(255) DEFAULT '' ",
        "ALTER TABLE envois ADD COLUMN statut_campagne VARCHAR(30) DEFAULT 'terminee' "
    ] as $sql) {
        try { $pdo->exec($sql); } catch (Exception $e) { /* colonne deja presente */ }
    }
    echo "<p class='ok'>OK Colonnes campagne pretes.</p>";

    // Pre-remplit les expediteurs depuis config.php (si table vide)
    $count = $pdo->query("SELECT COUNT(*) FROM expediteurs")->fetchColumn();
    if ($count == 0 && !empty($GLOBALS['EXPEDITEURS'])) {
        $ins = $pdo->prepare("INSERT INTO expediteurs (label,email,nom) VALUES (?,?,?)");
        foreach ($GLOBALS['EXPEDITEURS'] as $ex) {
            $ins->execute([$ex['label'], $ex['email'], $ex['nom']]);
        }
        echo "<p class='ok'>OK Expediteurs initialises depuis config.php.</p>";
    }

    echo "<h2 class='ok'>Installation reussie !</h2>";
    echo "<p>1. Supprime ce fichier <code>install.php</code> (securite). 2. Ouvre <code>index.php</code>.</p>";
} catch (Exception $e) {
    echo "<h2 class='err'>Erreur</h2><p class='err'>".htmlspecialchars($e->getMessage())."</p>";
    echo "<p>Verifie DB_NAME, DB_USER, DB_PASS dans <code>config.php</code>.</p>";
}

