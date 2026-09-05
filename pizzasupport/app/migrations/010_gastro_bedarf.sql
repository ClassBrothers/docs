-- Nachtrag 01, Etappe 5: neue Pflichtfragen im Bestellassistenten (Schritt 2
-- "Dein Bedarf"), getrennt von den optionalen Ersparnisrechner-Spalten
-- (einkaufspreis_cent, kartons_monat aus 005_ersparnisrechner.sql) - der
-- Rechner bleibt ein unverbindliches Beispiel oben auf der Seite, diese
-- Spalten sind die tatsaechliche Planungsgrundlage aus der Bestellung.
ALTER TABLE gastro_bestellungen ADD COLUMN kartons_woche INTEGER;
ALTER TABLE gastro_bestellungen ADD COLUMN aktueller_einkaufspreis_cent INTEGER;
ALTER TABLE gastro_bestellungen ADD COLUMN aktuelle_groesse TEXT;
ALTER TABLE gastro_bestellungen ADD COLUMN aktueller_lieferant TEXT;
ALTER TABLE gastro_bestellungen ADD COLUMN betriebsart_frei TEXT;
