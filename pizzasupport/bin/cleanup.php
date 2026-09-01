#!/usr/bin/env php
<?php
/**
 * Loeschkonzept als Cronjob. Taeglich laufen lassen:
 *   0 3 * * *  /usr/bin/php /pfad/zum/projekt/bin/cleanup.php >> storage/logs/cleanup.log 2>&1
 *
 * - abgelehnte Eintraege werden nach Frist geloescht
 * - Rohdaten der Reichweitenmessung werden zu Tagessummen verdichtet
 * - Rate-Limit-Zaehler und unbestaetigte Newsletter-Anmeldungen fliegen raus
 */

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$fristen = config('aufbewahrung');
$jetzt   = gmdate('Y-m-d H:i:s');
$log     = static fn(string $s) => print('[' . gmdate('Y-m-d H:i:s') . '] ' . $s . "\n");

// 1. Abgelehnte Bestellungen und Buchungen
$grenze = gmdate('Y-m-d H:i:s', time() - $fristen['abgelehnte_eintraege'] * 86400);
foreach (['gastro_bestellungen', 'werbebuchungen'] as $tabelle) {
    $n = db_run(
        "DELETE FROM {$tabelle} WHERE status = 'abgelehnt' AND COALESCE(status_am, erstellt_am) < ?",
        [$grenze]
    )->rowCount();
    $log("$tabelle: $n abgelehnte Einträge gelöscht");
}

// 2. Reichweitenmessung verdichten
$grenzeTag = gmdate('Y-m-d', time() - $fristen['analytics_roh'] * 86400);
$tage = db_all('SELECT DISTINCT tag FROM analytics_hits WHERE tag < ?', [$grenzeTag]);
foreach ($tage as $t) {
    $zeilen = db_all(
        'SELECT pfad, COUNT(*) AS aufrufe, COUNT(DISTINCT besucher_hash) AS besuche
           FROM analytics_hits WHERE tag = ? GROUP BY pfad',
        [$t['tag']]
    );
    foreach ($zeilen as $z) {
        db_run(
            'INSERT INTO analytics_tage (tag, pfad, aufrufe, besuche) VALUES (?, ?, ?, ?)
             ON CONFLICT (tag, pfad) DO UPDATE SET aufrufe = excluded.aufrufe, besuche = excluded.besuche',
            [$t['tag'], $z['pfad'], (int) $z['aufrufe'], (int) $z['besuche']]
        );
    }
    db_run('DELETE FROM analytics_hits WHERE tag = ?', [$t['tag']]);
    $log('Reichweite verdichtet für ' . $t['tag']);
}

// 3. Rate-Limit
$n = db_run('DELETE FROM rate_limit WHERE erstellt_am < ?',
    [gmdate('Y-m-d H:i:s', time() - $fristen['rate_limit'] * 86400)])->rowCount();
$log("Rate-Limit: $n Zeilen gelöscht");

// 4. Nie bestaetigte Newsletter-Anmeldungen
$n = db_run('DELETE FROM newsletter WHERE bestaetigt = 0 AND erstellt_am < ?',
    [gmdate('Y-m-d H:i:s', time() - $fristen['newsletter_unbestaetigt'] * 86400)])->rowCount();
$log("Newsletter: $n unbestätigte Anmeldungen gelöscht");

// 5. Verwaiste Motivdateien
$verwaist = 0;
$basis = APP_ROOT . '/storage/uploads';
if (is_dir($basis)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basis, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $datei) {
        if (!$datei->isFile()) {
            continue;
        }
        $rel = ltrim(str_replace($basis, '', $datei->getPathname()), '/');
        $bekannt = db_value('SELECT COUNT(*) FROM werbebuchungen WHERE motiv_pfad = ?', [$rel], 0);
        if (!$bekannt && $datei->getMTime() < time() - 86400) {
            @unlink($datei->getPathname());
            $verwaist++;
        }
    }
}
$log("Uploads: $verwaist verwaiste Dateien gelöscht");

db()->exec('VACUUM');
$log('Fertig.');
