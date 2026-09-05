<?php
/** Gaeste-Abstimmung auf /fuer-gaeste.html entgegennehmen. */

declare(strict_types=1);

$zurueck = '/fuer-gaeste.html#abstimmen';

if (!honeypot_ok($_POST)) {
    flash_set('abstimmung_ok', 'Danke für Deine Stimme!');
    redirect($zurueck);
}

if (!rate_limit_ok('abstimmung', 10, 3600)) {
    flash_set('abstimmung_fehler', ['aktion_bewertung' => 'Von hier kamen gerade sehr viele Stimmen. Bitte versuch es später noch einmal.']);
    flash_set('abstimmung_alt', $_POST);
    redirect($zurueck);
}

$maxAuswahl        = (int) config('abstimmung.max_auswahl');
$erlaubteMotive     = array_map('strval', array_column(
    db_all("SELECT id FROM werbebuchungen WHERE status = 'freigegeben'"),
    'id'
));

$v = new Validator($_POST);
$v->mehrfach('motiv_favorit', 'Dein Lieblingsmotiv', $erlaubteMotive)
  ->mehrfach('motiv_witzig', 'Das witzigste Motiv', $erlaubteMotive)
  ->auswahl('aktion_bewertung', 'Deine Einschätzung zur Aktion', ['super', 'gut', 'ok', 'schlecht'])
  ->langtext('feedback', 'Dein Feedback', false, 2000)
  ->plz('plz', 'Die Postleitzahl', false)
  ->zahl('alter_jahre', 'Dein Alter', 1, 120, false)
  ->text('name', 'Dein Name', false, 150)
  ->email('email', 'Die E-Mail-Adresse', false)
  ->checkbox('newsletter_ok', '', false);

if (count($v->get('motiv_favorit') ?? []) > $maxAuswahl) {
    $v->fehlerSetzen('motiv_favorit', 'Bitte wähle höchstens ' . $maxAuswahl . ' Motive aus.');
}
if (count($v->get('motiv_witzig') ?? []) > $maxAuswahl) {
    $v->fehlerSetzen('motiv_witzig', 'Bitte wähle höchstens ' . $maxAuswahl . ' Motive aus.');
}

// Name/Mail sind freiwillig - wer sie angibt, braucht die
// Datenschutz-Zustimmung dafuer; ohne Angabe entfaellt die Pflicht.
$moechteAntwort = $v->get('name') !== null || $v->get('email') !== null;
$v->checkbox(
    'datenschutz_ok',
    'Ohne Zustimmung zu den Datenschutzhinweisen dürfen wir Deinen Namen und Deine E-Mail-Adresse nicht speichern.',
    $moechteAntwort
);
if ($v->get('newsletter_ok') && $v->get('email') === null) {
    $v->fehlerSetzen('newsletter_ok', 'Für den Newsletter brauchen wir Deine E-Mail-Adresse.');
}

if (!$v->ok()) {
    flash_set('abstimmung_fehler', $v->fehler());
    flash_set('abstimmung_alt', $_POST);
    redirect($zurueck);
}

$d     = $v->daten();
$jetzt = gmdate('Y-m-d H:i:s');

db_run(
    'INSERT INTO gaeste_abstimmungen
        (motiv_favorit, motiv_witzig, aktion_bewertung, feedback, plz, alter_jahre,
         name, email, datenschutz_ok, newsletter_ok, erstellt_am, quelle)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
    [
        json_encode($d['motiv_favorit'], JSON_UNESCAPED_UNICODE),
        json_encode($d['motiv_witzig'], JSON_UNESCAPED_UNICODE),
        $d['aktion_bewertung'], $d['feedback'], $d['plz'], $d['alter_jahre'],
        $d['name'], $d['email'], $d['datenschutz_ok'], $d['newsletter_ok'], $jetzt, 'website',
    ]
);

// Newsletter-Anmeldung ueber denselben Double-Opt-in wie das eigenstaendige
// Newsletter-Formular (siehe app/forms/newsletter.php) - keine eigene
// zweite Anmeldelogik dafuer.
if ($d['newsletter_ok'] && $d['email']) {
    $email = (string) $d['email'];
    $vorhanden = db_one('SELECT id, bestaetigt FROM newsletter WHERE email = ?', [$email]);
    if (!$vorhanden || (int) $vorhanden['bestaetigt'] !== 1) {
        $token = bin2hex(random_bytes(32));
        if ($vorhanden) {
            db_run('UPDATE newsletter SET token = ?, erstellt_am = ? WHERE id = ?', [$token, $jetzt, $vorhanden['id']]);
        } else {
            db_run(
                'INSERT INTO newsletter (email, token, bestaetigt, einwilligung_zweck, erstellt_am) VALUES (?,?,?,?,?)',
                [$email, $token, 0, 'Informationen zum Projektstand von Pizza Support', $jetzt]
            );
        }
        mail_send(
            $email,
            'Bitte bestätige Deine Anmeldung',
            "Hallo,\n\n"
            . "Du möchtest erfahren, wie es mit Pizza Support weitergeht. Damit sicher ist, dass\n"
            . "die Anmeldung wirklich von Dir kommt, klick bitte einmal auf diesen Link:\n\n"
            . url('/newsletter-bestaetigen?token=' . $token) . "\n\n"
            . "Erst danach bist Du eingetragen. Wenn Du Dich nicht angemeldet hast, ignoriere\n"
            . "diese Nachricht einfach – wir löschen die Adresse dann automatisch wieder."
            . mail_signatur()
        );
    }
}

flash_set(
    'abstimmung_ok',
    'Danke für Deine Stimme!' . ($d['newsletter_ok'] && $d['email']
        ? ' Für den Newsletter bekommst Du gleich noch eine Bestätigungsmail.'
        : '')
);
redirect($zurueck);
