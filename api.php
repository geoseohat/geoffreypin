<?php
/* ============================================================
   API â€” traite les actions envoyees par index.php
============================================================ */
require __DIR__.'/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

function out($data){ echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function need_auth(){ if(empty($_SESSION['ok'])) out(['error'=>'non connecte']); }

$action = $_REQUEST['action'] ?? '';

/* ---------- LOGIN ---------- */
if ($action === 'login') {
    $pwd = $_POST['password'] ?? '';
    if (hash_equals(APP_PASSWORD, $pwd)) { $_SESSION['ok'] = true; out(['ok'=>true]); }
    out(['error'=>'Mot de passe incorrect']);
}
if ($action === 'logout') { session_destroy(); out(['ok'=>true]); }
if ($action === 'me') { out(['auth'=>!empty($_SESSION['ok'])]); }

need_auth();

/* ---------- LISTE des contacts + categories + expediteurs + modeles ---------- */
if ($action === 'list') {
    $rows = db()->query("
        SELECT c.*,
          (SELECT COUNT(*) FROM envois e WHERE e.contact_id = c.id AND e.statut_envoi='envoye') AS nb_envois
        FROM contacts c
        ORDER BY c.categorie, c.nom
    ")->fetchAll();
    $cats = db()->query("SELECT DISTINCT categorie FROM contacts ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
    $exp  = db()->query("SELECT * FROM expediteurs ORDER BY id")->fetchAll();
    $mods = db()->query("SELECT * FROM modeles ORDER BY maj_le DESC")->fetchAll();
    out(['contacts'=>$rows, 'categories'=>$cats, 'expediteurs'=>$exp, 'modeles'=>$mods]);
}

/* ---------- IMPORT CSV ---------- */
if ($action === 'import') {
    if (empty($_FILES['csv']['tmp_name'])) out(['error'=>'Aucun fichier recu']);
    $fh = fopen($_FILES['csv']['tmp_name'], 'r');
    if (!$fh) out(['error'=>'Lecture impossible']);
    $first = fgets($fh);
    $sep = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';
    rewind($fh);
    $headers = fgetcsv($fh, 0, $sep);
    if (!$headers) out(['error'=>'CSV vide']);
    $headers = array_map(function($h){ return mb_strtolower(trim($h)); }, $headers);
    $find = function($cands) use ($headers){
        foreach ($headers as $i=>$h) foreach ($cands as $c) if (mb_strpos($h, $c)!==false) return $i;
        return -1;
    };
    $iEmail = $find(['email','mail','courriel']);
    $iNom   = $find(['ecole','nom','name','etabliss','school','entreprise','societe','contact']);
    $iCat   = $find(['categor','source','type','ville','region','segment','liste']);
    if ($iEmail < 0) out(['error'=>'Pas de colonne email detectee. Colonnes vues : '.implode(', ',$headers)]);
    $added=0; $updated=0;
    $check = db()->prepare("SELECT 1 FROM contacts WHERE email=?");
    $stmt = db()->prepare("
        INSERT INTO contacts (email, nom, categorie) VALUES (:email, :nom, :cat)
        ON DUPLICATE KEY UPDATE
          nom = IF(VALUES(nom)<>'', VALUES(nom), nom),
          categorie = IF(VALUES(categorie)<>'', VALUES(categorie), categorie)
    ");
    while (($row = fgetcsv($fh, 0, $sep)) !== false) {
        $email = strtolower(trim($row[$iEmail] ?? ''));
        if (!$email || strpos($email,'@')===false) continue;
        $nom = $iNom>=0 ? trim($row[$iNom] ?? '') : '';
        $cat = $iCat>=0 ? trim($row[$iCat] ?? '') : '';
        if ($cat==='') $cat = '(sans categorie)';
        $check->execute([$email]); $isNew = !$check->fetchColumn();
        $stmt->execute([':email'=>$email, ':nom'=>$nom, ':cat'=>$cat]);
        $isNew ? $added++ : $updated++;
    }
    fclose($fh);
    out(['ok'=>true, 'added'=>$added, 'updated'=>$updated]);
}

/* ---------- CATEGORIE sur UN contact ---------- */
if ($action === 'set_category') {
    $id = (int)($_POST['id'] ?? 0);
    $cat = trim($_POST['categorie'] ?? '') ?: '(sans categorie)';
    db()->prepare("UPDATE contacts SET categorie=? WHERE id=?")->execute([$cat, $id]);
    out(['ok'=>true]);
}

/* ---------- CATEGORIE sur PLUSIEURS contacts (en masse) ---------- */
if ($action === 'set_category_bulk') {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    $cat = trim($_POST['categorie'] ?? '') ?: '(sans categorie)';
    if (!is_array($ids) || !count($ids)) out(['error'=>'Aucune ligne selectionnee']);
    $ids = array_map('intval', $ids);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = db()->prepare("UPDATE contacts SET categorie=? WHERE id IN ($in)");
    $st->execute(array_merge([$cat], $ids));
    out(['ok'=>true, 'count'=>count($ids)]);
}

/* ---------- SUPPRIMER plusieurs contacts ---------- */
if ($action === 'delete_bulk') {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    if (!is_array($ids) || !count($ids)) out(['error'=>'Aucune ligne']);
    $ids = array_map('intval', $ids);
    $in = implode(',', array_fill(0, count($ids), '?'));
    db()->prepare("DELETE FROM contacts WHERE id IN ($in)")->execute($ids);
    out(['ok'=>true, 'count'=>count($ids)]);
}

/* ---------- AJOUTER un contact ---------- */
if ($action === 'add_contact') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!$email || strpos($email,'@')===false) out(['error'=>'Email invalide']);
    $nom = trim($_POST['nom'] ?? '');
    $cat = trim($_POST['categorie'] ?? '') ?: '(sans categorie)';
    try {
        db()->prepare("INSERT INTO contacts (email,nom,categorie) VALUES (?,?,?)")->execute([$email,$nom,$cat]);
        out(['ok'=>true]);
    } catch (Exception $e) { out(['error'=>'Cet email existe deja']); }
}

/* ---------- EXPEDITEURS : ajouter / supprimer ---------- */
if ($action === 'add_expediteur') {
    $email = trim($_POST['email'] ?? '');
    if (!$email || strpos($email,'@')===false) out(['error'=>'Email invalide']);
    $label = trim($_POST['label'] ?? '') ?: $email;
    $nom   = trim($_POST['nom'] ?? 'Geoffrey Pin');
    db()->prepare("INSERT INTO expediteurs (label,email,nom) VALUES (?,?,?)")->execute([$label,$email,$nom]);
    out(['ok'=>true]);
}
if ($action === 'delete_expediteur') {
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare("DELETE FROM expediteurs WHERE id=?")->execute([$id]);
    out(['ok'=>true]);
}

/* ---------- MODELES : enregistrer / supprimer ---------- */
if ($action === 'save_modele') {
    $nom  = trim($_POST['nom'] ?? '') ?: 'Modele';
    $objet= trim($_POST['objet'] ?? '');
    $corps= $_POST['corps'] ?? '';
    db()->prepare("INSERT INTO modeles (nom,objet,corps) VALUES (?,?,?)")->execute([$nom,$objet,$corps]);
    out(['ok'=>true]);
}
if ($action === 'delete_modele') {
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare("DELETE FROM modeles WHERE id=?")->execute([$id]);
    out(['ok'=>true]);
}

/* ---------- Gmail API : recupere/rafraichit un access token ---------- */
function gmail_access_token($email){
    $st = db()->prepare("SELECT * FROM oauth_tokens WHERE email=?");
    $st->execute([$email]);
    $tok = $st->fetch();
    if (!$tok) return null;

    // encore valide ?
    if (!empty($tok['expires_at']) && strtotime($tok['expires_at']) > time()+30) {
        return $tok['access_token'];
    }
    // expire : on rafraichit avec le refresh_token
    if (empty($tok['refresh_token'])) return null;
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'refresh_token' => $tok['refresh_token'],
            'grant_type'    => 'refresh_token',
        ]),
    ]);
    $resp = json_decode(curl_exec($ch), true); curl_close($ch);
    if (empty($resp['access_token'])) return null;
    $expiresAt = date('Y-m-d H:i:s', time() + (int)($resp['expires_in'] ?? 3600));
    db()->prepare("UPDATE oauth_tokens SET access_token=?, expires_at=? WHERE email=?")
        ->execute([$resp['access_token'], $expiresAt, $email]);
    return $resp['access_token'];
}

/* ---------- Envoi via l'API Gmail (vrai envoi authentifie) ---------- */
function envoyer_gmail_api($accessToken, $toEmail, $objet, $corps, $fromEm, $fromNom){
    $raw = "To: $toEmail\r\n"
         . "From: ".mb_encode_mimeheader($fromNom)." <$fromEm>\r\n"
         . "Subject: ".mb_encode_mimeheader($objet, 'UTF-8')."\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n"
         . "Content-Transfer-Encoding: base64\r\n\r\n"
         . base64_encode($corps);
    $rawUrl = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

    $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer '.$accessToken, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['raw'=>$rawUrl]),
    ]);
    $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code >= 200 && $code < 300) return [true, null];
    $j = json_decode($resp, true);
    return [false, $j['error']['message'] ?? ('HTTP '.$code)];
}

/* ---------- Construire + envoyer un mail (route Gmail API ou mail() classique) ---------- */
function envoyer_mail($toEmail, $objet, $corps, $fromEm, $fromNom){
    $fromEm = $fromEm ?: 'contact@localhost';

    // Si cette adresse est connectee via OAuth Gmail, on envoie via l'API (vrai envoi)
    $token = gmail_access_token($fromEm);
    if ($token) {
        list($ok, $err) = envoyer_gmail_api($token, $toEmail, $objet, $corps, $fromEm, $fromNom);
        return $ok ? true : ['error'=>$err];
    }

    // sinon, envoi classique via le serveur (adresses du domaine)
    $headers  = "From: ".mb_encode_mimeheader($fromNom)." <".$fromEm.">\r\n";
    $headers .= "Reply-To: ".$fromEm."\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $objetEnc = mb_encode_mimeheader($objet, 'UTF-8');
    return @mail($toEmail, $objetEnc, $corps, $headers, "-f".$fromEm);
}

/* ---------- ENVOYER a UN contact ---------- */
if ($action === 'send_one') {
    $id      = (int)($_POST['id'] ?? 0);
    $objet   = trim($_POST['objet'] ?? '');
    $corps   = $_POST['corps'] ?? '';
    $fromEm  = trim($_POST['from_email'] ?? '');
    $fromNom = trim($_POST['from_nom'] ?? 'Geoffrey Pin');
    $campagne= trim($_POST['campagne'] ?? '');
    $c = db()->prepare("SELECT * FROM contacts WHERE id=?");
    $c->execute([$id]); $contact = $c->fetch();
    if (!$contact) out(['error'=>'Contact introuvable']);
    $corpsP = str_replace(['{{nom}}','{{categorie}}'], [$contact['nom'], $contact['categorie']], $corps);
    $res = envoyer_mail($contact['email'], $objet, $corpsP, $fromEm, $fromNom);
    $log = db()->prepare("INSERT INTO envois (contact_id,email,objet,expediteur,statut_envoi,erreur,campagne) VALUES (?,?,?,?,?,?,?)");
    if ($res === true) {
        $log->execute([$id,$contact['email'],$objet,$fromEm,'envoye',null,$campagne]);
        db()->prepare("UPDATE contacts SET statut='Email envoye', date_contact=NOW() WHERE id=?")->execute([$id]);
        out(['ok'=>true, 'email'=>$contact['email']]);
    } else {
        $errMsg = is_array($res) ? $res['error'] : 'mail() a echoue';
        $log->execute([$id,$contact['email'],$objet,$fromEm,'echec',$errMsg,$campagne]);
        out(['error'=>"Echec de l'envoi a ".$contact['email'].' â€” '.$errMsg]);
    }
}

/* ---------- TEST vers une adresse libre (ton email perso) ---------- */
if ($action === 'send_test') {
    $to      = strtolower(trim($_POST['to'] ?? ''));
    if (!$to || strpos($to,'@')===false) out(['error'=>'Adresse de test invalide']);
    $objet   = trim($_POST['objet'] ?? '(test)');
    $corps   = $_POST['corps'] ?? '';
    $fromEm  = trim($_POST['from_email'] ?? '');
    $fromNom = trim($_POST['from_nom'] ?? 'Geoffrey Pin');
    $corpsP  = str_replace(['{{nom}}','{{categorie}}'], ['Test','Test'], $corps);
    $res = envoyer_mail($to, '[TEST] '.$objet, $corpsP, $fromEm, $fromNom);
    if ($res === true) out(['ok'=>true, 'to'=>$to]);
    $errMsg = is_array($res) ? $res['error'] : 'mail() a echoue';
    out(['error'=>"Echec du test vers ".$to.' â€” '.$errMsg]);
}

/* ---------- CAMPAGNES : liste groupee avec performances ---------- */
if ($action === 'campagnes') {
    $rows = db()->query("
        SELECT
          campagne,
          MAX(statut_campagne) AS statut_campagne,
          MIN(envoye_le) AS debut,
          MAX(envoye_le) AS fin,
          MAX(objet) AS objet,
          MAX(expediteur) AS expediteur,
          COUNT(*) AS total,
          SUM(statut_envoi='envoye') AS envoyes,
          SUM(statut_envoi='echec') AS echecs
        FROM envois
        WHERE campagne <> ''
        GROUP BY campagne, statut_campagne
        ORDER BY debut DESC
    ")->fetchAll();
    out(['campagnes'=>$rows]);
}

/* ---------- CAMPAGNE : changer le statut (archivee / supprimee / terminee) ---------- */
if ($action === 'campagne_statut') {
    $nom = trim($_POST['campagne'] ?? '');
    $statut = trim($_POST['statut'] ?? 'terminee'); // terminee, archivee, supprimee
    if ($nom==='') out(['error'=>'Campagne manquante']);
    if ($statut==='supprimee_def') {
        db()->prepare("DELETE FROM envois WHERE campagne=?")->execute([$nom]);
        out(['ok'=>true]);
    }
    db()->prepare("UPDATE envois SET statut_campagne=? WHERE campagne=?")->execute([$statut, $nom]);
    out(['ok'=>true]);
}

/* ---------- HISTORIQUE (detail d'une campagne) ---------- */
if ($action === 'history') {
    $camp = $_POST['campagne'] ?? '';
    if ($camp !== '') {
        $st = db()->prepare("
            SELECT e.*, c.nom AS nom_ecole, c.categorie
            FROM envois e LEFT JOIN contacts c ON c.id = e.contact_id
            WHERE e.campagne=? ORDER BY e.envoye_le DESC
        ");
        $st->execute([$camp]);
        out(['envois'=>$st->fetchAll()]);
    }
    $rows = db()->query("
        SELECT e.*, c.nom AS nom_ecole, c.categorie
        FROM envois e LEFT JOIN contacts c ON c.id = e.contact_id
        ORDER BY e.envoye_le DESC LIMIT 1000
    ")->fetchAll();
    out(['envois'=>$rows]);
}

/* ---------- RESET ---------- */
if ($action === 'reset') {
    db()->exec("DELETE FROM envois");
    db()->exec("DELETE FROM contacts");
    out(['ok'=>true]);
}

out(['error'=>'Action inconnue']);

