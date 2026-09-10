<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

Auth::verlangen();

$art = (string) ($_GET['art'] ?? 'briefe');
$filter = ['kampagne' => 'pizzasupport'];
foreach (['stadtteil', 'pizza_fokus', 'suche', 'tag'] as $feld) {
    if (($_GET[$feld] ?? '') !== '') {
        $filter[$feld] = (string) $_GET[$feld];
    }
}
foreach (['prioritaet', 'stage'] as $feld) {
    if (($_GET[$feld] ?? '') !== '') {
        $filter[$feld] = explode(',', (string) $_GET[$feld]);
    }
}
foreach (['followup_faellig', 'ohne_kontakt'] as $feld) {
    if (!empty($_GET[$feld]) && $_GET[$feld] !== '0') {
        $filter[$feld] = true;
    }
}
if (!empty($_GET['ids'])) {
    $filter['ids'] = array_map('intval', explode(',', (string) $_GET['ids']));
}
$filter['sort'] = 'name';

$betriebe = Repo::liste($filter);
if (!$betriebe) {
    http_response_code(404);
    exit('Keine Betriebe im aktuellen Filter.');
}

$datum = date('Y-m-d');

try {
    if ($art === 'etiketten') {
        $pdf = EtikettenRenderer::erzeugen(
            $betriebe,
            (string) ($_GET['format'] ?? ''),
            max(1, (int) ($_GET['start'] ?? 1)),
            !empty($_GET['hilfslinien'])
        );
        ausliefern($pdf, "pizza-support_etiketten_{$datum}.pdf");
    }

    if ($art === 'csv') {
        csvAusliefern($betriebe, "pizza-support_akquise_{$datum}.csv");
    }

    // Standard: Briefe
    $vorlage = Vorlagen::finde((int) ($_GET['vorlage'] ?? 0)) ?? Vorlagen::standard('brief');
    if (!$vorlage) {
        http_response_code(500);
        exit('Keine Briefvorlage vorhanden.');
    }
    $pdf = BriefRenderer::erzeugen($betriebe, $vorlage);

    // Brief-Export wird als Kontaktversuch protokolliert, sofern gewuenscht.
    if (!empty($_GET['protokollieren'])) {
        foreach ($betriebe as $betrieb) {
            Repo::aktivitaetAnlegen((int) $betrieb['id'], [
                'typ'   => 'brief',
                'notiz' => 'Anschreiben erzeugt: ' . $vorlage['name'],
            ]);
            if ((string) $betrieb['pipeline_stage'] === 'neu') {
                Repo::stageSetzen((int) $betrieb['id'], 'kontaktversuch_1', 'Automatisch beim Brief-Export.');
            }
        }
    }
    ausliefern($pdf, "pizza-support_anschreiben_{$datum}.pdf");
} catch (Throwable $e) {
    http_response_code(500);
    exit('Fehler beim Export: ' . $e->getMessage());
}

function ausliefern(string $inhalt, string $dateiname): never
{
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $dateiname . '"');
    header('Content-Length: ' . strlen($inhalt));
    header('X-Robots-Tag: noindex, nofollow');
    echo $inhalt;
    exit;
}

/** @param array<int,array<string,mixed>> $betriebe */
function csvAusliefern(array $betriebe, string $dateiname): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $dateiname . '"');
    $spalten = ['name', 'inhaber', 'strasse', 'plz', 'stadtteil', 'ort', 'telefon', 'email', 'website',
        'art_gastronomie', 'pizza_fokus', 'google_bewertung', 'anzahl_bewertungen', 'pizzenvolumen_text',
        'pizzenvolumen_monat', 'prioritaet', 'stage_label', 'kontaktversuche', 'letzter_kontakt',
        'naechster_follow_up', 'vertragsdatum', 'anzahl_flaechen_gebucht', 'tags', 'notizen', 'anreicherung_status'];
    $ausgabe = fopen('php://output', 'wb');
    fwrite($ausgabe, "\xEF\xBB\xBF"); // BOM, damit Excel die Umlaute richtig liest
    fputcsv($ausgabe, $spalten, ';', '"', '\\');
    foreach ($betriebe as $betrieb) {
        fputcsv($ausgabe, array_map(static fn (string $s): string => (string) ($betrieb[$s] ?? ''), $spalten), ';', '"', '\\');
    }
    fclose($ausgabe);
    exit;
}
