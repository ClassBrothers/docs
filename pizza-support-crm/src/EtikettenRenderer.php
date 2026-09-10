<?php
declare(strict_types=1);

/** Versandetiketten auf Standard-Etikettenboegen (Avery & Co.). */
final class EtikettenRenderer
{
    /**
     * @param array<int,array<string,mixed>> $betriebe
     * @param string $format Schluessel aus PS_ETIKETTEN
     * @param int    $startPosition 1-basierte Startposition auf dem ersten Bogen
     *                              (damit angebrochene Boegen weiterverwendet werden koennen)
     */
    public static function erzeugen(array $betriebe, ?string $format = null, int $startPosition = 1, bool $hilfslinien = false): string
    {
        $format = $format && isset(PS_ETIKETTEN[$format]) ? $format : (string) ps_cfg('etikettenformat', 'L7160');
        $bogen = PS_ETIKETTEN[$format] ?? PS_ETIKETTEN['L7160'];
        $proSeite = $bogen['spalten'] * $bogen['zeilen'];

        $pdf = new Pdf('Pizza Support — Versandetiketten');
        $position = max(0, min($proSeite - 1, $startPosition - 1));
        $erste = true;

        foreach ($betriebe as $betrieb) {
            if ($position >= $proSeite) {
                $position = 0;
                $erste = false;
                $pdf->neueSeite();
            } elseif ($erste && $position === 0) {
                $erste = false;
            }
            self::etikett($pdf, $bogen, $position, $betrieb, $hilfslinien);
            $position++;
        }
        return $pdf->ausgeben();
    }

    private static function etikett(Pdf $pdf, array $bogen, int $position, array $betrieb, bool $hilfslinien): void
    {
        $spalte = $position % $bogen['spalten'];
        $zeile = intdiv($position, $bogen['spalten']);
        $x = $bogen['rand_links'] + $spalte * ($bogen['breite'] + $bogen['abstand_x']);
        $y = $bogen['rand_oben'] + $zeile * ($bogen['hoehe'] + $bogen['abstand_y']);

        if ($hilfslinien) {
            $pdf->rechteck($x, $y, $bogen['breite'], $bogen['hoehe'], [215, 210, 205], false);
        }

        $innen = 4.0;
        $textBreite = $bogen['breite'] - 2 * $innen;
        $zeilen = Repo::adresse($betrieb);

        // Schriftgroesse an die Zeilenzahl anpassen, damit nichts aus dem Etikett laeuft.
        $groesse = count($zeilen) > 4 ? 8.5 : 9.5;
        $schritt = $groesse * 0.42;
        $hoeheGesamt = count($zeilen) * $schritt;
        $textY = $y + max($innen + $groesse * 0.35, ($bogen['hoehe'] - $hoeheGesamt) / 2 + $groesse * 0.35);

        foreach ($zeilen as $i => $zeilenText) {
            $schnitt = $i === 0 ? 'fett' : 'normal';
            $pdf->text($x + $innen, $textY, $pdf->kuerzen($zeilenText, $textBreite, $groesse, $schnitt), $groesse, $schnitt);
            $textY += $schritt;
        }
    }
}
