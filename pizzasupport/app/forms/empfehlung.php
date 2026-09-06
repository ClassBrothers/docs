<?php
/** Stoerer: Jemand schlaegt uns seine Lieblings-Pizzeria vor. */

declare(strict_types=1);

$zurueck = $_SERVER['HTTP_REFERER'] ?? '/';
// Nur auf eigene Seiten zurueckspringen, nie auf eine fremde Adresse.
$zielPfad = parse_url($zurueck, PHP_URL_PATH) ?: '/';
$eigenHost = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);
$refHost   = parse_url($zurueck, PHP_URL_HOST);
$zurueck   = ($refHost === null || $refHost === $eigenHost) ? $zielPfad : '/';

if (!honeypot_ok($_POST)) {
    flash_set('empfehlung_ok', 'Danke für den Tipp!');
    redirect($zurueck);
}

if (!rate_limit_ok('empfehlung', 10, 3600)) {
    flash_set('empfehlung_fehler', ['name' => 'Von hier kamen gerade viele Vorschläge. Bitte versuch es später noch einmal.']);
    redirect($zurueck);
}

$v = new Validator($_POST);
$v->text('name', 'Der Name der Pizzeria', true, 120)
  ->text('strasse', 'Die Straße', false, 150)
  ->plz('plz', 'Die Postleitzahl', false)
  ->text('ort', 'Der Ort', false, 100)
  ->langtext('hinweis', 'Dein Hinweis', false, 500)
  ->email('melder_email', 'Deine E-Mail', false)
  ->checkbox('datenschutz_ok', 'Ohne Zustimmung zu den Datenschutzhinweisen dürfen wir den Vorschlag nicht speichern.');

if (!$v->ok()) {
    flash_set('empfehlung_fehler', $v->fehler());
    flash_set('empfehlung_alt', $_POST);
    redirect($zurueck . '#modal-empfehlung');
}

$d = $v->daten();

db_run(
    'INSERT INTO pizzeria_empfehlungen (name, strasse, plz, ort, hinweis, melder_email, status, erstellt_am)
     VALUES (?,?,?,?,?,?,?,?)',
    [$d['name'], $d['strasse'], $d['plz'], $d['ort'], $d['hinweis'], $d['melder_email'], 'neu', gmdate('Y-m-d H:i:s')]
);

mail_ops(
    'Pizzeria-Vorschlag: ' . $d['name'],
    "Jemand hat uns eine Pizzeria vorgeschlagen:\n\n"
    . "Name:    {$d['name']}\n"
    . 'Adresse: ' . trim(($d['strasse'] ?: '') . ', ' . ($d['plz'] ?: '') . ' ' . ($d['ort'] ?: ''), ', ') . "\n"
    . 'Hinweis: ' . ($d['hinweis'] ?: '–') . "\n"
    . 'Melder:  ' . ($d['melder_email'] ?: 'anonym') . "\n\n"
    . "Bitte den Betrieb ansprechen. Dem Betrieb gegenüber nennen wir nicht, wer ihn\n"
    . "vorgeschlagen hat.\n\n"
    . url('/admin/empfehlungen'),
    $d['melder_email'] ?: null
);

flash_set('empfehlung_ok', 'Danke Dir! Wir melden uns bei ' . $d['name'] . ' und erklären, worum es geht.');
redirect($zurueck . '?empfohlen=1#modal-empfehlung');
