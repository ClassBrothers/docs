<?php
declare(strict_types=1);

/** Passwort-Hash fuer config.local.php erzeugen:  php bin/passwort.php "MeinPasswort" */
$passwort = $argv[1] ?? null;
if ($passwort === null || $passwort === '') {
    fwrite(STDERR, "Aufruf: php bin/passwort.php \"MeinPasswort\"\n");
    exit(1);
}
echo password_hash($passwort, PASSWORD_DEFAULT), PHP_EOL;
