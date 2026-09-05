<?php
/**
 * Klick auf den Bestaetigungslink aus der Werbebuchungs-Mail.
 *
 * Vergibt bei erstem Klick jede gebuchte Flaechen-Kennung fest an diese
 * Buchung - "fest" heisst: ein Zeileneintrag in flaechen_vergabe, dessen
 * UNIQUE-Vorgabe auf kennung verhindert, dass zwei Buchungen dieselbe
 * Flaeche bekommen. Wer zuerst bestaetigt, bekommt sie; alle anderen
 * bekommen Bescheid und werden nicht belastet (siehe AGB).
 *
 * Liest sowohl 'formate' (aktuelles Buchungsformular: die direkt gewaehlten
 * Flaechen-Kennungen) als auch das aeltere 'wunschflaechen' (aus Buchungen,
 * die noch unter dem frueheren Paket-Modell abgeschickt wurden, aber deren
 * Bestaetigungslink erst jetzt geklickt wird) und vereinigt beide - so geht
 * kein bereits verschickter Link ins Leere. flaechenkatalog_eintrag()
 * filtert dabei automatisch alles heraus, was keine echte Kennung ist
 * (z.B. ein alter Paket-Code wie "deckel-klein").
 */

declare(strict_types=1);

$token    = (string) ($_GET['token'] ?? '');
$ok       = false;
$vergeben = [];
$verloren = [];

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    // Faengt fehlende Spalten ab: Diese Spalten kommen erst mit der
    // Migration - kann eigentlich nur bei einem veralteten Link aus der
    // kurzen Zeit zwischen FTP-Upload und Migrationsklick auftreten.
    try {
        $zeile = db_one(
            'SELECT id, formate, wunschflaechen FROM werbebuchungen WHERE bestaetigung_token = ? AND bestaetigt_am IS NULL',
            [$token]
        );
    } catch (PDOException $e) {
        $zeile = null;
    }
    if ($zeile) {
        $jetzt = gmdate('Y-m-d H:i:s');
        db_run(
            'UPDATE werbebuchungen SET bestaetigt_am = ?, bestaetigung_token = ? WHERE id = ?',
            [$jetzt, bin2hex(random_bytes(32)), $zeile['id']]
        );

        $formate        = json_decode((string) $zeile['formate'], true) ?: [];
        $wunschflaechen = json_decode((string) $zeile['wunschflaechen'], true) ?: [];
        $kennungen      = array_unique(array_merge($formate, $wunschflaechen));

        foreach ($kennungen as $kennung) {
            if (flaechenkatalog_eintrag((string) $kennung) === null) {
                continue;
            }
            try {
                // Platzhalter (Gruendungspartner-Flaechen, siehe
                // app/lib/gruendungspartner.php) weichen automatisch einer
                // echten Buchung: die DELETE-Zeile trifft nur eine
                // Platzhalter-Zeile fuer genau diese Kennung, eine echte
                // Vergabe bleibt unberuehrt und laesst den INSERT danach an
                // der UNIQUE-Vorgabe scheitern - "wer zuerst bestaetigt,
                // bekommt sie" gilt fuer echte Buchungen unveraendert.
                db_run('DELETE FROM flaechen_vergabe WHERE kennung = ? AND ist_platzhalter = 1', [$kennung]);
                db_run(
                    'INSERT INTO flaechen_vergabe (kennung, werbebuchung_id, vergeben_am, ist_platzhalter) VALUES (?,?,?,0)',
                    [$kennung, $zeile['id'], $jetzt]
                );
                $vergeben[] = $kennung;
            } catch (PDOException $e) {
                // Schon vergeben - eine andere Buchung war mit ihrer
                // Bestaetigung frueher dran. Kein Fehler, nur Pech.
                $verloren[] = $kennung;
            }
        }
        $ok = true;
    }
}

flash_set('werbebuchung_bestaetigt_zustand', $ok ? 'ok' : 'fehler');
flash_set('werbebuchung_bestaetigt_vergeben', $vergeben);
flash_set('werbebuchung_bestaetigt_verloren', $verloren);
redirect('/werbebuchung-bestaetigt.html');
