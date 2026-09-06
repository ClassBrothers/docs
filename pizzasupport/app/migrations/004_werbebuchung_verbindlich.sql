-- Verbindliche Buchungszusage fuer den Fall, dass das Projekt zustande kommt
-- (Etappe 2, Werbepartner-Formular). Eigene Spalte, damit die Zustimmung wie
-- agb_ok/motivvorbehalt_ok/datenschutz_ok nachweisbar bleibt.
ALTER TABLE werbebuchungen ADD COLUMN verbindlich_ok INTEGER NOT NULL DEFAULT 0;
