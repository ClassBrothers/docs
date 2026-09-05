<?php
/**
 * Bestellung der Gastronomie entgegennehmen.
 * Wird vom Front Controller nach bestandener CSRF-Pruefung eingebunden.
 *
 * Eine Bestellung kann mehrere Kartonformate mischen - je Format eine Menge,
 * die als eigene Zeile in bestellpositionen landet.
 */

declare(strict_types=1);

$zurueck       = '/#bestellen';
// Eigener Anker direkt auf der Fehlermeldung (siehe formular-gastro.php):
// ohne ihn landet ein Ruecksprung nach einem Fehler ganz oben im
// "bestellen"-Abschnitt, spuerbar oberhalb der eigentlichen Meldung und der
// markierten Felder - das wirkte wie ein Sprung "ueber" das Formular hinweg.
$zurueckFehler = '/#gastro-fehler';

if (!honeypot_ok($_POST)) {
    // Bots bekommen dieselbe freundliche Antwort wie Menschen. Wer nicht
    // weiss, dass er erkannt wurde, versucht es seltener erneut.
    flash_set('gastro_ok', 'Danke! Wir haben Deine Bestellung notiert.');
    redirect($zurueck);
}

if (!rate_limit_ok('gastro', 5, 3600)) {
    flash_set('gastro_fehler', ['betrieb' => 'Da kamen gerade sehr viele Anfragen von hier. Bitte versuch es in einer Stunde noch einmal oder schreib uns direkt.']);
    flash_set('gastro_alt', $_POST);
    redirect($zurueckFehler);
}

$formate   = config('karton_formate');
$mengen    = config('mengen');
$porto     = config('porto');
$lieferung = config('lieferung');

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
  ->text('betriebsart_frei', 'Die Angabe, was Ihr macht', false, 150)
  ->text('aktueller_lieferant', 'Der aktuelle Lieferant', false, 150)
  ->auswahl('aktuelle_groesse', 'Die aktuelle Größe', ['28', '30', '32', '33', 'andere'], false)
  ->langtext('anmerkung', 'Deine Anmerkung', false, 1500)
  ->checkbox('bestellung_ok', 'Bitte bestätige, dass Du gemäß der AGB verbindlich bestellst - ohne dieses Häkchen können wir Deine Menge nicht einplanen.')
  ->checkbox('karte_ok', '', false)
  ->checkbox('datenschutz_ok', 'Bitte bestätige, dass Du die Datenschutzhinweise gelesen hast und mit der Verarbeitung Deiner Angaben einverstanden bist - ohne dieses Häkchen dürfen wir sie nicht verarbeiten.');

// Betriebsart muss aus unserer Liste stammen.
$betriebsarten = ['Pizzeria', 'Restaurant', 'Imbiss', 'Lieferdienst mit eigener Küche',
                  'Foodtruck', 'Bäckerei', 'Café', 'Bar mit Küche', 'Anderes'];
if ($v->get('betriebsart') !== null && !in_array($v->get('betriebsart'), $betriebsarten, true)) {
    $v->fehlerSetzen('betriebsart', 'Bitte wähle eine Betriebsart aus der Liste.');
}

// "Was macht Ihr?" ist nur dann Pflicht, wenn "Anderes" gewaehlt wurde -
// ein bedingtes Pflichtfeld darf nicht greifen, wenn sein Ausloeser gar
// nicht gesetzt ist (siehe Nachtrag 01, Etappe 5.2).
if ($v->get('betriebsart') === 'Anderes' && $v->get('betriebsart_frei') === null) {
    $v->fehlerSetzen('betriebsart_frei', 'Bitte kurz sagen, was Ihr macht.');
}

// Vier freiwillige Fragen zur Bedarfsplanung (Schritt "Dein Bedarf") -
// helfen uns beim Planen, sind aber kein Grund, jemanden an der Bestellung
// zu hindern. Nur bei tatsaechlicher Angabe pruefen.
$kartonsMonatBedarfRoh = trim(str_replace(['.', ' ', "\u{00A0}"], '', (string) ($_POST['kartons_monat_bedarf'] ?? '')));
$kartonsMonatBedarf    = null;
if ($kartonsMonatBedarfRoh !== '') {
    if (!preg_match('/^\d+$/', $kartonsMonatBedarfRoh) || (int) $kartonsMonatBedarfRoh < 1) {
        $v->fehlerSetzen('kartons_monat_bedarf', 'Das sollte eine Zahl größer als null sein.');
    } else {
        $kartonsMonatBedarf = (int) $kartonsMonatBedarfRoh;
    }
}

$aktuellerEinkaufspreisRoh  = trim(str_replace(',', '.', (string) ($_POST['aktueller_einkaufspreis'] ?? '')));
$aktuellerEinkaufspreisCent = null;
if ($aktuellerEinkaufspreisRoh !== '') {
    if (!is_numeric($aktuellerEinkaufspreisRoh)) {
        $v->fehlerSetzen('aktueller_einkaufspreis', 'Das sollte eine Zahl sein, zum Beispiel 0,45.');
    } else {
        $aktuellerEinkaufspreisCent = (int) round(((float) $aktuellerEinkaufspreisRoh) * 100);
        if ($aktuellerEinkaufspreisCent <= 0) {
            $v->fehlerSetzen('aktueller_einkaufspreis', 'Der Einkaufspreis muss größer als null sein.');
            $aktuellerEinkaufspreisCent = null;
        }
    }
}

// Ausserhalb des kostenfreien Liefergebiets gibt es keinen festen Zuschlag,
// sondern nur einen Hinweis im Formular - Abholung oder Lieferung nach
// Aufwand, wir melden uns dazu (siehe Nachtrag 5). Kein Pflichtfeld, die
// Spalte versand_zuschlag_ok haelt daher nur den serverseitig ermittelten
// Befund fest, nie eine Angabe aus dem Formular.
$plzWert   = $v->get('plz');
$ortWert   = $v->get('ort');
$plzAusserhalb = $plzWert !== null
    && ((int) $plzWert < (int) $porto['plz_von'] || (int) $plzWert > (int) $porto['plz_bis']);
$ortIstFrei = $ortWert !== null && in_array(mb_strtolower($ortWert), array_map('mb_strtolower', $porto['freie_orte']), true);
$ausserhalbFreiburg = $plzAusserhalb && !$ortIstFrei;

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

// Ersparnisrechner: beide Felder sind freiwillig, nur bei Angabe pruefen.
// Wer nur rechnet und nicht bestellt, sendet dieses Formular nie ab - hier
// landen nur Werte, die tatsaechlich zu einer Bestellung gehoeren.
$er                = config('ersparnisrechner');
$einkaufspreisCent = null;
$einkaufspreisRoh  = trim(str_replace(',', '.', (string) ($_POST['einkaufspreis'] ?? '')));
if ($einkaufspreisRoh !== '') {
    if (!is_numeric($einkaufspreisRoh)) {
        $v->fehlerSetzen('einkaufspreis', 'Der Einkaufspreis muss eine Zahl sein, zum Beispiel 0,45.');
    } else {
        $einkaufspreisCent = (int) round(((float) $einkaufspreisRoh) * 100);
        if ($einkaufspreisCent < (int) $er['einkaufspreis_min_cent'] || $einkaufspreisCent > (int) $er['einkaufspreis_max_cent']) {
            $v->fehlerSetzen('einkaufspreis', 'Der Einkaufspreis sollte zwischen '
                . preis((int) $er['einkaufspreis_min_cent']) . ' und ' . preis((int) $er['einkaufspreis_max_cent']) . ' liegen.');
        }
    }
}

$kartonsMonat    = null;
$kartonsMonatRoh = trim((string) ($_POST['kartons_monat'] ?? ''));
if ($kartonsMonatRoh !== '') {
    if (!preg_match('/^\d+$/', $kartonsMonatRoh)) {
        $v->fehlerSetzen('kartons_monat', 'Die Kartons pro Monat müssen eine Zahl sein.');
    } else {
        $kartonsMonat = (int) $kartonsMonatRoh;
        if ($kartonsMonat < (int) $er['kartons_monat_min'] || $kartonsMonat > (int) $er['kartons_monat_max']) {
            $v->fehlerSetzen('kartons_monat', 'Die Kartons pro Monat sollten zwischen '
                . zahl((int) $er['kartons_monat_min']) . ' und ' . zahl((int) $er['kartons_monat_max']) . ' liegen.');
        }
    }
}

// Lieferart: auf einmal, monatlicher Abruf oder Abholung. Die Abrufmenge
// gilt nur beim monatlichen Abruf und muss innerhalb der Gesamtmenge liegen.
$erlaubteLieferarten = ['gesamt', 'abruf', 'abholung'];
$lieferart = (string) ($_POST['lieferart'] ?? 'gesamt');
if (!in_array($lieferart, $erlaubteLieferarten, true)) {
    $lieferart = 'gesamt';
}

$abrufMenge = null;
if ($lieferart === 'abruf') {
    $abrufRoh = trim(str_replace(['.', ' ', "\u{00A0}"], '', (string) ($_POST['abruf_menge'] ?? '')));
    if ($abrufRoh === '' || !preg_match('/^\d+$/', $abrufRoh)) {
        $v->fehlerSetzen('abruf_menge', 'Bitte gib die gewünschte Menge pro Monat ein.');
    } else {
        $abrufMenge = (int) $abrufRoh;
        if ($abrufMenge < (int) $lieferung['abruf_min']) {
            $v->fehlerSetzen('abruf_menge', 'Mindestens ' . zahl((int) $lieferung['abruf_min']) . ' Stück pro Monat.');
        } elseif ($mengeFehler === null && $abrufMenge > $gesamtmenge) {
            $v->fehlerSetzen('abruf_menge', 'Die Menge pro Monat kann nicht größer sein als Deine Gesamtbestellung.');
        }
    }
}

if (!$v->ok()) {
    flash_set('gastro_fehler', $v->fehler());
    flash_set('gastro_alt', $_POST);
    redirect($zurueckFehler);
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
             betriebsart, betriebsart_frei, anmerkung,
             bestellung_ok, karte_ok, datenschutz_ok, versand_zuschlag_ok,
             einkaufspreis_cent, kartons_monat, lieferart, abruf_menge,
             kartons_monat_bedarf, aktueller_einkaufspreis_cent, aktuelle_groesse, aktueller_lieferant,
             einwilligung_am, einwilligung_zweck, status, erstellt_am, quelle)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [
            $d['vorname'], $d['nachname'], $d['betrieb'], $d['strasse'], $d['plz'], $d['ort'],
            $d['email'], encrypt_field($d['telefon']), $d['website'],
            $d['betriebsart'], $d['betriebsart_frei'], $d['anmerkung'],
            $d['bestellung_ok'], $d['karte_ok'], $d['datenschutz_ok'], (int) $ausserhalbFreiburg,
            $einkaufspreisCent, $kartonsMonat, $lieferart, $abrufMenge,
            $kartonsMonatBedarf, $aktuellerEinkaufspreisCent, $d['aktuelle_groesse'], $d['aktueller_lieferant'],
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
        redirect($zurueckFehler);
    }
    error_log('gastro-insert: ' . $eDb->getMessage());
    flash_set('gastro_fehler', ['betrieb' => 'Da ist bei uns etwas schiefgegangen. Bitte versuch es gleich noch einmal oder schreib uns direkt.']);
    flash_set('gastro_alt', $_POST);
    redirect($zurueckFehler);
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

$lieferartLabels = ['gesamt' => 'Alles auf einmal', 'abruf' => 'Monatlicher Abruf', 'abholung' => 'Abholung'];
$lieferartText = $lieferartLabels[$lieferart]
    . ($lieferart === 'abruf' ? ' (' . zahl((int) $abrufMenge) . ' Kartons pro Monat)' : '');

// Bestaetigung an den Betrieb
mail_send(
    $d['email'],
    'Deine Bestellung bei Pizza Support',
    "Hallo {$d['vorname']} {$d['nachname']},\n\n"
    . "Deine Bestellung ist bei uns angekommen. Hier noch einmal, was wir notiert haben:\n\n"
    . "Betrieb:   {$d['betrieb']}\n"
    . "Adresse:   {$d['strasse']}, {$d['plz']} {$d['ort']}\n"
    . "Formate:\n{$positionsText}\n"
    . 'Gesamt:    ' . zahl($gesamtmenge) . " Kartons\n"
    . 'Lieferung: ' . $lieferartText
    . ($lieferart === 'abholung' ? ' – wir rufen Dich zur Terminvereinbarung an.' : '') . "\n\n"
    . ($ausserhalbFreiburg
        ? 'Da Du außerhalb ' . $porto['frei_in'] . " bestellst: Abholung oder Lieferung nach\n"
          . "Aufwand, wir melden uns bei Dir dazu.\n\n"
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
    . "Betrieb:      {$d['betrieb']} ({$d['betriebsart']}" . ($d['betriebsart_frei'] ? ': ' . $d['betriebsart_frei'] : '') . ")\n"
    . "Ansprechpartner: {$d['vorname']} {$d['nachname']}\n"
    . "Adresse:      {$d['strasse']}, {$d['plz']} {$d['ort']}\n"
    . "E-Mail:       {$d['email']}\n"
    . "Telefon:      {$d['telefon']}\n"
    . 'Website:      ' . ($d['website'] ?: '–') . "\n"
    . "Formate:\n{$positionsText}\n"
    . 'Gesamt:       ' . zahl($gesamtmenge) . "\n"
    . 'Lieferung:    ' . $lieferartText . "\n"
    . 'Versandzuschlag: ' . ($ausserhalbFreiburg ? 'ja – außerhalb ' . $porto['frei_in'] : 'nein') . "\n"
    . 'Karte:        ' . ($d['karte_ok'] ? 'JA – bitte freigeben' : 'nein') . "\n"
    . "Bedarf laut Angabe (freiwillig, kann fehlen):\n"
    . '  Kartons/Monat: ' . ($kartonsMonatBedarf !== null ? zahl($kartonsMonatBedarf) : '–') . "\n"
    . '  Einkaufspreis: ' . ($aktuellerEinkaufspreisCent !== null ? preis($aktuellerEinkaufspreisCent) : '–') . "\n"
    . '  Aktuelle Größe: ' . ($d['aktuelle_groesse'] ?: '–') . "\n"
    . '  Aktueller Lieferant: ' . ($d['aktueller_lieferant'] ?: '–') . "\n"
    . 'Anmerkung:    ' . ($d['anmerkung'] ?: '–') . "\n\n"
    . 'Freigabe: ' . url('/admin'),
    $d['email']
);

flash_set(
    'gastro_ok',
    'Deine Bestellung über ' . zahl($gesamtmenge) . ' Kartons ist notiert. Eine Bestätigung liegt in Deinem Postfach.'
);
redirect('/?bestellt=1#danke');
