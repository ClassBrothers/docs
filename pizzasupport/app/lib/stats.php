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
        "SELECT COALESCE(SUM(bp.menge), 0)
           FROM bestellpositionen bp
           JOIN gastro_bestellungen g ON g.id = bp.bestellung_id
          WHERE g.status = 'freigegeben'", [], 0
    );

    // Ersparnisrechner, oeffentliche Gesamtsumme: nur aus freigegebenen
    // Bestellungen mit angegebenem Einkaufspreis, nie aus reinen
    // Rechnereingaben. Rechnet live, damit eine geloeschte Bestellung ihren
    // Anteil automatisch verliert.
    //
    // Faengt fehlende Spalten ab: Beim FTP-Deploy kommen neue Dateien vor der
    // Migration an (die Migration laeuft erst per Knopf im Adminpanel), und
    // fortschritt() wird auch von admin_seite() aufgerufen - ohne Abfangen
    // wuerde ein fehlender Spaltenname in diesem kurzen Zwischenzustand die
    // gesamte Seite inklusive Adminpanel lahmlegen.
    try {
        $ersparnis_cent = (int) db_value(
            "SELECT COALESCE(SUM(g.einkaufspreis_cent * bp.gesamt), 0)
               FROM gastro_bestellungen g
               JOIN (SELECT bestellung_id, SUM(menge) AS gesamt FROM bestellpositionen GROUP BY bestellung_id) bp
                 ON bp.bestellung_id = g.id
              WHERE g.status = 'freigegeben' AND g.einkaufspreis_cent IS NOT NULL", [], 0
        );
    } catch (PDOException $e) {
        $ersparnis_cent = 0;
    }

    // Schwellenwerte fuer den Startschuss rechnen immer mit den echten,
    // ungeschoenten Zahlen - die Mindestanzeige weiter unten betrifft
    // ausschliesslich das, was auf der Startseite zu sehen ist.
    $q_betriebe = $cfg['betriebe'] > 0 ? min(1.0, $betriebe / $cfg['betriebe']) : 0.0;
    $q_budget   = $cfg['budget_cent'] > 0 ? min(1.0, $budget / $cfg['budget_cent']) : 0.0;
    $ausgeloest = ($betriebe >= $cfg['betriebe'] && $budget >= $cfg['budget_cent']);

    // Mindestanzeige, damit die Startseite nicht mit Nullen dasteht, bevor
    // die ersten echten Eintragungen da sind. Sobald eine Zahl die
    // Voreinstellung uebersteigt, zeigen wir nur noch die echte Zahl.
    $mindest           = config('fortschritt_mindestanzeige', []);
    $betriebe_anzeige  = max($betriebe, (int) ($mindest['betriebe'] ?? 0));
    $unternehmen_anzeige = max($unternehmen, (int) ($mindest['unternehmen'] ?? 0));
    $kartons_anzeige   = max($kartons, (int) ($mindest['kartons'] ?? 0));

    // Der Balken fuer "Gastronomie" zeigt dieselbe Zahl wie die
    // Fortschrittsanzeige direkt daneben ("16 von 40") - sonst wirkt ein
    // Balken bei 0 % neben einer Mindestanzeige von 16 unstimmig.
    $q_betriebe_anzeige = $cfg['betriebe'] > 0 ? min(1.0, $betriebe_anzeige / $cfg['betriebe']) : 0.0;

    return [
        'betriebe'         => $betriebe_anzeige,
        'betriebe_ziel'    => $cfg['betriebe'],
        'betriebe_prozent' => (int) round($q_betriebe_anzeige * 100),
        'unternehmen'      => $unternehmen_anzeige,
        'budget_cent'      => $budget,
        'budget_ziel_cent' => $cfg['budget_cent'],
        'budget_prozent'   => (int) round($q_budget * 100),
        'kartons'          => $kartons_anzeige,
        // Gesamtstand ist der schwaechere der beiden Werte: Der Startschuss
        // faellt erst, wenn beide Seiten stehen. Rechnet mit den echten
        // Werten, nicht der Mindestanzeige.
        'gesamt_prozent'   => (int) round(min($q_betriebe, $q_budget) * 100),
        'ausgeloest'       => $ausgeloest,
        'ersparnis_cent'   => $ersparnis_cent,
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
