#!/usr/bin/env php
<?php
/** Erzeugt den Passwort-Hash fuer den Admin-Zugang. */
declare(strict_types=1);
$pw = $argv[1] ?? '';
if (strlen($pw) < 12) {
    fwrite(STDERR, "Aufruf: php bin/adminpass.php \"passwort\"  (mindestens 12 Zeichen)\n");
    exit(1);
}
echo "ADMIN_PASS_HASH=" . password_hash($pw, PASSWORD_DEFAULT) . "\n";
