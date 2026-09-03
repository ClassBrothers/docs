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
        } elseif ($aktion === 'liefermenge-setzen' && $id > 0) {
            $menge = (int) ($_POST['geliefert_menge'] ?? -1);
            if ($menge < 0) {
                $meldung = 'Ungültige Menge, nichts geändert.';
            } else {
                db_run('UPDATE gastro_bestellungen SET geliefert_menge = ? WHERE id = ?', [$menge, $id]);
                $meldung = 'Gelieferte Menge gespeichert.';
            }
        } elseif ($aktion === 'loeschen' && in_array($tabelle, $erlaubteTabellen, true) && $id > 0) {
            // Vollstaendige Loeschung fuer den Auskunfts-/Loeschanspruch aus der
            // Datenschutzerklaerung. Abhaengige Zeilen (bestellpositionen) raeumt
            // die Fremdschluessel-Loeschweitergabe automatisch mit auf; eine
            // hochgeladene Motivdatei muss von Hand von der Platte verschwinden.
            if ($tabelle === 'werbebuchungen') {
                $motiv = db_value('SELECT motiv_pfad FROM werbebuchungen WHERE id = ?', [$id], null);
                if ($motiv) {
                    @unlink(APP_ROOT . '/storage/uploads/' . $motiv);
                }
            }
            db_run("DELETE FROM {$tabelle} WHERE id = ?", [$id]);
            $meldung = 'Eintrag endgültig gelöscht.';
        } elseif ($aktion === 'geocode' && in_array($tabelle, ['gastro_bestellungen', 'werbebuchungen'], true) && $id > 0) {
            $z = db_all("SELECT * FROM {$tabelle} WHERE id = ?", [$id])[0] ?? null;
            if (!$z) {
                $meldung = 'Eintrag nicht gefunden.';
            } else {
                $adresse = $tabelle === 'gastro_bestellungen'
                    ? $z['strasse'] . ', ' . $z['plz'] . ' ' . $z['ort'] . ', Deutschland'
                    : $z['plz'] . ' ' . $z['ort'] . ', Deutschland';
                $treffer = geocode_adresse($adresse);
                if ($treffer === null) {
                    $meldung = 'Adresse nicht gefunden. Schreibweise prüfen oder später erneut versuchen.';
                } else {
                    db_run("UPDATE {$tabelle} SET lat = ?, lon = ? WHERE id = ?", [$treffer['lat'], $treffer['lon'], $id]);
                    $meldung = 'Koordinaten gefunden und gespeichert.';
                }
            }
        } elseif ($aktion === 'adresse-setzen' && in_array($tabelle, ['gastro_bestellungen', 'werbebuchungen'], true) && $id > 0) {
            $plz = trim((string) ($_POST['plz'] ?? ''));
            $ort = trim((string) ($_POST['ort'] ?? ''));
            $strasse = trim((string) ($_POST['strasse'] ?? ''));
            if ($plz === '' || $ort === '' || ($tabelle === 'gastro_bestellungen' && $strasse === '')) {
                $meldung = 'Adresse unvollständig, nichts gespeichert.';
            } else {
                // lat/lon geloescht: die alten Koordinaten gehoerten zur alten
                // Adresse und waeren nach der Korrektur falsch - der Knopf
                // "Koordinaten ermitteln" erscheint dadurch automatisch wieder.
                if ($tabelle === 'gastro_bestellungen') {
                    db_run('UPDATE gastro_bestellungen SET strasse = ?, plz = ?, ort = ?, lat = NULL, lon = NULL WHERE id = ?', [$strasse, $plz, $ort, $id]);
                } else {
                    db_run('UPDATE werbebuchungen SET plz = ?, ort = ?, lat = NULL, lon = NULL WHERE id = ?', [$plz, $ort, $id]);
                }
                $meldung = 'Adresse gespeichert. Koordinaten bitte neu ermitteln.';
            }
        } elseif ($aktion === 'qr-freigeben' && $id > 0) {
            db_run('UPDATE qr_redirects SET aktiv = 1, gesperrt_am = ? WHERE id = ?', [gmdate('Y-m-d H:i:s'), $id]);
            $meldung = 'QR-Weiterleitung ist scharf, das Ziel ist jetzt fest.';
        } elseif ($aktion === 'qr-aus' && $id > 0) {
            db_run('UPDATE qr_redirects SET aktiv = 0 WHERE id = ?', [$id]);
            $meldung = 'QR-Weiterleitung abgeschaltet.';
        } elseif ($aktion === 'migrieren') {
            $ergebnis = migrationen_ausfuehren();
            if ($ergebnis['fehler'] !== null) {
                $meldung = 'Fehler beim Einspielen: ' . $ergebnis['fehler'];
            } elseif ($ergebnis['eingespielt'] === []) {
                $meldung = 'Datenbank ist bereits aktuell, nichts einzuspielen.';
            } else {
                $meldung = 'Eingespielt: ' . implode(', ', $ergebnis['eingespielt']) . '.';
            }
        } elseif ($aktion === 'gruendungspartner-anlegen') {
            $meldung = gruendungspartner_anlegen()['hinweis'];
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

/** Ein Knopf, der einen Status setzt - erscheint nur, wenn der Datensatz nicht schon dort steht. */
function admin_knopf(string $tabelle, array $z, string $aktion, string $label, string $klasse = ''): string
{
    if ($z['status'] === $aktion) {
        return '';
    }
    return '<form method="post" class="inline">' . csrf_field()
         . '<input type="hidden" name="tabelle" value="' . e($tabelle) . '">'
         . '<input type="hidden" name="id" value="' . (int) $z['id'] . '">'
         . '<input type="hidden" name="aktion" value="' . e($aktion) . '">'
         . '<button class="' . e($klasse) . '" type="submit">' . e($label) . '</button></form>';
}

/** Koordinaten ueber Nominatim ermitteln - erscheint nur ohne vorhandene Koordinaten. */
function admin_knopf_geocode(string $tabelle, int $id): string
{
    return '<form method="post" class="inline">' . csrf_field()
         . '<input type="hidden" name="tabelle" value="' . e($tabelle) . '">'
         . '<input type="hidden" name="id" value="' . $id . '">'
         . '<input type="hidden" name="aktion" value="geocode">'
         . '<button type="submit">Koordinaten ermitteln</button></form>';
}

/** Zugeklapptes Adressformular - Tippfehler in Strasse/PLZ/Ort korrigieren. */
function admin_adresse_bearbeiten(string $tabelle, int $id, ?string $strasse, string $plz, string $ort): string
{
    $strassenfeld = $strasse !== null
        ? '<label>Straße <input name="strasse" value="' . e($strasse) . '" required></label>'
        : '';
    return '<details class="adresse-bearbeiten"><summary>Adresse ändern</summary>'
         . '<form method="post" class="inline">' . csrf_field()
         . '<input type="hidden" name="tabelle" value="' . e($tabelle) . '">'
         . '<input type="hidden" name="id" value="' . $id . '">'
         . '<input type="hidden" name="aktion" value="adresse-setzen">'
         . $strassenfeld
         . '<label>PLZ <input name="plz" value="' . e($plz) . '" required></label>'
         . '<label>Ort <input name="ort" value="' . e($ort) . '" required></label>'
         . '<button type="submit">Speichern</button></form></details>';
}

/** Endgueltiges Loeschen, mit Rueckfrage im Browser vor dem Absenden. */
function admin_knopf_loeschen(string $tabelle, int $id, string $frage): string
{
    return '<form method="post" class="inline">' . csrf_field()
         . '<input type="hidden" name="tabelle" value="' . e($tabelle) . '">'
         . '<input type="hidden" name="id" value="' . $id . '">'
         . '<input type="hidden" name="aktion" value="loeschen">'
         . '<button class="schlecht" type="submit" data-loeschen-frage="' . e($frage) . '">Löschen</button></form>';
}

function admin_status(string $status): string
{
    return '<span class="status status-' . e($status) . '">' . e($status) . '</span>';
}

function admin_seite(?string $meldung): void
{
    admin_kopf('Verwaltung');
    $f     = fortschritt();
    $nonce = $GLOBALS['csp_nonce'] ?? '';

    // Rueckfrage vor dem endgueltigen Loeschen. Ohne fremden Dienst, ohne
    // Bibliothek - ein einzeiliger Bestaetigungsdialog des Browsers reicht.
    echo '<script nonce="' . e($nonce) . '">document.addEventListener("submit",function(e){'
       . 'var k=e.submitter;if(k&&k.hasAttribute("data-loeschen-frage")&&'
       . '!window.confirm(k.getAttribute("data-loeschen-frage"))){e.preventDefault();}'
       . '});</script>';

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
    $lieferartLabels = ['gesamt' => 'Alles auf einmal', 'abruf' => 'Monatlicher Abruf', 'abholung' => 'Abholung'];
    echo '<h2>Gastro-Bestellungen</h2>'
       . '<p class="hinweis-klein">„Koordinaten ermitteln" sucht die Adresse kostenlos bei OpenStreetMap '
       . '(Nominatim) und setzt den Punkt auf der Teilnehmerkarte. Ohne Koordinaten erscheint ein Eintrag '
       . 'weiterhin in der Liste, nur nicht auf der Karte.</p>'
       . '<div class="tabelle-wrap"><table><thead><tr>'
       . '<th>Betrieb</th><th>Kontakt</th><th>Menge</th><th>Lieferung</th><th>Karte</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
    foreach (db_all('SELECT * FROM gastro_bestellungen ORDER BY id DESC LIMIT 200') as $z) {
        $positionen = db_all('SELECT format, menge FROM bestellpositionen WHERE bestellung_id = ? ORDER BY format', [(int) $z['id']]);
        $gesamt = 0;
        $zeilen = [];
        foreach ($positionen as $p) {
            $gesamt += (int) $p['menge'];
            $fm = kartonformat((string) $p['format']);
            $zeilen[] = zahl((int) $p['menge']) . '× ' . ($fm['label'] ?? $p['format'] . ' cm');
        }
        $lieferText = $lieferartLabels[$z['lieferart']] ?? $z['lieferart'];
        if ($z['lieferart'] === 'abruf' && $z['abruf_menge']) {
            $lieferText .= '<br><small>' . zahl((int) $z['abruf_menge']) . ' je Abruf</small>';
        }
        echo '<tr>'
           . '<td><strong>' . e($z['betrieb']) . '</strong><br><small>' . e($z['strasse']) . ', ' . e($z['plz']) . ' ' . e($z['ort']) . '<br>' . e($z['betriebsart']) . '</small>'
           . admin_adresse_bearbeiten('gastro_bestellungen', (int) $z['id'], (string) $z['strasse'], (string) $z['plz'], (string) $z['ort']) . '</td>'
           . '<td><small>' . e($z['vorname']) . ' ' . e($z['nachname']) . '<br>' . e($z['email']) . '<br>' . e((string) decrypt_field($z['telefon_enc'])) . '</small></td>'
           . '<td><strong>' . zahl($gesamt) . '</strong><br><small>' . e(implode(', ', $zeilen)) . '</small>'
           . ((int) $z['versand_zuschlag_ok'] ? '<br><small>+ Versandzuschlag</small>' : '') . '</td>'
           . '<td>' . $lieferText
           . '<form method="post" class="inline liefermenge-form">' . csrf_field()
           . '<input type="hidden" name="aktion" value="liefermenge-setzen">'
           . '<input type="hidden" name="id" value="' . (int) $z['id'] . '">'
           . '<label>geliefert <input type="number" name="geliefert_menge" min="0" value="' . (int) $z['geliefert_menge'] . '"></label>'
           . ' / ' . zahl($gesamt)
           . '<button type="submit">Speichern</button></form></td>'
           . '<td>' . ((int) $z['karte_ok'] ? 'ja' : 'nein') . '</td>'
           . '<td>' . admin_status($z['status']) . '</td>'
           . '<td class="aktionen">'
           . admin_knopf('gastro_bestellungen', $z, 'freigegeben', 'Freigeben', 'gut')
           . admin_knopf('gastro_bestellungen', $z, 'abgelehnt', 'Ablehnen', 'schlecht')
           . ($z['lat'] === null ? admin_knopf_geocode('gastro_bestellungen', (int) $z['id']) : '')
           . admin_knopf_loeschen('gastro_bestellungen', (int) $z['id'], 'Bestellung von „' . $z['betrieb'] . '" endgültig löschen, mit allen Positionen? Das lässt sich nicht rückgängig machen.')
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
        $wunschflaechen = json_decode((string) $z['wunschflaechen'], true) ?: [];
        echo '<tr>'
           . '<td><strong>' . e($z['firma']) . '</strong><br><small>' . e($z['art']) . '<br>' . e((string) $z['plz']) . ' ' . e((string) $z['ort']) . '</small>'
           . admin_adresse_bearbeiten('werbebuchungen', (int) $z['id'], null, (string) $z['plz'], (string) $z['ort']) . '</td>'
           . '<td><small>' . e($z['ansprechpartner']) . '<br>' . e($z['email']) . '<br>' . e((string) decrypt_field($z['telefon_enc'])) . '</small></td>'
           . '<td><small>' . e(implode(', ', $labels)) . ($z['coupon'] ? '<br>mit Coupon' : '') . '</small>'
           . ($wunschflaechen
                ? '<br><small><em>Wunsch: ' . e(implode(', ', $wunschflaechen)) . '</em>'
                    . ($z['wunschflaeche_notiz'] ? ' – ' . e((string) $z['wunschflaeche_notiz']) : '') . '</small>'
                : '') . '</td>'
           . '<td>' . e(preis((int) $z['summe_cent'])) . '<br><small>netto</small></td>'
           . '<td><small>' . e((string) ($z['motiv_name'] ?: ((int) $z['motiv_spaeter'] ? 'wird nachgereicht' : '–'))) . '</small></td>'
           . '<td>' . admin_status($z['status']) . '</td>'
           . '<td class="aktionen">'
           . admin_knopf('werbebuchungen', $z, 'freigegeben', 'Freigeben', 'gut')
           . admin_knopf('werbebuchungen', $z, 'abgelehnt', 'Ablehnen', 'schlecht')
           . ($z['lat'] === null ? admin_knopf_geocode('werbebuchungen', (int) $z['id']) : '')
           . admin_knopf_loeschen('werbebuchungen', (int) $z['id'], 'Buchung von „' . $z['firma'] . '" endgültig löschen, inklusive Motiv? Das lässt sich nicht rückgängig machen.')
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
           . admin_knopf('pizzeria_empfehlungen', $z, 'kontaktiert', 'Kontaktiert')
           . admin_knopf('pizzeria_empfehlungen', $z, 'erledigt', 'Erledigt', 'gut')
           . admin_knopf_loeschen('pizzeria_empfehlungen', (int) $z['id'], 'Vorschlag „' . $z['name'] . '" endgültig löschen? Das lässt sich nicht rückgängig machen.')
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

    // Wartung: neue Datenbank-Migrationen einspielen, ohne Kommandozeilenzugang.
    echo '<h2>Wartung</h2>'
       . '<p class="hinweis-klein">Nach dem Hochladen neuer Dateien per FTP hier klicken, damit neue '
       . 'Datenbank-Tabellen oder -Spalten angelegt werden. Ist nichts Neues dabei, passiert nichts.</p>'
       . '<form method="post" class="inline">' . csrf_field()
       . '<input type="hidden" name="aktion" value="migrieren">'
       . '<button type="submit">Migrationen ausführen</button></form>'
       . '<p class="hinweis-klein">Einmalig, sobald die neuen Dateien für die Gründungspartner-Buchungen '
       . 'hochgeladen sind: legt die vier Buchungen von Badische Entertainment, Class Brothers, '
       . 'KI-Assistenz und SnackWorks an. Ein zweiter Klick tut nichts, wenn sie schon existieren.</p>'
       . '<form method="post" class="inline">' . csrf_field()
       . '<input type="hidden" name="aktion" value="gruendungspartner-anlegen">'
       . '<button type="submit">Gründungspartner anlegen</button></form>';

    echo '<p class="fuss-hinweis">Auskunft und Löschung einzelner Personen laufen über die Kommandozeile: '
       . '<code>php bin/export.php auskunft adresse@example.com</code> und '
       . '<code>php bin/export.php loeschen adresse@example.com</code>.</p>';

    echo '</body></html>';
}
