<?php
declare(strict_types=1);

/**
 * Einfacher Passwortschutz. Das Tool enthaelt Akquisedaten und gehoert nicht
 * offen ins Netz — ohne gesetzten Hash ist es deshalb nur lokal erreichbar.
 */
final class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('pscrm');
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => self::istHttps()]);
            session_start();
        }
    }

    public static function geschuetzt(): bool
    {
        return ((string) ps_cfg('passwort_hash', '')) !== '';
    }

    public static function angemeldet(): bool
    {
        self::start();
        if (!self::geschuetzt()) {
            // Ohne Passwort nur vom selben Rechner aus zugelassen.
            return self::istLokal();
        }
        return !empty($_SESSION['angemeldet']);
    }

    public static function anmelden(string $passwort): bool
    {
        self::start();
        $hash = (string) ps_cfg('passwort_hash', '');
        if ($hash === '' || !password_verify($passwort, $hash)) {
            usleep(400_000); // Bremse gegen Durchprobieren
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['angemeldet'] = true;
        return true;
    }

    public static function abmelden(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    public static function verlangen(): void
    {
        if (self::angemeldet()) {
            return;
        }
        if (self::geschuetzt()) {
            header('Location: login.php');
            exit;
        }
        http_response_code(403);
        exit('Kein Passwort gesetzt (config.local.php) — Zugriff daher nur von 127.0.0.1 erlaubt.');
    }

    /** CSRF-Token fuer schreibende Anfragen. */
    public static function token(): string
    {
        self::start();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return (string) $_SESSION['csrf'];
    }

    public static function tokenPruefen(?string $token): bool
    {
        self::start();
        return is_string($token) && !empty($_SESSION['csrf']) && hash_equals((string) $_SESSION['csrf'], $token);
    }

    private static function istLokal(): bool
    {
        return in_array($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', ['127.0.0.1', '::1'], true);
    }

    private static function istHttps(): bool
    {
        return ($_SERVER['HTTPS'] ?? '') !== '' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
