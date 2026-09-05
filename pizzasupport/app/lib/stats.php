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

    // Anteil der Gruendungspartner-Platzhalter am Werbeflaechen-Fortschritt
    // (siehe app/lib/gruendungspartner.php) - fuer das Kleingedruckte
    // "davon X aus dem eigenen Umfeld" unter dem Balken.
    $platzhalterAnzahl = (int) db_value(
        "SELECT COUNT(*) FROM werbebuchungen WHERE quelle = 'gruendungspartner' AND status = 'freigegeben'", [], 0
    );
    $platzhalterCent = (int) db_value(
        "SELECT COALESCE(SUM(summe_cent), 0) FROM werbebuchungen WHERE quelle = 'gruendungspartner' AND status = 'freigegeben'", [], 0
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
    // ungeschoenten Zahlen.
    $q_betriebe = $cfg['betriebe'] > 0 ? min(1.0, $betriebe / $cfg['betriebe']) : 0.0;
    $q_budget   = $cfg['budget_cent'] > 0 ? min(1.0, $budget / $cfg['budget_cent']) : 0.0;

    $betriebe_erreicht = $betriebe >= $cfg['betriebe'];
    $budget_erreicht   = $budget >= $cfg['budget_cent'];
    $ausgeloest        = $betriebe_erreicht && $budget_erreicht;

    return [
        'betriebe'          => $betriebe,
        'betriebe_ziel'     => $cfg['betriebe'],
        'betriebe_prozent'  => (int) round($q_betriebe * 100),
        'betriebe_erreicht' => $betriebe_erreicht,
        'unternehmen'       => $unternehmen,
        'budget_cent'       => $budget,
        'budget_ziel_cent'  => $cfg['budget_cent'],
        'budget_prozent'    => (int) round($q_budget * 100),
        'budget_erreicht'   => $budget_erreicht,
        'kartons'           => $kartons,
        // Gesamtstand ist der schwaechere der beiden Werte: Der Startschuss
        // faellt erst, wenn beide Seiten stehen.
        'gesamt_prozent'    => (int) round(min($q_betriebe, $q_budget) * 100),
        'ausgeloest'        => $ausgeloest,
        'ersparnis_cent'    => $ersparnis_cent,
        'platzhalter_anzahl' => $platzhalterAnzahl,
        'platzhalter_cent'   => $platzhalterCent,
    ];
}

/**
 * Oeffentliche Fassung von fortschritt(): die konkreten Zaehlerstaende
 * (Betriebe/Kartons auf der Gastro-Seite, Unternehmen/Budget auf der
 * Werbepartner-Seite) bleiben verborgen, bis das jeweilige Ziel wirklich
 * erreicht ist - vorher sind nur die Prozent-Balken zu sehen, die weiter
 * live mitlaufen. Gilt fuer alles, was das Frontend zu sehen bekommt,
 * einschliesslich der oeffentlichen JSON-Endpunkte - eine Zahl, die auf
 * der Seite versteckt ist, aber ueber die API abrufbar bleibt, waere keine
 * echte Geheimhaltung.
 */
function fortschritt_oeffentlich(): array
{
    $f = fortschritt();

    if (!$f['betriebe_erreicht']) {
        $f['betriebe'] = null;
        $f['kartons']  = null;
    }
    if (!$f['budget_erreicht']) {
        $f['unternehmen']  = null;
        $f['budget_cent']  = null;
    }

    return $f;
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
