<?php
/**
 * Freigabe-Workflow.
 *
 * Bewusst kompakt: eine Datei, keine Frameworks, ein Passwort aus der .env.
 * Alles hier ist hinter der Anmeldung, wird nicht indexiert und steht in der
 * robots.txt auf Disallow.
 */

declare(strict_types=1);

header('X-Robots-Tag: noindex, nofollow');
session_boot();

$pfad = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';

// -----------------------------------------------------------------------
// Anmeldung
// -----------------------------------------------------------------------
if ($pfad === '/admin/logout') {
    $_SESSION = [];
    session_destroy();
    redirect('/admin');
}

$angemeldet = !empty($_SESSION['admin']);
$loginFehler = null;

if (!$angemeldet && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['passwort'])) {
    if (!csrf_valid($_POST['_token'] ?? null)) {
        $loginFehler = 'Die Sitzung war abgelaufen. Bitte noch einmal.';
    } elseif (!rate_limit_ok('admin-login', 8, 900)) {
        $loginFehler = 'Zu viele Versuche. Bitte in 15 Minuten noch einmal.';
    } else {
        $hash = (string) env('ADMIN_PASS_HASH', '');
        $user = (string) env('ADMIN_USER', 'admin');
        $userOk = hash_equals($user, (string) ($_POST['benutzer'] ?? ''));
        // Immer beide Prüfungen durchlaufen, damit die Antwortzeit nichts verrät.
        $passOk = $hash !== '' && password_verify((string) $_POST['passwort'], $hash);
        if ($userOk && $passOk) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            redirect('/admin');
        }
        $loginFehler = 'Benutzername oder Passwort stimmen nicht.';
        usleep(400000);
    }
}

if (!$angemeldet) {
    admin_login($loginFehler);
    exit;
}

// -----------------------------------------------------------------------
// Aktionen
// -----------------------------------------------------------------------
$meldung = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['aktion'])) {
    if (!csrf_valid($_POST['_token'] ?? null)) {
        $meldung = 'Sitzung abgelaufen, nichts geändert.';
    } else {
        $id      = (int) ($_POST['id'] ?? 0);
        $tabelle = (string) ($_POST['tabelle'] ?? '');
        $aktion  = (string) ($_POST['aktion'] ?? '');

        $erlaubteTabellen = ['gastro_bestellungen', 'werbebuchungen', 'pizzeria_empfehlungen'];
        $erlaubteStatus   = ['freigegeben', 'abgelehnt', 'neu', 'kontaktiert', 'erledigt'];

        if (in_array($tabelle, $erlaubteTabellen, true) && in_array($aktion, $erlaubteStatus, true) && $id > 0) {
            db_run(
                "UPDATE {$tabelle} SET status = ?, status_am = ? WHERE id = ?",
                [$aktion, gmdate('Y-m-d H:i:s'), $id]
            );
            $meldung = 'Status auf „' . $aktion . '" gesetzt.';
        } elseif ($aktion === 'qr-freigeben' && $id > 0) {
            db_run('UPDATE qr_redirects SET aktiv = 1, gesperrt_am = ? WHERE id = ?', [gmdate('Y-m-d H:i:s'), $id]);
            $meldung = 'QR-Weiterleitung ist scharf, das Ziel ist jetzt fest.';
        } elseif ($aktion === 'qr-aus' && $id > 0) {
            db_run('UPDATE qr_redirects SET aktiv = 0 WHERE id = ?', [$id]);
            $meldung = 'QR-Weiterleitung abgeschaltet.';
        } else {
            $meldung = 'Unbekannte Aktion, nichts geändert.';
        }
    }
}

admin_seite($meldung);
exit;


// =======================================================================
// Ausgabe
// =======================================================================

function admin_kopf(string $titel): void
{
    $nonce = $GLOBALS['csp_nonce'] ?? '';
    echo '<!doctype html><html lang="de"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<meta name="robots" content="noindex,nofollow">'
       . '<title>' . e($titel) . '</title>'
       . '<style nonce="' . e($nonce) . '">';
    readfile(APP_ROOT . '/public/assets/css/admin.css');
    echo '</style></head><body>';
}

function admin_login(?string $fehler): void
{
    admin_kopf('Anmeldung');
    echo '<div class="login"><h1>Pizza Support – Verwaltung</h1>';
    if ($fehler) {
        echo '<p class="fehler">' . e($fehler) . '</p>';
    }
    echo '<form method="post">' . csrf_field()
       . '<label for="b">Benutzer</label><input id="b" name="benutzer" autocomplete="username" required>'
       . '<label for="p">Passwort</label><input id="p" type="password" name="passwort" autocomplete="current-password" required>'
       . '<button type="submit">Anmelden</button></form></div></body></html>';
}

/** Ein Knopf, der einen Status setzt. */
function admin_knopf(string $tabelle, int $id, string $aktion, string $label, string $klasse = ''): string
{
    return '<form method="post" class="inline">' . csrf_field()
         . '<input type="hidden" name="tabelle" value="' . e($tabelle) . '">'
         . '<input type="hidden" name="id" value="' . $id . '">'
         . '<input type="hidden" name="aktion" value="' . e($aktion) . '">'
         . '<button class="' . e($klasse) . '" type="submit">' . e($label) . '</button></form>';
}

function admin_status(string $status): string
{
    return '<span class="status status-' . e($status) . '">' . e($status) . '</span>';
}

function admin_seite(?string $meldung): void
{
    admin_kopf('Verwaltung');
    $f = fortschritt();

    echo '<header class="admin-kopf"><h1>Pizza Support – Verwaltung</h1>'
       . '<a href="/admin/logout">Abmelden</a></header>';

    if ($meldung) {
        echo '<p class="meldung">' . e($meldung) . '</p>';
    }

    // Überblick
    echo '<section class="kacheln">'
       . '<div class="kachel"><strong>' . zahl($f['betriebe']) . ' / ' . zahl($f['betriebe_ziel']) . '</strong><span>Betriebe freigegeben</span></div>'
       . '<div class="kachel"><strong>' . preis($f['budget_cent']) . '</strong><span>von ' . preis($f['budget_ziel_cent']) . ' Werbevolumen</span></div>'
       . '<div class="kachel"><strong>' . zahl($f['kartons']) . '</strong><span>Kartons vorgemerkt</span></div>'
       . '<div class="kachel ' . ($f['ausgeloest'] ? 'kachel-gut' : '') . '"><strong>' . $f['gesamt_prozent'] . ' %</strong><span>' . ($f['ausgeloest'] ? 'Startschuss erreicht' : 'bis zum Startschuss') . '</span></div>'
       . '</section>';

    // Gastro
    echo '<h2>Gastro-Bestellungen</h2><div class="tabelle-wrap"><table><thead><tr>'
       . '<th>Betrieb</th><th>Kontakt</th><th>Menge</th><th>Karte</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
    foreach (db_all('SELECT * FROM gastro_bestellungen ORDER BY id DESC LIMIT 200') as $z) {
        $positionen = db_all('SELECT format, menge FROM bestellpositionen WHERE bestellung_id = ? ORDER BY format', [(int) $z['id']]);
        $gesamt = 0;
        $zeilen = [];
        foreach ($positionen as $p) {
            $gesamt += (int) $p['menge'];
            $fm = kartonformat((string) $p['format']);
            $zeilen[] = zahl((int) $p['menge']) . '× ' . ($fm['label'] ?? $p['format'] . ' cm');
        }
        echo '<tr>'
           . '<td><strong>' . e($z['betrieb']) . '</strong><br><small>' . e($z['strasse']) . ', ' . e($z['plz']) . ' ' . e($z['ort']) . '<br>' . e($z['betriebsart']) . '</small></td>'
           . '<td><small>' . e($z['vorname']) . ' ' . e($z['nachname']) . '<br>' . e($z['email']) . '<br>' . e((string) decrypt_field($z['telefon_enc'])) . '</small></td>'
           . '<td><strong>' . zahl($gesamt) . '</strong><br><small>' . e(implode(', ', $zeilen)) . '</small>'
           . ((int) $z['versand_zuschlag_ok'] ? '<br><small>+ Versandzuschlag</small>' : '') . '</td>'
           . '<td>' . ((int) $z['karte_ok'] ? 'ja' : 'nein') . '</td>'
           . '<td>' . admin_status($z['status']) . '</td>'
           . '<td class="aktionen">'
           . admin_knopf('gastro_bestellungen', (int) $z['id'], 'freigegeben', 'Freigeben', 'gut')
           . admin_knopf('gastro_bestellungen', (int) $z['id'], 'abgelehnt', 'Ablehnen', 'schlecht')
           . '</td></tr>';
    }
    echo '</tbody></table></div>';

    // Werbung
    echo '<h2>Werbebuchungen</h2><div class="tabelle-wrap"><table><thead><tr>'
       . '<th>Firma</th><th>Kontakt</th><th>Flächen</th><th>Wert</th><th>Motiv</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
    foreach (db_all('SELECT * FROM werbebuchungen ORDER BY id DESC LIMIT 200') as $z) {
        $formate = json_decode((string) $z['formate'], true) ?: [];
        $labels  = [];
        foreach ($formate as $fid) {
            $wf = werbeformat((string) $fid);
            $labels[] = $wf ? $wf['label'] : (string) $fid;
        }
        echo '<tr>'
           . '<td><strong>' . e($z['firma']) . '</strong><br><small>' . e($z['art']) . '<br>' . e((string) $z['plz']) . ' ' . e((string) $z['ort']) . '</small></td>'
           . '<td><small>' . e($z['ansprechpartner']) . '<br>' . e($z['email']) . '<br>' . e((string) decrypt_field($z['telefon_enc'])) . '</small></td>'
           . '<td><small>' . e(implode(', ', $labels)) . ($z['coupon'] ? '<br>mit Coupon' : '') . '</small></td>'
           . '<td>' . e(preis((int) $z['summe_cent'])) . '<br><small>netto</small></td>'
           . '<td><small>' . e((string) ($z['motiv_name'] ?: ((int) $z['motiv_spaeter'] ? 'wird nachgereicht' : '–'))) . '</small></td>'
           . '<td>' . admin_status($z['status']) . '</td>'
           . '<td class="aktionen">'
           . admin_knopf('werbebuchungen', (int) $z['id'], 'freigegeben', 'Freigeben', 'gut')
           . admin_knopf('werbebuchungen', (int) $z['id'], 'abgelehnt', 'Ablehnen', 'schlecht')
           . '</td></tr>';
    }
    echo '</tbody></table></div>';

    // Empfehlungen
    echo '<h2>Pizzeria-Vorschläge</h2><div class="tabelle-wrap"><table><thead><tr>'
       . '<th>Pizzeria</th><th>Hinweis</th><th>Melder</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
    foreach (db_all('SELECT * FROM pizzeria_empfehlungen ORDER BY id DESC LIMIT 200') as $z) {
        echo '<tr>'
           . '<td><strong>' . e($z['name']) . '</strong><br><small>' . e((string) $z['strasse']) . ' ' . e((string) $z['plz']) . ' ' . e((string) $z['ort']) . '</small></td>'
           . '<td><small>' . e((string) $z['hinweis']) . '</small></td>'
           . '<td><small>' . e((string) ($z['melder_email'] ?: 'anonym')) . '</small></td>'
           . '<td>' . admin_status($z['status']) . '</td>'
           . '<td class="aktionen">'
           . admin_knopf('pizzeria_empfehlungen', (int) $z['id'], 'kontaktiert', 'Kontaktiert')
           . admin_knopf('pizzeria_empfehlungen', (int) $z['id'], 'erledigt', 'Erledigt', 'gut')
           . '</td></tr>';
    }
    echo '</tbody></table></div>';

    // QR
    echo '<h2>QR-Weiterleitungen</h2><p class="hinweis-klein">Nach dem Freigeben ist das Ziel fest – so steht es in den AGB. Abschalten geht immer.</p>'
       . '<div class="tabelle-wrap"><table><thead><tr>'
       . '<th>Code</th><th>Ziel</th><th>Für</th><th>Klicks</th><th>Aktiv</th><th>Aktion</th></tr></thead><tbody>';
    foreach (db_all('SELECT * FROM qr_redirects ORDER BY id DESC LIMIT 200') as $z) {
        $klicks = (int) db_value('SELECT COUNT(*) FROM qr_klicks WHERE redirect_id = ?', [(int) $z['id']], 0);
        echo '<tr>'
           . '<td><code>/r/' . e($z['code']) . '</code></td>'
           . '<td><small>' . e($z['ziel_url']) . '</small></td>'
           . '<td><small>' . e((string) $z['beschreibung']) . '</small></td>'
           . '<td>' . zahl($klicks) . '</td>'
           . '<td>' . ((int) $z['aktiv'] ? 'ja' : 'nein') . '</td>'
           . '<td class="aktionen">'
           . ((int) $z['aktiv']
                ? '<form method="post" class="inline">' . csrf_field() . '<input type="hidden" name="id" value="' . (int) $z['id'] . '"><input type="hidden" name="aktion" value="qr-aus"><button class="schlecht" type="submit">Abschalten</button></form>'
                : '<form method="post" class="inline">' . csrf_field() . '<input type="hidden" name="id" value="' . (int) $z['id'] . '"><input type="hidden" name="aktion" value="qr-freigeben"><button class="gut" type="submit">Freigeben</button></form>')
           . '</td></tr>';
    }
    echo '</tbody></table></div>';

    echo '<p class="fuss-hinweis">Auskunft und Löschung einzelner Personen laufen über die Kommandozeile: '
       . '<code>php bin/export.php auskunft adresse@example.com</code> und '
       . '<code>php bin/export.php loeschen adresse@example.com</code>.</p>';

    echo '</body></html>';
}
