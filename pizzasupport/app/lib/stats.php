<?php
/**
 * Zahlen fuer die Fortschrittsanzeige und die Teilnehmerkarte.
 * Es fliessen ausschliesslich freigegebene Eintraege ein.
 */

declare(strict_types=1);

function fortschritt(): array
{
    $cfg = config('startschuss');

    $betriebe = (int) db_value(
        "SELECT COUNT(*) FROM gastro_bestellungen WHERE status = 'freigegeben'", [], 0
    );
    $unternehmen = (int) db_value(
        "SELECT COUNT(DISTINCT firma) FROM werbebuchungen WHERE status = 'freigegeben'", [], 0
    );
    $budget = (int) db_value(
        "SELECT COALESCE(SUM(summe_cent), 0) FROM werbebuchungen WHERE status = 'freigegeben'", [], 0
    );
    $kartons = (int) db_value(
        "SELECT COALESCE(SUM(menge), 0) FROM gastro_bestellungen WHERE status = 'freigegeben'", [], 0
    );

    $q_betriebe = $cfg['betriebe'] > 0 ? min(1.0, $betriebe / $cfg['betriebe']) : 0.0;
    $q_budget   = $cfg['budget_cent'] > 0 ? min(1.0, $budget / $cfg['budget_cent']) : 0.0;

    return [
        'betriebe'         => $betriebe,
        'betriebe_ziel'    => $cfg['betriebe'],
        'betriebe_prozent' => (int) round($q_betriebe * 100),
        'unternehmen'      => $unternehmen,
        'budget_cent'      => $budget,
        'budget_ziel_cent' => $cfg['budget_cent'],
        'budget_prozent'   => (int) round($q_budget * 100),
        'kartons'          => $kartons,
        // Gesamtstand ist der schwaechere der beiden Werte: Der Startschuss
        // faellt erst, wenn beide Seiten stehen.
        'gesamt_prozent'   => (int) round(min($q_betriebe, $q_budget) * 100),
        'ausgeloest'       => ($betriebe >= $cfg['betriebe'] && $budget >= $cfg['budget_cent']),
    ];
}

/** Freigegebene Kartenpunkte. Adressen nur so genau wie eingewilligt. */
function teilnehmer_liste(): array
{
    $out = [];

    $gastro = db_all(
        "SELECT id, betrieb, strasse, plz, ort, website, betriebsart, lat, lon
           FROM gastro_bestellungen
          WHERE status = 'freigegeben' AND karte_ok = 1
       ORDER BY betrieb COLLATE NOCASE"
    );
    foreach ($gastro as $g) {
        $out[] = [
            'typ'     => 'gastro',
            'id'      => 'g' . $g['id'],
            'name'    => $g['betrieb'],
            'strasse' => $g['strasse'],
            'plz'     => $g['plz'],
            'ort'     => $g['ort'],
            'website' => $g['website'],
            'sparte'  => $g['betriebsart'],
            'lat'     => $g['lat'] !== null ? (float) $g['lat'] : null,
            'lon'     => $g['lon'] !== null ? (float) $g['lon'] : null,
        ];
    }

    $firmen = db_all(
        "SELECT id, firma, plz, ort, website, lat, lon
           FROM werbebuchungen
          WHERE status = 'freigegeben' AND karte_ok = 1
       ORDER BY firma COLLATE NOCASE"
    );
    foreach ($firmen as $f) {
        $out[] = [
            'typ'     => 'unternehmen',
            'id'      => 'u' . $f['id'],
            'name'    => $f['firma'],
            'strasse' => null,
            'plz'     => $f['plz'],
            'ort'     => $f['ort'],
            'website' => $f['website'],
            'sparte'  => null,
            'lat'     => $f['lat'] !== null ? (float) $f['lat'] : null,
            'lon'     => $f['lon'] !== null ? (float) $f['lon'] : null,
        ];
    }

    return $out;
}
