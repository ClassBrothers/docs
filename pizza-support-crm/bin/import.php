<?php
declare(strict_types=1);

/**
 * Import der Akquiseliste.
 * Aufruf:  php bin/import.php <datei.xlsx|datei.csv> [--blatt=Akquiseliste] [--kampagne=pizzasupport] [--probelauf]
 */
require __DIR__ . '/../src/bootstrap.php';

$argumente = array_slice($argv, 1);
$datei = null;
$optionen = ['blatt' => 'Akquiseliste', 'kampagne' => 'pizzasupport', 'probelauf' => false];

foreach ($argumente as $argument) {
    if (str_starts_with($argument, '--')) {
        [$name, $wert] = array_pad(explode('=', substr($argument, 2), 2), 2, true);
        $optionen[$name] = $wert;
        continue;
    }
    $datei ??= $argument;
}

if ($datei === null) {
    fwrite(STDERR, "Aufruf: php bin/import.php <datei.xlsx|csv> [--blatt=Akquiseliste] [--kampagne=pizzasupport] [--probelauf]\n");
    exit(1);
}

try {
    Db::pdo();
    $bericht = Importer::ausDatei($datei, (string) $optionen['blatt'], (string) $optionen['kampagne'], (bool) $optionen['probelauf']);
} catch (Throwable $e) {
    fwrite(STDERR, 'Fehler: ' . $e->getMessage() . "\n");
    exit(1);
}

printf(
    "%s\n  gelesen:       %d\n  neu angelegt:  %d\n  aktualisiert:  %d\n  uebersprungen: %d\n",
    $optionen['probelauf'] ? 'PROBELAUF (nichts gespeichert)' : 'Import abgeschlossen',
    $bericht['gelesen'],
    $bericht['neu'],
    $bericht['aktualisiert'],
    $bericht['uebersprungen']
);
foreach ($bericht['warnungen'] as $warnung) {
    printf("  ! %s\n", $warnung);
}

$offen = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = ? AND anreicherung_status = 'offen'", [$optionen['kampagne']]);
if ($offen > 0) {
    printf("\nHinweis: %d Betriebe mit offenen Feldern (Inhaber/E-Mail/Website).\n  Anreicherung starten:  php bin/enrich.php --report\n", $offen);
}
