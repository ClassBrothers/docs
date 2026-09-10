<?php
/**
 * Kopiere diese Datei nach config.local.php und passe die Werte an.
 * config.local.php ist per .gitignore ausgeschlossen und gehoert NICHT ins Repo.
 * Alternativ koennen alle Werte per Umgebungsvariable gesetzt werden (ENV gewinnt).
 */
return [
    // Login-Schutz. Hash erzeugen mit:  php bin/passwort.php "MeinPasswort"
    // Leer lassen = kein Login (nur fuer rein lokalen Betrieb auf 127.0.0.1 vertretbar).
    'passwort_hash' => '',

    // Pfad zur SQLite-Datei (absolut oder relativ zum Projektverzeichnis).
    'db_pfad' => __DIR__ . '/data/crm.sqlite',

    // Ziel-Schwelle "Startschuss"
    'ziel_gewonnen' => 50,

    // Etikettenbogen: L7160 (3x7, 63,5x38,1mm) | L7163 (2x7, 99,1x38,1mm) | L7165 (2x4, 99,1x67,7mm)
    'etikettenformat' => 'L7160',

    // Absender fuer Briefkopf und Etiketten-Ruecksender
    'absender' => [
        'firma'    => 'Class Brothers GmbH',
        'zusatz'   => 'Kampagne Pizza Support',
        'strasse'  => '',
        'plz_ort'  => '',
        'telefon'  => '',
        'email'    => '',
        'web'      => 'pizzasupport.de',
    ],

    // SMTP fuer EINZELversand (siehe README, Abschnitt Recht/UWG).
    // Solange 'host' leer ist, ist die Mail-Funktion im UI deaktiviert.
    'smtp' => [
        'host'      => getenv('PS_SMTP_HOST') ?: '',
        'port'      => (int) (getenv('PS_SMTP_PORT') ?: 587),
        'user'      => getenv('PS_SMTP_USER') ?: '',
        'pass'      => getenv('PS_SMTP_PASS') ?: '',
        'sicherheit'=> getenv('PS_SMTP_SECURITY') ?: 'tls', // tls | ssl | none
        'von_mail'  => getenv('PS_SMTP_FROM') ?: '',
        'von_name'  => 'Pizza Support | Class Brothers GmbH',
    ],
];
