<?php
/**
 * Motiv-Uploads der Werbepartner.
 *
 * Regeln: nur Bild- und PDF-Formate, maximal 12 MB, Ablage ausserhalb des
 * Web-Roots unter zufaelligem Namen. Der Originalname wandert nur in die
 * Datenbank, nie ins Dateisystem.
 */

declare(strict_types=1);

const UPLOAD_MAX_BYTES = 12 * 1024 * 1024;

function upload_erlaubte_typen(): array
{
    return [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
        'application/pdf' => 'pdf',
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
    if (!isset($typen[$mime])) {
        return ['ok' => false, 'fehler' => 'Erlaubt sind JPG, PNG, WebP und PDF.'];
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
