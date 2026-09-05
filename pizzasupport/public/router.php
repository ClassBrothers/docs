<?php
/**
 * Router fuer den eingebauten PHP-Server (nur Entwicklung):
 *   php -S 127.0.0.1:8000 -t public public/router.php
 *
 * Auf dem echten Server macht das die .htaccess. Diese Datei wird
 * in der Produktion nicht gebraucht und niemals aufgerufen.
 */
declare(strict_types=1);

$pfad = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$datei = __DIR__ . $pfad;

// Vorhandene statische Dateien direkt ausliefern, alles andere an den
// Front Controller - genau wie die RewriteRules in der .htaccess.
if ($pfad !== '/' && is_file($datei)) {
    return false;
}

require __DIR__ . '/index.php';
