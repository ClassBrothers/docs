<?php
/**
 * SQLite-Zugriff. Eine Verbindung pro Request, Prepared Statements ueberall.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = env('DB_PATH', 'storage/db/pizzasupport.sqlite');
    if ($path[0] !== '/') {
        $path = APP_ROOT . '/' . $path;
    }

    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
        throw new RuntimeException('Datenbankverzeichnis nicht anlegbar: ' . $dir);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // WAL haelt Lesezugriffe waehrend Schreibvorgaengen am Laufen,
    // busy_timeout faengt parallele Formularabsendungen ab.
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    @chmod($path, 0660);

    return $pdo;
}

/** Kurzform fuer parametrisierte Queries. Nie String-Konkatenation. */
function db_run(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_run($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_all(string $sql, array $params = []): array
{
    return db_run($sql, $params)->fetchAll();
}

function db_value(string $sql, array $params = [], $default = null)
{
    $v = db_run($sql, $params)->fetchColumn();
    return $v === false ? $default : $v;
}
