<?php
declare(strict_types=1);

/**
 * Minimaler XLSX-Leser (ZipArchive + SimpleXML), ohne externe Bibliothek.
 * Unterstuetzt sharedStrings und inline strings; Formeln werden mit ihrem
 * zwischengespeicherten Wert gelesen.
 */
final class Xlsx
{
    /**
     * @return array<int,array<string,string>> Zeilen als assoziatives Array,
     *         Schluessel = Spaltenueberschrift aus der ersten Zeile.
     */
    public static function lies(string $pfad, ?string $blattName = null): array
    {
        if (!is_file($pfad)) {
            throw new RuntimeException("Datei nicht gefunden: {$pfad}");
        }
        $zip = new ZipArchive();
        if ($zip->open($pfad) !== true) {
            throw new RuntimeException("Kann Datei nicht oeffnen: {$pfad}");
        }
        try {
            $sharedStrings = self::sharedStrings($zip);
            $blattPfad = self::blattPfad($zip, $blattName);
            $xml = self::xml($zip, $blattPfad);

            $tabelle = [];
            foreach ($xml->sheetData->row ?? [] as $zeile) {
                $zellen = [];
                foreach ($zeile->c ?? [] as $zelle) {
                    $ref = (string) $zelle['r'];
                    $spalte = rtrim($ref, '0123456789');
                    $zellen[$spalte] = self::zellenWert($zelle, $sharedStrings);
                }
                $tabelle[] = $zellen;
            }
            $zip->close();
            return self::mitUeberschriften($tabelle);
        } catch (Throwable $e) {
            $zip->close();
            throw $e;
        }
    }

    /** @return array<int,string> */
    public static function blaetter(string $pfad): array
    {
        $zip = new ZipArchive();
        if ($zip->open($pfad) !== true) {
            throw new RuntimeException("Kann Datei nicht oeffnen: {$pfad}");
        }
        $wb = self::xml($zip, 'xl/workbook.xml');
        $namen = [];
        foreach ($wb->sheets->sheet ?? [] as $blatt) {
            $namen[] = (string) $blatt['name'];
        }
        $zip->close();
        return $namen;
    }

    private static function xml(ZipArchive $zip, string $pfad): SimpleXMLElement
    {
        $inhalt = $zip->getFromName($pfad);
        if ($inhalt === false) {
            throw new RuntimeException("Bestandteil fehlt in der XLSX-Datei: {$pfad}");
        }
        $xml = simplexml_load_string($inhalt);
        if ($xml === false) {
            throw new RuntimeException("XML nicht lesbar: {$pfad}");
        }
        return $xml;
    }

    /** @return array<int,string> */
    private static function sharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }
        $xml = self::xml($zip, 'xl/sharedStrings.xml');
        $strings = [];
        foreach ($xml->si ?? [] as $si) {
            $strings[] = self::textAusSi($si);
        }
        return $strings;
    }

    private static function textAusSi(SimpleXMLElement $si): string
    {
        if (isset($si->t)) {
            return (string) $si->t;
        }
        $text = '';
        foreach ($si->r ?? [] as $lauf) {
            $text .= (string) $lauf->t;
        }
        return $text;
    }

    private static function blattPfad(ZipArchive $zip, ?string $name): string
    {
        $wb = self::xml($zip, 'xl/workbook.xml');
        $rels = self::xml($zip, 'xl/_rels/workbook.xml.rels');

        $zielProId = [];
        foreach ($rels->Relationship ?? [] as $rel) {
            $ziel = (string) $rel['Target'];
            $zielProId[(string) $rel['Id']] = str_starts_with($ziel, '/') ? ltrim($ziel, '/') : 'xl/' . ltrim($ziel, './');
        }

        $erstes = null;
        foreach ($wb->sheets->sheet ?? [] as $blatt) {
            $rid = (string) $blatt->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $pfad = $zielProId[$rid] ?? null;
            if ($pfad === null) {
                continue;
            }
            $erstes ??= $pfad;
            if ($name !== null && mb_strtolower((string) $blatt['name']) === mb_strtolower($name)) {
                return $pfad;
            }
        }
        if ($name !== null && $erstes !== null) {
            throw new RuntimeException("Blatt \"{$name}\" nicht gefunden.");
        }
        if ($erstes === null) {
            throw new RuntimeException('Keine Arbeitsblaetter gefunden.');
        }
        return $erstes;
    }

    /** @param array<int,string> $sharedStrings */
    private static function zellenWert(SimpleXMLElement $zelle, array $sharedStrings): string
    {
        $typ = (string) $zelle['t'];
        if ($typ === 'inlineStr') {
            return isset($zelle->is) ? self::textAusSi($zelle->is) : '';
        }
        $roh = isset($zelle->v) ? (string) $zelle->v : '';
        if ($typ === 's') {
            return $sharedStrings[(int) $roh] ?? '';
        }
        if ($typ === 'b') {
            return $roh === '1' ? 'wahr' : 'falsch';
        }
        return $roh;
    }

    /**
     * Erste Zeile als Ueberschriften verwenden.
     * @param array<int,array<string,string>> $tabelle
     * @return array<int,array<string,string>>
     */
    private static function mitUeberschriften(array $tabelle): array
    {
        if (!$tabelle) {
            return [];
        }
        $kopf = array_shift($tabelle);
        $ergebnis = [];
        foreach ($tabelle as $zeile) {
            $datensatz = [];
            foreach ($kopf as $spalte => $ueberschrift) {
                $ueberschrift = trim($ueberschrift);
                if ($ueberschrift === '') {
                    continue;
                }
                $datensatz[$ueberschrift] = trim($zeile[$spalte] ?? '');
            }
            // vollstaendig leere Zeilen ueberspringen
            if (implode('', $datensatz) !== '') {
                $ergebnis[] = $datensatz;
            }
        }
        return $ergebnis;
    }
}
