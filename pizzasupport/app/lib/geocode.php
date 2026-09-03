<?php
/**
 * Einmalige Adresssuche ueber Nominatim (OpenStreetMap), fuer den
 * "Koordinaten ermitteln"-Knopf im Adminpanel - siehe "Offene Punkte" im
 * README. Bei einem Klick pro Adresse durch eine Person haelt das die
 * Nutzungsbedingungen (max. eine Anfrage pro Sekunde, kein Bulk) von selbst
 * ein; ein eigener Zwischenspeicher lohnt sich bei dieser Menge nicht.
 */

declare(strict_types=1);

/** Liefert ['lat' => float, 'lon' => float] zum ersten Treffer, oder null. */
function geocode_adresse(string $adresse): ?array
{
    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($adresse);
    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            // Nominatim verlangt einen erkennbaren, kontaktierbaren Absender -
            // ohne das werden Anfragen ohne Erklaerung blockiert.
            'header'  => 'User-Agent: PizzaSupportFreiburg/1.0 (' . firma_email_link() . ')',
            'timeout' => 8,
        ],
    ]);

    $antwort = @file_get_contents($url, false, $context);
    if ($antwort === false) {
        return null;
    }

    $daten = json_decode($antwort, true);
    if (!is_array($daten) || !isset($daten[0]['lat'], $daten[0]['lon'])) {
        return null;
    }

    return ['lat' => (float) $daten[0]['lat'], 'lon' => (float) $daten[0]['lon']];
}
