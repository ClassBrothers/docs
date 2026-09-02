<?php
/**
 * Gemeinsame Logik zum Einspielen von Migrationen aus app/migrations/*.sql.
 * Wird von bin/migrate.php (Kommandozeile) und vom Admin-Bereich genutzt -
 * fuer Betrieb ohne Kommandozeilenzugang zum Server.
 */

declare(strict_types=1);

/**
 * Spielt alle noch nicht ausgefuehrten Migrationsdateien ein.
 *
 * @return array{eingespielt: string[], uebersprungen: string[], fehler: ?string}
 */
function migrationen_ausfuehren(): array
{
    $pdo = db();
    $pdo->exec('CREATE TABLE IF NOT EXISTS migrationen (datei TEXT PRIMARY KEY, ausgefuehrt_am TEXT NOT NULL)');

    $dateien = glob(APP_ROOT . '/app/migrations/*.sql') ?: [];
    sort($dateien);

    $eingespielt   = [];
    $uebersprungen = [];

    foreach ($dateien as $datei) {
        $name  = basename($datei);
        $schon = db_value('SELECT COUNT(*) FROM migrationen WHERE datei = ?', [$name], 0);
        if ($schon) {
            $uebersprungen[] = $name;
            continue;
        }

        $sql = file_get_contents($datei);
        if ($sql === false) {
            return ['eingespielt' => $eingespielt, 'uebersprungen' => $uebersprungen, 'fehler' => "Kann $name nicht lesen."];
        }

        try {
            $pdo->exec($sql);
            db_run('INSERT INTO migrationen (datei, ausgefuehrt_am) VALUES (?, ?)', [$name, gmdate('Y-m-d H:i:s')]);
            $eingespielt[] = $name;
        } catch (Throwable $eFehler) {
            return ['eingespielt' => $eingespielt, 'uebersprungen' => $uebersprungen, 'fehler' => "Fehler in $name: " . $eFehler->getMessage()];
        }
    }

    return ['eingespielt' => $eingespielt, 'uebersprungen' => $uebersprungen, 'fehler' => null];
}
