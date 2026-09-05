-- Nachtrag 01, Etappe 3.3: die vier Gruendungspartner-Flaechen sind
-- Platzhalter, keine echten Verkaeufe. Sie zaehlen weiter als verfuegbar
-- und weichen automatisch, sobald ein zahlender Kunde dieselbe Flaeche
-- bestaetigt (siehe werbebuchung-bestaetigen.php).
ALTER TABLE flaechen_vergabe ADD COLUMN ist_platzhalter INTEGER NOT NULL DEFAULT 0;
UPDATE flaechen_vergabe SET ist_platzhalter = 1
  WHERE werbebuchung_id IN (SELECT id FROM werbebuchungen WHERE quelle = 'gruendungspartner');
