<?php
/**
 * Verschluesselung fuer die Felder, die uns bei einem Datenleck wehtun
 * wuerden: Telefonnummern, Rechnungsanschriften, USt-IdNr.
 *
 * AES-256-GCM mit zufaelligem IV je Datensatz. Der Schluessel liegt in
 * der .env, nie im Repo. Ohne Schluessel sind die Spalten wertlos.
 */

declare(strict_types=1);

function crypto_key(): string
{
    static $key = null;
    if ($key !== null) {
        return $key;
    }
    $raw = base64_decode((string) env('APP_KEY', ''), true);
    if ($raw === false || strlen($raw) !== 32) {
        throw new RuntimeException('APP_KEY fehlt oder ist ungueltig. Erzeugen mit: php bin/keygen.php');
    }
    return $key = $raw;
}

function encrypt_field(?string $plain): ?string
{
    if ($plain === null || $plain === '') {
        return null;
    }
    $iv  = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', crypto_key(), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) {
        throw new RuntimeException('Verschluesselung fehlgeschlagen.');
    }
    return base64_encode($iv . $tag . $cipher);
}

function decrypt_field(?string $stored): ?string
{
    if ($stored === null || $stored === '') {
        return null;
    }
    $raw = base64_decode($stored, true);
    if ($raw === false || strlen($raw) < 29) {
        return null;
    }
    $iv     = substr($raw, 0, 12);
    $tag    = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $plain  = openssl_decrypt($cipher, 'aes-256-gcm', crypto_key(), OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? null : $plain;
}
