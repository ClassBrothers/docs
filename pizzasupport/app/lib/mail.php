<?php
/**
 * Mailversand ueber die PHP-Funktion mail(). Bewusst schlank: klassisches
 * Webhosting stellt einen lokalen MTA bereit, ein SMTP-Client waere hier
 * zusaetzliche Angriffsflaeche ohne Gewinn.
 *
 * Wenn spaeter doch SMTP gebraucht wird, ist mail_send() die einzige
 * Stelle, die getauscht werden muss.
 */

declare(strict_types=1);

function mail_send(string $an, string $betreff, string $text, ?string $antwortAn = null): bool
{
    // Header-Injection: Zeilenumbrueche in Adressfeldern sind der klassische
    // Weg, fremde Empfaenger unterzuschieben.
    $an = trim(preg_replace('/[\r\n]+/', '', $an) ?? '');
    if (!filter_var($an, FILTER_VALIDATE_EMAIL)) {
        error_log('mail_send: ungueltige Empfaengeradresse abgewiesen');
        return false;
    }

    $von     = (string) env('MAIL_FROM', 'noreply@pizzasupport.de');
    $vonName = (string) env('MAIL_FROM_NAME', 'Pizza Support');
    $betreff = preg_replace('/[\r\n]+/', ' ', $betreff) ?? '';

    $header = [
        'From: ' . mb_encode_mimeheader($vonName, 'UTF-8') . ' <' . $von . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: pizzasupport.de',
    ];
    if ($antwortAn && filter_var($antwortAn, FILTER_VALIDATE_EMAIL)) {
        $header[] = 'Reply-To: ' . $antwortAn;
    }

    $text = str_replace("\r\n", "\n", $text);
    $text = wordwrap($text, 78, "\n", false);

    $ok = @mail(
        $an,
        mb_encode_mimeheader($betreff, 'UTF-8'),
        $text,
        implode("\r\n", $header),
        '-f' . $von
    );

    if (!$ok) {
        error_log('mail_send: Versand fehlgeschlagen an ' . substr($an, 0, 3) . '***');
    }
    return $ok;
}

function mail_ops(string $betreff, string $text, ?string $antwortAn = null): bool
{
    return mail_send((string) env('MAIL_TO_OPS', 'hallo@pizzasupport.de'), $betreff, $text, $antwortAn);
}

/** Einheitlicher Fuss unter allen Bestaetigungsmails. */
function mail_signatur(): string
{
    return "\n\n--\nPizza Support\nEin Projekt der Class Brothers GmbH, Freiburg\n"
         . env('APP_URL', 'https://pizzasupport.de') . "\n\n"
         . "Diese Nachricht wurde automatisch erzeugt, weil auf pizzasupport.de\n"
         . "ein Formular mit dieser Adresse abgeschickt wurde. Wenn Du das nicht\n"
         . "warst, antworte kurz auf diese Mail – dann löschen wir den Eintrag.";
}
