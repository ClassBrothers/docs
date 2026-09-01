<?php
/**
 * Ausgabe-Escaping, CSRF, Honeypot, Rate-Limit, Security-Header.
 */

declare(strict_types=1);

/** Jede Ausgabe laeuft hierdurch. Ohne Ausnahme. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Attributwerte in JSON-Kontexten (data-Attribute). */
function ejson($data): string
{
    return e(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP));
}

function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('ps_session');
    session_start();
}

function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token): bool
{
    session_boot();
    return is_string($token)
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}

/**
 * Tagesrotierende, gesalzene Kennung des Clients.
 * Reicht fuer Rate-Limit und Reichweitenzaehlung, erlaubt aber keine
 * Wiedererkennung ueber Tage hinweg und speichert nie eine IP im Klartext.
 */
function client_hash(): string
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $salt = (string) env('HASH_SALT', 'bitte-setzen');
    return substr(hash('sha256', $salt . '|' . gmdate('Y-m-d') . '|' . $ip), 0, 32);
}

/**
 * Serverseitiges Rate-Limit. Zaehlt Versuche je Aktion und Client-Hash
 * in einem Zeitfenster. Gibt false zurueck, wenn Schluss ist.
 */
function rate_limit_ok(string $aktion, int $max = 5, int $fenster_sekunden = 900): bool
{
    $since = gmdate('Y-m-d H:i:s', time() - $fenster_sekunden);
    $hash  = client_hash();

    $n = (int) db_value(
        'SELECT COUNT(*) FROM rate_limit WHERE aktion = ? AND client_hash = ? AND erstellt_am > ?',
        [$aktion, $hash, $since],
        0
    );
    db_run(
        'INSERT INTO rate_limit (aktion, client_hash, erstellt_am) VALUES (?, ?, ?)',
        [$aktion, $hash, gmdate('Y-m-d H:i:s')]
    );

    return $n < $max;
}

/**
 * Honeypot plus Zeitfalle. Bots fuellen das versteckte Feld aus oder
 * senden das Formular in unter drei Sekunden ab.
 */
function honeypot_field(): string
{
    $t = time();
    return '<div class="hp" aria-hidden="true">'
         . '<label for="hp_website2">Dieses Feld bitte frei lassen</label>'
         . '<input type="text" id="hp_website2" name="website2" tabindex="-1" autocomplete="off">'
         . '<input type="hidden" name="_ts" value="' . $t . '">'
         . '</div>';
}

function honeypot_ok(array $post): bool
{
    if (!empty($post['website2'])) {
        return false;
    }
    $ts = (int) ($post['_ts'] ?? 0);
    if ($ts <= 0 || (time() - $ts) < 3 || (time() - $ts) > 7200) {
        return false;
    }
    return true;
}

/**
 * Security-Header. Die CSP kommt ohne 'unsafe-inline' fuer Skripte aus;
 * das einzige Inline-Stylesheet (Critical CSS) laeuft ueber einen Nonce.
 */
function send_security_headers(string $nonce): void
{
    $csp = implode('; ', [
        "default-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'none'",
        "object-src 'none'",
        // Der Nonce deckt genau zwei Stellen ab: das eingebettete Critical CSS
        // und den kleinen Lader, der das restliche Stylesheet nachzieht.
        // 'unsafe-inline' braucht die Seite dadurch an keiner Stelle.
        // googletagmanager.com liefert gtag.js aus - nur nachdem im Consent-Banner
        // zugestimmt wurde (siehe consent.php/main.js), nie vorher.
        "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com",
        "style-src 'self' 'nonce-{$nonce}'",
        // OpenStreetMap-Kacheln und das Google-Analytics-Logo-Pixel sind die
        // einzigen externen Hosts.
        "img-src 'self' data: https://tile.openstreetmap.org https://*.tile.openstreetmap.org https://www.google-analytics.com",
        "font-src 'self'",
        "connect-src 'self' https://www.google-analytics.com https://*.google-analytics.com https://www.googletagmanager.com",
        "manifest-src 'self'",
        'upgrade-insecure-requests',
    ]);

    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: DENY');
    header('Permissions-Policy: geolocation=(self), camera=(), microphone=(), payment=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header_remove('X-Powered-By');

    if (!empty($_SERVER['HTTPS'])) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}
