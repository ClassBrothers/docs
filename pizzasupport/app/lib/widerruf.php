<?php
/**
 * Widerrufsbelehrung fuer Verbraucher (§ 13 BGB), einmal gepflegt.
 *
 * Betrifft ausschliesslich private Fun-Area-Buchungen - Deckel- und
 * Seitenflaechen sind Geschaefte unter Unternehmern ohne gesetzliches
 * Widerrufsrecht. Der Text steht hier ein einziges Mal und wird sowohl in
 * den AGB (HTML) als auch in der Buchungsbestaetigung per E-Mail (Klartext)
 * ausgegeben, damit beide Fassungen nie auseinanderlaufen.
 */

declare(strict_types=1);

const WIDERRUF_ADRESSPLATZHALTER = '{{adresse}}';

/** @return array<int, array{0: string, 1: string[]}> */
function widerrufsbelehrung_abschnitte(): array
{
    return [
        ['Widerrufsrecht', [
            'Sie haben das Recht, binnen vierzehn Tagen ohne Angabe von Gründen diesen Vertrag zu '
            . 'widerrufen. Die Widerrufsfrist beträgt vierzehn Tage ab dem Tag des Vertragsabschlusses.',
            'Um Ihr Widerrufsrecht auszuüben, müssen Sie uns',
            WIDERRUF_ADRESSPLATZHALTER,
            'mittels einer eindeutigen Erklärung (zum Beispiel ein mit der Post versandter Brief oder eine '
            . 'E-Mail) über Ihren Entschluss, diesen Vertrag zu widerrufen, informieren. Sie können dafür '
            . 'das Muster-Widerrufsformular verwenden, das aber nicht vorgeschrieben ist.',
            'Zur Wahrung der Widerrufsfrist reicht es aus, dass Sie die Mitteilung über die Ausübung des '
            . 'Widerrufsrechts vor Ablauf der Widerrufsfrist absenden.',
        ]],
        ['Folgen des Widerrufs', [
            'Wenn Sie diesen Vertrag widerrufen, haben wir Ihnen alle Zahlungen, die wir von Ihnen '
            . 'erhalten haben, einschließlich der Lieferkosten (mit Ausnahme der zusätzlichen Kosten, '
            . 'die sich daraus ergeben, dass Sie eine andere Art der Lieferung als die von uns '
            . 'angebotene, günstigste Standardlieferung gewählt haben), unverzüglich und spätestens '
            . 'binnen vierzehn Tagen ab dem Tag zurückzuzahlen, an dem die Mitteilung über Ihren '
            . 'Widerruf dieses Vertrags bei uns eingegangen ist. Für diese Rückzahlung verwenden wir '
            . 'dasselbe Zahlungsmittel, das Sie bei der ursprünglichen Transaktion eingesetzt haben, '
            . 'es sei denn, mit Ihnen wurde ausdrücklich etwas anderes vereinbart; in keinem Fall '
            . 'werden Ihnen wegen dieser Rückzahlung Entgelte berechnet.',
            'Haben Sie verlangt, dass die Leistung während der Widerrufsfrist beginnen soll, so haben '
            . 'Sie uns einen angemessenen Betrag zu zahlen, der dem Anteil der bis zu dem Zeitpunkt, zu '
            . 'dem Sie uns von der Ausübung des Widerrufsrechts hinsichtlich dieses Vertrags '
            . 'unterrichten, bereits erbrachten Leistungen im Vergleich zum Gesamtumfang der im Vertrag '
            . 'vorgesehenen Leistungen entspricht.',
        ]],
        ['Vorzeitiges Erlöschen des Widerrufsrechts', [
            'Das Widerrufsrecht erlischt vorzeitig, wenn wir die Leistung vollständig erbracht haben und '
            . 'mit der Ausführung erst begonnen haben, nachdem Sie dazu Ihre ausdrückliche Zustimmung '
            . 'gegeben und gleichzeitig bestätigt haben, dass Sie Ihr Widerrufsrecht bei vollständiger '
            . 'Vertragserfüllung verlieren.',
            'In der Regel tritt dieser Fall bei uns nicht ein: Wir geben die Produktion erst frei, wenn '
            . 'genug Betriebe und genug gebuchte Flächen zusammengekommen sind. Zwischen Ihrer Buchung '
            . 'und dem Druck liegen daher üblicherweise mehrere Wochen, und Ihre Widerrufsfrist ist '
            . 'längst abgelaufen, bevor Ihr Motiv in Produktion geht.',
        ]],
    ];
}

/** HTML-Fassung fuer § 10 der AGB. */
function widerrufsbelehrung_html(): string
{
    $mailLink    = firma_email_link();
    $mailAnzeige = config('firma.email');
    $out = '';
    foreach (widerrufsbelehrung_abschnitte() as [$titel, $absaetze]) {
        $out .= '<p><strong>' . e($titel) . "</strong></p>\n";
        foreach ($absaetze as $absatz) {
            if ($absatz === WIDERRUF_ADRESSPLATZHALTER) {
                $out .= '<p class="adressblock">'
                      . e(config('firma.name')) . '<br>'
                      . e(config('firma.strasse')) . '<br>'
                      . e(config('firma.plz_ort')) . '<br>'
                      . 'Telefon: ' . e(config('firma.telefon')) . '<br>'
                      . 'E-Mail: <a href="mailto:' . e($mailLink) . '">' . e($mailAnzeige) . "</a></p>\n";
                continue;
            }
            $out .= '<p>' . e($absatz) . "</p>\n";
        }
    }
    return $out;
}

/** Klartext-Fassung fuer die Buchungsbestätigung privater Fun-Area-Buchungen. */
function widerrufsbelehrung_text(): string
{
    $adresse = implode("\n", [
        config('firma.name'),
        config('firma.strasse'),
        config('firma.plz_ort'),
        'Telefon: ' . config('firma.telefon'),
        'E-Mail: ' . config('firma.email'),
    ]);

    $out = '';
    foreach (widerrufsbelehrung_abschnitte() as [$titel, $absaetze]) {
        $out .= mb_strtoupper($titel) . "\n\n";
        foreach ($absaetze as $absatz) {
            $out .= ($absatz === WIDERRUF_ADRESSPLATZHALTER ? $adresse : $absatz) . "\n\n";
        }
    }
    return rtrim($out);
}
