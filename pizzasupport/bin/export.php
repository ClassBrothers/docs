#!/usr/bin/env php
<?php
/**
 * Auskunft und Export nach Art. 15/20 DSGVO.
 *
 *   php bin/export.php auskunft person@example.com   -> alles zu einer Adresse
 *   php bin/export.php loeschen person@example.com   -> alles zu einer Adresse löschen
 *   php bin/export.php csv gastro                    -> Tabelle als CSV auf stdout
 */

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$befehl = $argv[1] ?? '';
$arg    = $argv[2] ?? '';

$tabellen = [
    'gastro'      => ['gastro_bestellungen', 'email'],
    'werbung'     => ['werbebuchungen', 'email'],
    'newsletter'  => ['newsletter', 'email'],
    'kontakt'     => ['kontaktanfragen', 'email'],
    'empfehlung'  => ['pizzeria_empfehlungen', 'melder_email'],
    'abstimmung'  => ['gaeste_abstimmungen', 'email'],
];

/** Verschluesselte Spalten fuer die Ausgabe entschluesseln. */
function lesbar(array $zeile): array
{
    foreach ($zeile as $k => $v) {
        if (str_ends_with($k, '_enc') && is_string($v)) {
            $zeile[substr($k, 0, -4)] = decrypt_field($v);
            unset($zeile[$k]);
        }
    }
    return $zeile;
}

if ($befehl === 'auskunft' && $arg !== '') {
    $alles = [];
    foreach ($tabellen as $name => [$tab, $spalte]) {
        $zeilen = db_all("SELECT * FROM {$tab} WHERE {$spalte} = ?", [mb_strtolower($arg)]);
        if ($zeilen) {
            $alles[$name] = array_map('lesbar', $zeilen);
        }
    }
    echo json_encode($alles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
    exit;
}

if ($befehl === 'loeschen' && $arg !== '') {
    $summe = 0;
    foreach ($tabellen as [$tab, $spalte]) {
        $summe += db_run("DELETE FROM {$tab} WHERE {$spalte} = ?", [mb_strtolower($arg)])->rowCount();
    }
    echo "$summe Datensätze gelöscht.\n";
    exit;
}

if ($befehl === 'csv' && isset($tabellen[$arg])) {
    [$tab] = $tabellen[$arg];
    $zeilen = db_all("SELECT * FROM {$tab} ORDER BY id");
    if (!$zeilen) {
        echo "Keine Daten.\n";
        exit;
    }
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // BOM, damit Excel UTF-8 versteht
    $erste = lesbar($zeilen[0]);
    fputcsv($out, array_keys($erste), ';');
    foreach ($zeilen as $z) {
        fputcsv($out, array_values(lesbar($z)), ';');
    }
    exit;
}

fwrite(STDERR, "Aufruf:\n  php bin/export.php auskunft <email>\n  php bin/export.php loeschen <email>\n  php bin/export.php csv <"
    . implode('|', array_keys($tabellen)) . ">\n");
exit(1);
