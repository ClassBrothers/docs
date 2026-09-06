-- Ersparnisrechner (Nachtrag 3): die Werte gehoeren an die Bestellung, nicht
-- in eine eigene Tabelle. Beide Spalten sind optional - wer nur rechnet und
-- nicht bestellt, hinterlaesst nichts; wer bestellt, muss den Rechner nicht
-- benutzt haben.
ALTER TABLE gastro_bestellungen ADD COLUMN einkaufspreis_cent INTEGER;
ALTER TABLE gastro_bestellungen ADD COLUMN kartons_monat INTEGER;
