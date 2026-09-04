<?php
/**
 * Einmalige Anlage der vier Gründungspartner-Buchungen (Nachtrag 1, Punkt 1):
 * Badische Entertainment GmbH, Class Brothers GmbH, KI-Assistenz und
 * SnackWorks haben selbst je eine Fläche belegt und decken damit den
 * Startwert der Fortschrittsanzeige für Werbeflächen.
 *
 * Kontaktdaten sind, wo nicht öffentlich bekannt, ausdrücklich als
 * Platzhalter markiert - der Kunde korrigiert sie nach eigener Aussage
 * selbst. Jede Firma bekommt eine eigene, real existierende Fläche aus dem
 * Katalog (nicht mehr dieselbe wie frueher) - das ist eine sinnvolle
 * Näherung an einen Startwert, keine real verhandelte Einzelvergabe.
 */

declare(strict_types=1);

/** @return array{angelegt: bool, hinweis: string} */
function gruendungspartner_anlegen(): array
{
    $vorhanden = (int) db_value("SELECT COUNT(*) FROM werbebuchungen WHERE quelle = 'gruendungspartner'", [], 0);
    if ($vorhanden > 0) {
        return ['angelegt' => false, 'hinweis' => 'Gründungspartner sind bereits angelegt, nichts geändert.'];
    }

    $firmen = [
        [
            'firma'    => 'Badische Entertainment GmbH',
            'rechnung' => 'Platzhalter – bitte in der Verwaltung korrigieren.',
            'plz'      => '79098',
            'ort'      => 'Freiburg im Breisgau',
            'kennung'  => 'D3',
        ],
        [
            'firma'    => 'Class Brothers GmbH',
            'rechnung' => config('firma.strasse') . "\n" . config('firma.plz_ort'),
            'plz'      => '79112',
            'ort'      => 'Freiburg im Breisgau',
            'kennung'  => 'D5',
        ],
        [
            'firma'    => 'KI-Assistenz',
            'rechnung' => 'Platzhalter – bitte in der Verwaltung korrigieren.',
            'plz'      => '79098',
            'ort'      => 'Freiburg im Breisgau',
            'kennung'  => 'D7',
        ],
        [
            'firma'    => 'SnackWorks',
            'rechnung' => 'Platzhalter – bitte in der Verwaltung korrigieren.',
            'plz'      => '79098',
            'ort'      => 'Freiburg im Breisgau',
            'kennung'  => 'D6',
        ],
    ];

    $jetzt  = gmdate('Y-m-d H:i:s');
    $zwecke = 'Anbahnung und Abwicklung der Werbebuchung; Nennung auf der öffentlichen Teilnehmerkarte';

    $gesamt = 0;
    db()->beginTransaction();
    try {
        foreach ($firmen as $f) {
            $flaeche = flaechenkatalog_eintrag($f['kennung']);
            if ($flaeche === null || $flaeche['preis'] === null) {
                throw new RuntimeException('Fläche „' . $f['kennung'] . '“ nicht im Katalog gefunden.');
            }
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
                    json_encode([$f['kennung']], JSON_UNESCAPED_UNICODE), 0, (int) $flaeche['preis'],
                    null, null, null, 1, null,
                    'Gründungspartner-Eintrag, von Hand angelegt: Den Anfang haben wir selbst gemacht.',
                    1, 1, 1, 1,
                    $jetzt, $zwecke,
                    'freigegeben', $jetzt, $jetzt, 'gruendungspartner',
                ]
            );
            $buchungId = (int) db()->lastInsertId();
            db_run(
                'INSERT INTO flaechen_vergabe (kennung, werbebuchung_id, vergeben_am) VALUES (?,?,?)',
                [$f['kennung'], $buchungId, $jetzt]
            );
            $gesamt += (int) $flaeche['preis'];
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        return ['angelegt' => false, 'hinweis' => 'Fehler beim Anlegen: ' . $e->getMessage()];
    }

    $ziel    = (int) config('startschuss.budget_cent');
    $prozent = $ziel > 0 ? round($gesamt / $ziel * 100, 1) : 0;

    return [
        'angelegt' => true,
        'hinweis'  => 'Vier Gründungspartner-Buchungen angelegt (je eine eigene Fläche), zusammen '
                    . preis($gesamt) . " netto, das sind rund {$prozent} % des Werbeflächen-Ziels.",
    ];
}
