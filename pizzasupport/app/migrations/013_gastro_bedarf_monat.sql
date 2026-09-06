-- Kundenwunsch: "Kartons pro Woche" wird zu "Bedarf Pizzakartons pro Monat"
-- und alle vier Bedarfsfragen aus Schritt "Dein Bedarf" werden freiwillig
-- statt Pflicht. Spalte entsprechend umbenannt, damit Name und Inhalt
-- zusammenpassen (SQLite unterstuetzt RENAME COLUMN seit Version 3.25).
ALTER TABLE gastro_bestellungen RENAME COLUMN kartons_woche TO kartons_monat_bedarf;
