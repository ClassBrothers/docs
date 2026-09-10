<?php
declare(strict_types=1);

/** Datenzugriff auf Betriebe und Aktivitaeten. */
final class Repo
{
    public const FELDER = [
        'name', 'inhaber', 'strasse', 'plz', 'stadtteil', 'ort', 'telefon', 'email', 'website',
        'art_gastronomie', 'pizza_fokus', 'google_bewertung', 'anzahl_bewertungen',
        'pizzenvolumen_text', 'pizzenvolumen_monat', 'volumen_klasse', 'prioritaet', 'status_quelle', 'notiz_quelle',
        'pipeline_stage', 'kontaktversuche', 'letzter_kontakt', 'naechster_follow_up',
        'vertragsdatum', 'anzahl_flaechen_gebucht', 'tags', 'notizen',
        'anreicherung_status', 'anreicherung_quelle', 'anreicherung_datum',
    ];

    /**
     * Liste mit Filtern.
     * @param array<string,mixed> $filter prioritaet, stadtteil, pizza_fokus, stage, suche, followup_faellig, kampagne
     * @return array<int,array<string,mixed>>
     */
    public static function liste(array $filter = []): array
    {
        [$where, $params] = self::whereBauen($filter);
        $sortierung = match ($filter['sort'] ?? '') {
            'name'        => 'name COLLATE NOCASE ASC',
            'volumen'     => 'COALESCE(pizzenvolumen_monat,0) DESC',
            'followup'    => "COALESCE(naechster_follow_up,'9999-12-31') ASC",
            'kontakt'     => "COALESCE(letzter_kontakt,'0000-01-01') ASC",
            default       => "CASE prioritaet WHEN 'A' THEN 0 WHEN 'B' THEN 1 ELSE 2 END, COALESCE(pizzenvolumen_monat,0) DESC, name COLLATE NOCASE",
        };
        $zeilen = Db::alle("SELECT * FROM betriebe WHERE {$where} ORDER BY {$sortierung}", $params);
        return array_map([self::class, 'anreichern'], $zeilen);
    }

    public static function finde(int $id): ?array
    {
        $zeile = Db::eine('SELECT * FROM betriebe WHERE id = ?', [$id]);
        return $zeile ? self::anreichern($zeile) : null;
    }

    /** Berechnete Felder ergaenzen, die das Frontend sonst mehrfach nachbauen muesste. */
    private static function anreichern(array $b): array
    {
        $b['id'] = (int) $b['id'];
        $b['tage_seit_kontakt'] = null;
        if (!empty($b['letzter_kontakt'])) {
            $tage = (new DateTimeImmutable(substr((string) $b['letzter_kontakt'], 0, 10)))
                ->diff(new DateTimeImmutable('today'))->days;
            $b['tage_seit_kontakt'] = (int) $tage;
        }
        $heute = date('Y-m-d');
        $b['followup_faellig'] = !empty($b['naechster_follow_up']) && substr((string) $b['naechster_follow_up'], 0, 10) <= $heute;
        // "Kalt": laenger als 7 Tage kein Kontakt UND kein Follow-up-Termin gesetzt.
        $b['ueberfaellig'] = ($b['tage_seit_kontakt'] !== null && $b['tage_seit_kontakt'] > 7 && empty($b['naechster_follow_up']));
        $b['stage_label'] = PS_STAGES[$b['pipeline_stage']] ?? $b['pipeline_stage'];
        $b['tags_liste'] = array_values(array_filter(array_map('trim', explode(',', (string) $b['tags']))));
        $b['adresse_zeilen'] = self::adresse($b);
        $b['mail_erlaubt'] = ((int) $b['kontaktversuche']) >= 1 && self::istMail((string) $b['email']);
        return $b;
    }

    /** @return array<int,string> Postalische Adresszeilen (leere Zeilen entfallen). */
    public static function adresse(array $b): array
    {
        $zeilen = [trim((string) $b['name'])];
        if (!empty($b['inhaber']) && !self::istPlatzhalter((string) $b['inhaber'])) {
            $zeilen[] = 'z. Hd. ' . trim((string) $b['inhaber']);
        }
        if (!empty($b['strasse'])) {
            $zeilen[] = trim((string) $b['strasse']);
        }
        $plzOrt = trim(trim((string) $b['plz']) . ' ' . trim((string) ($b['ort'] ?: 'Freiburg im Breisgau')));
        if ($plzOrt !== '') {
            $zeilen[] = $plzOrt;
        }
        return $zeilen;
    }

    public static function istPlatzhalter(string $wert): bool
    {
        $w = mb_strtolower(trim($wert));
        return $w === '' || str_contains($w, 'zu recherchieren') || str_contains($w, 'unbekannt')
            || str_contains($w, 'kein eigenes impressum');
    }

    public static function istMail(string $wert): bool
    {
        return !self::istPlatzhalter($wert) && (bool) filter_var(trim($wert), FILTER_VALIDATE_EMAIL);
    }

    /** @return array{0:string,1:array<string,mixed>} */
    private static function whereBauen(array $f): array
    {
        $where = ['kampagne = :kampagne'];
        $params = ['kampagne' => $f['kampagne'] ?? 'pizzasupport'];

        if (!empty($f['prioritaet'])) {
            $prios = array_values(array_filter((array) $f['prioritaet']));
            if ($prios) {
                $platz = [];
                foreach ($prios as $i => $p) {
                    $platz[] = ":p{$i}";
                    $params["p{$i}"] = $p;
                }
                $where[] = 'prioritaet IN (' . implode(',', $platz) . ')';
            }
        }
        if (!empty($f['stadtteil'])) {
            $where[] = 'stadtteil = :stadtteil';
            $params['stadtteil'] = $f['stadtteil'];
        }
        if (!empty($f['pizza_fokus'])) {
            $where[] = 'pizza_fokus = :pizza_fokus';
            $params['pizza_fokus'] = $f['pizza_fokus'];
        }
        if (!empty($f['stage'])) {
            $stages = array_values(array_filter((array) $f['stage']));
            if ($stages) {
                $platz = [];
                foreach ($stages as $i => $s) {
                    $platz[] = ":s{$i}";
                    $params["s{$i}"] = $s;
                }
                $where[] = 'pipeline_stage IN (' . implode(',', $platz) . ')';
            }
        }
        if (!empty($f['ohne_kontakt'])) {
            $where[] = 'kontaktversuche = 0';
        }
        if (!empty($f['followup_faellig'])) {
            $where[] = "naechster_follow_up IS NOT NULL AND naechster_follow_up <> '' AND substr(naechster_follow_up,1,10) <= :heute";
            $params['heute'] = date('Y-m-d');
        }
        if (!empty($f['tag'])) {
            $where[] = "(',' || REPLACE(tags,', ',',') || ',') LIKE :tag";
            $params['tag'] = '%,' . $f['tag'] . ',%';
        }
        if (!empty($f['suche'])) {
            $where[] = '(name LIKE :q OR inhaber LIKE :q OR strasse LIKE :q OR stadtteil LIKE :q OR telefon LIKE :q OR notizen LIKE :q)';
            $params['q'] = '%' . $f['suche'] . '%';
        }
        if (!empty($f['ids']) && is_array($f['ids'])) {
            // Ganzzahlen direkt einsetzen: benannte und positionelle Platzhalter
            // lassen sich in einem Statement nicht mischen.
            $ids = array_map('intval', $f['ids']);
            $where[] = 'id IN (' . implode(',', $ids ?: [0]) . ')';
        }
        return [implode(' AND ', $where), $params];
    }

    /** Anlegen. Gibt die neue ID zurueck. */
    public static function anlegen(array $daten): int
    {
        $daten = self::saeubern($daten);
        $daten['kampagne'] = $daten['kampagne'] ?? 'pizzasupport';
        $spalten = array_keys($daten);
        $sql = 'INSERT INTO betriebe (' . implode(',', $spalten) . ') VALUES (:' . implode(',:', $spalten) . ')';
        Db::fuehreAus($sql, $daten);
        return (int) Db::pdo()->lastInsertId();
    }

    public static function aktualisieren(int $id, array $daten): void
    {
        $daten = self::saeubern($daten);
        if (!$daten) {
            return;
        }
        $sets = [];
        foreach (array_keys($daten) as $spalte) {
            $sets[] = "{$spalte} = :{$spalte}";
        }
        $daten['id'] = $id;
        Db::fuehreAus('UPDATE betriebe SET ' . implode(',', $sets) . ", geaendert_am = datetime('now') WHERE id = :id", $daten);
    }

    public static function loeschen(int $id): void
    {
        Db::fuehreAus('DELETE FROM betriebe WHERE id = ?', [$id]);
    }

    /** Nur bekannte Felder durchlassen und Typen normalisieren. */
    private static function saeubern(array $daten): array
    {
        $sauber = [];
        foreach ($daten as $schluessel => $wert) {
            if ($schluessel === 'kampagne') {
                $sauber[$schluessel] = (string) $wert;
                continue;
            }
            if (!in_array($schluessel, self::FELDER, true)) {
                continue;
            }
            if (in_array($schluessel, ['anzahl_bewertungen', 'pizzenvolumen_monat', 'kontaktversuche', 'anzahl_flaechen_gebucht'], true)) {
                $sauber[$schluessel] = ($wert === '' || $wert === null) ? null : (int) $wert;
                continue;
            }
            if ($schluessel === 'google_bewertung') {
                $sauber[$schluessel] = ($wert === '' || $wert === null) ? null : (float) str_replace(',', '.', (string) $wert);
                continue;
            }
            if ($schluessel === 'pipeline_stage' && !isset(PS_STAGES[(string) $wert])) {
                continue;
            }
            $sauber[$schluessel] = is_string($wert) ? trim($wert) : $wert;
        }
        return $sauber;
    }

    /**
     * Stufe setzen und Wechsel automatisch protokollieren.
     * Bei 'gewonnen' wird vertragsdatum gesetzt, falls noch leer.
     */
    public static function stageSetzen(int $id, string $stage, string $notiz = ''): array
    {
        $betrieb = self::finde($id);
        if (!$betrieb) {
            throw new RuntimeException('Betrieb nicht gefunden.');
        }
        if (!isset(PS_STAGES[$stage])) {
            throw new InvalidArgumentException('Unbekannte Pipeline-Stufe.');
        }
        if ($betrieb['pipeline_stage'] === $stage) {
            return $betrieb;
        }
        $daten = ['pipeline_stage' => $stage];
        if ($stage === 'gewonnen' && empty($betrieb['vertragsdatum'])) {
            $daten['vertragsdatum'] = date('Y-m-d');
        }
        self::aktualisieren($id, $daten);
        self::aktivitaetAnlegen($id, [
            'typ'   => 'stage',
            'notiz' => trim(sprintf('%s → %s. %s', PS_STAGES[$betrieb['pipeline_stage']] ?? $betrieb['pipeline_stage'], PS_STAGES[$stage], $notiz)),
        ], false);
        return self::finde($id) ?? $betrieb;
    }

    /**
     * Aktivitaet protokollieren. Zaehlt bei Anruf/Brief/Mail/Besuch die Kontaktversuche hoch
     * und schreibt letzter_kontakt.
     */
    public static function aktivitaetAnlegen(int $betriebId, array $daten, bool $zaehlen = true): int
    {
        $typ = (string) ($daten['typ'] ?? 'sonstiges');
        if (!isset(PS_AKTIVITAETS_TYPEN[$typ])) {
            $typ = 'sonstiges';
        }
        $datum = trim((string) ($daten['datum'] ?? '')) ?: date('Y-m-d H:i:s');
        Db::fuehreAus(
            'INSERT INTO aktivitaeten (betrieb_id, datum, typ, notiz, ergebnis) VALUES (?,?,?,?,?)',
            [$betriebId, $datum, $typ, trim((string) ($daten['notiz'] ?? '')), trim((string) ($daten['ergebnis'] ?? ''))]
        );
        $id = (int) Db::pdo()->lastInsertId();

        $update = [];
        if ($zaehlen && in_array($typ, ['anruf', 'brief', 'mail', 'besuch'], true)) {
            Db::fuehreAus("UPDATE betriebe SET kontaktversuche = kontaktversuche + 1, letzter_kontakt = ?, geaendert_am = datetime('now') WHERE id = ?", [substr($datum, 0, 10), $betriebId]);
        }
        if (array_key_exists('naechster_follow_up', $daten)) {
            $update['naechster_follow_up'] = $daten['naechster_follow_up'] ?: null;
        }
        if ($update) {
            self::aktualisieren($betriebId, $update);
        }
        return $id;
    }

    /** @return array<int,array<string,mixed>> */
    public static function aktivitaeten(int $betriebId): array
    {
        return Db::alle('SELECT * FROM aktivitaeten WHERE betrieb_id = ? ORDER BY datum DESC, id DESC', [$betriebId]);
    }

    public static function aktivitaetLoeschen(int $id): void
    {
        Db::fuehreAus('DELETE FROM aktivitaeten WHERE id = ?', [$id]);
    }

    /** Werte fuer die Filterleiste. */
    public static function filterWerte(string $kampagne = 'pizzasupport'): array
    {
        $spalte = static fn (string $s): array => array_values(array_filter(array_column(
            Db::alle("SELECT DISTINCT {$s} AS w FROM betriebe WHERE kampagne = ? AND {$s} IS NOT NULL AND {$s} <> '' ORDER BY w COLLATE NOCASE", [$kampagne]),
            'w'
        )));
        $tags = [];
        foreach (Db::alle('SELECT tags FROM betriebe WHERE kampagne = ? AND tags <> ""', [$kampagne]) as $zeile) {
            foreach (explode(',', (string) $zeile['tags']) as $tag) {
                $tag = trim($tag);
                if ($tag !== '') {
                    $tags[$tag] = true;
                }
            }
        }
        ksort($tags);
        return [
            'stadtteile'  => $spalte('stadtteil'),
            'pizza_fokus' => $spalte('pizza_fokus'),
            'prioritaeten' => $spalte('prioritaet'),
            'tags'        => array_keys($tags),
        ];
    }
}
