-- Lieferart (Nachtrag 4+5): auf einmal, monatlicher Abruf oder Abholung.
-- geliefert_menge pflegt die Verwaltung von Hand, kein Lieferplanungssystem.
ALTER TABLE gastro_bestellungen ADD COLUMN lieferart TEXT NOT NULL DEFAULT 'gesamt';
ALTER TABLE gastro_bestellungen ADD COLUMN abruf_menge INTEGER;
ALTER TABLE gastro_bestellungen ADD COLUMN geliefert_menge INTEGER NOT NULL DEFAULT 0;
