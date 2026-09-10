<?php
declare(strict_types=1);

/** Gemeinsamer Einstiegspunkt: Konfiguration, Autoload, Konstanten. */

const PS_ROOT = __DIR__ . '/..';

spl_autoload_register(static function (string $klasse): void {
    $datei = __DIR__ . '/' . $klasse . '.php';
    if (is_file($datei)) {
        require_once $datei;
    }
});

/** Pipeline-Stufen: Schluessel => Anzeigename. Reihenfolge = Reihenfolge der Kanban-Spalten. */
const PS_STAGES = [
    'neu'                   => 'Neu',
    'kontaktversuch_1'      => 'Kontaktversuch 1',
    'kontaktversuch_2'      => 'Kontaktversuch 2',
    'erreicht_interessiert' => 'Erreicht/Interessiert',
    'angebot_verschickt'    => 'Angebot verschickt',
    'verhandlung'           => 'Verhandlung',
    'gewonnen'              => 'Gewonnen (Vertrag)',
    'abgelehnt'             => 'Abgelehnt',
    'kein_interesse'        => 'Kein Interesse/Nicht erreichbar',
];

/** Stufen, die als "aktiv in Arbeit" zaehlen (fuer Dashboard-Funnel). */
const PS_STAGES_KONTAKTIERT = ['kontaktversuch_1', 'kontaktversuch_2', 'erreicht_interessiert', 'angebot_verschickt', 'verhandlung', 'gewonnen'];
const PS_STAGES_INTERESSIERT = ['erreicht_interessiert', 'angebot_verschickt', 'verhandlung', 'gewonnen'];

const PS_AKTIVITAETS_TYPEN = ['anruf' => 'Anruf', 'brief' => 'Brief', 'mail' => 'E-Mail', 'besuch' => 'Besuch', 'stage' => 'Statuswechsel', 'sonstiges' => 'Sonstiges'];

/** Etikettenbogen-Definitionen, alle Masse in Millimetern. Leicht erweiterbar. */
const PS_ETIKETTEN = [
    'L7160' => ['spalten' => 3, 'zeilen' => 7, 'breite' => 63.5, 'hoehe' => 38.1, 'rand_oben' => 15.0, 'rand_links' => 7.2, 'abstand_x' => 2.5, 'abstand_y' => 0.0, 'label' => 'Avery L7160 — 3x7, 63,5 x 38,1 mm'],
    'L7163' => ['spalten' => 2, 'zeilen' => 7, 'breite' => 99.1, 'hoehe' => 38.1, 'rand_oben' => 15.1, 'rand_links' => 5.0, 'abstand_x' => 2.5, 'abstand_y' => 0.0, 'label' => 'Avery L7163 — 2x7, 99,1 x 38,1 mm'],
    'L7165' => ['spalten' => 2, 'zeilen' => 4, 'breite' => 99.1, 'hoehe' => 67.7, 'rand_oben' => 13.0, 'rand_links' => 5.0, 'abstand_x' => 2.5, 'abstand_y' => 0.0, 'label' => 'Avery L7165 — 2x4, 99,1 x 67,7 mm'],
];

function ps_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }
    $standard = require PS_ROOT . '/config.example.php';
    $lokal = is_file(PS_ROOT . '/config.local.php') ? require PS_ROOT . '/config.local.php' : [];
    $config = array_replace_recursive($standard, $lokal);

    if ($hash = getenv('PS_PASSWORT_HASH')) {
        $config['passwort_hash'] = $hash;
    }
    if ($db = getenv('PS_DB_PFAD')) {
        $config['db_pfad'] = $db;
    }
    return $config;
}

function ps_cfg(string $pfad, mixed $fallback = null): mixed
{
    $wert = ps_config();
    foreach (explode('.', $pfad) as $teil) {
        if (!is_array($wert) || !array_key_exists($teil, $wert)) {
            return $fallback;
        }
        $wert = $wert[$teil];
    }
    return $wert;
}
