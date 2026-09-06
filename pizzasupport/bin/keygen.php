#!/usr/bin/env php
<?php
/** Erzeugt einen APP_KEY fuer die Feldverschluesselung. */
declare(strict_types=1);
echo "APP_KEY=" . base64_encode(random_bytes(32)) . "\n";
echo "HASH_SALT=" . bin2hex(random_bytes(16)) . "\n";
echo "\nBeide Zeilen in die .env übernehmen. Der APP_KEY darf danach nicht mehr\n";
echo "geändert werden – sonst sind bereits gespeicherte Felder nicht mehr lesbar.\n";
