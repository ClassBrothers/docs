#!/usr/bin/env php
<?php
/**
 * Spielt alle noch nicht ausgefuehrten Dateien aus app/migrations ein.
 * Aufruf:  php bin/migrate.php
 *
 * Ohne Kommandozeilenzugang geht dasselbe ueber den Knopf "Migrationen
 * ausfuehren" im Admin-Bereich (/admin) - siehe app/lib/migrate.php.
 */

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$ergebnis = migrationen_ausfuehren();

foreach ($ergebnis['uebersprungen'] as $name) {
    echo "übersprungen  $name\n";
}
foreach ($ergebnis['eingespielt'] as $name) {
    echo "eingespielt   $name\n";
}

if ($ergebnis['fehler'] !== null) {
    fwrite(STDERR, $ergebnis['fehler'] . "\n");
    exit(1);
}

$neu = count($ergebnis['eingespielt']);
echo $neu === 0 ? "Datenbank ist aktuell.\n" : "$neu Migration(en) eingespielt.\n";
