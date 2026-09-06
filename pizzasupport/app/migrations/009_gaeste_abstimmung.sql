-- Abstimmung auf der "Fuer Gaeste"-Landingpage: Gaeste bewerten die
-- gedruckten Werbemotive und die Aktion insgesamt. Motiv-Favoriten werden
-- als JSON-Array von werbebuchungen.id gespeichert (Mehrfachauswahl bis
-- max. 3, serverseitig geprueft) statt als eigene Zuordnungstabelle, weil
-- pro Abstimmung nur gelesen, nie einzeln nachtraeglich verknuepft wird.
CREATE TABLE IF NOT EXISTS gaeste_abstimmungen (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    motiv_favorit     TEXT    NOT NULL,
    motiv_witzig      TEXT    NOT NULL,
    aktion_bewertung  TEXT    NOT NULL,
    feedback          TEXT,
    plz               TEXT,
    alter_jahre       INTEGER,
    name              TEXT,
    email             TEXT,
    datenschutz_ok    INTEGER NOT NULL DEFAULT 0,
    newsletter_ok     INTEGER NOT NULL DEFAULT 0,
    erstellt_am       TEXT    NOT NULL,
    quelle            TEXT    NOT NULL DEFAULT 'website'
);
