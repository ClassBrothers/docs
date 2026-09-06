-- ---------------------------------------------------------------------------
-- Mengen je Format statt einer einzelnen Format-/Mengenspalte.
--
-- Ein Betrieb kann jetzt mehrere Kartonformate in einer Bestellung mischen.
-- Jede gewaehlte Menge bekommt eine eigene Zeile, verknuepft ueber eine
-- Loeschweitergabe: entfaellt die Bestellung, entfallen automatisch auch
-- ihre Positionen.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS bestellpositionen (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    bestellung_id INTEGER NOT NULL REFERENCES gastro_bestellungen (id) ON DELETE CASCADE,
    format        TEXT    NOT NULL,
    menge         INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_bestellpositionen_bestellung ON bestellpositionen (bestellung_id);

-- Zustimmung zum Versandzuschlag ausserhalb Freiburgs (§ 3 AGB), per PLZ
-- ermittelt und vor dem Absenden vom Betrieb bestaetigt.
ALTER TABLE gastro_bestellungen ADD COLUMN versand_zuschlag_ok INTEGER NOT NULL DEFAULT 0;

-- Ersetzt durch bestellpositionen, siehe oben. Es lagen bislang keine
-- Datensaetze vor, ein Uebertrag entfaellt daher.
ALTER TABLE gastro_bestellungen DROP COLUMN format;
ALTER TABLE gastro_bestellungen DROP COLUMN menge;
