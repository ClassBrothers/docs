<?php
/**
 * Klick auf den Bestaetigungslink aus der Werbebuchungs-Mail.
 *
 * Vergibt bei erstem Klick jede gewuenschte Flaechen-Kennung fest an diese
 * Buchung - "fest" heisst: ein Zeileneintrag in flaechen_vergabe, dessen
 * UNIQUE-Vorgabe auf kennung verhindert, dass zwei Buchungen dieselbe
 * Flaeche bekommen. Wer zuerst bestaetigt, bekommt sie; alle anderen
 * behalten ihren Wunsch nur als Wunsch (siehe AGB).
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
            'SELECT id, wunschflaechen FROM werbebuchungen WHERE bestaetigung_token = ? AND bestaetigt_am IS NULL',
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

        $wunschflaechen = json_decode((string) $zeile['wunschflaechen'], true) ?: [];
        foreach ($wunschflaechen as $kennung) {
            try {
                db_run(
                    'INSERT INTO flaechen_vergabe (kennung, werbebuchung_id, vergeben_am) VALUES (?,?,?)',
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
