<?php
/**
 * Startet die Anwendung: Konfiguration, Umgebung, Sicherheit, Bibliotheken.
 * Wird von public/index.php und von den Skripten in bin/ eingebunden.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/app/lib/env.php';
env_load(APP_ROOT . '/.env');

$istDev = env('APP_ENV', 'prod') === 'dev';
ini_set('display_errors', $istDev ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/php-error.log');
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Berlin');

require APP_ROOT . '/app/lib/security.php';
require APP_ROOT . '/app/lib/db.php';
require APP_ROOT . '/app/lib/crypto.php';
require APP_ROOT . '/app/lib/validate.php';
require APP_ROOT . '/app/lib/mail.php';
require APP_ROOT . '/app/lib/stats.php';
require APP_ROOT . '/app/lib/analytics.php';
require APP_ROOT . '/app/lib/upload.php';

/** Zugriff auf die Konfiguration, punktgetrennt: config('startschuss.betriebe') */
function config(?string $pfad = null, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require APP_ROOT . '/app/config.php';
    }
    if ($pfad === null) {
        return $cfg;
    }
    $wert = $cfg;
    foreach (explode('.', $pfad) as $teil) {
        if (!is_array($wert) || !array_key_exists($teil, $wert)) {
            return $default;
        }
        $wert = $wert[$teil];
    }
    return $wert;
}

/** Absolute URL zu einem Pfad. */
function url(string $pfad = '/'): string
{
    return rtrim((string) env('APP_URL', 'https://pizzasupport.de'), '/') . $pfad;
}

/** Cache-Buster fuer Assets: Dateizeit statt Handpflege von Versionsnummern. */
function asset(string $pfad): string
{
    $datei = APP_ROOT . '/public' . $pfad;
    $v = is_file($datei) ? (string) filemtime($datei) : '1';
    return $pfad . '?v=' . substr(md5($v), 0, 8);
}

/** Cent als deutscher Preis. */
function preis(int $cent, bool $mitWaehrung = true): string
{
    $s = number_format($cent / 100, 2, ',', '.');
    return $mitWaehrung ? $s . ' €' : $s;
}

/** Ganze Zahl mit Tausenderpunkt. */
function zahl(int $n): string
{
    return number_format($n, 0, ',', '.');
}

/**
 * E-Mail-Adresse aus der Konfiguration, bereinigt fuer mailto:-Links.
 * In app/config.php steht sie zum Schutz vor Sammelprogrammen mit
 * Leerzeichen (hallo @ pizzasupport . de) - fuer einen Link muessen die raus.
 * Die Anzeige nutzt weiterhin config('firma.email') direkt.
 */
function firma_email_link(): string
{
    return str_replace(' ', '', (string) config('firma.email'));
}

/** Ein Werbeformat aus der Konfiguration holen. */
function werbeformat(string $id): ?array
{
    foreach (config('werbeformate', []) as $f) {
        if ($f['id'] === $id) {
            return $f;
        }
    }
    return null;
}

/** Flash-Nachricht zwischen POST und Redirect. */
function flash_set(string $schluessel, $wert): void
{
    session_boot();
    $_SESSION['flash'][$schluessel] = $wert;
}

function flash_get(string $schluessel, $default = null)
{
    session_boot();
    $wert = $_SESSION['flash'][$schluessel] ?? $default;
    unset($_SESSION['flash'][$schluessel]);
    return $wert;
}

/** Alte Eingaben nach einem Fehler zurueck ins Formular. */
function alt(array $alt, string $feld, string $default = ''): string
{
    $v = $alt[$feld] ?? $default;
    return is_scalar($v) ? (string) $v : $default;
}

function redirect(string $pfad, int $code = 303): never
{
    header('Location: ' . $pfad, true, $code);
    exit;
}
