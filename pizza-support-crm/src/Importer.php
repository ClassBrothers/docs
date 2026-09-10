<?php
declare(strict_types=1);

/** Import der Akquiseliste (XLSX oder CSV) in die Datenbank. */
final class Importer
{
    /** Excel-Spaltenueberschrift => DB-Spalte. */
    private const ZUORDNUNG = [
        'Name'                            => 'name',
        'Inhaber/Ansprechpartner'         => 'inhaber',
        'Straße'                          => 'strasse',
        'Strasse'                         => 'strasse',
        'PLZ'                             => 'plz',
        'Stadtteil'                       => 'stadtteil',
        'Ort'                             => 'ort',
        'Telefonnummer'                   => 'telefon',
        'Telefon'                         => 'telefon',
        'E-Mail'                          => 'email',
        'Website'                         => 'website',
        'Art der Gastronomie'             => 'art_gastronomie',
        'Pizza-Hauptfokus'                => 'pizza_fokus',
        'Google-Bewertung'                => 'google_bewertung',
        'Anzahl Bewertungen'              => 'anzahl_bewertungen',
        'Geschätztes Pizzenvolumen/Monat' => 'pizzenvolumen_text',
        'Priorität'                       => 'prioritaet',
        'Status'                          => 'status_quelle',
        'Notiz/Quelle'                    => 'notiz_quelle',
    ];

    /** Status-Rohwert aus der Excel-Liste => Pipeline-Stufe. */
    private const STATUS_ZUORDNUNG = [
        'neu'                  => 'neu',
        'kontaktiert'          => 'kontaktversuch_1',
        'kontaktversuch 1'     => 'kontaktversuch_1',
        'kontaktversuch 2'     => 'kontaktversuch_2',
        'erreicht'             => 'erreicht_interessiert',
        'interessiert'         => 'erreicht_interessiert',
        'angebot verschickt'   => 'angebot_verschickt',
        'verhandlung'          => 'verhandlung',
        'gewonnen'             => 'gewonnen',
        'abgelehnt'            => 'abgelehnt',
        'kein interesse'       => 'kein_interesse',
        'nicht erreichbar'     => 'kein_interesse',
    ];

    /**
     * @return array{gelesen:int,neu:int,aktualisiert:int,uebersprungen:int,warnungen:array<int,string>}
     */
    public static function ausDatei(string $pfad, string $blatt = 'Akquiseliste', string $kampagne = 'pizzasupport', bool $probelauf = false): array
    {
        $endung = strtolower(pathinfo($pfad, PATHINFO_EXTENSION));
        $zeilen = $endung === 'csv' ? self::csvLesen($pfad) : Xlsx::lies($pfad, $blatt);

        $bericht = ['gelesen' => count($zeilen), 'neu' => 0, 'aktualisiert' => 0, 'uebersprungen' => 0, 'warnungen' => []];
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            foreach ($zeilen as $nr => $zeile) {
                $daten = self::zeileUebersetzen($zeile);
                if (($daten['name'] ?? '') === '') {
                    $bericht['uebersprungen']++;
                    $bericht['warnungen'][] = sprintf('Zeile %d ohne Namen — uebersprungen.', $nr + 2);
                    continue;
                }
                $daten['kampagne'] = $kampagne;

                $vorhanden = Db::eine(
                    'SELECT id FROM betriebe WHERE kampagne = ? AND name = ? AND strasse = ?',
                    [$kampagne, $daten['name'], $daten['strasse'] ?? '']
                );
                if ($vorhanden) {
                    // Bestand nicht ueberschreiben: nur Stammdaten auffrischen,
                    // CRM-Felder (Stufe, Kontaktversuche, Notizen) bleiben unangetastet.
                    unset($daten['pipeline_stage'], $daten['status_quelle']);
                    if (!$probelauf) {
                        Repo::aktualisieren((int) $vorhanden['id'], $daten);
                    }
                    $bericht['aktualisiert']++;
                    continue;
                }
                if (!$probelauf) {
                    Repo::anlegen($daten);
                }
                $bericht['neu']++;
            }
            $probelauf ? $pdo->rollBack() : $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $bericht;
    }

    /** @param array<string,string> $zeile */
    private static function zeileUebersetzen(array $zeile): array
    {
        $daten = [];
        foreach ($zeile as $ueberschrift => $wert) {
            $spalte = self::ZUORDNUNG[trim($ueberschrift)] ?? null;
            if ($spalte === null) {
                continue;
            }
            $daten[$spalte] = trim($wert);
        }

        // "zu recherchieren" ist kein Wert, sondern eine offene Aufgabe -> leeres Feld,
        // Rohwert bleibt ueber anreicherung_status nachvollziehbar.
        $offen = [];
        foreach (['inhaber', 'email', 'website', 'telefon'] as $feld) {
            if (isset($daten[$feld]) && Repo::istPlatzhalter($daten[$feld])) {
                $offen[] = $feld;
                $daten[$feld] = '';
            }
        }
        $daten['anreicherung_status'] = $offen ? 'offen' : 'gefunden';

        if (isset($daten['prioritaet'])) {
            $prio = strtoupper(substr(trim($daten['prioritaet']), 0, 1));
            $daten['prioritaet'] = in_array($prio, ['A', 'B', 'C'], true) ? $prio : 'B';
        }
        if (isset($daten['google_bewertung'])) {
            $daten['google_bewertung'] = str_replace(',', '.', $daten['google_bewertung']);
        }
        if (isset($daten['pizzenvolumen_text'])) {
            [$daten['pizzenvolumen_monat'], $daten['volumen_klasse']] = self::volumenAuswerten($daten['pizzenvolumen_text']);
        }
        $daten['pipeline_stage'] = self::stageAusStatus($daten['status_quelle'] ?? '');
        if (($daten['ort'] ?? '') === '') {
            $daten['ort'] = 'Freiburg im Breisgau';
        }
        return $daten;
    }

    private static function stageAusStatus(string $status): string
    {
        $schluessel = mb_strtolower(trim($status));
        foreach (self::STATUS_ZUORDNUNG as $muster => $stage) {
            if ($schluessel === $muster || str_starts_with($schluessel, $muster)) {
                return $stage;
            }
        }
        return 'neu';
    }

    /**
     * Aus dem Freitext der Excel-Spalte einen Zahlenwert und eine Klasse ableiten.
     * "Mittel – ca. 600–1.500 Pizzen/Monat (Schätzwert)" => [1050, 'Mittel']
     * "… ca. <250 …" => [150, …];  "… ca. 3.000+ …" => [3450, …]
     * @return array{0:?int,1:string}
     */
    public static function volumenAuswerten(string $text): array
    {
        $klasse = trim((string) (preg_split('/[–—-]/u', $text, 2)[0] ?? ''));
        // "Mittel-Hoch" wird durch den Bindestrich-Split zerlegt — hier zurueckholen.
        if (preg_match('/^(Sehr hoch|Mittel-Hoch|Niedrig\/unbekannt|Hoch|Mittel|Niedrig)/u', trim($text), $t)) {
            $klasse = $t[1];
        }

        $zahlenTeil = $text;
        // Tausenderpunkte entfernen (3.000 -> 3000), Kommas ignorieren.
        $zahlenTeil = preg_replace('/(\d)\.(\d{3})\b/u', '$1$2', $zahlenTeil) ?? $zahlenTeil;
        preg_match_all('/\d+/u', $zahlenTeil, $treffer);
        $zahlen = array_map('intval', $treffer[0] ?? []);
        if (!$zahlen) {
            return [null, $klasse];
        }
        if (count($zahlen) >= 2) {
            $schaetzung = (int) round(($zahlen[0] + $zahlen[1]) / 2);
        } elseif (str_contains($zahlenTeil, '<')) {
            $schaetzung = (int) round($zahlen[0] * 0.6);   // Obergrenze -> konservativ darunter
        } elseif (str_contains($zahlenTeil, '+')) {
            $schaetzung = (int) round($zahlen[0] * 1.15);  // Untergrenze -> leicht darueber
        } else {
            $schaetzung = $zahlen[0];
        }
        return [$schaetzung, $klasse];
    }

    /** @return array<int,array<string,string>> */
    private static function csvLesen(string $pfad): array
    {
        $zeiger = fopen($pfad, 'rb');
        if ($zeiger === false) {
            throw new RuntimeException("CSV nicht lesbar: {$pfad}");
        }
        $erste = fgets($zeiger);
        $trenner = ($erste !== false && substr_count($erste, ';') > substr_count($erste, ',')) ? ';' : ',';
        rewind($zeiger);

        $kopf = fgetcsv($zeiger, 0, $trenner, '"', '\\');
        if ($kopf === false) {
            fclose($zeiger);
            return [];
        }
        $kopf = array_map(static fn ($s): string => trim((string) $s, " \t\n\r\0\x0B\u{FEFF}"), $kopf);
        $zeilen = [];
        while (($werte = fgetcsv($zeiger, 0, $trenner, '"', '\\')) !== false) {
            if ($werte === [null] || implode('', array_map('strval', $werte)) === '') {
                continue;
            }
            $datensatz = [];
            foreach ($kopf as $i => $ueberschrift) {
                $datensatz[$ueberschrift] = trim((string) ($werte[$i] ?? ''));
            }
            $zeilen[] = $datensatz;
        }
        fclose($zeiger);
        return $zeilen;
    }
}
