ALTER TABLE werbebuchungen ADD COLUMN bestaetigung_token TEXT;
ALTER TABLE werbebuchungen ADD COLUMN bestaetigt_am TEXT;
ALTER TABLE werbebuchungen ADD COLUMN naechste_auflage_bevorzugt INTEGER NOT NULL DEFAULT 0;

-- Feste Zuordnung einer Flaechen-Kennung (aus dem Flaechenkatalog) zu genau
-- einer Werbebuchung, sobald diese per Mail-Link bestaetigt wurde. Die
-- UNIQUE-Vorgabe auf kennung ist die eigentliche Absicherung: Wer zuerst
-- bestaetigt, bekommt die Flaeche - kein Zeitfenster fuer doppelte Vergabe.
CREATE TABLE IF NOT EXISTS flaechen_vergabe (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    kennung         TEXT    NOT NULL UNIQUE,
    werbebuchung_id INTEGER NOT NULL REFERENCES werbebuchungen(id) ON DELETE CASCADE,
    vergeben_am     TEXT    NOT NULL
);
