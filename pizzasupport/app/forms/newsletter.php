<?php
/** Newsletter-Anmeldung mit Double-Opt-in. */

declare(strict_types=1);

$zurueck = '/teilnehmer.html#newsletter';

if (!honeypot_ok($_POST)) {
    flash_set('newsletter_ok', 'Fast geschafft: Bitte bestätigen Sie noch den Link in Ihrer E-Mail.');
    redirect($zurueck);
}

if (!rate_limit_ok('newsletter', 5, 3600)) {
    flash_set('newsletter_fehler', ['email' => 'Von hier kamen gerade viele Anmeldungen. Bitte versuchen Sie es später noch einmal.']);
    redirect($zurueck);
}

$v = new Validator($_POST);
$v->email('email', 'Die E-Mail-Adresse')
  ->checkbox('datenschutz_ok', 'Ohne Ihre Einwilligung dürfen wir Ihnen nichts schicken.');

if (!$v->ok()) {
    flash_set('newsletter_fehler', $v->fehler());
    redirect($zurueck);
}

$email = (string) $v->get('email');
$token = bin2hex(random_bytes(32));
$jetzt = gmdate('Y-m-d H:i:s');

$vorhanden = db_one('SELECT id, bestaetigt FROM newsletter WHERE email = ?', [$email]);

if ($vorhanden && (int) $vorhanden['bestaetigt'] === 1) {
    // Nicht verraten, wer schon eingetragen ist – dieselbe Antwort für alle.
    flash_set('newsletter_ok', 'Fast geschafft: Wenn die Adresse neu ist, liegt gleich eine Bestätigungsmail in Ihrem Postfach.');
    redirect($zurueck);
}

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
    'Bitte bestätigen Sie Ihre Anmeldung',
    "Guten Tag,\n\n"
    . "Sie möchten erfahren, wie es mit Pizza Support weitergeht. Damit sicher ist, dass\n"
    . "die Anmeldung wirklich von Ihnen kommt, klicken Sie bitte einmal auf diesen Link:\n\n"
    . url('/newsletter-bestaetigen?token=' . $token) . "\n\n"
    . "Erst danach sind Sie eingetragen. Wenn Sie sich nicht angemeldet haben, ignorieren\n"
    . "Sie diese Nachricht einfach – wir löschen die Adresse dann automatisch wieder.\n\n"
    . "Wir schreiben selten: wenn die Schwelle fällt, wenn die Produktion startet und wenn\n"
    . "die Kartons unterwegs sind."
    . mail_signatur()
);

flash_set('newsletter_ok', 'Fast geschafft: Wenn die Adresse neu ist, liegt gleich eine Bestätigungsmail in Ihrem Postfach.');
redirect($zurueck);
