<?php
/**
 * Bestellung der Gastronomie entgegennehmen.
 * Wird vom Front Controller nach bestandener CSRF-Pruefung eingebunden.
 *
 * Eine Bestellung kann mehrere Kartonformate mischen - je Format eine Menge,
 * die als eigene Zeile in bestellpositionen landet.
 */

declare(strict_types=1);

$zurueck = '/#bestellen';

if (!honeypot_ok($_POST)) {
    // Bots bekommen dieselbe freundliche Antwort wie Menschen. Wer nicht
    // weiss, dass er erkannt wurde, versucht es seltener erneut.
    flash_set('gastro_ok', 'Danke! Wir haben Deine Bestellung notiert.');
    redirect($zurueck);
}

if (!rate_limit_ok('gastro', 5, 3600)) {
    flash_set('gastro_fehler', ['betrieb' => 'Da kamen gerade sehr viele Anfragen von hier. Bitte versuch es in einer Stunde noch einmal oder schreib uns direkt.']);
    flash_set('gastro_alt', $_POST);
    redirect($zurueck);
}

$formate = config('karton_formate');
$mengen  = config('mengen');
$porto   = config('porto');

$v = new Validator($_POST);
$v->text('betrieb', 'Der Name der Gastronomie', true, 150)
  ->text('vorname', 'Dein Vorname', true, 80)
  ->text('nachname', 'Dein Nachname', true, 80)
  ->text('strasse', 'Die Straße', true, 150)
  ->plz('plz', 'Die Postleitzahl')
  ->text('ort', 'Der Ort', true, 100)
  ->email('email', 'Die E-Mail-Adresse')
  ->telefon('telefon', 'Die Telefonnummer')
  ->url('website', 'Die Website', false)
  ->text('betriebsart', 'Die Betriebsart', true, 60)
  ->langtext('anmerkung', 'Deine Anmerkung', false, 1500)
  ->checkbox('bestellung_ok', 'Ohne diese Bestätigung können wir Deine Menge nicht einplanen.')
  ->checkbox('karte_ok', '', false)
  ->checkbox('datenschutz_ok', 'Ohne Zustimmung zu den Datenschutzhinweisen dürfen wir Deine Angaben nicht verarbeiten.');

// Betriebsart muss aus unserer Liste stammen.
$betriebsarten = ['Pizzeria', 'Restaurant', 'Imbiss', 'Lieferdienst mit eigener Küche',
                  'Foodtruck', 'Bäckerei', 'Café', 'Bar mit Küche', 'Anderes'];
if ($v->get('betriebsart') !== null && !in_array($v->get('betriebsart'), $betriebsarten, true)) {
    $v->fehlerSetzen('betriebsart', 'Bitte wähle eine Betriebsart aus der Liste.');
}

// Ausserhalb Freiburgs faellt eine Portopauschale an - die Zustimmung dazu
// ist nur dann Pflicht, wenn die eingetragene PLZ ausserhalb liegt.
$plzWert = $v->get('plz');
$ausserhalbFreiburg = $plzWert !== null
    && ((int) $plzWert < (int) $porto['plz_von'] || (int) $plzWert > (int) $porto['plz_bis']);
$v->checkbox(
    'versand_zuschlag_ok',
    'Bitte bestätige den Versandzuschlag außerhalb ' . $porto['frei_in'] . '.',
    $ausserhalbFreiburg
);

// Mengen je Format einsammeln und pruefen. Leer oder 0 heisst: dieses
// Format wurde nicht bestellt.
$mengenRoh   = is_array($_POST['menge'] ?? null) ? $_POST['menge'] : [];
$positionen  = [];
$mengeFehler = null;
foreach ($formate as $fm) {
    $roh = $mengenRoh[$fm['id']] ?? '';
    $roh = is_string($roh) ? trim(str_replace(['.', ' ', "\u{00A0}"], '', $roh)) : '';
    if ($roh === '' || $roh === '0') {
        continue;
    }
    if (!preg_match('/^\d+$/', $roh)) {
        $mengeFehler = 'Die Menge bei ' . $fm['label'] . ' muss eine Zahl sein.';
        break;
    }
    $n = (int) $roh;
    if ($n < (int) $mengen['format_min']) {
        $mengeFehler = 'Bei ' . $fm['label'] . ' sind mindestens ' . zahl((int) $mengen['format_min']) . ' Kartons nötig.';
        break;
    }
    // Auf das Raster runden, damit die Druckerei damit rechnen kann.
    if ($mengen['step'] > 1) {
        $n = (int) (ceil($n / $mengen['step']) * $mengen['step']);
    }
    $positionen[$fm['id']] = $n;
}

$gesamtmenge = array_sum($positionen);
if ($mengeFehler === null) {
    if ($positionen === []) {
        $mengeFehler = 'Bitte gib bei mindestens einem Format eine Menge ein.';
    } elseif ($gesamtmenge < (int) $mengen['min']) {
        $mengeFehler = 'Insgesamt sind mindestens ' . zahl((int) $mengen['min']) . ' Kartons nötig.';
    } elseif ($gesamtmenge > (int) $mengen['max']) {
        $mengeFehler = 'Für mehr als ' . zahl((int) $mengen['max']) . ' Kartons melde Dich bitte direkt bei uns.';
    }
}
if ($mengeFehler !== null) {
    $v->fehlerSetzen('menge', $mengeFehler);
}

if (!$v->ok()) {
    flash_set('gastro_fehler', $v->fehler());
    flash_set('gastro_alt', $_POST);
    redirect($zurueck);
}

$d     = $v->daten();
$jetzt = gmdate('Y-m-d H:i:s');

$zwecke = ['Bestellabwicklung nach dem Startschuss-Prinzip'];
if ($d['karte_ok']) {
    $zwecke[] = 'Anzeige auf der öffentlichen Teilnehmerkarte';
}

try {
    db()->beginTransaction();

    db_run(
        'INSERT INTO gastro_bestellungen
            (vorname, nachname, betrieb, strasse, plz, ort, email, telefon_enc, website,
             betriebsart, anmerkung,
             bestellung_ok, karte_ok, datenschutz_ok, versand_zuschlag_ok,
             einwilligung_am, einwilligung_zweck, status, erstellt_am, quelle)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [
            $d['vorname'], $d['nachname'], $d['betrieb'], $d['strasse'], $d['plz'], $d['ort'],
            $d['email'], encrypt_field($d['telefon']), $d['website'],
            $d['betriebsart'], $d['anmerkung'],
            $d['bestellung_ok'], $d['karte_ok'], $d['datenschutz_ok'], $d['versand_zuschlag_ok'],
            $jetzt, implode('; ', $zwecke), 'neu', $jetzt, 'website',
        ]
    );
    $bestellungId = (int) db()->lastInsertId();

    foreach ($positionen as $formatId => $n) {
        db_run(
            'INSERT INTO bestellpositionen (bestellung_id, format, menge) VALUES (?,?,?)',
            [$bestellungId, $formatId, $n]
        );
    }

    db()->commit();
} catch (PDOException $eDb) {
    db()->rollBack();
    // Der eindeutige Index auf E-Mail und Betrieb verhindert Doppeleintraege.
    if (str_contains($eDb->getMessage(), 'UNIQUE')) {
        flash_set('gastro_fehler', ['email' => 'Diesen Betrieb haben wir mit dieser Adresse schon in der Liste. Wenn Du die Menge ändern willst, schreib uns kurz.']);
        flash_set('gastro_alt', $_POST);
        redirect($zurueck);
    }
    error_log('gastro-insert: ' . $eDb->getMessage());
    flash_set('gastro_fehler', ['betrieb' => 'Da ist bei uns etwas schiefgegangen. Bitte versuch es gleich noch einmal oder schreib uns direkt.']);
    flash_set('gastro_alt', $_POST);
    redirect($zurueck);
}

$positionsZeilen = [];
foreach ($positionen as $formatId => $n) {
    // Rein numerische Format-IDs ('30', '32', ...) werden von PHP als
    // Array-Schluessel automatisch zu int - fuer die Format-Suche und die
    // Anzeige wieder zurueck in einen String.
    $formatId = (string) $formatId;
    $fm = kartonformat($formatId);
    $positionsZeilen[] = '  - ' . ($fm['label'] ?? $formatId . ' cm') . ': ' . zahl($n) . ' Kartons';
}
$positionsText = implode("\n", $positionsZeilen);

// Bestaetigung an den Betrieb
mail_send(
    $d['email'],
    'Deine Bestellung bei Pizza Support',
    "Hallo {$d['vorname']} {$d['nachname']},\n\n"
    . "Deine Bestellung ist bei uns angekommen. Hier noch einmal, was wir notiert haben:\n\n"
    . "Betrieb:   {$d['betrieb']}\n"
    . "Adresse:   {$d['strasse']}, {$d['plz']} {$d['ort']}\n"
    . "Formate:\n{$positionsText}\n"
    . 'Gesamt:    ' . zahl($gesamtmenge) . " Kartons\n\n"
    . ($ausserhalbFreiburg
        ? 'Da Du außerhalb ' . $porto['frei_in'] . " bestellst, fällt eine Portopauschale von\n"
          . preis((int) $porto['pauschale_cent'], false) . ' € netto zzgl. ' . (int) config('mwst_prozent')
          . ' % MwSt. je angefangene ' . zahl((int) $porto['je_kartons']) . " Kartons an.\n\n"
        : '')
    . "Wie es weitergeht: Wir sammeln weiter Betriebe und Werbepartner. Sobald genug\n"
    . "zusammengekommen ist, geben wir die Produktion frei und melden uns bei Dir. Die\n"
    . 'Kartons sind dann rund ' . config('startschuss.lieferwochen') . " Wochen später da.\n\n"
    . "Bis dahin kostet Dich das nichts und Du kannst jederzeit absagen – eine kurze\n"
    . "Antwort auf diese Mail genügt.\n\n"
    . 'Den aktuellen Stand siehst Du hier: ' . url('/teilnehmer.html')
    . mail_signatur(),
    (string) env('MAIL_TO_OPS')
);

// Benachrichtigung an uns
mail_ops(
    'Neue Gastro-Bestellung: ' . $d['betrieb'],
    "Neue Bestellung über die Website:\n\n"
    . "Betrieb:      {$d['betrieb']} ({$d['betriebsart']})\n"
    . "Ansprechpartner: {$d['vorname']} {$d['nachname']}\n"
    . "Adresse:      {$d['strasse']}, {$d['plz']} {$d['ort']}\n"
    . "E-Mail:       {$d['email']}\n"
    . "Telefon:      {$d['telefon']}\n"
    . 'Website:      ' . ($d['website'] ?: '–') . "\n"
    . "Formate:\n{$positionsText}\n"
    . 'Gesamt:       ' . zahl($gesamtmenge) . "\n"
    . 'Versandzuschlag: ' . ($ausserhalbFreiburg ? 'ja – außerhalb ' . $porto['frei_in'] : 'nein') . "\n"
    . 'Karte:        ' . ($d['karte_ok'] ? 'JA – bitte freigeben' : 'nein') . "\n"
    . 'Anmerkung:    ' . ($d['anmerkung'] ?: '–') . "\n\n"
    . 'Freigabe: ' . url('/admin'),
    $d['email']
);

flash_set(
    'gastro_ok',
    'Deine Bestellung über ' . zahl($gesamtmenge) . ' Kartons ist notiert. Eine Bestätigung liegt in Deinem Postfach.'
);
redirect('/?bestellt=1#danke');
