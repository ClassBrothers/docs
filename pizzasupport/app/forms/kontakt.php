<?php
/** Kontaktformular. */

declare(strict_types=1);

$zurueck       = '/kontakt.html';
// Anker direkt auf der Fehlermeldung (siehe kontakt.php), damit ein
// Ruecksprung nach einem Fehler nicht ueber das Formular hinweg ganz nach
// oben auf die Seite springt.
$zurueckFehler = '/kontakt.html#kontakt-fehler';

if (!honeypot_ok($_POST)) {
    flash_set('kontakt_ok', 'Danke, Deine Nachricht ist bei uns. Wir melden uns binnen 24 Stunden.');
    redirect($zurueck);
}

if (!rate_limit_ok('kontakt', 5, 3600)) {
    flash_set('kontakt_fehler', ['nachricht' => 'Von hier kamen gerade viele Nachrichten. Bitte versuch es später noch einmal.']);
    flash_set('kontakt_alt', $_POST);
    redirect($zurueckFehler);
}

$v = new Validator($_POST);
$v->text('name', 'Dein Name', true, 120)
  ->email('email', 'Deine E-Mail-Adresse')
  ->text('betreff', 'Der Betreff', false, 150)
  ->langtext('nachricht', 'Deine Nachricht', true, 4000)
  ->checkbox('datenschutz_ok', 'Ohne Zustimmung zu den Datenschutzhinweisen dürfen wir Deine Anfrage nicht bearbeiten.');

if (mb_strlen((string) $v->get('nachricht')) < 10) {
    $v->fehlerSetzen('nachricht', 'Ein paar Worte mehr helfen uns, Dir sinnvoll zu antworten.');
}

// Einfache Sicherheitsabfrage: entweder der Klick auf die Pizza oder die
// Textalternative fuer Tastatur- und Screenreader-Nutzung muss stimmen.
// Zwei unterschiedliche Meldungen: gar nichts ausgewaehlt (freundlicher
// Hinweis, was zu tun ist) vs. eine falsche Option angeklickt oder
// eingetippt (pointierter Hinweis, dass die Antwort konkret falsch war).
$captchaKlick = (string) ($_POST['captcha_klick'] ?? '');
$captchaText  = mb_strtolower(trim((string) ($_POST['captcha_text'] ?? '')));
if ($captchaKlick !== 'pizza' && $captchaText !== 'pizza') {
    if ($captchaKlick === '' && $captchaText === '') {
        $v->fehlerSetzen('captcha', 'Bitte beantworte die Sicherheitsfrage: Klick auf die Pizza, oder nenne sie im Textfeld.');
    } else {
        $v->fehlerSetzen('captcha', 'Echt jetzt? Hier geht’s um welches Superfood?');
    }
}

if (!$v->ok()) {
    flash_set('kontakt_fehler', $v->fehler());
    flash_set('kontakt_alt', $_POST);
    redirect($zurueckFehler);
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
    'Deine Nachricht an Pizza Support',
    "Hallo {$d['name']},\n\n"
    . "Deine Nachricht ist bei uns angekommen. Wir melden uns werktags in der Regel am\n"
    . "selben oder am nächsten Tag.\n\n"
    . "Zur Sicherheit hier noch einmal, was Du geschrieben hast:\n\n"
    . "---\n" . $d['nachricht'] . "\n---"
    . mail_signatur(),
    (string) env('MAIL_TO_OPS')
);

flash_set('kontakt_ok', 'Danke, Deine Nachricht ist bei uns. Wir melden uns binnen 24 Stunden.');
redirect($zurueck);
