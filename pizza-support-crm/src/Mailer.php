<?php
declare(strict_types=1);

/**
 * Minimaler SMTP-Versand fuer EINZELmails.
 *
 * Rechtlicher Rahmen (§ 7 UWG): Kalt-Werbemails an Gewerbetreibende sind ohne
 * Einwilligung unzulaessig. Deshalb gibt es hier bewusst KEINEN Sammelversand;
 * jeder Versand setzt mindestens einen dokumentierten Vorkontakt voraus.
 */
final class Mailer
{
    public static function konfiguriert(): bool
    {
        return ((string) ps_cfg('smtp.host', '')) !== '' && ((string) ps_cfg('smtp.von_mail', '')) !== '';
    }

    /**
     * @throws RuntimeException wenn die Vorbedingungen nicht erfuellt sind
     * @return array{empfaenger:string,betreff:string}
     */
    public static function anBetrieb(int $betriebId, string $betreff, string $koerper): array
    {
        $betrieb = Repo::finde($betriebId);
        if (!$betrieb) {
            throw new RuntimeException('Betrieb nicht gefunden.');
        }
        if ((int) $betrieb['kontaktversuche'] < 1) {
            throw new RuntimeException('Kein dokumentierter Vorkontakt: E-Mail erst nach Telefonat, Brief oder Besuch zulaessig (§ 7 UWG).');
        }
        if (!Repo::istMail((string) $betrieb['email'])) {
            throw new RuntimeException('Keine gueltige E-Mail-Adresse hinterlegt.');
        }
        if (!self::konfiguriert()) {
            throw new RuntimeException('SMTP ist nicht konfiguriert (siehe config.local.php).');
        }

        $betreff = Vorlagen::fuellen($betreff, $betrieb);
        $koerper = Vorlagen::fuellen($koerper, $betrieb);
        self::senden((string) $betrieb['email'], $betreff, $koerper);

        Repo::aktivitaetAnlegen($betriebId, [
            'typ'   => 'mail',
            'notiz' => 'E-Mail versendet an ' . $betrieb['email'] . ' — Betreff: ' . $betreff,
        ]);
        return ['empfaenger' => (string) $betrieb['email'], 'betreff' => $betreff];
    }

    private static function senden(string $an, string $betreff, string $koerper): void
    {
        $host = (string) ps_cfg('smtp.host');
        $port = (int) ps_cfg('smtp.port', 587);
        $sicherheit = (string) ps_cfg('smtp.sicherheit', 'tls');
        $vonMail = (string) ps_cfg('smtp.von_mail');
        $vonName = (string) ps_cfg('smtp.von_name', '');

        $ziel = ($sicherheit === 'ssl' ? 'ssl://' : '') . $host;
        $verbindung = @stream_socket_client("{$ziel}:{$port}", $fehlerNr, $fehlerText, 15);
        if (!$verbindung) {
            throw new RuntimeException("SMTP-Verbindung fehlgeschlagen: {$fehlerText}");
        }
        stream_set_timeout($verbindung, 15);

        try {
            self::erwarte($verbindung, 220);
            self::befehl($verbindung, 'EHLO ' . (gethostname() ?: 'localhost'), 250);

            if ($sicherheit === 'tls') {
                self::befehl($verbindung, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($verbindung, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('TLS-Aushandlung fehlgeschlagen.');
                }
                self::befehl($verbindung, 'EHLO ' . (gethostname() ?: 'localhost'), 250);
            }

            $benutzer = (string) ps_cfg('smtp.user', '');
            if ($benutzer !== '') {
                self::befehl($verbindung, 'AUTH LOGIN', 334);
                self::befehl($verbindung, base64_encode($benutzer), 334);
                self::befehl($verbindung, base64_encode((string) ps_cfg('smtp.pass', '')), 235);
            }

            self::befehl($verbindung, "MAIL FROM:<{$vonMail}>", 250);
            self::befehl($verbindung, "RCPT TO:<{$an}>", 250);
            self::befehl($verbindung, 'DATA', 354);

            $kopf = [
                'From: ' . ($vonName !== '' ? self::kodiereKopf($vonName) . " <{$vonMail}>" : $vonMail),
                'To: <' . $an . '>',
                'Subject: ' . self::kodiereKopf($betreff),
                'Date: ' . date('r'),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', $vonMail)[1] ?? 'localhost') . '>',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];
            $nachricht = implode("\r\n", $kopf) . "\r\n\r\n" . self::punkteMaskieren($koerper) . "\r\n.";
            self::befehl($verbindung, $nachricht, 250);
            self::befehl($verbindung, 'QUIT', 221);
        } finally {
            fclose($verbindung);
        }
    }

    /** @param resource $verbindung */
    private static function befehl($verbindung, string $befehl, int $erwartet): string
    {
        fwrite($verbindung, $befehl . "\r\n");
        return self::erwarte($verbindung, $erwartet);
    }

    /** @param resource $verbindung */
    private static function erwarte($verbindung, int $code): string
    {
        $antwort = '';
        while (($zeile = fgets($verbindung, 515)) !== false) {
            $antwort .= $zeile;
            if (strlen($zeile) < 4 || $zeile[3] !== '-') {
                break;
            }
        }
        if ((int) substr($antwort, 0, 3) !== $code) {
            throw new RuntimeException('SMTP-Fehler: ' . trim($antwort));
        }
        return $antwort;
    }

    private static function kodiereKopf(string $text): string
    {
        return preg_match('/[\x80-\xFF]/', $text) ? '=?UTF-8?B?' . base64_encode($text) . '?=' : $text;
    }

    /** Zeilen, die mit einem Punkt beginnen, im DATA-Block verdoppeln. */
    private static function punkteMaskieren(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        return preg_replace('/^\./m', '..', str_replace("\n", "\r\n", $text)) ?? $text;
    }
}
