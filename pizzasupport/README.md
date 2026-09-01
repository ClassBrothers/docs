# pizzasupport.de

Kampagnen-Website für Pizza Support: kostenlose, werbefinanzierte Pizzakartons
für die Freiburger Gastronomie. Ein Projekt der Class Brothers GmbH mit
gastronomischer Unterstützung der Badischen Entertainment GmbH.

---

## Was hier drin ist

Eine mehrseitige Website mit sechs öffentlichen Seiten, drei Rechtsseiten, vier
Formularen, einer Teilnehmerkarte, einem QR-Weiterleitungsdienst und einem
kleinen Freigabe-Bereich. Kein Framework, keine Build-Kette, keine
Abhängigkeiten aus dem Netz – reines PHP 8 mit SQLite, ausgeliefert von einem
gewöhnlichen Webhosting.

Wer eine Datei ändern will, öffnet sie und ändert sie. Es gibt nichts zu
kompilieren.

---

## Aufbau

    pizzasupport/
    ├── public/               ← DocumentRoot zeigt hierher, sonst nirgendwo
    │   ├── index.php         Front Controller: alle Adressen laufen hier durch
    │   ├── router.php        nur für den eingebauten PHP-Server (Entwicklung)
    │   ├── .htaccess         Rewrites, Caching, Kompression, Zugriffsschutz
    │   └── assets/           CSS, JS, Bilder, Schriften
    ├── app/                  ← außerhalb des Web-Roots
    │   ├── config.php        Preise, Formate, Mengen, Schwellen, Kontaktdaten
    │   ├── bootstrap.php     Start der Anwendung, Hilfsfunktionen
    │   ├── admin.php         Freigabe-Workflow
    │   ├── lib/              DB, Sicherheit, Verschlüsselung, Mail, Validierung
    │   ├── forms/            ein Handler je Formular
    │   ├── views/            Layout, Seiten, Bausteine
    │   └── migrations/       SQL-Schema
    ├── storage/              ← außerhalb des Web-Roots
    │   ├── db/               SQLite-Datei
    │   ├── uploads/          Motive der Werbepartner, nicht über HTTP erreichbar
    │   └── logs/
    ├── bin/                  Kommandozeilen-Werkzeuge
    └── docs/                 nginx-Beispiel

**Alles, was sich fachlich ändert, steht in `app/config.php`.** Preise, die vier
Kartonformate, Mindest- und Höchstmenge, die Schwellenwerte für den Startschuss,
die Firmendaten und der Pfad zur Logodatei. Wer dort etwas ändert, muss keine
Vorlage anfassen.

---

## Einrichten

Voraussetzungen: PHP 8.1 oder neuer mit den Erweiterungen `pdo_sqlite`,
`openssl`, `mbstring` und `fileinfo`. Alle vier sind bei üblichen Hostern
standardmäßig aktiv.

```bash
# 1. Konfiguration anlegen
cp .env.example .env

# 2. Schlüssel erzeugen und die beiden Zeilen in die .env übernehmen
php bin/keygen.php

# 3. Passwort für den Freigabe-Bereich setzen
php bin/adminpass.php "ein-langes-passwort"     # Ausgabe in die .env

# 4. Datenbank anlegen
php bin/migrate.php

# 5. Lokal ansehen
php -S 127.0.0.1:8000 -t public public/router.php
```

Danach läuft die Seite auf <http://127.0.0.1:8000>, der Freigabe-Bereich auf
`/admin`.

### Schriften

`public/assets/fonts/` ist absichtlich leer. Wie die WOFF2-Dateien dorthin
kommen und warum die Seite auch ohne sie vollständig funktioniert, steht in
`public/assets/fonts/LIESMICH.md`.

### Logo

Das Logo liegt als `public/assets/img/logo-pizzasupport.svg` bei. Um es
auszutauschen, die neue Datei in dasselbe Verzeichnis legen und in
`app/config.php` unter `logo` den Pfad sowie die Bildmaße anpassen – eine
Stelle, mehr nicht. Header, Footer und das Vorschaubild für soziale Netzwerke
ziehen von dort.

---

## Deployment

1. Verzeichnis auf den Server spielen, **`public/` als DocumentRoot setzen**.
   Zeigt der DocumentRoot versehentlich auf das Projektverzeichnis, fängt die
   `.htaccess` im Wurzelverzeichnis den Fall ab – verlassen sollte man sich
   darauf nicht.
2. `.env` anlegen (siehe oben). Sie darf nie ins Repository.
3. `php bin/migrate.php` ausführen.
4. Schreibrechte: `storage/` muss für den Webserver-Benutzer beschreibbar sein,
   `app/` und `bin/` nicht.
5. SSL einrichten. Die Seite erzwingt HTTPS und sendet HSTS, sobald sie
   verschlüsselt ausgeliefert wird.
6. Für nginx liegt eine Beispielkonfiguration in `docs/nginx.conf.example`.

### Cronjobs

```cron
# Löschfristen, Verdichtung der Statistik, verwaiste Uploads – täglich nachts
0 3 * * * /usr/bin/php /pfad/zu/pizzasupport/bin/cleanup.php >> /pfad/zu/pizzasupport/storage/logs/cleanup.log 2>&1
```

Die Sitemap braucht keinen Cronjob: `/sitemap.xml` wird bei jedem Abruf aus
`app/config.php` erzeugt.

---

## Kommandozeile

```bash
php bin/migrate.php                          # Schema einspielen
php bin/keygen.php                           # Schlüssel erzeugen
php bin/adminpass.php "passwort"             # Admin-Hash erzeugen
php bin/cleanup.php                          # Löschfristen anwenden
php bin/export.php auskunft mail@example.de  # Auskunft nach Art. 15 DSGVO
php bin/export.php loeschen mail@example.de  # Löschung nach Art. 17 DSGVO
php bin/export.php csv gastro                # Tabelle als CSV
```

---

## Wie die Seite arbeitet

### Adressen

Alle Anfragen laufen über `public/index.php`. Die öffentlichen Adressen enden
auf `.html`, dahinter arbeitet PHP – das ist Absicht und bleibt für Besucher
wie für Suchmaschinen unsichtbar. Getippte Kurzformen (`/kontakt`,
`/index.html`) werden per 301 auf die kanonische Fassung geleitet.

### Startschuss-Prinzip

Produziert wird erst, wenn zwei Schwellen gleichzeitig erreicht sind: genug
teilnehmende Betriebe **und** genug gebuchtes Werbevolumen. Beide Werte stehen
in `app/config.php` unter `startschuss`. Die Fortschrittsanzeige rechnet den
Gesamtstand als den **schwächeren** der beiden Werte – der Balken ist erst voll,
wenn beide Seiten stehen.

Es fließen ausschließlich **freigegebene** Einträge in die Zahlen ein.

### Freigabe-Workflow

Nichts erscheint ungeprüft öffentlich. Jede Bestellung und jede Buchung landet
mit dem Status `neu` in der Datenbank und muss unter `/admin` von Hand
freigegeben werden. Erst dann zählt sie für den Fortschritt und erscheint – bei
entsprechender Einwilligung – auf der Teilnehmerkarte.

### QR-Weiterleitungen

Jeder Code auf einem Karton zeigt auf `pizzasupport.de/r/{code}` und wird von
dort weitergeleitet. Damit bleibt die Hoheit über das Ziel bei uns: Eine
Weiterleitung lässt sich jederzeit abschalten, und wir können dem Inserenten die
Zahl der Scans nennen. Neu angelegte Weiterleitungen sind **inaktiv** und müssen
unter `/admin` freigegeben werden; mit der Freigabe wird das Ziel festgeschrieben
(so steht es auch in den AGB).

Gezählt werden nur Tag, Stunde und eine tagesrotierende Kennung – keine IP.

### Karte

Die Teilnehmerkarte lädt Kacheln von OpenStreetMap und damit von einem fremden
Server. Deshalb wird sie **erst nach ausdrücklicher Zustimmung** geladen; ohne
Zustimmung bleibt die vollständige Liste nutzbar. Google Maps kommt nicht zum
Einsatz, ein API-Schlüssel wird nirgends gebraucht.

Das Kartenmodul (`public/assets/js/karte.js`) ist eigenständig: Es zeichnet
Kacheln, versteht Ziehen, Zoomen und Marker und wiegt einen Bruchteil eines
fertigen Kartenpakets. Wer lieber Leaflet einsetzt, legt `leaflet.js` nach
`public/assets/js/` und bindet es vor `karte.js` ein – das Modul erkennt
`window.L` und tritt dann zurück.

Damit Punkte auf der Karte erscheinen, brauchen die Datensätze Koordinaten in
den Spalten `lat`/`lon`. Diese werden derzeit **nicht automatisch ermittelt** –
siehe „Offene Punkte".

---

## Datenschutz und Sicherheit

- **Verschlüsselung:** Telefonnummern, Rechnungsanschriften und
  Umsatzsteuer-Identifikationsnummern liegen AES-256-GCM-verschlüsselt in der
  Datenbank. Der Schlüssel steht in der `.env`. Wird er getauscht, sind
  bestehende Datensätze nicht mehr lesbar.
- **SQL:** ausschließlich Prepared Statements, keine Zeichenkettenverkettung.
- **XSS:** jede Ausgabe läuft durch `e()`. Eine Content-Security-Policy ohne
  `unsafe-inline` fängt den Rest ab; die beiden nötigen Inline-Blöcke tragen
  einen Nonce.
- **CSRF:** Token in allen Formularen, geprüft im Front Controller.
- **Spam:** Honeypot-Feld plus Zeitprüfung plus serverseitiges Rate-Limit je
  Aktion. Kein externes Captcha, damit keine Einwilligung nötig wird.
- **Uploads:** Typprüfung am Dateiinhalt (nicht an der Endung), 12 MB Grenze,
  Ablage außerhalb des Web-Roots unter zufälligem Namen.
- **Statistik:** cookiefrei, ohne Drittanbieter. Gespeichert wird eine Kennung
  aus IP, geheimem Salt und Datum – eine Einwegberechnung, die sich täglich
  ändert. Die IP selbst wird nie gespeichert. Nach 30 Tagen bleiben nur
  anonyme Tagessummen.
- **Löschkonzept:** Fristen stehen in `app/config.php` unter `aufbewahrung`,
  angewandt von `bin/cleanup.php`.

---

## Vor dem Livegang

- [ ] **Impressum vervollständigen** – die mit `PLATZHALTER` markierten Angaben
      in `app/config.php` durch die Handelsregisterdaten ersetzen.
- [ ] **Rechtstexte anwaltlich prüfen lassen** – Impressum, Datenschutz und AGB
      sind vollständig ausformuliert, aber nicht juristisch abgenommen. In den
      AGB fehlen bewusst zwei Passagen: die Widerrufsbelehrung für Verbraucher
      und die verpackungsrechtlichen Pflichtangaben.
- [ ] **Hosting-Anbieter** in der Datenschutzerklärung eintragen (Abschnitt 3).
- [ ] **Schriftdateien** ablegen, siehe `public/assets/fonts/LIESMICH.md`.
- [ ] **Logo** gegen die Originaldatei tauschen, falls gewünscht.
- [ ] **`.env`** mit frischen Schlüsseln anlegen; die aus der Entwicklung nicht
      übernehmen.
- [ ] **SSL** aktivieren und prüfen, dass HTTPS erzwungen wird.
- [ ] **Backups** einrichten: `storage/db/pizzasupport.sqlite` und
      `storage/uploads/` sind die einzigen Verzeichnisse, die nicht aus dem
      Repository wiederherstellbar sind.
- [ ] **Google Search Console** einrichten und `https://pizzasupport.de/sitemap.xml`
      einreichen.
- [ ] **Verpackungssteuer-Seite gegenprüfen**: Die genannten Beträge folgen dem
      Tübinger Satzungsmodell. Vor Veröffentlichung mit der geltenden Freiburger
      Satzung abgleichen und in `app/config.php` unter `steuer` anpassen.
- [ ] **Schwellenwerte** in `app/config.php` festlegen (aktuell 40 Betriebe und
      60.000 € Werbevolumen als Platzhalter).
- [ ] **Testdaten löschen**: `rm storage/db/pizzasupport.sqlite && php bin/migrate.php`

---

## Offene Punkte

- **Koordinaten für die Karte.** Die Spalten `lat`/`lon` werden beim Absenden
  nicht gefüllt. Für den Anfang lassen sie sich im Admin von Hand nachtragen;
  wenn es mehr Einträge werden, ist eine Anbindung an einen Geocoder
  (Nominatim, mit Zwischenspeicher und Beachtung der Nutzungsbedingungen) der
  nächste Schritt. Einträge ohne Koordinaten erscheinen weiterhin in der Liste,
  nur nicht als Punkt auf der Karte.
- **Zahlung.** Die Anzahlung läuft per Rechnung. Ein Online-Bezahlvorgang ist
  weder eingebaut noch vorbereitet.
- **Kartonformate.** Alle vier Formate (28/30/32/33 cm) stehen zur Auswahl, mit
  dem Hinweis, dass die Erstauflage auf 32 × 32 cm läuft. Reduzieren heißt:
  Zeilen in `app/config.php` unter `karton_formate` löschen.
