<?php
/* ============================================================
   OAUTH GMAIL â€” connecte une adresse Gmail comme expediteur.
   Cette page gere : le depart vers Google, et le retour (callback).
============================================================ */
require __DIR__.'/config.php';
session_start();

if (empty($_SESSION['ok'])) { http_response_code(403); echo "Connecte-toi d'abord a l'outil."; exit; }

$SCOPES = 'https://www.googleapis.com/auth/gmail.send openid email profile';

// ---------- ETAPE 1 : redirection vers Google ----------
if (!isset($_GET['code'])) {
    $params = [
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => $SCOPES,
        'access_type'   => 'offline',   // pour obtenir un refresh_token
        'prompt'        => 'consent',   // force le refresh_token meme si deja autorise
    ];
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params));
    exit;
}

// ---------- ETAPE 2 : Google revient avec un "code" ----------
$code = $_GET['code'];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'code'          => $code,
        'client_id'     => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'grant_type'    => 'authorization_code',
    ]),
]);
$resp = curl_exec($ch);
$err  = curl_error($ch);
curl_close($ch);

$data = json_decode($resp, true);
if (!empty($err) || empty($data['access_token'])) {
    echo "<h2>Erreur de connexion Google</h2><pre>".htmlspecialchars($resp ?: $err)."</pre>";
    echo '<p><a href="index.php">Retour</a></p>';
    exit;
}

// Recupere l'adresse email du compte connecte
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$data['access_token']],
]);
$info = json_decode(curl_exec($ch), true);
curl_close($ch);

$email = $info['email'] ?? null;
$name  = $info['name'] ?? 'Geoffrey Pin';
if (!$email) { echo "Impossible de recuperer l'adresse Gmail."; exit; }

$expiresAt = date('Y-m-d H:i:s', time() + (int)($data['expires_in'] ?? 3600));
$refresh   = $data['refresh_token'] ?? null;

$pdo = db();
if ($refresh) {
    // premiere autorisation : on a le refresh token, on enregistre tout
    $pdo->prepare("
        INSERT INTO oauth_tokens (email, access_token, refresh_token, expires_at)
        VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE access_token=VALUES(access_token), refresh_token=VALUES(refresh_token), expires_at=VALUES(expires_at)
    ")->execute([$email, $data['access_token'], $refresh, $expiresAt]);
} else {
    // reautorisation sans nouveau refresh token : on met juste a jour l'access token
    $pdo->prepare("
        UPDATE oauth_tokens SET access_token=?, expires_at=? WHERE email=?
    ")->execute([$data['access_token'], $expiresAt, $email]);
}

// Ajoute cette adresse comme expediteur si elle n'existe pas encore
$exists = $pdo->prepare("SELECT 1 FROM expediteurs WHERE email=?");
$exists->execute([$email]);
if (!$exists->fetchColumn()) {
    $pdo->prepare("INSERT INTO expediteurs (label,email,nom,connecte) VALUES (?,?,?,1)")
        ->execute(['Gmail connecte', $email, $name]);
} else {
    $pdo->prepare("UPDATE expediteurs SET connecte=1 WHERE email=?")->execute([$email]);
}

header('Location: index.php?gmail=connecte');
exit;

