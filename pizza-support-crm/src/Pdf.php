<?php
declare(strict_types=1);

/**
 * Sehr schlanker PDF-Schreiber ohne externe Bibliothek.
 * Deckt genau das ab, was das CRM braucht: A4-Seiten, Helvetica in drei Schnitten,
 * umbrechender Text, Linien und Rechtecke. Masse werden durchgaengig in Millimetern
 * angegeben, Nullpunkt ist die linke OBERE Ecke (PDF selbst rechnet von unten links).
 */
final class Pdf
{
    public const A4_BREITE = 210.0;
    public const A4_HOEHE  = 297.0;

    private const SCHRIFTEN = ['normal' => 'F1', 'fett' => 'F2', 'kursiv' => 'F3'];

    /**
     * Zeichenbreiten der Standard-14-Schriften, Einheit 1/1000 em, jeweils vier Ziffern
     * pro Zeichen fuer die Bytes 32..126 (Leerzeichen bis Tilde).
     */
    private const BREITEN_NORMAL =
        '027802780355055605560889066701910333033303890584027803330278027805560556055605560556055605560556055605560278'
        . '02780584058405840556101506670667072207220667061107780722027805000667055608330722077806670778072206670611072206670944'
        . '066706670611027802780278046905560333055605560500055605560278055605560222022205000222083305560556055605560333050002780556'
        . '050007220500050005000334026003340584';
    private const BREITEN_FETT =
        '027803330474055605560889072202380333033303890584027803330278027805560556055605560556055605560556055605560333'
        . '03330584058405840611097507220722072207220667061107780722027805560722061108330722077806670778072206670611072206670944'
        . '066706670611033302780333058405560333055606110556061105560333061106110278027805560278088906110611061106110389055603330611'
        . '055607780556055605000389028003890584';

    /** Breiten fuer haeufige WinAnsi-Zeichen oberhalb von 127 (Umlaute, Anfuehrungen, Striche). */
    private const BREITEN_HOCH_NORMAL = [
        0x80 => 556, 0x91 => 222, 0x92 => 222, 0x93 => 333, 0x94 => 333, 0x96 => 556, 0x97 => 1000,
        0xA0 => 278, 0xA7 => 556, 0xB0 => 400, 0xC4 => 667, 0xC9 => 667, 0xD6 => 778, 0xDC => 722,
        0xDF => 556, 0xE0 => 556, 0xE4 => 556, 0xE8 => 556, 0xE9 => 556, 0xF6 => 556, 0xFC => 556,
    ];
    private const BREITEN_HOCH_FETT = [
        0x80 => 556, 0x91 => 278, 0x92 => 278, 0x93 => 500, 0x94 => 500, 0x96 => 556, 0x97 => 1000,
        0xA0 => 278, 0xA7 => 556, 0xB0 => 400, 0xC4 => 722, 0xC9 => 667, 0xD6 => 778, 0xDC => 722,
        0xDF => 611, 0xE0 => 556, 0xE4 => 556, 0xE8 => 556, 0xE9 => 556, 0xF6 => 611, 0xFC => 611,
    ];

    /** @var array<int,string> Inhaltsstroeme je Seite */
    private array $seiten = [];
    private string $aktuell = '';
    private string $titel;

    public function __construct(string $titel = 'Pizza Support')
    {
        $this->titel = $titel;
    }

    public function neueSeite(): void
    {
        if ($this->aktuell !== '') {
            $this->seiten[] = $this->aktuell;
        }
        $this->aktuell = '';
    }

    /** Text an fester Position. $y ist die Grundlinie, gemessen von oben. */
    public function text(float $x, float $y, string $text, float $groesse = 10.0, string $schnitt = 'normal', ?array $farbe = null): void
    {
        if (trim($text) === '') {
            return;
        }
        $font = self::SCHRIFTEN[$schnitt] ?? 'F1';
        $farbBefehl = $farbe ? sprintf("%.3F %.3F %.3F rg\n", $farbe[0] / 255, $farbe[1] / 255, $farbe[2] / 255) : '';
        $zurueck = $farbe ? "0 0 0 rg\n" : '';
        $this->aktuell .= sprintf(
            "BT\n%s/%s %.2F Tf\n%.2F %.2F Td\n(%s) Tj\nET\n%s",
            $farbBefehl,
            $font,
            $groesse,
            self::mm($x),
            self::mm(self::A4_HOEHE - $y),
            self::maskieren($text),
            $zurueck
        );
    }

    /**
     * Umbrechender Textblock. Gibt die y-Position UNTER dem Block zurueck.
     * Leerzeilen im Text erzeugen Absatzabstand, Zeilen mit "- " werden eingerueckt.
     */
    public function textBlock(float $x, float $y, float $breite, string $text, float $groesse = 10.0, string $schnitt = 'normal', float $zeilenAbstand = 1.45): float
    {
        $schritt = $groesse * $zeilenAbstand * 25.4 / 72;
        foreach (preg_split('/\R/u', $text) ?: [] as $absatz) {
            if (trim($absatz) === '') {
                $y += $schritt * 0.7;
                continue;
            }
            $einzug = 0.0;
            if (preg_match('/^(\s*[-•]\s+)/u', $absatz, $treffer)) {
                $einzug = $this->breite($treffer[1], $groesse, $schnitt);
            } elseif (preg_match('/^\s+/u', $absatz)) {
                $einzug = 6.0;
                $absatz = ltrim($absatz);
                $x += $einzug;
            }
            $erste = true;
            foreach ($this->umbrechen($absatz, $breite - ($erste ? 0 : $einzug), $groesse, $schnitt) as $zeile) {
                $this->text($x + ($erste ? 0 : $einzug), $y, $zeile, $groesse, $schnitt);
                $y += $schritt;
                $erste = false;
            }
            if ($einzug > 0 && !preg_match('/^\s*[-•]/u', $absatz)) {
                $x -= $einzug;
            }
        }
        return $y;
    }

    public function linie(float $x1, float $y1, float $x2, float $y2, float $staerke = 0.2, array $farbe = [180, 180, 180]): void
    {
        $this->aktuell .= sprintf(
            "q\n%.3F %.3F %.3F RG\n%.2F w\n%.2F %.2F m %.2F %.2F l S\nQ\n",
            $farbe[0] / 255, $farbe[1] / 255, $farbe[2] / 255, $staerke,
            self::mm($x1), self::mm(self::A4_HOEHE - $y1), self::mm($x2), self::mm(self::A4_HOEHE - $y2)
        );
    }

    public function rechteck(float $x, float $y, float $breite, float $hoehe, array $farbe, bool $gefuellt = true): void
    {
        $operator = $gefuellt ? 'f' : 'S';
        $farbOperator = $gefuellt ? 'rg' : 'RG';
        $this->aktuell .= sprintf(
            "q\n%.3F %.3F %.3F %s\n%.2F %.2F %.2F %.2F re %s\nQ\n",
            $farbe[0] / 255, $farbe[1] / 255, $farbe[2] / 255, $farbOperator,
            self::mm($x), self::mm(self::A4_HOEHE - $y - $hoehe), self::mm($breite), self::mm($hoehe), $operator
        );
    }

    /** Textbreite in Millimetern. */
    public function breite(string $text, float $groesse, string $schnitt = 'normal'): float
    {
        $kodiert = self::kodieren($text);
        $fett = $schnitt === 'fett';
        $tabelle = $fett ? self::BREITEN_FETT : self::BREITEN_NORMAL;
        $hoch = $fett ? self::BREITEN_HOCH_FETT : self::BREITEN_HOCH_NORMAL;
        $tabelle = str_replace(' ', '', $tabelle);

        $summe = 0;
        for ($i = 0, $laenge = strlen($kodiert); $i < $laenge; $i++) {
            $byte = ord($kodiert[$i]);
            if ($byte >= 32 && $byte <= 126) {
                $summe += (int) substr($tabelle, ($byte - 32) * 4, 4);
            } else {
                $summe += $hoch[$byte] ?? 556;
            }
        }
        return $summe / 1000 * $groesse * 25.4 / 72;
    }

    /** @return array<int,string> */
    public function umbrechen(string $text, float $breite, float $groesse, string $schnitt = 'normal'): array
    {
        $woerter = preg_split('/\s+/u', trim($text)) ?: [];
        $zeilen = [];
        $zeile = '';
        foreach ($woerter as $wort) {
            $versuch = $zeile === '' ? $wort : $zeile . ' ' . $wort;
            if ($this->breite($versuch, $groesse, $schnitt) <= $breite || $zeile === '') {
                $zeile = $versuch;
                continue;
            }
            $zeilen[] = $zeile;
            $zeile = $wort;
        }
        if ($zeile !== '') {
            $zeilen[] = $zeile;
        }
        return $zeilen ?: [''];
    }

    /** Text auf eine Breite kuerzen und mit Auslassung versehen. */
    public function kuerzen(string $text, float $breite, float $groesse, string $schnitt = 'normal'): string
    {
        if ($this->breite($text, $groesse, $schnitt) <= $breite) {
            return $text;
        }
        $laenge = mb_strlen($text);
        while ($laenge > 1) {
            $laenge--;
            $versuch = rtrim(mb_substr($text, 0, $laenge)) . '…';
            if ($this->breite($versuch, $groesse, $schnitt) <= $breite) {
                return $versuch;
            }
        }
        return '';
    }

    public function seitenzahl(): int
    {
        return count($this->seiten) + ($this->aktuell !== '' ? 1 : 0);
    }

    /** Fertiges PDF als String. */
    public function ausgeben(): string
    {
        if ($this->aktuell !== '') {
            $this->seiten[] = $this->aktuell;
            $this->aktuell = '';
        }
        if (!$this->seiten) {
            $this->seiten[] = '';
        }

        $objekte = [];
        $seitenAnzahl = count($this->seiten);
        // 1 = Catalog, 2 = Pages, 3..5 = Fonts, danach je Seite Page + Contents
        $ersteSeite = 6;

        $seitenIds = [];
        for ($i = 0; $i < $seitenAnzahl; $i++) {
            $seitenIds[] = $ersteSeite + $i * 2;
        }

        $objekte[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objekte[2] = sprintf(
            "<< /Type /Pages /Kids [%s] /Count %d >>",
            implode(' ', array_map(static fn (int $id): string => "{$id} 0 R", $seitenIds)),
            $seitenAnzahl
        );
        $objekte[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objekte[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
        $objekte[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >>";

        foreach ($this->seiten as $i => $inhalt) {
            $seiteId = $ersteSeite + $i * 2;
            $inhaltId = $seiteId + 1;
            $objekte[$seiteId] = sprintf(
                "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents %d 0 R >>",
                self::mm(self::A4_BREITE),
                self::mm(self::A4_HOEHE),
                $inhaltId
            );
            $gepackt = gzcompress($inhalt, 9);
            $objekte[$inhaltId] = sprintf("<< /Length %d /Filter /FlateDecode >>\nstream\n%s\nendstream", strlen($gepackt), $gepackt);
        }

        $infoId = max(array_keys($objekte)) + 1;
        $objekte[$infoId] = sprintf(
            "<< /Title (%s) /Producer (Pizza Support CRM) /CreationDate (D:%s) >>",
            self::maskieren($this->titel),
            date('YmdHis')
        );

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $versatz = [];
        ksort($objekte);
        foreach ($objekte as $id => $koerper) {
            $versatz[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$koerper}\nendobj\n";
        }
        $xrefStart = strlen($pdf);
        $anzahl = $infoId + 1;
        $pdf .= "xref\n0 {$anzahl}\n0000000000 65535 f \n";
        for ($id = 1; $id <= $infoId; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $versatz[$id] ?? 0);
        }
        $pdf .= sprintf("trailer\n<< /Size %d /Root 1 0 R /Info %d 0 R >>\nstartxref\n%d\n%%%%EOF\n", $anzahl, $infoId, $xrefStart);
        return $pdf;
    }

    /** Millimeter in PDF-Punkte. */
    private static function mm(float $mm): float
    {
        return $mm * 72 / 25.4;
    }

    /** UTF-8 nach WinAnsi (CP1252), damit die Standardschriften die Umlaute treffen. */
    private static function kodieren(string $text): string
    {
        $ersetzt = strtr($text, ['€' => "\x80", '„' => '"', '“' => '"', '”' => '"', '‚' => "'", '‘' => "'", '’' => "'", '–' => "\x96", '—' => "\x97", '…' => '...', "\u{00A0}" => ' ']);
        $kodiert = @iconv('UTF-8', 'CP1252//TRANSLIT', $ersetzt);
        return $kodiert === false ? preg_replace('/[^\x20-\x7E]/', '?', $ersetzt) ?? '' : $kodiert;
    }

    private static function maskieren(string $text): string
    {
        return strtr(self::kodieren($text), ['\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => '', "\n" => ' ']);
    }
}
