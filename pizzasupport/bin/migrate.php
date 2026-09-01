#!/usr/bin/env php
<?php
/**
 * Spielt alle noch nicht ausgefuehrten Dateien aus app/migrations ein.
 * Aufruf:  php bin/migrate.php
 */

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$pdo = db();
$pdo->exec('CREATE TABLE IF NOT EXISTS migrationen (datei TEXT PRIMARY KEY, ausgefuehrt_am TEXT NOT NULL)');

$dateien = glob(APP_ROOT . '/app/migrations/*.sql') ?: [];
sort($dateien);

$neu = 0;
foreach ($dateien as $datei) {
    $name = basename($datei);
    $schon = db_value('SELECT COUNT(*) FROM migrationen WHERE datei = ?', [$name], 0);
    if ($schon) {
        echo "übersprungen  $name\n";
        continue;
    }
    $sql = file_get_contents($datei);
    if ($sql === false) {
        fwrite(STDERR, "Kann $name nicht lesen.\n");
        exit(1);
    }
    try {
        $pdo->exec($sql);
        db_run('INSERT INTO migrationen (datei, ausgefuehrt_am) VALUES (?, ?)', [$name, gmdate('Y-m-d H:i:s')]);
        echo "eingespielt   $name\n";
        $neu++;
    } catch (Throwable $eFehler) {
        fwrite(STDERR, "Fehler in $name: " . $eFehler->getMessage() . "\n");
        exit(1);
    }
}

echo $neu === 0 ? "Datenbank ist aktuell.\n" : "$neu Migration(en) eingespielt.\n";
