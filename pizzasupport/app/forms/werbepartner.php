<?php
/** Werbebuchung entgegennehmen. Ansprache in allen Texten: Sie. */

declare(strict_types=1);

$zurueck = '/flaeche-buchen.html';

if (!honeypot_ok($_POST)) {
    flash_set('werbung_ok', 'Vielen Dank, Ihre Buchung ist bei uns eingegangen.');
    redirect($zurueck);
}

if (!rate_limit_ok('werbung', 5, 3600)) {
    flash_set('werbung_fehler', ['firma' => 'Von hier kamen gerade sehr viele Anfragen. Bitte versuchen Sie es später noch einmal oder schreiben Sie uns direkt.']);
    flash_set('werbung_alt', $_POST);
    redirect($zurueck);
}

$erlaubteFlaechen = array_column(
    array_filter(config('flaechenkatalog.flaechen', []), fn (array $f): bool => $f['buchbar']),
    'id'
);

$v = new Validator($_POST);
$v->mehrfach('flaechen', 'Mindestens eine Werbefläche', $erlaubteFlaechen)
  ->langtext('notiz', 'Die Anmerkung zur Platzierung', false, 500)
  ->text('firma', 'Firma', true, 150)
  ->text('ansprechpartner', 'Der Ansprechpartner', true, 120)
  ->email('email', 'Die E-Mail-Adresse')
  ->telefon('telefon', 'Die Telefonnummer')
  ->langtext('rechnung', 'Die Rechnungsadresse', true, 200)
  ->plz('plz', 'Die Postleitzahl')
  ->text('ort', 'Der Ort', true, 100)
  ->text('ustid', 'Die USt-IdNr.', false, 20)
  ->url('website', 'Die Website', false)
  ->url('zielurl', 'Das QR-Ziel', false)
  ->langtext('nachricht', 'Ihre Anmerkung', false, 1500)
  ->checkbox('coupon', '', false)
  ->checkbox('motiv_spaeter', '', false)
  ->checkbox('karte_ok', '', false)
  ->checkbox('naechste_auflage_bevorzugt', '', false)
  ->checkbox('agb_ok', 'Ohne Zustimmung zu den AGB können wir die Buchung nicht annehmen.')
  ->checkbox('motivvorbehalt_ok', 'Bitte bestätigen Sie, dass Sie den Motiv-Vorbehalt kennen.')
  ->checkbox('verbindlich_ok', 'Bitte bestätigen Sie die verbindliche Buchung für den Fall, dass das Projekt zustande kommt.')
  ->checkbox('datenschutz_ok', 'Ohne Zustimmung zu den Datenschutzhinweisen dürfen wir Ihre Angaben nicht verarbeiten.');

// USt-IdNr. nur grob auf Form pruefen – die inhaltliche Pruefung macht
// ohnehin die Buchhaltung.
$ustid = $v->get('ustid');
if ($ustid !== null && $ustid !== '' && !preg_match('/^[A-Z]{2}[0-9A-Z]{6,14}$/i', $ustid)) {
    $v->fehlerSetzen('ustid', 'Diese USt-IdNr. sieht nicht richtig aus, zum Beispiel DE123456789.');
}

$upload = upload_motiv($_FILES['motiv'] ?? null);
if (!$upload['ok']) {
    $v->fehlerSetzen('motiv', (string) $upload['fehler']);
}

if (!$v->ok()) {
    // Eine hochgeladene Datei ist nach dem Redirect weg – das sagen wir dazu.
    if (!empty($_FILES['motiv']['name']) && !isset($v->fehler()['motiv'])) {
        $v->fehlerSetzen('motiv', 'Bitte wählen Sie die Datei noch einmal aus, sie ging beim Zurückspringen verloren.');
    }
    if (!empty($upload['pfad'])) {
        @unlink(APP_ROOT . '/storage/uploads/' . $upload['pfad']);
    }
    flash_set('werbung_fehler', $v->fehler());
    $altw = $_POST;
    unset($altw['_token'], $altw['_ts']);
    flash_set('werbung_alt', $altw);
    redirect($zurueck);
}

$d = $v->daten();

// -----------------------------------------------------------------------
// Auftragswert berechnen. Massgeblich sind die Preise aus dem
// Flaechenkatalog, niemals ein Wert aus dem Formular.
// -----------------------------------------------------------------------
$mwst    = (int) config('mwst_prozent');
$netto   = 0;
$gewaehlteLabels = [];
foreach ($d['flaechen'] as $id) {
    $flaeche = flaechenkatalog_eintrag($id);
    if ($flaeche === null || $flaeche['preis'] === null) {
        continue;
    }
    $netto += (int) $flaeche['preis'];
    $gewaehlteLabels[] = $id . ' – ' . $flaeche['bezeichnung'] . ' (' . $flaeche['masse'] . ')';
}

$rabatt = 0;
if ($d['coupon']) {
    $rabatt = (int) round($netto * config('coupon_rabatt_prozent') / 100);
    $netto -= $rabatt;
}

$jetzt  = gmdate('Y-m-d H:i:s');
$zwecke = ['Anbahnung und Abwicklung der Werbebuchung'];
if ($d['karte_ok']) {
    $zwecke[] = 'Nennung auf der öffentlichen Teilnehmerkarte';
}

// Bestaetigungslink per Mail (Double-Opt-in): erst nach Klick gilt die
// Buchung als bestaetigt und die Flaechen werden fest vergeben - siehe
// werbebuchung-bestaetigen.php. Bis dahin koennte theoretisch eine zweite
// Buchung dieselbe Flaeche waehlen; wer zuerst bestaetigt, bekommt sie.
$bestaetigungToken = bin2hex(random_bytes(32));

try {
    db_run(
        'INSERT INTO werbebuchungen
            (art, firma, ansprechpartner, email, telefon_enc, website,
             rechnung_enc, ustid_enc, plz, ort,
             formate, coupon, summe_cent,
             motiv_pfad, motiv_name, motiv_groesse, motiv_spaeter, zielurl, nachricht,
             wunschflaeche_notiz,
             agb_ok, motivvorbehalt_ok, verbindlich_ok, karte_ok, datenschutz_ok, einwilligung_am, einwilligung_zweck,
             naechste_auflage_bevorzugt, bestaetigung_token,
             status, erstellt_am, quelle)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [
            'unternehmen', $d['firma'], $d['ansprechpartner'], $d['email'],
            encrypt_field($d['telefon']), $d['website'],
            encrypt_field($d['rechnung']), encrypt_field($d['ustid']), $d['plz'], $d['ort'],
            json_encode($d['flaechen'], JSON_UNESCAPED_UNICODE), $d['coupon'], $netto,
            $upload['pfad'] ?? null, $upload['name'] ?? null, $upload['groesse'] ?? null,
            $d['motiv_spaeter'], $d['zielurl'], $d['nachricht'],
            $d['notiz'],
            $d['agb_ok'], $d['motivvorbehalt_ok'], $d['verbindlich_ok'], $d['karte_ok'], $d['datenschutz_ok'],
            $jetzt, implode('; ', $zwecke),
            $d['naechste_auflage_bevorzugt'], $bestaetigungToken,
            'neu', $jetzt, 'website',
        ]
    );
    $buchungId = (int) db()->lastInsertId();
} catch (PDOException $eDb) {
    error_log('werbung-insert: ' . $eDb->getMessage());
    if (!empty($upload['pfad'])) {
        @unlink(APP_ROOT . '/storage/uploads/' . $upload['pfad']);
    }
    flash_set('werbung_fehler', ['firma' => 'Da ist bei uns etwas schiefgegangen. Bitte versuchen Sie es noch einmal oder schreiben Sie uns direkt.']);
    flash_set('werbung_alt', $_POST);
    redirect($zurueck);
}

// QR-Weiterleitung anlegen, damit der Code auf dem Karton uns gehoert.
if ($d['zielurl']) {
    $code = substr(strtr(base64_encode(random_bytes(9)), '+/', 'ab'), 0, 8);
    db_run(
        'INSERT INTO qr_redirects (code, buchung_id, ziel_url, beschreibung, aktiv, erstellt_am)
         VALUES (?,?,?,?,?,?)',
        [$code, $buchungId, $d['zielurl'], $d['firma'], 0, $jetzt]
    );
}

$brutto     = (int) round($netto * (1 + $mwst / 100));
$anzahlung  = (int) round($brutto * config('startschuss.anzahlung') / 100);
$formatText = '  - ' . implode("\n  - ", $gewaehlteLabels);

mail_send(
    $d['email'],
    'Bitte bestätigen Sie Ihre Reservierung bei Pizza Support',
    "Guten Tag {$d['ansprechpartner']},\n\n"
    . "vielen Dank für Ihre Buchung. Bitte bestätigen Sie sie noch mit einem Klick auf\n"
    . "diesen Link, erst danach sind Ihre Flächen für Sie reserviert:\n\n"
    . url('/werbebuchung-bestaetigen?token=' . $bestaetigungToken) . "\n\n"
    . "Die Fläche ist begrenzt, wir vergeben in der Reihenfolge der Bestätigungen -\n"
    . "je früher der Klick, desto sicherer Ihre gewählte Fläche.\n\n"
    . "Wir haben Folgendes notiert:\n\n"
    . "Buchende Firma: {$d['firma']}\n"
    . "Flächen:\n{$formatText}\n"
    . ($d['notiz'] ? "\nAnmerkung zur Platzierung: {$d['notiz']}\n" : '')
    . ($d['coupon'] ? 'Gutscheinmotiv: ja, ' . (int) config('coupon_rabatt_prozent') . " % Nachlass berücksichtigt\n" : '')
    . 'Auftragswert:   ' . preis($netto) . ' netto, ' . preis($brutto) . " brutto\n\n"
    . "So geht es weiter: Ihre Reservierung ist bis zum Startschuss kostenfrei und\n"
    . "unverbindlich. Sobald genug Betriebe und genug Werbevolumen zusammengekommen sind,\n"
    . 'erhalten Sie eine Auftragsbestätigung und eine Teilrechnung über '
    . (int) config('startschuss.anzahlung') . " % des Auftragswerts\n"
    . '(derzeit ' . preis($anzahlung) . " brutto). Erst ab diesem Zeitpunkt wird die Buchung\n"
    . "verbindlich. Der Restbetrag wird mit Auslieferung fällig.\n\n"
    . ($d['motiv_spaeter'] || empty($upload['pfad'])
        ? "Ihr Motiv reichen Sie später ein – wir melden uns rechtzeitig vor der Druckfreigabe.\n\n"
        : 'Ihr Motiv (' . ($upload['name'] ?? '') . ") ist bei uns eingegangen und wird geprüft.\n\n")
    . 'Und weil Sie uns unterstützen, unterstützen wir Sie zurück: Auf alle Leistungen unserer '
    . 'eigenen Häuser bekommen Sie als Werbepartner ' . (int) config('partnernachlass.prozent')
    . ' % Nachlass, ' . (int) config('partnernachlass.monate') . ' Monate ab dieser Buchung. '
    . "Melden Sie sich einfach, wenn Sie etwas brauchen: " . url('/ueber-uns.html#sonst-titel') . "\n\n"
    . 'Den aktuellen Projektstand sehen Sie hier: ' . url('/teilnehmer.html') . "\n"
    . mail_signatur(),
    (string) env('MAIL_TO_OPS')
);

mail_ops(
    'Neue Werbebuchung: ' . $d['firma'] . ' (' . preis($netto) . ' netto)',
    "Neue Buchung über die Website:\n\n"
    . "Firma:          {$d['firma']}\n"
    . "Ansprechpartner:{$d['ansprechpartner']}\n"
    . "E-Mail:         {$d['email']}\n"
    . "Telefon:        {$d['telefon']}\n"
    . "Rechnung:       " . str_replace("\n", ' / ', (string) $d['rechnung']) . "\n"
    . "PLZ/Ort:        {$d['plz']} {$d['ort']}\n"
    . 'USt-IdNr.:      ' . ($d['ustid'] ?: '–') . "\n"
    . "Flächen:\n{$formatText}\n"
    . 'Anmerkung zur Platzierung: ' . ($d['notiz'] ?: '–') . "\n"
    . 'Coupon:         ' . ($d['coupon'] ? 'ja (-' . preis($rabatt) . ')' : 'nein') . "\n"
    . 'Auftragswert:   ' . preis($netto) . " netto\n"
    . 'Motiv:          ' . ($upload['name'] ?? ($d['motiv_spaeter'] ? 'wird nachgereicht' : 'keins')) . "\n"
    . 'QR-Ziel:        ' . ($d['zielurl'] ?: '–') . "\n"
    . 'Karte:          ' . ($d['karte_ok'] ? 'JA – bitte freigeben' : 'nein') . "\n"
    . 'Nächste Auflage bevorzugt: ' . ($d['naechste_auflage_bevorzugt'] ? 'ja' : 'nein') . "\n"
    . 'Anmerkung:      ' . ($d['nachricht'] ?: '–') . "\n\n"
    . "Noch unbestätigt - die Flächen werden erst nach Klick auf den Bestätigungslink\n"
    . "in der Kundenmail fest vergeben.\n\n"
    . 'Freigabe: ' . url('/admin'),
    $d['email']
);

flash_set(
    'werbung_ok',
    'Fast geschafft: Bitte bestätigen Sie den Link, den wir Ihnen gerade geschickt haben – '
    . 'erst danach sind Ihre Flächen für Sie reserviert. Wir haben eine Reservierung über '
    . preis($netto) . ' netto notiert. Bis zum Startschuss ist sie kostenfrei und unverbindlich – '
    . 'sobald er fällt, erhalten Sie eine Auftragsbestätigung und eine Teilrechnung über '
    . (int) config('startschuss.anzahlung') . ' % des Auftragswerts. '
    . 'Und weil Sie uns unterstützen, unterstützen wir Sie zurück: Auf alle Leistungen unserer '
    . 'eigenen Häuser bekommen Sie als Werbepartner ' . (int) config('partnernachlass.prozent')
    . ' % Nachlass, ' . (int) config('partnernachlass.monate') . ' Monate ab dieser Buchung – '
    . 'mehr dazu im Abschnitt „Was wir sonst so können" auf unserer Über-uns-Seite.'
);
redirect('/flaeche-buchen.html?gebucht=1#danke');
