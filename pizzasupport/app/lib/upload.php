<?php
/**
 * Motiv-Uploads der Werbepartner.
 *
 * Regeln: nur druckfaehige Bild-, PDF- und Vektorformate, maximal 12 MB,
 * Ablage ausserhalb des Web-Roots unter zufaelligem Namen. Der Originalname
 * wandert nur in die Datenbank, nie ins Dateisystem.
 *
 * SVG und EPS koennen Skripte enthalten. Das ist hier vertretbar, weil die
 * Dateien nie ueber den Webserver ausgeliefert werden: Sie liegen unter
 * storage/uploads ausserhalb des Web-Roots, der Adminbereich zeigt nur den
 * Dateinamen als Text an, und geholt werden sie per FTP. Wer die Ablage
 * spaeter doch oeffentlich ausliefert, muss diese Typen vorher entfernen
 * oder die Dateien beim Ausliefern zwingend als Download deklarieren.
 */

declare(strict_types=1);

const UPLOAD_MAX_BYTES = 12 * 1024 * 1024;

function upload_erlaubte_typen(): array
{
    return [
        'image/jpeg'            => 'jpg',
        'image/tiff'            => 'tif',
        'application/pdf'       => 'pdf',
        'image/svg+xml'         => 'svg',
        'application/postscript' => 'eps',
        'image/x-eps'           => 'eps',
        // PNG war bisher schon erlaubt und bleibt es - im Hinweistext steht
        // es nicht mehr, abgewiesen wird eine brauchbare Datei deswegen aber
        // nicht.
        'image/png'             => 'png',
    ];
}

/**
 * @return array{ok: bool, fehler?: string, pfad?: string, name?: string, groesse?: int}
 */
function upload_motiv(?array $datei): array
{
    if (!$datei || ($datei['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true];   // Motiv nachreichen ist ausdruecklich erlaubt.
    }
    if ($datei['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'fehler' => 'Die Datei konnte nicht übertragen werden. Bitte noch einmal versuchen.'];
    }
    if (!is_uploaded_file($datei['tmp_name'])) {
        return ['ok' => false, 'fehler' => 'Ungültiger Upload.'];
    }
    if ($datei['size'] > UPLOAD_MAX_BYTES) {
        return ['ok' => false, 'fehler' => 'Die Datei ist größer als 12 MB. Schick uns das Motiv bitte per E-Mail.'];
    }

    // Auf den tatsaechlichen Inhalt schauen, nicht auf die Endung und nicht
    // auf den vom Browser gemeldeten Typ.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string) $finfo->file($datei['tmp_name']);
    $typen = upload_erlaubte_typen();

    // Aeltere libmagic-Versionen erkennen eine SVG-Datei nur als XML oder
    // Text. Dann selbst nachsehen, statt eine gueltige Datei abzuweisen -
    // der Inhalt entscheidet weiterhin, nicht die Endung.
    if (!isset($typen[$mime]) && in_array($mime, ['text/xml', 'application/xml', 'text/plain', 'text/html'], true)) {
        $anfang = (string) file_get_contents($datei['tmp_name'], false, null, 0, 4096);
        if (stripos($anfang, '<svg') !== false) {
            $mime = 'image/svg+xml';
        }
    }

    if (!isset($typen[$mime])) {
        return ['ok' => false, 'fehler' => 'Erlaubt sind JPG, Tiff, PDF, SVG und EPS.'];
    }

    $ziel_dir = APP_ROOT . '/storage/uploads/' . gmdate('Y/m');
    if (!is_dir($ziel_dir) && !mkdir($ziel_dir, 0770, true) && !is_dir($ziel_dir)) {
        return ['ok' => false, 'fehler' => 'Ablage nicht möglich. Bitte melde Dich kurz bei uns.'];
    }

    $name = bin2hex(random_bytes(16)) . '.' . $typen[$mime];
    $ziel = $ziel_dir . '/' . $name;
    if (!move_uploaded_file($datei['tmp_name'], $ziel)) {
        return ['ok' => false, 'fehler' => 'Ablage nicht möglich. Bitte melde Dich kurz bei uns.'];
    }
    @chmod($ziel, 0640);

    return [
        'ok'      => true,
        'pfad'    => gmdate('Y/m') . '/' . $name,
        'name'    => mb_substr(basename((string) $datei['name']), 0, 180),
        'groesse' => (int) $datei['size'],
    ];
}
