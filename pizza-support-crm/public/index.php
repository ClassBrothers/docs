<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
Auth::verlangen();
$stand = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = 'pizzasupport'");
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Pizza Support — Akquise-CRM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/app.css?v=1">
</head>
<body>

<header class="kopf">
  <div class="kopf-innen">
    <div class="marke"><b>Pizza Support</b><span>Akquise Freiburg</span></div>
    <div class="zielchip" id="zielchip" title="Fortschritt Richtung Startschuss">
      <span>Startschuss</span><b><span id="ziel-ist">0</span>/<span id="ziel-soll">50</span></b>
      <span class="balken"><i id="ziel-balken" style="width:0%"></i></span>
    </div>
    <nav class="reiter" id="reiter">
      <button data-ansicht="kanban" class="aktiv">Kanban</button>
      <button data-ansicht="liste">Liste</button>
      <button data-ansicht="dashboard">Dashboard</button>
      <button data-ansicht="vorlagen">Vorlagen</button>
    </nav>
  </div>

  <div class="filter" id="filterleiste">
    <label>Priorität</label>
    <div class="prio-gruppe" id="prio-gruppe">
      <button data-prio="A">A</button><button data-prio="B">B</button><button data-prio="C">C</button>
    </div>
    <select id="f-stadtteil"><option value="">Alle Stadtteile</option></select>
    <select id="f-fokus"><option value="">Pizza-Fokus: alle</option></select>
    <select id="f-tag"><option value="">Alle Tags</option></select>
    <button class="schalter" id="f-faellig">⏰ Nur Follow-up fällig</button>
    <button class="schalter" id="f-ohnekontakt">Nur unkontaktiert</button>
    <input type="search" id="f-suche" placeholder="Suche: Name, Straße, Notiz …">
    <div class="rechts">
      <span class="treffer" id="treffer">– Betriebe</span>
      <button class="knopf" id="btn-neu">+ Betrieb</button>
      <button class="knopf" id="btn-brief">📄 Briefe</button>
      <button class="knopf" id="btn-etiketten">🏷 Etiketten</button>
      <button class="knopf" id="btn-csv">⤓ CSV</button>
    </div>
  </div>
</header>

<main id="haupt">
  <section id="ansicht-kanban"><div class="tafel" id="tafel"></div></section>
  <section id="ansicht-liste" hidden>
    <div class="spaltenwahl" id="spaltenwahl"></div>
    <div class="tabelle-huelle"><table class="liste" id="tabelle"><thead></thead><tbody></tbody></table></div>
  </section>
  <section id="ansicht-dashboard" hidden><div class="dash" id="dash"></div></section>
  <section id="ansicht-vorlagen" hidden><div class="vorlagen-gitter" id="vorlagen"></div></section>
</main>

<div id="schublade-huelle"></div>
<div id="meldungen"></div>

<script>window.PS_STAND = <?= $stand ?>;</script>
<script src="assets/app.js?v=1"></script>
</body>
</html>
