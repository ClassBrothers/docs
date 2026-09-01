<?php
/**
 * Front Controller.
 *
 * Alle Anfragen laufen hier durch. Statische Dateien holt der Webserver
 * selbst (siehe .htaccess), sodass Bilder und Stylesheets ohne PHP
 * ausgeliefert werden.
 *
 * Die oeffentlichen Adressen enden bewusst auf .html – kurz, sprechend und
 * unabhaengig davon, dass dahinter PHP arbeitet.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$pfad   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$pfad   = '/' . ltrim(rawurldecode($pfad), '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Nonce fuer das eingebettete Critical CSS.
$GLOBALS['csp_nonce'] = base64_encode(random_bytes(16));
send_security_headers($GLOBALS['csp_nonce']);

// -----------------------------------------------------------------------
// Formularverarbeitung
// -----------------------------------------------------------------------
$formulare = [
    '/senden/gastro'       => 'gastro.php',
    '/senden/werbepartner' => 'werbepartner.php',
    '/senden/empfehlung'   => 'empfehlung.php',
    '/senden/newsletter'   => 'newsletter.php',
    '/senden/kontakt'      => 'kontakt.php',
];

if (isset($formulare[$pfad])) {
    if ($method !== 'POST') {
        redirect('/', 303);
    }
    if (!csrf_valid($_POST['_token'] ?? null)) {
        // Meist eine abgelaufene Sitzung, kein Angriff. Freundlich abfangen.
        flash_set('fehler_global', 'Das Formular war zu lange offen. Bitte schick es noch einmal ab.');
        redirect('/', 303);
    }
    require APP_ROOT . '/app/forms/' . $formulare[$pfad];
    exit;
}

// -----------------------------------------------------------------------
// Maschinenlesbares
// -----------------------------------------------------------------------
switch ($pfad) {
    case '/sitemap.xml':
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $stand = date('Y-m-d');
        foreach (config('sitemap', []) as $eintrag) {
            echo "  <url>\n"
               . '    <loc>' . e(url($eintrag['pfad'])) . "</loc>\n"
               . '    <lastmod>' . $stand . "</lastmod>\n"
               . '    <changefreq>' . e($eintrag['freq']) . "</changefreq>\n"
               . '    <priority>' . e($eintrag['prio']) . "</priority>\n"
               . "  </url>\n";
        }
        echo '</urlset>';
        exit;

    case '/robots.txt':
        header('Content-Type: text/plain; charset=UTF-8');
        echo "User-agent: *\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /senden/\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /r/\n";
        echo "Allow: /\n\n";
        echo 'Sitemap: ' . url('/sitemap.xml') . "\n";
        exit;

    case '/api/teilnehmer.json':
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: public, max-age=300');
        echo json_encode([
            'stand'       => date('c'),
            'teilnehmer'  => teilnehmer_liste(),
            'fortschritt' => fortschritt(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;

    case '/api/fortschritt.json':
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: public, max-age=120');
        echo json_encode(fortschritt(), JSON_UNESCAPED_UNICODE);
        exit;
}

// QR-Weiterleitung: /r/{code}
if (preg_match('~^/r/([A-Za-z0-9_-]{3,32})$~', $pfad, $treffer)) {
    $ziel = qr_ziel($treffer[1]);
    if ($ziel && (int) $ziel['aktiv'] === 1) {
        qr_klick_zaehlen((int) $ziel['id']);
        header('Referrer-Policy: no-referrer');
        header('Cache-Control: no-store');
        redirect($ziel['ziel_url'], 302);
    }
    // Unbekannter oder abgeschalteter Code: nicht ins Leere laufen lassen.
    redirect('/?qr=unbekannt', 302);
}

// Admin-Bereich
if ($pfad === '/admin' || str_starts_with($pfad, '/admin/')) {
    require APP_ROOT . '/app/admin.php';
    exit;
}

// -----------------------------------------------------------------------
// Seiten
// -----------------------------------------------------------------------
$seiten = [
    '/'                                => 'startseite',
    '/werbepartner.html'               => 'werbepartner',
    '/teilnehmer.html'                 => 'teilnehmer',
    '/verpackungssteuer-freiburg.html' => 'verpackungssteuer',
    '/ueber-uns.html'                  => 'ueber-uns',
    '/kontakt.html'                    => 'kontakt',
    '/impressum.html'                  => 'impressum',
    '/datenschutz.html'                => 'datenschutz',
    '/agb.html'                        => 'agb',
    '/newsletter-bestaetigt.html'      => 'newsletter-bestaetigt',
];

// Alte oder getippte Adressen sauber auf die kanonische Fassung leiten.
$umleitungen = [
    '/index.html'          => '/',
    '/index.php'           => '/',
    '/home'                => '/',
    '/werbepartner'        => '/werbepartner.html',
    '/teilnehmer'          => '/teilnehmer.html',
    '/ueber-uns'           => '/ueber-uns.html',
    '/kontakt'             => '/kontakt.html',
    '/impressum'           => '/impressum.html',
    '/datenschutz'         => '/datenschutz.html',
    '/agb'                 => '/agb.html',
    '/verpackungssteuer'   => '/verpackungssteuer-freiburg.html',
    '/verpackungssteuer-freiburg' => '/verpackungssteuer-freiburg.html',
];
if (isset($umleitungen[$pfad])) {
    redirect($umleitungen[$pfad], 301);
}

// Newsletter-Bestaetigung aus der Double-Opt-in-Mail
if ($pfad === '/newsletter-bestaetigen') {
    require APP_ROOT . '/app/forms/newsletter-bestaetigen.php';
    exit;
}

if (!isset($seiten[$pfad])) {
    http_response_code(404);
    $seite = '404';
} else {
    $seite = $seiten[$pfad];
    analytics_hit($pfad, $_SERVER['HTTP_REFERER'] ?? null);
}

require APP_ROOT . '/app/views/render.php';
render_seite($seite, $pfad);
