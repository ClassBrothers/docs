<?php
/**
 * Cookiefreie Reichweitenmessung.
 *
 * Gespeichert wird: Pfad, Tag, Stunde, grobe Quelle (Host des Referrers)
 * und ein tagesrotierender Hash des Besuchers, damit "Besuche" von
 * "Aufrufen" unterschieden werden koennen. Keine IP, kein Cookie, kein
 * Fingerprint, keine Weitergabe. Nach 30 Tagen wird zu Tagessummen
 * verdichtet (siehe bin/cleanup.php).
 */

declare(strict_types=1);

function analytics_hit(string $pfad, ?string $referrer = null): void
{
    // Statistik ist berechtigtes Interesse, solange sie anonym bleibt.
    // Wer trotzdem nicht gezaehlt werden will, sagt es ueber Do-Not-Track.
    if (($_SERVER['HTTP_DNT'] ?? '') === '1' || ($_SERVER['HTTP_SEC_GPC'] ?? '') === '1') {
        return;
    }
    if (analytics_ist_bot()) {
        return;
    }

    $pfad = mb_substr($pfad, 0, 180);
    $quelle = null;
    if ($referrer) {
        $host = parse_url($referrer, PHP_URL_HOST);
        if (is_string($host)) {
            $eigen = parse_url((string) env('APP_URL', ''), PHP_URL_HOST);
            $quelle = ($host === $eigen) ? null : mb_substr($host, 0, 100);
        }
    }

    try {
        db_run(
            'INSERT INTO analytics_hits (pfad, quelle, besucher_hash, tag, stunde)
             VALUES (?, ?, ?, ?, ?)',
            [$pfad, $quelle, client_hash(), gmdate('Y-m-d'), (int) gmdate('G')]
        );
    } catch (Throwable $e) {
        // Statistik darf niemals eine Seite zerlegen.
        error_log('analytics: ' . $e->getMessage());
    }
}

function analytics_ist_bot(): bool
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') {
        return true;
    }
    foreach (['bot', 'crawl', 'spider', 'slurp', 'headless', 'preview', 'monitor', 'curl/', 'wget'] as $muster) {
        if (str_contains($ua, $muster)) {
            return true;
        }
    }
    return false;
}

/**
 * QR-Weiterleitung. Alle Codes auf den Kartons zeigen auf
 * pizzasupport.de/r/{code}. Damit bleibt die Hoheit ueber das Ziel bei
 * uns, und wir koennen dem Inserenten sagen, wie oft gescannt wurde.
 */
function qr_ziel(string $code): ?array
{
    if (!preg_match('/^[A-Za-z0-9_-]{3,32}$/', $code)) {
        return null;
    }
    return db_one(
        'SELECT id, code, ziel_url, aktiv FROM qr_redirects WHERE code = ?',
        [$code]
    );
}

function qr_klick_zaehlen(int $redirectId): void
{
    try {
        db_run(
            'INSERT INTO qr_klicks (redirect_id, tag, stunde, besucher_hash) VALUES (?, ?, ?, ?)',
            [$redirectId, gmdate('Y-m-d'), (int) gmdate('G'), client_hash()]
        );
    } catch (Throwable $e) {
        error_log('qr_klick: ' . $e->getMessage());
    }
}
