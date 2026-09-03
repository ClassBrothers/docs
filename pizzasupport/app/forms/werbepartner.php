<?php
/** Werbebuchung entgegennehmen. Ansprache in allen Texten: Sie. */

declare(strict_types=1);

$zurueck = '/werbepartner.html#buchen';

if (!honeypot_ok($_POST)) {
    flash_set('werbung_ok', 'Vielen Dank, Ihre Buchung ist bei uns eingegangen.');
    redirect($zurueck);
}

if (!rate_limit_ok('werbung', 5, 3600)) {
    flash_set('werbung_fehler', ['firma' => 'Von hier kamen gerade sehr viele Anfragen. Bitte versuchen Sie es später noch einmal oder schreiben Sie uns direkt.']);
    flash_set('werbung_alt', $_POST);
    redirect($zurueck);
}

$erlaubteFormate = array_column(config('werbeformate'), 'id');
$erlaubteWunschflaechen = array_column(
    array_filter(config('flaechenkatalog.flaechen', []), fn (array $f): bool => $f['buchbar']),
    'id'
);

$v = new Validator($_POST);
$v->auswahl('art', 'Die Art des Buchenden', ['unternehmen', 'privat'])
  ->mehrfach('formate', 'Mindestens eine Werbefläche', $erlaubteFormate)
  ->mehrfach('wunschflaechen', 'Die Wunschfläche', $erlaubteWunschflaechen, false)
  ->langtext('wunschflaeche_notiz', 'Die Anmerkung zur Platzierung', false, 500)
  ->text('firma', 'Firma oder Name', true, 150)
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

// Fun Area: Preis haengt von der tatsaechlich gewaehlten Flaeche ab, nicht
// von einem Festpreis (Nachtrag 2+5). Flaeche auf eine Nachkommastelle
// runden, danach mit dem Preis je cm² multiplizieren.
$funArea       = config('fun_area');
$funFlaecheCm2 = null;
$funPreisCent  = null;
if (in_array('fun-area', $v->get('formate') ?? [], true)) {
    $breiteRoh = str_replace(',', '.', trim((string) ($_POST['fun_breite'] ?? '')));
    $hoeheRoh  = str_replace(',', '.', trim((string) ($_POST['fun_hoehe'] ?? '')));
    if ($breiteRoh === '' || !is_numeric($breiteRoh) || (float) $breiteRoh <= 0) {
        $v->fehlerSetzen('fun_breite', 'Bitte geben Sie die Breite der Fun-Area-Fläche in Zentimetern an.');
    } elseif ($hoeheRoh === '' || !is_numeric($hoeheRoh) || (float) $hoeheRoh <= 0) {
        $v->fehlerSetzen('fun_hoehe', 'Bitte geben Sie die Höhe der Fun-Area-Fläche in Zentimetern an.');
    } else {
        $funFlaecheCm2 = round(((float) $breiteRoh) * ((float) $hoeheRoh), 1);
        if ($funFlaecheCm2 < (float) $funArea['mindestflaeche_cm2']) {
            $v->fehlerSetzen('fun_hoehe', 'Die Fläche muss mindestens ' . $funArea['mindestflaeche_cm2']
                . ' cm² groß sein, aktuell ' . $funFlaecheCm2 . ' cm².');
        } else {
            $funPreisCent = (int) round($funFlaecheCm2 * (int) $funArea['preis_je_cm2_cent']);
        }
    }
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
// Auftragswert berechnen. Massgeblich sind die Preise aus der Konfiguration,
// niemals ein Wert aus dem Formular.
// -----------------------------------------------------------------------
$mwst    = (int) config('mwst_prozent');
$netto   = 0;
$gewaehlteLabels = [];
foreach ($d['formate'] as $id) {
    $wf = werbeformat($id);
    if ($wf === null) {
        continue;
    }
    // Fun Area hat keinen Festpreis mehr: die Flaeche kommt aus dem
    // Formular, der Preis je cm² ausschliesslich aus der Konfiguration.
    $liniePreis = ($id === 'fun-area' && $funPreisCent !== null) ? $funPreisCent : (int) $wf['preis'];
    // Brutto-Preise auf netto zurueckrechnen, damit die Schwelle eine
    // einheitliche Bezugsgroesse hat.
    $netto += $wf['brutto']
        ? (int) round($liniePreis / (1 + $mwst / 100))
        : $liniePreis;
    $gewaehlteLabels[] = $wf['label'] . ' (' . $wf['masse'] . ')'
        . ($id === 'fun-area' && $funFlaecheCm2 !== null ? ', ' . $funFlaecheCm2 . ' cm²' : '');
}

// Wunschflaeche: nur Anzeige/Wunsch, keine Preiswirkung - Kennung + Maß aus
// dem Flaechenkatalog, nie aus dem Formular, damit hier nichts gefaelscht
// ankommen kann.
$wunschflaechenLabels = [];
foreach ($d['wunschflaechen'] as $id) {
    $flaeche = flaechenkatalog_eintrag($id);
    if ($flaeche !== null) {
        $wunschflaechenLabels[] = $id . ' – ' . $flaeche['bezeichnung'] . ' (' . $flaeche['masse'] . ')';
    }
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

try {
    db_run(
        'INSERT INTO werbebuchungen
            (art, firma, ansprechpartner, email, telefon_enc, website,
             rechnung_enc, ustid_enc, plz, ort,
             formate, coupon, summe_cent,
             motiv_pfad, motiv_name, motiv_groesse, motiv_spaeter, zielurl, nachricht,
             wunschflaechen, wunschflaeche_notiz,
             agb_ok, motivvorbehalt_ok, verbindlich_ok, karte_ok, datenschutz_ok, einwilligung_am, einwilligung_zweck,
             status, erstellt_am, quelle)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [
            $d['art'], $d['firma'], $d['ansprechpartner'], $d['email'],
            encrypt_field($d['telefon']), $d['website'],
            encrypt_field($d['rechnung']), encrypt_field($d['ustid']), $d['plz'], $d['ort'],
            json_encode($d['formate'], JSON_UNESCAPED_UNICODE), $d['coupon'], $netto,
            $upload['pfad'] ?? null, $upload['name'] ?? null, $upload['groesse'] ?? null,
            $d['motiv_spaeter'], $d['zielurl'], $d['nachricht'],
            $d['wunschflaechen'] ? json_encode($d['wunschflaechen'], JSON_UNESCAPED_UNICODE) : null, $d['wunschflaeche_notiz'],
            $d['agb_ok'], $d['motivvorbehalt_ok'], $d['verbindlich_ok'], $d['karte_ok'], $d['datenschutz_ok'],
            $jetzt, implode('; ', $zwecke),
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
    'Ihre Reservierung bei Pizza Support',
    "Guten Tag {$d['ansprechpartner']},\n\n"
    . "vielen Dank für Ihre Buchung. Wir haben Folgendes notiert:\n\n"
    . "Buchende Firma: {$d['firma']}\n"
    . "Flächen:\n{$formatText}\n"
    . ($wunschflaechenLabels
        ? "\nIhre Wunschfläche (ein Wunsch, keine Zusage – siehe unsere AGB):\n  - "
          . implode("\n  - ", $wunschflaechenLabels) . "\n"
        : '')
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
    . ($d['art'] === 'unternehmen'
        ? 'Und weil Sie uns unterstützen, unterstützen wir Sie zurück: Auf alle Leistungen unserer '
          . 'eigenen Häuser bekommen Sie als Werbepartner ' . (int) config('partnernachlass.prozent')
          . ' % Nachlass, ' . (int) config('partnernachlass.monate') . ' Monate ab dieser Buchung. '
          . "Melden Sie sich einfach, wenn Sie etwas brauchen: " . url('/ueber-uns.html#sonst-titel') . "\n\n"
        : '')
    . 'Den aktuellen Projektstand sehen Sie hier: ' . url('/teilnehmer.html') . "\n"
    . ($d['art'] === 'privat'
        ? "\n" . str_repeat('-', 40) . "\nWIDERRUFSBELEHRUNG\n" . str_repeat('-', 40) . "\n\n"
          . widerrufsbelehrung_text() . "\n"
        : '')
    . mail_signatur(),
    (string) env('MAIL_TO_OPS')
);

mail_ops(
    'Neue Werbebuchung: ' . $d['firma'] . ' (' . preis($netto) . ' netto)',
    "Neue Buchung über die Website:\n\n"
    . "Art:            {$d['art']}\n"
    . "Firma:          {$d['firma']}\n"
    . "Ansprechpartner:{$d['ansprechpartner']}\n"
    . "E-Mail:         {$d['email']}\n"
    . "Telefon:        {$d['telefon']}\n"
    . "Rechnung:       " . str_replace("\n", ' / ', (string) $d['rechnung']) . "\n"
    . "PLZ/Ort:        {$d['plz']} {$d['ort']}\n"
    . 'USt-IdNr.:      ' . ($d['ustid'] ?: '–') . "\n"
    . "Flächen:\n{$formatText}\n"
    . ($wunschflaechenLabels
        ? "Wunschfläche:\n  - " . implode("\n  - ", $wunschflaechenLabels) . "\n"
          . 'Anmerkung dazu:  ' . ($d['wunschflaeche_notiz'] ?: '–') . "\n"
        : '')
    . 'Coupon:         ' . ($d['coupon'] ? 'ja (-' . preis($rabatt) . ')' : 'nein') . "\n"
    . 'Auftragswert:   ' . preis($netto) . " netto\n"
    . 'Motiv:          ' . ($upload['name'] ?? ($d['motiv_spaeter'] ? 'wird nachgereicht' : 'keins')) . "\n"
    . 'QR-Ziel:        ' . ($d['zielurl'] ?: '–') . "\n"
    . 'Karte:          ' . ($d['karte_ok'] ? 'JA – bitte freigeben' : 'nein') . "\n"
    . 'Anmerkung:      ' . ($d['nachricht'] ?: '–') . "\n\n"
    . 'Freigabe: ' . url('/admin'),
    $d['email']
);

flash_set(
    'werbung_ok',
    'Herzlichen Dank für Ihre Unterstützung! Wir haben Ihre Reservierung über ' . preis($netto)
    . ' netto notiert. Bis zum Startschuss ist sie kostenfrei und unverbindlich – sobald er fällt, '
    . 'erhalten Sie eine Auftragsbestätigung und eine Teilrechnung über '
    . (int) config('startschuss.anzahlung') . ' % des Auftragswerts.'
    . ($d['art'] === 'unternehmen'
        ? ' Und weil Sie uns unterstützen, unterstützen wir Sie zurück: Auf alle Leistungen unserer '
          . 'eigenen Häuser bekommen Sie als Werbepartner ' . (int) config('partnernachlass.prozent')
          . ' % Nachlass, ' . (int) config('partnernachlass.monate') . ' Monate ab dieser Buchung – '
          . 'mehr dazu im Abschnitt „Was wir sonst so können" auf unserer Über-uns-Seite.'
        : '')
);
redirect('/werbepartner.html?gebucht=1#danke');
