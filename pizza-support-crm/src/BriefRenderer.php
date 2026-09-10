<?php
declare(strict_types=1);

/**
 * Erzeugt Anschreiben als PDF im Format DIN 5008 Form A
 * (Anschriftfeld ab 45 mm, Betreff bei 98,4 mm) — passt in Fensterumschlaege DIN lang.
 */
final class BriefRenderer
{
    private const ROT   = [166, 42, 36];   // San Marzano
    private const GRUEN = [74, 124, 66];   // Basilikum
    private const GOLD  = [176, 141, 62];  // Olivgold
    private const GRAU  = [110, 105, 98];

    private const RAND_LINKS  = 25.0;
    private const RAND_RECHTS = 20.0;
    private const TEXT_BREITE = Pdf::A4_BREITE - self::RAND_LINKS - self::RAND_RECHTS;
    private const FUSS_OBEN   = 272.0;

    /**
     * @param array<int,array<string,mixed>> $betriebe
     * @param array{betreff?:string,koerper:string} $vorlage
     */
    public static function erzeugen(array $betriebe, array $vorlage): string
    {
        $pdf = new Pdf('Pizza Support — Anschreiben');
        $erste = true;
        foreach ($betriebe as $betrieb) {
            if (!$erste) {
                $pdf->neueSeite();
            }
            self::seite($pdf, $betrieb, $vorlage);
            $erste = false;
        }
        return $pdf->ausgeben();
    }

    private static function seite(Pdf $pdf, array $betrieb, array $vorlage): void
    {
        $absender = (array) ps_cfg('absender', []);
        self::briefkopf($pdf, $absender);
        self::anschriftfeld($pdf, $betrieb, $absender);
        self::infoBlock($pdf, $absender);

        $betreff = Vorlagen::fuellen((string) ($vorlage['betreff'] ?? ''), $betrieb);
        $koerper = Vorlagen::fuellen((string) $vorlage['koerper'], $betrieb);

        // Ist der Betreff nicht separat gepflegt, steht er als erste Zeile im Koerper.
        if ($betreff === '' && preg_match('/^\s*Betreff:\s*(.+)$/mu', $koerper, $treffer)) {
            $betreff = trim($treffer[1]);
            $koerper = trim((string) preg_replace('/^\s*Betreff:.*$/mu', '', $koerper, 1));
        }

        $y = 98.4;
        if ($betreff !== '') {
            foreach ($pdf->umbrechen($betreff, self::TEXT_BREITE, 11, 'fett') as $zeile) {
                $pdf->text(self::RAND_LINKS, $y, $zeile, 11, 'fett');
                $y += 5.5;
            }
            $y += 6.0;
        }

        $pdf->textBlock(self::RAND_LINKS, $y, self::TEXT_BREITE, $koerper, 10.5, 'normal', 1.5);
        self::fusszeile($pdf, $absender);
    }

    private static function briefkopf(Pdf $pdf, array $absender): void
    {
        $pdf->rechteck(0, 0, Pdf::A4_BREITE, 3.0, self::ROT);
        $pdf->text(self::RAND_LINKS, 18.0, 'PIZZA SUPPORT', 20, 'fett', self::ROT);
        $pdf->text(self::RAND_LINKS, 24.0, 'Werbefinanzierte Pizzakartons für Freiburg', 9.5, 'normal', self::GRUEN);

        $rechts = Pdf::A4_BREITE - self::RAND_RECHTS;
        $zeilen = array_values(array_filter([
            (string) ($absender['firma'] ?? ''),
            trim((string) ($absender['strasse'] ?? '')),
            trim((string) ($absender['plz_ort'] ?? '')),
            trim((string) ($absender['web'] ?? '')),
        ]));
        $y = 14.0;
        foreach ($zeilen as $zeile) {
            $pdf->text($rechts - $pdf->breite($zeile, 8.5), $y, $zeile, 8.5, 'normal', self::GRAU);
            $y += 4.0;
        }
        $pdf->linie(self::RAND_LINKS, 30.0, $rechts, 30.0, 0.5, self::GOLD);
    }

    private static function anschriftfeld(Pdf $pdf, array $betrieb, array $absender): void
    {
        // Ruecksendeangabe in der Zone 45–50 mm, klein und einzeilig.
        $ruecksender = trim(implode(' · ', array_filter([
            (string) ($absender['firma'] ?? ''),
            trim((string) ($absender['strasse'] ?? '')),
            trim((string) ($absender['plz_ort'] ?? '')),
        ])));
        if ($ruecksender !== '') {
            $pdf->text(self::RAND_LINKS, 45.5, $pdf->kuerzen($ruecksender, 85.0, 7), 7, 'normal', self::GRAU);
            $pdf->linie(self::RAND_LINKS, 46.6, self::RAND_LINKS + 85.0, 46.6, 0.2, [190, 185, 178]);
        }

        $y = 53.0;
        foreach (Repo::adresse($betrieb) as $zeile) {
            $pdf->text(self::RAND_LINKS, $y, $pdf->kuerzen($zeile, 85.0, 11), 11);
            $y += 5.0;
        }
    }

    private static function infoBlock(Pdf $pdf, array $absender): void
    {
        $rechts = Pdf::A4_BREITE - self::RAND_RECHTS;
        $eintraege = array_filter([
            'Telefon' => (string) ($absender['telefon'] ?? ''),
            'E-Mail'  => (string) ($absender['email'] ?? ''),
            'Datum'   => Vorlagen::datumLang(),
        ]);
        $y = 53.0;
        foreach ($eintraege as $bezeichnung => $wert) {
            $pdf->text($rechts - $pdf->breite($wert, 9), $y, $wert, 9);
            $pdf->text($rechts - $pdf->breite($wert, 9) - 3 - $pdf->breite($bezeichnung, 8), $y, $bezeichnung, 8, 'normal', self::GRAU);
            $y += 4.6;
        }
    }

    private static function fusszeile(Pdf $pdf, array $absender): void
    {
        $pdf->linie(self::RAND_LINKS, self::FUSS_OBEN, Pdf::A4_BREITE - self::RAND_RECHTS, self::FUSS_OBEN, 0.4, self::GOLD);
        $teile = array_filter([
            (string) ($absender['firma'] ?? ''),
            trim((string) ($absender['strasse'] ?? '')),
            trim((string) ($absender['plz_ort'] ?? '')),
            ($absender['telefon'] ?? '') ? 'Tel. ' . $absender['telefon'] : '',
            (string) ($absender['email'] ?? ''),
            (string) ($absender['web'] ?? ''),
        ]);
        $zeile = implode('  ·  ', $teile);
        foreach ($pdf->umbrechen($zeile, self::TEXT_BREITE, 7.5) as $i => $teil) {
            $pdf->text(self::RAND_LINKS, self::FUSS_OBEN + 4.5 + $i * 3.4, $teil, 7.5, 'normal', self::GRAU);
        }
    }
}
