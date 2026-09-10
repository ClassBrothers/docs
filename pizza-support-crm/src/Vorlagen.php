<?php
declare(strict_types=1);

/** Brief- und Mail-Vorlagen inkl. Platzhalter-Ersetzung. */
final class Vorlagen
{
    /** @return array<int,array<string,mixed>> */
    public static function liste(?string $art = null): array
    {
        $sql = 'SELECT * FROM vorlagen';
        $params = [];
        if ($art !== null) {
            $sql .= ' WHERE art = ?';
            $params[] = $art;
        }
        return Db::alle($sql . ' ORDER BY ist_standard DESC, name COLLATE NOCASE', $params);
    }

    public static function finde(int $id): ?array
    {
        return Db::eine('SELECT * FROM vorlagen WHERE id = ?', [$id]);
    }

    public static function standard(string $art): ?array
    {
        return Db::eine('SELECT * FROM vorlagen WHERE art = ? ORDER BY ist_standard DESC, id ASC LIMIT 1', [$art]);
    }

    public static function speichern(?int $id, array $daten): int
    {
        $art = in_array($daten['art'] ?? '', ['brief', 'mail'], true) ? $daten['art'] : 'brief';
        $name = trim((string) ($daten['name'] ?? 'Ohne Titel'));
        $betreff = trim((string) ($daten['betreff'] ?? ''));
        $koerper = (string) ($daten['koerper'] ?? '');

        if ($id) {
            Db::fuehreAus("UPDATE vorlagen SET art=?, name=?, betreff=?, koerper=?, geaendert_am=datetime('now') WHERE id=?", [$art, $name, $betreff, $koerper, $id]);
            return $id;
        }
        Db::fuehreAus('INSERT INTO vorlagen (art, name, betreff, koerper) VALUES (?,?,?,?)', [$art, $name, $betreff, $koerper]);
        return (int) Db::pdo()->lastInsertId();
    }

    public static function loeschen(int $id): void
    {
        Db::fuehreAus('DELETE FROM vorlagen WHERE id = ? AND ist_standard = 0', [$id]);
    }

    /**
     * Anrede aus dem Inhaber-Feld ableiten.
     * "zu recherchieren", leer oder Firmenname -> neutraler Fallback.
     */
    public static function anrede(array $betrieb): string
    {
        $inhaber = trim((string) ($betrieb['inhaber'] ?? ''));
        if (Repo::istPlatzhalter($inhaber)) {
            return 'Sehr geehrte Damen und Herren';
        }
        // Titel und Vornamen entfernen, nur den Nachnamen ansprechen.
        $inhaber = preg_replace('/\b(Herr|Frau|Dr\.|Prof\.|Dipl\.-\w+)\b\s*/iu', '', $inhaber) ?? $inhaber;
        $inhaber = trim(preg_replace('/\s*\(.*?\)\s*/u', ' ', $inhaber) ?? $inhaber);
        // Mehrere Personen ("A und B") -> neutral bleiben, sonst wird es schnell falsch.
        if (preg_match('/\s(und|&|\/)\s/u', $inhaber) || $inhaber === '') {
            return 'Sehr geehrte Damen und Herren';
        }
        $teile = preg_split('/\s+/u', $inhaber) ?: [];
        $nachname = (string) end($teile);
        if (mb_strlen($nachname) < 2) {
            return 'Sehr geehrte Damen und Herren';
        }
        // Ohne verlaessliches Geschlecht ist die Doppelanrede die sichere Variante.
        return 'Sehr geehrte Frau ' . $nachname . ', sehr geehrter Herr ' . $nachname;
    }

    /** @return array<string,string> */
    public static function platzhalter(array $betrieb): array
    {
        $plzOrt = trim(trim((string) $betrieb['plz']) . ' ' . trim((string) ($betrieb['ort'] ?: 'Freiburg im Breisgau')));
        $werte = [
            'Name'      => (string) $betrieb['name'],
            'Anrede'    => self::anrede($betrieb),
            'Straße'    => (string) $betrieb['strasse'],
            'Strasse'   => (string) $betrieb['strasse'],
            'PLZ_Ort'   => $plzOrt,
            'PLZ'       => (string) $betrieb['plz'],
            'Ort'       => (string) ($betrieb['ort'] ?: 'Freiburg im Breisgau'),
            'Stadtteil' => (string) $betrieb['stadtteil'],
            'Inhaber'   => Repo::istPlatzhalter((string) $betrieb['inhaber']) ? '' : (string) $betrieb['inhaber'],
            'Telefon'   => (string) $betrieb['telefon'],
            'Datum'     => self::datumLang(),
        ];
        foreach ((array) ps_cfg('absender', []) as $schluessel => $wert) {
            $werte['Absender_' . $schluessel] = (string) $wert;
        }
        return $werte;
    }

    public static function fuellen(string $text, array $betrieb): string
    {
        $werte = self::platzhalter($betrieb);
        $ersetzt = preg_replace_callback('/\{\{\s*([\wÄÖÜäöüß_]+)\s*\}\}/u', static function (array $treffer) use ($werte): string {
            return $werte[$treffer[1]] ?? '';
        }, $text);
        return $ersetzt ?? $text;
    }

    public static function datumLang(?string $datum = null): string
    {
        $monate = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        $zeit = new DateTimeImmutable($datum ?? 'today');
        return sprintf('%d. %s %d', (int) $zeit->format('j'), $monate[(int) $zeit->format('n') - 1], (int) $zeit->format('Y'));
    }

    /** Standardvorlagen einmalig anlegen (idempotent ueber ist_standard). */
    public static function standardsAnlegen(): void
    {
        $vorhanden = (int) Db::wert('SELECT COUNT(*) FROM vorlagen WHERE ist_standard = 1');
        if ($vorhanden > 0) {
            return;
        }
        $brief = (string) file_get_contents(PS_ROOT . '/templates/brief_standard.txt');
        $mail = (string) file_get_contents(PS_ROOT . '/templates/mail_nachfass.txt');
        [$mailBetreff, $mailKoerper] = self::betreffAbtrennen($mail);

        Db::fuehreAus('INSERT INTO vorlagen (art, name, betreff, koerper, ist_standard) VALUES (?,?,?,?,1)', ['brief', 'Erstanschreiben Pizza Support', '', $brief]);
        Db::fuehreAus('INSERT INTO vorlagen (art, name, betreff, koerper, ist_standard) VALUES (?,?,?,?,1)', ['mail', 'Nachfass nach Telefonat', $mailBetreff, $mailKoerper]);
    }

    /** Erste Zeile "Betreff: ..." aus einer Textdatei abtrennen. */
    private static function betreffAbtrennen(string $text): array
    {
        $zeilen = preg_split('/\R/u', $text) ?: [];
        if ($zeilen && str_starts_with(trim($zeilen[0]), 'Betreff:')) {
            $betreff = trim(substr(trim($zeilen[0]), 8));
            array_shift($zeilen);
            return [$betreff, ltrim(implode("\n", $zeilen))];
        }
        return ['', $text];
    }
}
