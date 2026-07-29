<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes applications — Geoffrey Pin</title>
<style>
:root{--ink:#18211d;--paper:#f7f4ed;--card:#fff;--line:#ded8cb;--green:#2d5a4a;--blue:#315f9d;--muted:#6b6256}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;font-family:Inter,system-ui,sans-serif;color:var(--ink);background:radial-gradient(circle at top right,#e8efe9 0,transparent 38%),var(--paper)}
main{width:min(1040px,calc(100% - 32px));margin:0 auto;padding:72px 0}
.eyebrow{text-transform:uppercase;letter-spacing:.18em;font-size:12px;font-weight:700;color:var(--green)}
h1{font-family:Georgia,serif;font-size:clamp(36px,7vw,64px);font-weight:500;line-height:1.02;margin:10px 0 14px}
.intro{max-width:650px;color:var(--muted);font-size:17px;line-height:1.6;margin-bottom:42px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}
.card{display:flex;flex-direction:column;min-height:260px;padding:26px;border:1px solid var(--line);border-radius:18px;background:var(--card);text-decoration:none;color:inherit;box-shadow:0 12px 35px rgba(42,49,44,.06);transition:transform .2s,box-shadow .2s}
.card:hover{transform:translateY(-4px);box-shadow:0 18px 45px rgba(42,49,44,.12)}
.icon{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#e7efe9;color:var(--green);font-size:24px}
.card.soap .icon{background:#e8eef8;color:var(--blue)}
.card h2{font-family:Georgia,serif;font-size:28px;margin:26px 0 8px}
.card p{color:var(--muted);line-height:1.55;margin:0}
.open{margin-top:auto;padding-top:24px;font-weight:700;color:var(--green)}
.soap .open{color:var(--blue)}
footer{margin-top:38px;color:var(--muted);font-size:13px}
</style>
</head>
<body>
<main>
  <div class="eyebrow">Geoffrey Pin</div>
  <h1>Mes applications</h1>
  <p class="intro">Un espace unique pour retrouver et utiliser mes outils. Choisissez une application pour commencer.</p>
  <section class="grid">
    <a class="card" href="mailer.php">
      <div class="icon">✉</div>
      <h2>Mailer</h2>
      <p>Gérer les contacts, préparer les campagnes et envoyer les emails avec Gmail.</p>
      <div class="open">Ouvrir le Mailer →</div>
    </a>
    <a class="card soap" href="calculateur/">
      <div class="icon">⚗</div>
      <h2>Calculateur de savons</h2>
      <p>Calculer une recette de saponification, la soude, le surgraissage et les proportions.</p>
      <div class="open">Ouvrir le calculateur →</div>
    </a>
    <a class="card" href="labo-deo/">
      <div class="icon">◉</div>
      <h2>Labo Déo</h2>
      <p>Créer, comparer, noter et archiver les formulations de déodorants.</p>
      <div class="open">Ouvrir le Labo Déo →</div>
    </a>
  </section>
  <footer>Portail d’applications · Mise à jour automatique par GitHub et SSH</footer>
</main>
</body>
</html>
