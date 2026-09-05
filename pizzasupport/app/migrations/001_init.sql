-- ---------------------------------------------------------------------------
-- pizzasupport.de – Grundschema
--
-- Grundsatz: Jeder Datensatz traegt seinen Zweck, seine Einwilligung mit
-- Zeitstempel und seinen Freigabestatus. Nichts erscheint ungeprueft
-- oeffentlich auf der Karte.
-- ---------------------------------------------------------------------------

-- Bestellungen der Gastronomie
CREATE TABLE IF NOT EXISTS gastro_bestellungen (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    vorname         TEXT    NOT NULL,
    nachname        TEXT    NOT NULL,
    betrieb         TEXT    NOT NULL,
    strasse         TEXT    NOT NULL,
    plz             TEXT    NOT NULL,
    ort             TEXT    NOT NULL,
    email           TEXT    NOT NULL,
    telefon_enc     TEXT,                       -- verschluesselt
    website         TEXT,
    betriebsart     TEXT    NOT NULL,
    format          TEXT    NOT NULL,           -- Kantenlaenge in cm, siehe config
    menge           INTEGER NOT NULL,
    anmerkung       TEXT,

    -- Einwilligungen, jede mit Zeitpunkt und Zweck
    bestellung_ok   INTEGER NOT NULL DEFAULT 0, -- verbindliche Bestellung nach Startschuss-Prinzip
    karte_ok        INTEGER NOT NULL DEFAULT 0, -- Anzeige auf der Teilnehmerkarte
    datenschutz_ok  INTEGER NOT NULL DEFAULT 0,
    einwilligung_am TEXT,
    einwilligung_zweck TEXT,

    -- Freigabe-Workflow
    status          TEXT    NOT NULL DEFAULT 'neu',  -- neu | freigegeben | abgelehnt
    status_am       TEXT,
    status_notiz    TEXT,

    lat             REAL,
    lon             REAL,

    erstellt_am     TEXT    NOT NULL,
    quelle          TEXT
);
CREATE INDEX IF NOT EXISTS idx_gastro_status ON gastro_bestellungen (status);
CREATE INDEX IF NOT EXISTS idx_gastro_plz    ON gastro_bestellungen (plz);
CREATE UNIQUE INDEX IF NOT EXISTS idx_gastro_email_betrieb
    ON gastro_bestellungen (email, betrieb);

-- Buchungen der Werbepartner
CREATE TABLE IF NOT EXISTS werbebuchungen (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    art             TEXT    NOT NULL DEFAULT 'unternehmen', -- unternehmen | privat
    firma           TEXT    NOT NULL,
    ansprechpartner TEXT    NOT NULL,
    email           TEXT    NOT NULL,
    telefon_enc     TEXT,
    website         TEXT,

    -- Rechnungsdaten, verschluesselt
    rechnung_enc    TEXT,
    ustid_enc       TEXT,
    plz             TEXT,
    ort             TEXT,

    formate         TEXT    NOT NULL,           -- JSON-Liste der Format-IDs
    coupon          INTEGER NOT NULL DEFAULT 0,
    summe_cent      INTEGER NOT NULL DEFAULT 0, -- netto, nach Coupon-Rabatt
    motiv_pfad      TEXT,
    motiv_name      TEXT,
    motiv_groesse   INTEGER,
    motiv_spaeter   INTEGER NOT NULL DEFAULT 0,
    zielurl         TEXT,                       -- gewuenschtes QR-Ziel
    nachricht       TEXT,

    agb_ok          INTEGER NOT NULL DEFAULT 0,
    motivvorbehalt_ok INTEGER NOT NULL DEFAULT 0,
    karte_ok        INTEGER NOT NULL DEFAULT 0,
    datenschutz_ok  INTEGER NOT NULL DEFAULT 0,
    einwilligung_am TEXT,
    einwilligung_zweck TEXT,

    status          TEXT    NOT NULL DEFAULT 'neu',
    status_am       TEXT,
    status_notiz    TEXT,

    lat             REAL,
    lon             REAL,

    erstellt_am     TEXT    NOT NULL,
    quelle          TEXT
);
CREATE INDEX IF NOT EXISTS idx_werbung_status ON werbebuchungen (status);

-- Empfehlungen aus dem Stoerer "Unterstütze Deine Lieblings-Pizzeria!"
CREATE TABLE IF NOT EXISTS pizzeria_empfehlungen (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,
    strasse         TEXT,
    plz             TEXT,
    ort             TEXT,
    hinweis         TEXT,
    melder_email    TEXT,                       -- freiwillig
    status          TEXT    NOT NULL DEFAULT 'neu',  -- neu | kontaktiert | erledigt | abgelehnt
    status_am       TEXT,
    erstellt_am     TEXT    NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_empfehlung_status ON pizzeria_empfehlungen (status);

-- Newsletter mit Double-Opt-in
CREATE TABLE IF NOT EXISTS newsletter (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    email           TEXT    NOT NULL UNIQUE,
    token           TEXT    NOT NULL,
    bestaetigt      INTEGER NOT NULL DEFAULT 0,
    bestaetigt_am   TEXT,
    abgemeldet_am   TEXT,
    einwilligung_zweck TEXT,
    erstellt_am     TEXT    NOT NULL
);

-- Kontaktanfragen
CREATE TABLE IF NOT EXISTS kontaktanfragen (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,
    email           TEXT    NOT NULL,
    betreff         TEXT,
    nachricht       TEXT    NOT NULL,
    datenschutz_ok  INTEGER NOT NULL DEFAULT 0,
    erledigt        INTEGER NOT NULL DEFAULT 0,
    erstellt_am     TEXT    NOT NULL
);

-- QR-Weiterleitungen: alle Codes auf den Kartons laufen ueber uns
CREATE TABLE IF NOT EXISTS qr_redirects (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    code            TEXT    NOT NULL UNIQUE,
    buchung_id      INTEGER REFERENCES werbebuchungen (id) ON DELETE SET NULL,
    ziel_url        TEXT    NOT NULL,
    beschreibung    TEXT,
    aktiv           INTEGER NOT NULL DEFAULT 1,
    gesperrt_am     TEXT,                       -- nach Freigabe ist das Ziel fix
    erstellt_am     TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS qr_klicks (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    redirect_id     INTEGER NOT NULL REFERENCES qr_redirects (id) ON DELETE CASCADE,
    tag             TEXT    NOT NULL,
    stunde          INTEGER NOT NULL,
    besucher_hash   TEXT    NOT NULL            -- tagesrotierend, keine IP
);
CREATE INDEX IF NOT EXISTS idx_qrklicks_tag ON qr_klicks (redirect_id, tag);

-- Cookiefreie Reichweitenmessung
CREATE TABLE IF NOT EXISTS analytics_hits (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    pfad            TEXT    NOT NULL,
    quelle          TEXT,
    besucher_hash   TEXT    NOT NULL,
    tag             TEXT    NOT NULL,
    stunde          INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_hits_tag ON analytics_hits (tag);

-- Tagessummen, auf die nach 30 Tagen verdichtet wird
CREATE TABLE IF NOT EXISTS analytics_tage (
    tag             TEXT    NOT NULL,
    pfad            TEXT    NOT NULL,
    aufrufe         INTEGER NOT NULL DEFAULT 0,
    besuche         INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (tag, pfad)
);

-- Rate-Limit-Zaehler, wird taeglich geleert
CREATE TABLE IF NOT EXISTS rate_limit (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    aktion          TEXT    NOT NULL,
    client_hash     TEXT    NOT NULL,
    erstellt_am     TEXT    NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_rate ON rate_limit (aktion, client_hash, erstellt_am);

-- Schemaversion
CREATE TABLE IF NOT EXISTS migrationen (
    datei           TEXT PRIMARY KEY,
    ausgefuehrt_am  TEXT NOT NULL
);
