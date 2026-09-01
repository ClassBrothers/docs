<?php
/** Kontaktformular. */

declare(strict_types=1);

$zurueck = '/kontakt.html';

if (!honeypot_ok($_POST)) {
    flash_set('kontakt_ok', 'Danke, Ihre Nachricht ist bei uns.');
    redirect($zurueck);
}

if (!rate_limit_ok('kontakt', 5, 3600)) {
    flash_set('kontakt_fehler', ['nachricht' => 'Von hier kamen gerade viele Nachrichten. Bitte versuchen Sie es später noch einmal.']);
    flash_set('kontakt_alt', $_POST);
    redirect($zurueck);
}

$v = new Validator($_POST);
$v->text('name', 'Ihr Name', true, 120)
  ->email('email', 'Ihre E-Mail-Adresse')
  ->text('betreff', 'Der Betreff', false, 150)
  ->langtext('nachricht', 'Ihre Nachricht', true, 4000)
  ->checkbox('datenschutz_ok', 'Ohne Zustimmung zu den Datenschutzhinweisen dürfen wir Ihre Anfrage nicht bearbeiten.');

if (mb_strlen((string) $v->get('nachricht')) < 10) {
    $v->fehlerSetzen('nachricht', 'Ein paar Worte mehr helfen uns, Ihnen sinnvoll zu antworten.');
}

if (!$v->ok()) {
    flash_set('kontakt_fehler', $v->fehler());
    flash_set('kontakt_alt', $_POST);
    redirect($zurueck);
}

$d = $v->daten();

db_run(
    'INSERT INTO kontaktanfragen (name, email, betreff, nachricht, datenschutz_ok, erstellt_am)
     VALUES (?,?,?,?,?,?)',
    [$d['name'], $d['email'], $d['betreff'], $d['nachricht'], $d['datenschutz_ok'], gmdate('Y-m-d H:i:s')]
);

mail_ops(
    'Kontaktanfrage: ' . ($d['betreff'] ?: 'ohne Betreff'),
    "Von:     {$d['name']} <{$d['email']}>\n"
    . 'Betreff: ' . ($d['betreff'] ?: '–') . "\n\n"
    . $d['nachricht'],
    $d['email']
);

mail_send(
    $d['email'],
    'Ihre Nachricht an Pizza Support',
    "Guten Tag {$d['name']},\n\n"
    . "Ihre Nachricht ist bei uns angekommen. Wir melden uns werktags in der Regel am\n"
    . "selben oder am nächsten Tag.\n\n"
    . "Zur Sicherheit hier noch einmal, was Sie geschrieben haben:\n\n"
    . "---\n" . $d['nachricht'] . "\n---"
    . mail_signatur(),
    (string) env('MAIL_TO_OPS')
);

flash_set('kontakt_ok', 'Danke, Ihre Nachricht ist bei uns. Wir melden uns werktags meist am selben Tag.');
redirect($zurueck);
