<?php
/** Klick auf den Bestaetigungslink aus der Double-Opt-in-Mail. */

declare(strict_types=1);

$token = (string) ($_GET['token'] ?? '');
$ok    = false;

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $zeile = db_one('SELECT id FROM newsletter WHERE token = ? AND bestaetigt = 0', [$token]);
    if ($zeile) {
        db_run(
            'UPDATE newsletter SET bestaetigt = 1, bestaetigt_am = ?, token = ? WHERE id = ?',
            [gmdate('Y-m-d H:i:s'), bin2hex(random_bytes(32)), $zeile['id']]
        );
        $ok = true;
    }
}

flash_set('newsletter_bestaetigt_zustand', $ok ? 'ok' : 'fehler');
redirect('/newsletter-bestaetigt.html');
