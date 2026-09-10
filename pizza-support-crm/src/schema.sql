-- Pizza Support CRM — Schema
-- SQLite. Alle Datums-Felder als ISO-Text (YYYY-MM-DD bzw. YYYY-MM-DD HH:MM:SS).

CREATE TABLE IF NOT EXISTS betriebe (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    -- Kampagnen-Trennung: erlaubt spaetere Wiederverwendung fuer andere
    -- Class-Brothers-Ventures (BVMW, Kreishandwerkerschaft, ...) in derselben DB.
    kampagne                TEXT    NOT NULL DEFAULT 'pizzasupport',

    -- 1:1 aus PizzaSupport_Akquiseliste_Freiburg.xlsx (Blatt "Akquiseliste")
    name                    TEXT    NOT NULL,
    inhaber                 TEXT    DEFAULT '',
    strasse                 TEXT    DEFAULT '',
    plz                     TEXT    DEFAULT '',
    stadtteil               TEXT    DEFAULT '',
    ort                     TEXT    DEFAULT 'Freiburg im Breisgau',
    telefon                 TEXT    DEFAULT '',
    email                   TEXT    DEFAULT '',
    website                 TEXT    DEFAULT '',
    art_gastronomie         TEXT    DEFAULT '',
    pizza_fokus             TEXT    DEFAULT '',
    google_bewertung        REAL,
    anzahl_bewertungen      INTEGER,
    pizzenvolumen_text      TEXT    DEFAULT '',   -- Rohwert aus Excel, z.B. "Mittel - ca. 600-1.500 Pizzen/Monat (Schaetzwert)"
    pizzenvolumen_monat     INTEGER,               -- daraus abgeleiteter Schaetzwert (Zahl) fuer Sortierung/Summen
    volumen_klasse          TEXT    DEFAULT '',    -- Sehr hoch | Hoch | Mittel-Hoch | Mittel | Niedrig/unbekannt
    prioritaet              TEXT    DEFAULT 'B',   -- A | B | C
    status_quelle           TEXT    DEFAULT '',    -- Status-Spalte aus Excel (Rohwert)
    notiz_quelle            TEXT    DEFAULT '',

    -- CRM-Felder (vom Tool verwaltet)
    pipeline_stage          TEXT    NOT NULL DEFAULT 'neu',
    kontaktversuche         INTEGER NOT NULL DEFAULT 0,
    letzter_kontakt         TEXT,
    naechster_follow_up     TEXT,
    vertragsdatum           TEXT,
    anzahl_flaechen_gebucht INTEGER NOT NULL DEFAULT 0,
    tags                    TEXT    DEFAULT '',    -- kommagetrennt
    notizen                 TEXT    DEFAULT '',    -- freies Notizfeld der Kontaktkarte

    -- Anreicherung (Task 3.8)
    anreicherung_status     TEXT    DEFAULT 'offen', -- offen | gefunden | kein_impressum | uebersprungen
    anreicherung_quelle     TEXT    DEFAULT '',
    anreicherung_datum      TEXT,

    erstellt_am             TEXT    NOT NULL DEFAULT (datetime('now')),
    geaendert_am            TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (kampagne, name, strasse)
);

CREATE INDEX IF NOT EXISTS idx_betriebe_stage    ON betriebe (kampagne, pipeline_stage);
CREATE INDEX IF NOT EXISTS idx_betriebe_prio     ON betriebe (kampagne, prioritaet);
CREATE INDEX IF NOT EXISTS idx_betriebe_followup ON betriebe (naechster_follow_up);

CREATE TABLE IF NOT EXISTS aktivitaeten (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    betrieb_id  INTEGER NOT NULL REFERENCES betriebe(id) ON DELETE CASCADE,
    datum       TEXT    NOT NULL DEFAULT (datetime('now')),
    typ         TEXT    NOT NULL,   -- anruf | brief | mail | besuch | stage | sonstiges
    notiz       TEXT    DEFAULT '',
    ergebnis    TEXT    DEFAULT '', -- erreicht | nicht_erreicht | rueckruf | interessiert | abgelehnt | ''
    erstellt_am TEXT    NOT NULL DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_akt_betrieb ON aktivitaeten (betrieb_id, datum DESC);

CREATE TABLE IF NOT EXISTS vorlagen (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    art         TEXT NOT NULL,          -- brief | mail
    name        TEXT NOT NULL,
    betreff     TEXT DEFAULT '',
    koerper     TEXT NOT NULL,
    ist_standard INTEGER NOT NULL DEFAULT 0,
    geaendert_am TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS einstellungen (
    schluessel TEXT PRIMARY KEY,
    wert       TEXT NOT NULL
);
