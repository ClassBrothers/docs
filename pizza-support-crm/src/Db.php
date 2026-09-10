<?php
declare(strict_types=1);

/** Duenner PDO-Wrapper inkl. Schema-Initialisierung. */
final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $pfad = (string) ps_cfg('db_pfad');
        $verzeichnis = dirname($pfad);
        if (!is_dir($verzeichnis)) {
            mkdir($verzeichnis, 0770, true);
        }
        $neu = !is_file($pfad);

        $pdo = new PDO('sqlite:' . $pfad, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // WAL ist schneller, scheitert aber auf manchen Netzwerk-Dateisystemen —
        // dort bleibt es beim Standard-Journal, das genuegt fuer den Einzelplatzbetrieb.
        try {
            $pdo->exec('PRAGMA journal_mode = WAL');
        } catch (PDOException) {
        }
        $pdo->exec('PRAGMA foreign_keys = ON');
        self::$pdo = $pdo;

        self::migriere();
        if ($neu) {
            @chmod($pfad, 0660);
        }
        return self::$pdo;
    }

    /** Schema anlegen bzw. fehlende Spalten nachziehen (idempotent). */
    public static function migriere(): void
    {
        $pdo = self::$pdo ?? self::pdo();
        $pdo->exec((string) file_get_contents(__DIR__ . '/schema.sql'));
        Vorlagen::standardsAnlegen();
    }

    /** @return array<int,array<string,mixed>> */
    public static function alle(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function eine(string $sql, array $params = []): ?array
    {
        $zeile = self::alle($sql, $params)[0] ?? null;
        return $zeile ?: null;
    }

    public static function wert(string $sql, array $params = []): mixed
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public static function fuehreAus(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
