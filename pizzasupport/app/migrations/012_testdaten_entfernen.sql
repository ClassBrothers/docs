-- Nachtrag 01, Etappe 3.2: zwei konkret benannte Testdatensaetze entfernen.
-- Nur exakte, eindeutige Treffer - keine Heuristik, damit hier nichts
-- Echtes verschwindet. Weitere moegliche Testdaten (z.B. Postleitzahlen
-- ausserhalb Baden-Wuerttembergs) werden im Adminbereich nur markiert,
-- nicht automatisch geloescht - das entscheidet der Mensch von Hand.
DELETE FROM gastro_bestellungen
  WHERE betrieb = 'Test' AND strasse LIKE '%Kaiser Joseph%' AND plz = '72000' AND ort = 'Sonstwo';

DELETE FROM werbebuchungen
  WHERE firma = 'PizzaSupport.de';
