<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
Auth::start();

if (!Auth::geschuetzt()) {
    header('Location: index.php');
    exit;
}
$fehler = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (Auth::anmelden((string) ($_POST['passwort'] ?? ''))) {
        header('Location: index.php');
        exit;
    }
    $fehler = 'Passwort stimmt nicht.';
}
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Pizza Support CRM — Anmeldung</title>
<link rel="stylesheet" href="assets/app.css">
</head>
<body>
<div style="max-width:360px;margin:14vh auto;padding:0 20px">
  <div class="marke" style="margin-bottom:18px"><b>Pizza Support</b><span>Akquise Freiburg</span></div>
  <form method="post" class="block">
    <div class="feld">
      <label for="passwort">Passwort</label>
      <input type="password" id="passwort" name="passwort" autofocus autocomplete="current-password" required>
    </div>
    <?php if ($fehler !== ''): ?><p class="sperrhinweis" style="margin:10px 0 0"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></p><?php endif; ?>
    <div class="zeile ende" style="margin-top:14px"><button class="knopf haupt" type="submit">Anmelden</button></div>
  </form>
  <p class="leise">Interne Akquisedaten — bitte nicht öffentlich erreichbar betreiben.</p>
</div>
</body>
</html>
