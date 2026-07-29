<?php require __DIR__.'/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Mailer â€” Lumen Juris</title>
<style>
  :root{--ink:#1a1f2b;--paper:#faf8f3;--line:#e3ded2;--accent:#2d5a4a;--accent2:#3b6fe0;--warn:#9a3b1b;--muted:#6b6256;--ok:#1f9d55}
  *{box-sizing:border-box}
  body{font-family:'Inter',system-ui,sans-serif;background:var(--paper);color:var(--ink);margin:0;padding:26px 20px}
  .wrap{max-width:1160px;margin:0 auto}
  .eyebrow{font-size:12px;letter-spacing:2px;text-transform:uppercase;color:var(--accent);font-weight:600}
  h1{font-family:Georgia,serif;font-size:30px;margin:4px 0 2px;font-weight:600}
  .sub{color:var(--muted);font-size:14px;margin:0 0 20px}
  .card{background:#fff;border:1px solid var(--line);border-radius:10px;padding:18px;margin-bottom:18px}
  .card h2{font-size:13px;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin:0 0 12px}
  .step{display:inline-block;width:22px;height:22px;line-height:22px;text-align:center;border-radius:50%;background:var(--accent);color:#fff;font-size:12px;margin-right:8px;font-weight:600}
  button{font-family:inherit;cursor:pointer;border-radius:8px;font-weight:600;font-size:14px;padding:10px 16px;border:1px solid transparent}
  .b-primary{background:var(--accent);color:#fff}.b-primary:disabled{background:#b9c5bf;cursor:not-allowed}
  .b-ghost{background:transparent;border-color:var(--accent);color:var(--accent)}
  .b-warn{background:transparent;border-color:var(--warn);color:var(--warn)}
  .b-sm{padding:6px 10px;font-size:12.5px}
  input,select,textarea{font-family:inherit;font-size:14px;padding:9px 11px;border:1px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);outline:none;width:100%}
  textarea{line-height:1.55;resize:vertical}
  label.fld{display:block;font-size:13px;color:var(--muted);margin:0 0 5px;font-weight:500}
  .row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
  .pill{font-size:12px;padding:2px 9px;border-radius:20px;color:#fff}
  table{width:100%;border-collapse:collapse;font-size:13.5px}
  th{text-align:left;font-size:11px;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);padding:8px 10px;border-bottom:1px solid var(--line);cursor:pointer;white-space:nowrap}
  td{padding:8px 10px;border-bottom:1px solid var(--line)}
  tr.sent{opacity:.55}
  .scroll{max-height:520px;overflow:auto;border:1px solid var(--line);border-radius:8px}
  .muted{color:var(--muted)}
  .msg{font-size:13px;padding:10px 12px;border-radius:8px;margin-top:10px}
  .m-ok{background:#eaf6ee;color:var(--ok)}.m-err{background:#fbeee9;color:var(--warn)}.m-info{background:#eef2fb;color:var(--accent2)}
  code{background:#f3efe7;padding:1px 5px;border-radius:4px;font-size:12.5px}
  .badge{background:#f3efe7;padding:3px 9px;border-radius:20px;font-size:12px;color:var(--muted)}
  .tabs{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
  .tab{padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;border:1px solid var(--line);background:#fff;color:var(--muted)}
  .tab.active{background:var(--accent);color:#fff;border-color:var(--accent)}
  #log{font-size:12.5px;margin-top:12px;max-height:200px;overflow-y:auto;font-family:ui-monospace,monospace}
  .hidden{display:none}
  .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:10px}
</style>
</head>
<body>
<div class="wrap">
  <div class="eyebrow">Lumen Juris</div>
  <h1>Mailer</h1>
  <p class="sub">Campagnes email, base de contacts categorisee, envoi etale et historique â€” tout stocke sur ton hebergement.</p>
  <p class="badge" style="display:inline-block;margin:0 0 18px">Mise &agrave; jour GitHub &middot; 29 juillet 2026</p>

  <!-- LOGIN -->
  <div id="loginCard" class="card hidden">
    <h2>Acces</h2>
    <div class="row">
      <div style="flex:1;min-width:200px"><label class="fld">Mot de passe de l'outil</label>
        <input id="pwd" type="password" placeholder="........" onkeydown="if(event.key==='Enter')login()" /></div>
      <button class="b-primary" onclick="login()">Entrer</button>
    </div>
    <div id="loginMsg" class="msg m-err hidden"></div>
  </div>

  <!-- APP -->
  <div id="app" class="hidden">
    <div class="tabs">
      <div class="tab active" data-tab="campagne" onclick="switchTab('campagne')">Nouveau</div>
      <div class="tab" data-tab="base" onclick="switchTab('base')">Base de donnees</div>
      <div class="tab" data-tab="historique" onclick="switchTab('historique')">Campagnes</div>
      <div class="tab" data-tab="reglages" onclick="switchTab('reglages')">Reglages</div>
      <button class="b-ghost" style="margin-left:auto" onclick="logout()">Deconnexion</button>
    </div>

    <!-- ===== CAMPAGNE ===== -->
    <div id="tab-campagne">
      <div class="card">
        <h2><span class="step">1</span>Importer la base (CSV / Excel)</h2>
        <div class="row">
          <div style="flex:1;min-width:240px"><label class="fld">Fichier CSV (colonnes : email, nom, categorie)</label>
            <input type="file" id="csvFile" accept=".csv" /></div>
          <button class="b-primary" onclick="doImport()">Importer dans la base</button>
        </div>
        <div id="importMsg" class="msg m-info hidden"></div>
        <p class="muted" style="font-size:12.5px;margin-top:10px">
          Pour un fichier Excel (.xlsx), exporte-le d'abord en CSV (Fichier &gt; Enregistrer sous &gt; CSV) puis importe-le ici.
          Reimport sans doublon : les emails existants sont mis a jour, les nouveaux sont ajoutes.
        </p>
      </div>

      <div class="card">
        <h2><span class="step">2</span>Selectionner les destinataires</h2>
        <div class="row" style="margin-bottom:12px">
          <div style="flex:1;min-width:150px"><label class="fld">Categorie</label>
            <select id="fCat" onchange="render()"><option value="__all__">Toutes</option></select></div>
          <div style="flex:1;min-width:150px"><label class="fld">Statut</label>
            <select id="fStatus" onchange="render()">
              <option value="__todo__">Pas encore envoye</option>
              <option value="__all__">Tous</option>
              <option value="sent">Deja envoye</option>
            </select></div>
          <div style="flex:1;min-width:150px"><label class="fld">Recherche</label>
            <input id="fSearch" placeholder="nom ou email..." oninput="render()" /></div>
        </div>
        <div class="toolbar">
          <button class="b-ghost b-sm" onclick="selectAllVisible()">Tout cocher</button>
          <button class="b-ghost b-sm" onclick="clearSel()">Tout decocher</button>
          <span class="badge" id="selCount">0 selectionnee</span>
          <div style="flex:1"></div>
          <span class="badge" id="totalCount">0 contact</span>
        </div>
        <div class="scroll">
          <table>
            <thead><tr><th style="width:34px"></th><th>Nom</th><th>Email</th><th>Categorie</th><th>Statut</th><th>Envoye le</th></tr></thead>
            <tbody id="tbody"></tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <h2><span class="step">3</span>Ecrire l'email</h2>
        <div class="row" style="margin-bottom:12px">
          <div style="flex:1;min-width:240px"><label class="fld">Envoyer depuis</label>
            <select id="fromSel"></select></div>
          <div style="flex:1;min-width:160px"><label class="fld">Delai entre 2 envois (min)</label>
            <input type="number" id="delay" value="<?=DELAI_DEFAUT_MIN?>" min="0" max="180" /></div>
          <div style="flex:1;min-width:200px"><label class="fld">Modele enregistre (optionnel)</label>
            <select id="modeleSel" onchange="applyModele()"><option value="">â€” Nouveau message â€”</option></select></div>
        </div>
        <label class="fld">Objet</label>
        <input id="objet" placeholder="Objet de ton email" style="margin-bottom:12px" />
        <label class="fld">Message â€” variables : <code>{{nom}}</code>, <code>{{categorie}}</code></label>
        <textarea id="corps" rows="11" placeholder="Bonjour,&#10;..."></textarea>
        <div class="row" style="margin-top:10px">
          <button class="b-ghost b-sm" onclick="saveModele()">Enregistrer ce message comme modele</button>
          <button class="b-warn b-sm" onclick="deleteModele()">Supprimer le modele selectionne</button>
        </div>
      </div>


      <div class="card">
        <h2><span class="step">4</span>Test &amp; envoi</h2>
        <div class="row" style="margin-bottom:12px">
          <div style="flex:1;min-width:240px"><label class="fld">Adresse pour le test</label>
            <input id="testTo" placeholder="ton-email-perso@gmail.com" /></div>
          <button class="b-ghost" onclick="sendTest()">Envoyer un test</button>
        </div>
        <div class="row">
          <button id="startBtn" class="b-primary" onclick="startCampaign()">Demarrer l'envoi etale</button>
          <button id="stopBtn" class="b-warn hidden" onclick="stopCampaign()">Arreter</button>
        </div>
        <div id="sendMsg" class="msg m-info hidden"></div>
        <div id="log"></div>
        <p class="muted" style="font-size:12.5px;margin-top:10px">Garde cet onglet ouvert pendant la campagne. L'historique est conserve : une adresse deja contactee ne repart pas.</p>
      </div>
    </div>

    <!-- ===== BASE DE DONNEES ===== -->
    <div id="tab-base" class="hidden">
      <div class="card">
        <h2>Base de donnees complete</h2>
        <div class="row" style="margin-bottom:12px">
          <div style="flex:1;min-width:150px"><label class="fld">Categorie</label>
            <select id="bCat" onchange="renderBase()"><option value="__all__">Toutes</option></select></div>
          <div style="flex:1;min-width:150px"><label class="fld">Statut</label>
            <select id="bStatus" onchange="renderBase()">
              <option value="__all__">Tous</option>
              <option value="sent">Envoye</option>
              <option value="todo">A contacter</option>
            </select></div>
          <div style="flex:1;min-width:150px"><label class="fld">Recherche</label>
            <input id="bSearch" placeholder="nom, email, categorie..." oninput="renderBase()" /></div>
        </div>
        <div class="toolbar">
          <button class="b-ghost b-sm" onclick="bSelectAll()">Tout cocher</button>
          <button class="b-ghost b-sm" onclick="bClear()">Tout decocher</button>
          <span class="badge" id="bSelCount">0 selectionnee</span>
          <div style="flex:1"></div>
          <input id="bulkCat" placeholder="Nom de categorie a assigner" style="max-width:240px" />
          <button class="b-primary b-sm" onclick="applyBulkCat()">Assigner aux cochees</button>
          <button class="b-warn b-sm" onclick="deleteBulk()">Supprimer les cochees</button>
        </div>
        <div class="scroll">
          <table>
            <thead><tr>
              <th style="width:34px"></th>
              <th onclick="sortBase('nom')">Nom</th>
              <th onclick="sortBase('email')">Email</th>
              <th onclick="sortBase('categorie')">Categorie</th>
              <th onclick="sortBase('statut')">Statut</th>
              <th onclick="sortBase('nb_envois')">Nb envois</th>
              <th onclick="sortBase('date_contact')">Envoye le</th>
              <th onclick="sortBase('cree_le')">Ajoute le</th>
            </tr></thead>
            <tbody id="baseBody"></tbody>
          </table>
        </div>
        <p class="muted" style="font-size:12.5px;margin-top:10px">Clique sur un en-tete de colonne pour trier. Coche des lignes puis assigne une categorie en masse.</p>
      </div>
    </div>

    <!-- ===== CAMPAGNES ===== -->
    <div id="tab-historique" class="hidden">
      <div class="card">
        <h2>Mes campagnes</h2>
        <div class="row" style="margin-bottom:12px">
          <select id="campFilter" onchange="loadCampagnes()">
            <option value="actives">En cours / terminees</option>
            <option value="archivee">Archivees</option>
            <option value="supprimee">Supprimees</option>
            <option value="all">Toutes</option>
          </select>
          <button class="b-ghost b-sm" onclick="loadCampagnes()">Rafraichir</button>
        </div>
        <div class="scroll">
          <table>
            <thead><tr><th>Campagne</th><th>Objet</th><th>Envoyes</th><th>Echecs</th><th>Taux</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody id="campBody"></tbody>
          </table>
        </div>
        <div id="campDetail"></div>
      </div>
    </div>

    <!-- ===== REGLAGES ===== -->
    <div id="tab-reglages" class="hidden">
      <div class="card">
        <h2>Adresses expeditrices</h2>
        <div class="row" style="margin-bottom:14px">
          <a href="oauth.php" class="b-primary" style="text-decoration:none;display:inline-block">Connecter mon Gmail</a>
          <span class="muted" style="font-size:13px">Ouvre la fenetre Google. Apres autorisation, ton Gmail apparait ci-dessous avec un badge Â« Connecte Â».</span>
        </div>
        <div class="scroll" style="max-height:240px;margin-bottom:14px">
          <table><thead><tr><th>Label</th><th>Email</th><th>Nom affiche</th><th>Connexion</th><th></th></tr></thead>
          <tbody id="expBody"></tbody></table>
        </div>
        <div class="row">
          <div style="flex:1;min-width:140px"><label class="fld">Label</label><input id="expLabel" placeholder="Pro, perso..." /></div>
          <div style="flex:1;min-width:180px"><label class="fld">Email</label><input id="expEmail" placeholder="contact@mondomaine.fr" /></div>
          <div style="flex:1;min-width:140px"><label class="fld">Nom affiche</label><input id="expNom" placeholder="Geoffrey Pin" /></div>
          <button class="b-primary" onclick="addExpediteur()">Ajouter (adresse de domaine)</button>
        </div>
        <p class="muted" style="font-size:12.5px;margin-top:10px">Pour une adresse de ton domaine (@geoffrey-pin.fr), l'envoi part directement du serveur. Pour Gmail, utilise le bouton Â« Connecter mon Gmail Â» ci-dessus â€” l'envoi sera alors un vrai envoi authentifie via ton compte.</p>
      </div>

      <div class="card">
        <h2>Gestion de la base</h2>
        <details>
          <summary class="muted" style="cursor:pointer;font-size:13px">Ajouter un contact a la main</summary>
          <div class="row" style="margin-top:10px">
            <div style="flex:1"><label class="fld">Email</label><input id="newEmail" /></div>
            <div style="flex:1"><label class="fld">Nom</label><input id="newNom" /></div>
            <div style="flex:1"><label class="fld">Categorie</label><input id="newCat" /></div>
            <button class="b-ghost" onclick="addContact()">Ajouter</button>
          </div>
        </details>
        <div class="row" style="margin-top:14px">
          <button class="b-warn" onclick="doReset()">Vider toute la base (contacts + historique)</button>
        </div>
        <div id="reglagesMsg" class="msg m-info hidden"></div>
        <p class="muted" style="font-size:12.5px;margin-top:10px">Pour importer des contacts, utilise l'etape 1 de l'onglet Campagne.</p>
      </div>
    </div>
  </div>
</div>

<script>
let DB=[], CATS=[], EXP=[], MODS=[];
let selected=new Set(), bSelected=new Set(), running=false, timer=null;
let bSort={key:'categorie',dir:1};

async function api(action, fd){
  const opts={method:'POST', body: fd || new FormData()};
  opts.body.append('action', action);
  const r=await fetch('api.php', opts); return r.json();
}
function esc(s){return (s||'').toString().replace(/[&<>"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));}
function fmtDate(d){ return d ? new Date(d.replace(' ','T')).toLocaleString('fr-FR') : 'â€”'; }

// AUTH
async function checkAuth(){
  const r=await api('me');
  if(r.auth){
    document.getElementById('app').classList.remove('hidden');
    await loadList();
    if(new URLSearchParams(location.search).get('gmail')==='connecte'){
      switchTab('reglages');
      showReglages('Gmail connecte avec succes ! Il apparait maintenant dans la liste des expediteurs.','ok');
      history.replaceState(null,'',location.pathname);
    }
  }
  else document.getElementById('loginCard').classList.remove('hidden');
}
async function login(){
  const fd=new FormData(); fd.append('password', document.getElementById('pwd').value);
  const r=await api('login', fd);
  if(r.ok) location.reload();
  else { const m=document.getElementById('loginMsg'); m.classList.remove('hidden'); m.textContent=r.error||'Erreur'; }
}
async function logout(){ await api('logout'); location.reload(); }

// TABS
function switchTab(t){
  document.querySelectorAll('.tab').forEach(el=>el.classList.toggle('active', el.dataset.tab===t));
  ['campagne','base','historique','reglages'].forEach(x=>document.getElementById('tab-'+x).classList.toggle('hidden', x!==t));
  if(t==='historique') loadCampagnes();
  if(t==='base'){ loadList(); }
}

// LOAD
async function loadList(){
  const r=await api('list');
  if(r.error){
    showReglages('Erreur de chargement : '+r.error+'. Si le message mentionne une table manquante (expediteurs, modeles), relance install.php une fois.','err');
    return;
  }
  DB=r.contacts; CATS=r.categories; EXP=r.expediteurs||[]; MODS=r.modeles||[];
  // categories dans les filtres
  ['fCat','bCat'].forEach(idsel=>{
    const sel=document.getElementById(idsel); const cur=sel.value;
    sel.innerHTML='<option value="__all__">Toutes</option>'+CATS.map(c=>`<option>${esc(c)}</option>`).join('');
    if([...sel.options].some(o=>o.value===cur)) sel.value=cur;
  });
  // expediteurs
  const fs=document.getElementById('fromSel');
  fs.innerHTML=EXP.map(e=>`<option value="${esc(e.email)}|${esc(e.nom)}">${esc(e.label)} â€” ${esc(e.email)}</option>`).join('')
    || '<option value="contact@localhost|Geoffrey">Aucun expediteur configure</option>';
  // modeles
  const ms=document.getElementById('modeleSel');
  ms.innerHTML='<option value="">â€” Nouveau message â€”</option>'+MODS.map(m=>`<option value="${m.id}">${esc(m.nom)}</option>`).join('');
  renderExp();
  render(); renderBase();
}

// ===== CAMPAGNE table =====
function visible(){
  const cat=document.getElementById('fCat').value, st=document.getElementById('fStatus').value;
  const q=document.getElementById('fSearch').value.toLowerCase().trim();
  return DB.filter(d=>{
    if(cat!=='__all__' && (d.categorie||'(sans categorie)')!==cat) return false;
    const sent=d.statut==='Email envoye';
    if(st==='__todo__' && sent) return false;
    if(st==='sent' && !sent) return false;
    if(q && !((d.nom||'').toLowerCase().includes(q)||d.email.includes(q))) return false;
    return true;
  });
}
function render(){
  const rows=visible(), tb=document.getElementById('tbody');
  tb.innerHTML=rows.map(d=>{
    const sent=d.statut==='Email envoye';
    return `<tr class="${sent?'sent':''}">
      <td><input type="checkbox" ${selected.has(String(d.id))?'checked':''} ${sent?'disabled':''} onchange="toggle('${d.id}',this.checked)"></td>
      <td>${esc(d.nom)||'â€”'}</td><td class="muted">${esc(d.email)}</td><td class="muted">${esc(d.categorie)}</td>
      <td>${sent?'<span class="pill" style="background:#3b6fe0">Envoye</span>':'<span class="pill" style="background:#e07b1f">A contacter</span>'}</td>
      <td class="muted">${fmtDate(d.date_contact)}</td></tr>`;
  }).join('');
  document.getElementById('totalCount').textContent=DB.length+' contact'+(DB.length>1?'s':'');
  updateCount();
}
function toggle(id,ch){ ch?selected.add(id):selected.delete(id); updateCount(); }
function updateCount(){ document.getElementById('selCount').textContent=selected.size+' selectionnee'+(selected.size>1?'s':''); }
function selectAllVisible(){ visible().forEach(d=>{ if(d.statut!=='Email envoye') selected.add(String(d.id)); }); render(); }
function clearSel(){ selected.clear(); render(); }

// ===== BASE table =====
function baseVisible(){
  const cat=document.getElementById('bCat').value, st=document.getElementById('bStatus').value;
  const q=document.getElementById('bSearch').value.toLowerCase().trim();
  let rows=DB.filter(d=>{
    if(cat!=='__all__' && (d.categorie||'(sans categorie)')!==cat) return false;
    const sent=d.statut==='Email envoye';
    if(st==='sent' && !sent) return false;
    if(st==='todo' && sent) return false;
    if(q && !((d.nom||'').toLowerCase().includes(q)||d.email.includes(q)||(d.categorie||'').toLowerCase().includes(q))) return false;
    return true;
  });
  rows.sort((a,b)=>{
    let x=a[bSort.key], y=b[bSort.key];
    if(bSort.key==='nb_envois'){ x=Number(x)||0; y=Number(y)||0; return (x-y)*bSort.dir; }
    x=(x||''); y=(y||'');
    return x<y?-bSort.dir:x>y?bSort.dir:0;
  });
  return rows;
}
function renderBase(){
  const rows=baseVisible(), tb=document.getElementById('baseBody');
  tb.innerHTML=rows.map(d=>{
    const sent=d.statut==='Email envoye';
    return `<tr>
      <td><input type="checkbox" ${bSelected.has(String(d.id))?'checked':''} onchange="bToggle('${d.id}',this.checked)"></td>
      <td>${esc(d.nom)||'â€”'}</td><td class="muted">${esc(d.email)}</td>
      <td><input value="${esc(d.categorie)}" class="b-sm" style="padding:4px 8px" onchange="setCat('${d.id}',this.value)"></td>
      <td>${sent?'<span class="pill" style="background:#3b6fe0">Envoye</span>':'<span class="pill" style="background:#e07b1f">A contacter</span>'}</td>
      <td style="text-align:center"><strong>${d.nb_envois||0}</strong></td>
      <td class="muted">${fmtDate(d.date_contact)}</td><td class="muted">${fmtDate(d.cree_le)}</td></tr>`;
  }).join('');
  document.getElementById('bSelCount').textContent=bSelected.size+' selectionnee'+(bSelected.size>1?'s':'');
}
function bToggle(id,ch){ ch?bSelected.add(id):bSelected.delete(id); renderBase(); }
function bSelectAll(){ baseVisible().forEach(d=>bSelected.add(String(d.id))); renderBase(); }
function bClear(){ bSelected.clear(); renderBase(); }
function sortBase(k){ if(bSort.key===k) bSort.dir*=-1; else {bSort.key=k;bSort.dir=1;} renderBase(); }
async function applyBulkCat(){
  const cat=document.getElementById('bulkCat').value.trim();
  if(!cat){ alert('Tape un nom de categorie.'); return; }
  if(!bSelected.size){ alert('Coche au moins une ligne.'); return; }
  const fd=new FormData(); fd.append('ids', JSON.stringify([...bSelected])); fd.append('categorie', cat);
  const r=await api('set_category_bulk', fd);
  if(r.ok){ bSelected.clear(); await loadList(); }
}
async function deleteBulk(){
  if(!bSelected.size){ alert('Coche au moins une ligne.'); return; }
  if(!confirm('Supprimer '+bSelected.size+' contact(s) ?')) return;
  const fd=new FormData(); fd.append('ids', JSON.stringify([...bSelected]));
  const r=await api('delete_bulk', fd);
  if(r.ok){ bSelected.clear(); await loadList(); }
}
async function setCat(id,val){ const fd=new FormData(); fd.append('id',id); fd.append('categorie',val); await api('set_category',fd); loadList(); }

// IMPORT / CONTACT
async function doImport(){
  const f=document.getElementById('csvFile').files[0];
  if(!f){ showImport('Choisis un fichier CSV.','err'); return; }
  const fd=new FormData(); fd.append('csv',f); showImport('Import en cours...','info');
  const r=await api('import',fd);
  if(r.ok) showImport(r.added+' ajoutees, '+r.updated+' mises a jour.','ok'); else showImport(r.error||'Erreur','err');
  loadList();
}
function showImport(t,k){ const m=document.getElementById('importMsg'); m.classList.remove('hidden'); m.className='msg m-'+(k==='err'?'err':k==='ok'?'ok':'info'); m.textContent=t; }
function showReglages(t,k){ const m=document.getElementById('reglagesMsg'); m.classList.remove('hidden'); m.className='msg m-'+(k==='err'?'err':k==='ok'?'ok':'info'); m.textContent=t; }
async function doReset(){ if(!confirm('Vider TOUTE la base ?')) return; await api('reset'); selected.clear(); bSelected.clear(); loadList(); showReglages('Base videe.','info'); }
async function addContact(){
  const fd=new FormData();
  fd.append('email',document.getElementById('newEmail').value);
  fd.append('nom',document.getElementById('newNom').value);
  fd.append('categorie',document.getElementById('newCat').value);
  const r=await api('add_contact',fd);
  if(r.ok){ document.getElementById('newEmail').value=''; document.getElementById('newNom').value=''; loadList(); showReglages('Contact ajoute.','ok'); }
  else showReglages(r.error,'err');
}

// EXPEDITEURS
function renderExp(){
  document.getElementById('expBody').innerHTML=EXP.map(e=>`
    <tr><td>${esc(e.label)}</td><td class="muted">${esc(e.email)}</td><td class="muted">${esc(e.nom)}</td>
    <td>${e.connecte==1?'<span class="pill" style="background:#1f9d55">Connecte (Gmail)</span>':'<span class="pill" style="background:#9a3b1b">Non connecte</span>'}</td>
    <td><button class="b-warn b-sm" onclick="delExp('${e.id}')">Suppr</button></td></tr>`).join('')
    || '<tr><td colspan="5" class="muted">Aucune adresse. Ajoute-en une ci-dessous ou connecte ton Gmail.</td></tr>';
}
async function addExpediteur(){
  const email=document.getElementById('expEmail').value.trim();
  if(!email || !email.includes('@')){ showReglages('Saisis une adresse email valide.','err'); return; }
  const fd=new FormData();
  fd.append('label',document.getElementById('expLabel').value || email);
  fd.append('email',email);
  fd.append('nom',document.getElementById('expNom').value || 'Geoffrey Pin');
  const r=await api('add_expediteur',fd);
  if(r.ok){ document.getElementById('expEmail').value=''; document.getElementById('expLabel').value=''; document.getElementById('expNom').value=''; loadList(); showReglages('Adresse expeditrice ajoutee.','ok'); }
  else showReglages(r.error||'Erreur lors de l\'ajout.','err');
}
async function delExp(id){ const fd=new FormData(); fd.append('id',id); await api('delete_expediteur',fd); loadList(); }

// MODELES
function applyModele(){
  const id=document.getElementById('modeleSel').value;
  if(!id) return;
  const m=MODS.find(x=>String(x.id)===id); if(!m) return;
  document.getElementById('objet').value=m.objet||'';
  document.getElementById('corps').value=m.corps||'';
}
async function saveModele(){
  const nom=prompt('Nom du modele ?'); if(!nom) return;
  const fd=new FormData();
  fd.append('nom',nom);
  fd.append('objet',document.getElementById('objet').value);
  fd.append('corps',document.getElementById('corps').value);
  const r=await api('save_modele',fd); if(r.ok) loadList();
}
async function deleteModele(){
  const id=document.getElementById('modeleSel').value;
  if(!id){ alert('Choisis un modele a supprimer.'); return; }
  if(!confirm('Supprimer ce modele ?')) return;
  const fd=new FormData(); fd.append('id',id); await api('delete_modele',fd); loadList();
}

// ENVOI
function fromParts(){ return document.getElementById('fromSel').value.split('|'); }
async function sendTest(){
  const to=document.getElementById('testTo').value.trim();
  if(!to){ showSend('Saisis une adresse de test.','err'); return; }
  const [em,nom]=fromParts(); const fd=new FormData();
  fd.append('to',to); fd.append('objet',document.getElementById('objet').value);
  fd.append('corps',document.getElementById('corps').value);
  fd.append('from_email',em); fd.append('from_nom',nom);
  showSend('Envoi du test a '+to+'...','info');
  const r=await api('send_test',fd);
  if(r.ok) showSend('Test envoye a '+r.to,'ok'); else showSend(r.error||'Echec','err');
}
let currentCampagne='';
function sendOneId(id){
  const [em,nom]=fromParts(); const fd=new FormData();
  fd.append('id',id); fd.append('objet',document.getElementById('objet').value);
  fd.append('corps',document.getElementById('corps').value);
  fd.append('from_email',em); fd.append('from_nom',nom);
  fd.append('campagne',currentCampagne);
  return api('send_one',fd);
}
async function startCampaign(){
  const queue=[...selected].map(id=>DB.find(d=>String(d.id)===id)).filter(d=>d && d.statut!=='Email envoye');
  if(!queue.length){ showSend('Selection vide ou deja envoyee.','err'); return; }
  if(!document.getElementById('objet').value.trim()){ showSend('Mets un objet.','err'); return; }
  // nom de campagne automatique
  const nomC=prompt('Nom de cette campagne ?', 'Campagne du '+new Date().toLocaleDateString('fr-FR'));
  if(nomC===null) return;
  currentCampagne=nomC || ('Campagne '+Date.now());
  running=true;
  document.getElementById('startBtn').classList.add('hidden');
  document.getElementById('stopBtn').classList.remove('hidden');
  const delay=Math.max(0,Number(document.getElementById('delay').value))*60*1000;
  if(delay===0){
    logLine('Campagne "'+currentCampagne+'" â€” envoi immediat de '+queue.length+' emails.');
    for(let i=0;i<queue.length && running;i++){
      const c=queue[i]; const r=await sendOneId(c.id);
      if(r.ok){ logLine('OK '+c.email+' ('+(i+1)+'/'+queue.length+')'); selected.delete(String(c.id)); }
      else logLine('ECHEC '+c.email+' â€” '+(r.error||''));
    }
    await loadList(); finish(); return;
  }
  logLine('Campagne "'+currentCampagne+'" demarree â€” '+queue.length+' emails, 1 toutes les '+(delay/60000)+' min.');
  let i=0;
  const step=async()=>{
    if(!running){ logLine('Arretee.'); return; }
    if(i>=queue.length){ finish(); return; }
    const c=queue[i]; const r=await sendOneId(c.id);
    if(r.ok){ logLine('OK '+c.email+' ('+(i+1)+'/'+queue.length+')'); selected.delete(String(c.id)); }
    else logLine('ECHEC '+c.email+' â€” '+(r.error||''));
    await loadList(); i++;
    if(i<queue.length && running){ showSend('Prochain envoi dans '+(delay/60000)+' min...','info'); timer=setTimeout(step,delay); }
    else finish();
  };
  step();
}
function stopCampaign(){ running=false; clearTimeout(timer); resetBtns(); }
function finish(){ running=false; clearTimeout(timer); resetBtns(); showSend('Campagne terminee.','ok'); logLine('â€” Fin â€”'); loadList(); }
function resetBtns(){ document.getElementById('startBtn').classList.remove('hidden'); document.getElementById('stopBtn').classList.add('hidden'); }
function showSend(t,k){ const m=document.getElementById('sendMsg'); m.classList.remove('hidden'); m.className='msg m-'+(k==='err'?'err':k==='ok'?'ok':'info'); m.textContent=t; }
function logLine(t){ const l=document.getElementById('log'); l.innerHTML='<div>'+new Date().toLocaleTimeString('fr-FR')+' â€” '+esc(t)+'</div>'+l.innerHTML; }

// CAMPAGNES
async function loadCampagnes(){
  const r=await api('campagnes');
  const filter=document.getElementById('campFilter').value;
  const tb=document.getElementById('campBody');
  document.getElementById('campDetail').innerHTML='';
  if(!r.campagnes || !r.campagnes.length){ tb.innerHTML='<tr><td colspan="8" class="muted">Aucune campagne pour le moment.</td></tr>'; return; }
  const rows=r.campagnes.filter(c=>{
    if(filter==='all') return true;
    if(filter==='actives') return c.statut_campagne!=='archivee' && c.statut_campagne!=='supprimee';
    return c.statut_campagne===filter;
  });
  tb.innerHTML=rows.map(c=>{
    const taux=c.total>0?Math.round(c.envoyes/c.total*100):0;
    const badge=c.statut_campagne==='archivee'?'#8a8a8a':c.statut_campagne==='supprimee'?'#9a3b1b':'#1f9d55';
    return `<tr>
      <td><a href="#" onclick="campDetail('${esc(c.campagne)}');return false" style="color:#2d5a4a;font-weight:600">${esc(c.campagne)}</a></td>
      <td class="muted">${esc(c.objet)}</td>
      <td>${c.envoyes}</td><td>${c.echecs}</td>
      <td><strong>${taux}%</strong></td>
      <td class="muted">${fmtDate(c.fin)}</td>
      <td><span class="pill" style="background:${badge}">${esc(c.statut_campagne)}</span></td>
      <td>
        <button class="b-ghost b-sm" onclick="campAction('${esc(c.campagne)}','archivee')">Archiver</button>
        <button class="b-warn b-sm" onclick="campAction('${esc(c.campagne)}','supprimee_def')">Suppr</button>
      </td></tr>`;
  }).join('') || '<tr><td colspan="8" class="muted">Aucune campagne dans ce filtre.</td></tr>';
}
async function campAction(nom,statut){
  if(statut==='supprimee_def' && !confirm('Supprimer definitivement la campagne "'+nom+'" et son historique ?')) return;
  const fd=new FormData(); fd.append('campagne',nom); fd.append('statut',statut);
  await api('campagne_statut',fd); loadCampagnes();
}
async function campDetail(nom){
  const fd=new FormData(); fd.append('campagne',nom);
  const r=await api('history',fd);
  const d=document.getElementById('campDetail');
  if(!r.envois){ d.innerHTML=''; return; }
  d.innerHTML='<h2 style="margin-top:18px">Detail : '+esc(nom)+'</h2><div class="scroll"><table>'+
    '<thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Depuis</th><th>Statut</th></tr></thead><tbody>'+
    r.envois.map(e=>`<tr><td class="muted">${fmtDate(e.envoye_le)}</td><td>${esc(e.nom_ecole)||'â€”'}</td>
      <td class="muted">${esc(e.email)}</td><td class="muted">${esc(e.expediteur)}</td>
      <td>${e.statut_envoi==='envoye'?'<span class="pill" style="background:#1f9d55">Envoye</span>':'<span class="pill" style="background:#9a3b1b">Echec</span>'}</td></tr>`).join('')+
    '</tbody></table></div>';
}

checkAuth();
</script>
</body>
</html>

