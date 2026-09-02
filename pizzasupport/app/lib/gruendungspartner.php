<?php
/**
 * Einmalige Anlage der vier Gründungspartner-Buchungen (Nachtrag 1, Punkt 1):
 * Badische Entertainment GmbH, Class Brothers GmbH, KI-Assistenz und
 * SnackWorks haben selbst je eine Fläche belegt und decken damit den
 * Startwert der Fortschrittsanzeige für Werbeflächen.
 *
 * Kontaktdaten sind, wo nicht öffentlich bekannt, ausdrücklich als
 * Platzhalter markiert - der Kunde korrigiert sie nach eigener Aussage
 * selbst. Buchungsformat und Aufteilung sind eine gleichmäßige Näherung an
 * die angepeilten ~7.200 EUR netto (12 % von 60.000 EUR), nicht real
 * verhandelte Einzelpreise.
 */

declare(strict_types=1);

/** @return array{angelegt: bool, hinweis: string} */
function gruendungspartner_anlegen(): array
{
    $vorhanden = (int) db_value("SELECT COUNT(*) FROM werbebuchungen WHERE quelle = 'gruendungspartner'", [], 0);
    if ($vorhanden > 0) {
        return ['angelegt' => false, 'hinweis' => 'Gründungspartner sind bereits angelegt, nichts geändert.'];
    }

    $wf = werbeformat('deckel-klein');
    if ($wf === null) {
        return ['angelegt' => false, 'hinweis' => 'Format „deckel-klein“ nicht gefunden, nichts angelegt.'];
    }

    $firmen = [
        [
            'firma'    => 'Badische Entertainment GmbH',
            'rechnung' => 'Platzhalter – bitte in der Verwaltung korrigieren.',
            'plz'      => '79098',
            'ort'      => 'Freiburg im Breisgau',
        ],
        [
            'firma'    => 'Class Brothers GmbH',
            'rechnung' => config('firma.strasse') . "\n" . config('firma.plz_ort'),
            'plz'      => '79112',
            'ort'      => 'Freiburg im Breisgau',
        ],
        [
            'firma'    => 'KI-Assistenz',
            'rechnung' => 'Platzhalter – bitte in der Verwaltung korrigieren.',
            'plz'      => '79098',
            'ort'      => 'Freiburg im Breisgau',
        ],
        [
            'firma'    => 'SnackWorks',
            'rechnung' => 'Platzhalter – bitte in der Verwaltung korrigieren.',
            'plz'      => '79098',
            'ort'      => 'Freiburg im Breisgau',
        ],
    ];

    $jetzt  = gmdate('Y-m-d H:i:s');
    $zwecke = 'Anbahnung und Abwicklung der Werbebuchung; Nennung auf der öffentlichen Teilnehmerkarte';

    db()->beginTransaction();
    try {
        foreach ($firmen as $f) {
            db_run(
                'INSERT INTO werbebuchungen
                    (art, firma, ansprechpartner, email, telefon_enc, website,
                     rechnung_enc, ustid_enc, plz, ort,
                     formate, coupon, summe_cent,
                     motiv_pfad, motiv_name, motiv_groesse, motiv_spaeter, zielurl,
                     nachricht,
                     agb_ok, motivvorbehalt_ok, karte_ok, datenschutz_ok,
                     einwilligung_am, einwilligung_zweck,
                     status, status_am, erstellt_am, quelle)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    'unternehmen', $f['firma'], 'Platzhalter – bitte ergänzen',
                    'platzhalter@pizzasupport.de', null, null,
                    encrypt_field($f['rechnung']), null, $f['plz'], $f['ort'],
                    json_encode([$wf['id']], JSON_UNESCAPED_UNICODE), 0, (int) $wf['preis'],
                    null, null, null, 1, null,
                    'Gründungspartner-Eintrag, von Hand angelegt: Den Anfang haben wir selbst gemacht.',
                    1, 1, 1, 1,
                    $jetzt, $zwecke,
                    'freigegeben', $jetzt, $jetzt, 'gruendungspartner',
                ]
            );
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        return ['angelegt' => false, 'hinweis' => 'Fehler beim Anlegen: ' . $e->getMessage()];
    }

    $gesamt   = (int) $wf['preis'] * count($firmen);
    $ziel     = (int) config('startschuss.budget_cent');
    $prozent  = $ziel > 0 ? round($gesamt / $ziel * 100, 1) : 0;

    return [
        'angelegt' => true,
        'hinweis'  => 'Vier Gründungspartner-Buchungen angelegt (je 1× Deckel klein), zusammen '
                    . preis($gesamt) . " netto, das sind rund {$prozent} % des Werbeflächen-Ziels.",
    ];
}
