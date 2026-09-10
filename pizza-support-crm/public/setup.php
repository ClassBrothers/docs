<?php
declare(strict_types=1);

/**
 * Einrichtungsassistent fuer die Installation per FTP.
 *
 * Auf den meisten Webpaketen gibt es keine Kommandozeile — Passwort setzen und
 * Import laufen deshalb hier ueber den Browser.
 *
 * Schutz: Die Seite arbeitet nur, solange die Datei data/setup-erlaubt.txt
 * existiert. Die legst du per FTP an und loeschst sie nach der Einrichtung
 * wieder. So kann niemand sonst den Assistenten aufrufen.
 */

require __DIR__ . '/../src/bootstrap.php';

const FREIGABE = PS_ROOT . '/data/setup-erlaubt.txt';
const KONFIG   = PS_ROOT . '/config.local.php';

$freigegeben = is_file(FREIGABE);
$meldungen = [];

/** Was in config.local.php stehen muesste, falls PHP nicht schreiben darf. */
$konfigEntwurf = '';

/** config.local.php lesen, mit neuen Werten zusammenfuehren und zurueckschreiben. */
function konfigSchreiben(array $neu): bool
{
    global $konfigEntwurf;
    $vorhanden = is_file(KONFIG) ? (require KONFIG) : [];
    $zusammen = array_replace_recursive(is_array($vorhanden) ? $vorhanden : [], $neu);
    $inhalt = "<?php\n// Von setup.php erzeugt — von Hand aenderbar.\nreturn "
        . var_export($zusammen, true) . ";\n";
    if (file_put_contents(KONFIG, $inhalt) !== false) {
        return true;
    }
    // Schreibt PHP nicht, kann der Inhalt wenigstens per FTP eingesetzt werden.
    $konfigEntwurf = $inhalt;
    return false;
}

if ($freigegeben && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $schritt = (string) ($_POST['schritt'] ?? '');

    if ($schritt === 'passwort') {
        $passwort = (string) ($_POST['passwort'] ?? '');
        if (mb_strlen($passwort) < 8) {
            $meldungen[] = ['fehler', 'Bitte mindestens 8 Zeichen verwenden.'];
        } elseif (konfigSchreiben(['passwort_hash' => password_hash($passwort, PASSWORD_DEFAULT)])) {
            $meldungen[] = ['gut', 'Passwort gespeichert. Ab jetzt fragt das Tool beim Aufruf danach.'];
        } else {
            $meldungen[] = ['fehler', 'config.local.php ist nicht schreibbar (Rechte auf 666 setzen). Ersatzweise den Inhalt unten per FTP in die Datei kopieren.'];
        }
    }

    if ($schritt === 'absender') {
        $absender = [];
        foreach (['firma', 'strasse', 'plz_ort', 'telefon', 'email', 'web'] as $feld) {
            $absender[$feld] = trim((string) ($_POST[$feld] ?? ''));
        }
        $ziel = (int) ($_POST['ziel_gewonnen'] ?? 50);
        if (konfigSchreiben(['absender' => $absender, 'ziel_gewonnen' => max(1, $ziel), 'etikettenformat' => (string) ($_POST['etikettenformat'] ?? 'L7160')])) {
            $meldungen[] = ['gut', 'Absenderdaten gespeichert.'];
        } else {
            $meldungen[] = ['fehler', 'config.local.php ist nicht schreibbar (Rechte auf 666 setzen). Ersatzweise den Inhalt unten per FTP in die Datei kopieren.'];
        }
    }

    if ($schritt === 'import') {
        try {
            $quelle = '';
            if (!empty($_FILES['datei']['tmp_name']) && is_uploaded_file($_FILES['datei']['tmp_name'])) {
                $endung = strtolower(pathinfo((string) $_FILES['datei']['name'], PATHINFO_EXTENSION));
                if (!in_array($endung, ['xlsx', 'csv'], true)) {
                    throw new RuntimeException('Nur .xlsx oder .csv möglich.');
                }
                $quelle = PS_ROOT . '/data/import-' . date('Ymd-His') . '.' . $endung;
                move_uploaded_file($_FILES['datei']['tmp_name'], $quelle);
            } else {
                $quelle = PS_ROOT . '/data/' . basename((string) ($_POST['vorhandene'] ?? ''));
                if (!is_file($quelle)) {
                    throw new RuntimeException('Keine Datei ausgewählt.');
                }
            }
            $bericht = Importer::ausDatei($quelle, 'Akquiseliste');
            $meldungen[] = ['gut', sprintf(
                '%d Zeilen gelesen — %d neu angelegt, %d aktualisiert, %d übersprungen.',
                $bericht['gelesen'], $bericht['neu'], $bericht['aktualisiert'], $bericht['uebersprungen']
            )];
        } catch (Throwable $e) {
            $meldungen[] = ['fehler', 'Import fehlgeschlagen: ' . $e->getMessage()];
        }
    }
}

/* ---------- Pruefungen ---------- */

$pruefungen = [];
$pruefungen[] = ['PHP-Version ' . PHP_VERSION, version_compare(PHP_VERSION, '8.1.0', '>='), 'PHP 8.1 oder neuer nötig — beim Hoster umstellen.'];
foreach (['zip' => 'Excel-Import', 'pdo_sqlite' => 'Datenbank', 'mbstring' => 'Umlaute', 'curl' => 'Anreicherung'] as $erweiterung => $wofuer) {
    $pruefungen[] = ["Erweiterung {$erweiterung} ({$wofuer})", extension_loaded($erweiterung), "Beim Hoster aktivieren — ohne {$erweiterung} fehlt: {$wofuer}."];
}
$pruefungen[] = ['Ordner data/ beschreibbar', is_writable(PS_ROOT . '/data'), 'Rechte auf 755 (notfalls 775) setzen.'];
$pruefungen[] = ['config.local.php beschreibbar', is_file(KONFIG) ? is_writable(KONFIG) : is_writable(PS_ROOT), 'Datei per FTP anlegen und auf 644 setzen.'];
$pruefungen[] = ['mod_rewrite aktiv', function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules(), true) : null, 'Ohne Rewrite muss das Document Root direkt auf public/ zeigen.'];

$hashGesetzt = ((string) ps_cfg('passwort_hash', '')) !== '';
$anzahl = 0;
$dbFehler = '';
try {
    Db::pdo();
    $anzahl = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = 'pizzasupport'");
} catch (Throwable $e) {
    $dbFehler = $e->getMessage();
}

/** Prueft, ob die Datenbankdatei ueber das Web erreichbar ist. */
function datenbankOeffentlich(): ?bool
{
    $db = (string) ps_cfg('db_pfad');
    if (!is_file($db)) {
        return null;
    }
    $basis = ($_SERVER['HTTPS'] ?? '') !== '' ? 'https://' : 'http://';
    $basis .= (string) ($_SERVER['HTTP_HOST'] ?? '');
    $pfad = dirname((string) ($_SERVER['REQUEST_URI'] ?? '/')); // .../public
    $url = $basis . rtrim(dirname($pfad), '/') . '/data/' . basename($db);

    $griff = curl_init($url);
    curl_setopt_array($griff, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6, CURLOPT_SSL_VERIFYPEER => false]);
    $erfolg = curl_exec($griff);
    $status = (int) curl_getinfo($griff, CURLINFO_RESPONSE_CODE);
    curl_close($griff);
    if ($erfolg === false || $status === 0) {
        return null;
    }
    return $status === 200;
}
$oeffentlich = extension_loaded('curl') ? datenbankOeffentlich() : null;

$importDateien = array_values(array_filter(
    scandir(PS_ROOT . '/data') ?: [],
    static fn (string $d): bool => (bool) preg_match('/\.(xlsx|csv)$/i', $d)
));

function symbol(?bool $wert): string
{
    return $wert === true ? '✓' : ($wert === false ? '✕' : '?');
}
function farbe(?bool $wert): string
{
    return $wert === true ? 'var(--gruen)' : ($wert === false ? 'var(--rot)' : 'var(--tinte-leise)');
}
$e = static fn (?string $s): string => htmlspecialchars((string) $s, ENT_QUOTES);
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Pizza Support CRM — Einrichtung</title>
<link rel="stylesheet" href="assets/app.css">
<style>
  .huelle { max-width: 780px; margin: 30px auto 60px; padding: 0 20px; }
  .schritt { counter-increment: schritt; }
  .schritt > h3::before { content: counter(schritt) ". "; color: var(--gold); }
  .huelle { counter-reset: schritt; }
  .pruefliste { list-style: none; margin: 0; padding: 0; font-size: 13px; }
  .pruefliste li { display: flex; gap: 9px; padding: 5px 0; border-bottom: 1px solid #f0eade; align-items: baseline; }
  .pruefliste li:last-child { border-bottom: 0; }
  .pruefliste .zeichen { font-weight: 700; width: 14px; flex: 0 0 14px; }
  .pruefliste .rat { color: var(--tinte-leise); font-size: 12px; }
</style>
</head>
<body>
<div class="huelle">
  <div class="marke" style="margin-bottom:6px"><b>Pizza Support</b><span>Einrichtung</span></div>
  <p class="leise">Dieser Assistent ersetzt die Kommandozeile. Nach der Einrichtung löschst du <code>data/setup-erlaubt.txt</code> per FTP — dann ist er gesperrt.</p>

<?php if (!$freigegeben): ?>
  <div class="warnbanner"><div>
    <b>Gesperrt.</b> Lege per FTP die leere Datei <code>data/setup-erlaubt.txt</code> an und lade diese Seite neu.
    Das ist der Nachweis, dass du Zugriff auf den Server hast — sonst könnte jeder die Einrichtung aufrufen.
  </div></div>
</div></body></html>
<?php exit; endif; ?>

<?php if ($konfigEntwurf !== ''): ?>
  <div class="block">
    <h3>Inhalt für config.local.php</h3>
    <p class="leise" style="margin-top:0">PHP durfte die Datei nicht schreiben. Kopiere diesen Text per FTP vollständig in <code>config.local.php</code> im Hauptverzeichnis.</p>
    <textarea class="vorschau" style="width:100%;min-height:220px;font-family:ui-monospace,monospace;font-size:12px" readonly><?= $e($konfigEntwurf) ?></textarea>
  </div>
<?php endif; ?>

<?php foreach ($meldungen as [$art, $text]): ?>
  <div class="warnbanner" style="<?= $art === 'gut' ? 'background:#f0f6ee;border-color:#c3ddba;border-left-color:var(--gruen)' : '' ?>">
    <div><?= $e($text) ?></div>
  </div>
<?php endforeach; ?>

  <div class="block schritt">
    <h3>Systemcheck</h3>
    <ul class="pruefliste">
      <?php foreach ($pruefungen as [$titel, $ergebnis, $rat]): ?>
        <li>
          <span class="zeichen" style="color:<?= farbe($ergebnis) ?>"><?= symbol($ergebnis) ?></span>
          <span><?= $e($titel) ?><?php if ($ergebnis !== true): ?><br><span class="rat"><?= $e($rat) ?></span><?php endif; ?></span>
        </li>
      <?php endforeach; ?>
      <li>
        <span class="zeichen" style="color:<?= farbe($oeffentlich === null ? null : !$oeffentlich) ?>"><?= symbol($oeffentlich === null ? null : !$oeffentlich) ?></span>
        <span>Datenbank nicht öffentlich abrufbar
          <?php if ($oeffentlich === true): ?>
            <br><span class="rat" style="color:var(--rot)"><b>Achtung:</b> die SQLite-Datei ist über das Web herunterladbar. Document Root auf <code>public/</code> zeigen lassen oder prüfen, ob die <code>.htaccess</code> greift.</span>
          <?php elseif ($oeffentlich === null): ?>
            <br><span class="rat">Konnte nicht automatisch geprüft werden — ruf <code>…/data/crm.sqlite</code> einmal im Browser auf. Kommt ein Download, ist es offen.</span>
          <?php endif; ?>
        </span>
      </li>
    </ul>
  </div>

  <form method="post" class="block schritt">
    <h3>Passwort setzen</h3>
    <p class="leise" style="margin-top:0">Pflicht, sobald das Tool auf einem Server liegt. Ohne Passwort antwortet es nur auf Aufrufe von 127.0.0.1.</p>
    <input type="hidden" name="schritt" value="passwort">
    <div class="feldgitter">
      <div class="feld breit">
        <label for="passwort">Passwort (mindestens 8 Zeichen)</label>
        <input type="password" id="passwort" name="passwort" autocomplete="new-password" required>
      </div>
    </div>
    <div class="zeile ende" style="margin-top:12px">
      <span class="leise" style="margin-right:auto">Status: <?= $hashGesetzt ? 'gesetzt ✓' : 'noch nicht gesetzt' ?></span>
      <button class="knopf haupt" type="submit">Passwort speichern</button>
    </div>
  </form>

  <form method="post" class="block schritt">
    <h3>Absenderdaten</h3>
    <p class="leise" style="margin-top:0">Erscheinen im Briefkopf, in der Fußzeile und als Platzhalter in den Vorlagen. Die Telefonnummer ist der Call-to-Action im Anschreiben.</p>
    <input type="hidden" name="schritt" value="absender">
    <div class="feldgitter">
      <div class="feld breit"><label>Firma</label><input name="firma" value="<?= $e((string) ps_cfg('absender.firma', 'Class Brothers GmbH')) ?>"></div>
      <div class="feld"><label>Straße</label><input name="strasse" value="<?= $e((string) ps_cfg('absender.strasse', '')) ?>"></div>
      <div class="feld"><label>PLZ und Ort</label><input name="plz_ort" value="<?= $e((string) ps_cfg('absender.plz_ort', '')) ?>"></div>
      <div class="feld"><label>Telefon</label><input name="telefon" value="<?= $e((string) ps_cfg('absender.telefon', '')) ?>"></div>
      <div class="feld"><label>E-Mail</label><input name="email" value="<?= $e((string) ps_cfg('absender.email', '')) ?>"></div>
      <div class="feld"><label>Website</label><input name="web" value="<?= $e((string) ps_cfg('absender.web', 'pizzasupport.de')) ?>"></div>
      <div class="feld"><label>Ziel (Startschuss)</label><input type="number" name="ziel_gewonnen" min="1" value="<?= (int) ps_cfg('ziel_gewonnen', 50) ?>"></div>
      <div class="feld breit"><label>Etikettenbogen</label>
        <select name="etikettenformat">
          <?php foreach (PS_ETIKETTEN as $schluessel => $bogen): ?>
            <option value="<?= $e($schluessel) ?>" <?= ps_cfg('etikettenformat') === $schluessel ? 'selected' : '' ?>><?= $e($bogen['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="zeile ende" style="margin-top:12px"><button class="knopf haupt" type="submit">Speichern</button></div>
  </form>

  <form method="post" enctype="multipart/form-data" class="block schritt">
    <h3>Akquiseliste importieren</h3>
    <p class="leise" style="margin-top:0">
      <?php if ($dbFehler !== ''): ?>
        <span style="color:var(--rot)">Datenbank nicht erreichbar: <?= $e($dbFehler) ?></span>
      <?php else: ?>
        Aktuell <b><?= $anzahl ?></b> Betriebe in der Datenbank. Ein erneuter Import frischt nur Stammdaten auf — Stufen, Kontaktversuche und Notizen bleiben erhalten.
      <?php endif; ?>
    </p>
    <input type="hidden" name="schritt" value="import">
    <div class="feldgitter">
      <?php if ($importDateien): ?>
        <div class="feld breit"><label>Datei aus dem Ordner data/</label>
          <select name="vorhandene">
            <?php foreach ($importDateien as $datei): ?>
              <option value="<?= $e($datei) ?>"><?= $e($datei) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="feld breit"><label>… oder hier hochladen (.xlsx / .csv)</label><input type="file" name="datei" accept=".xlsx,.csv"></div>
    </div>
    <div class="zeile ende" style="margin-top:12px"><button class="knopf haupt" type="submit">Importieren</button></div>
  </form>

  <div class="block schritt">
    <h3>Fertig</h3>
    <ol style="margin:0;padding-left:18px;line-height:1.7;font-size:13.5px">
      <li><b>Wichtig:</b> <code>data/setup-erlaubt.txt</code> per FTP löschen — sonst bleibt dieser Assistent für jeden offen.</li>
      <li>Zum Tool: <a href="index.php">index.php</a></li>
      <li>Sicherung: Die Datei <code>data/crm.sqlite</code> ist deine komplette Datenbank. Ab und zu per FTP herunterladen.</li>
    </ol>
  </div>
</div>
</body>
</html>
