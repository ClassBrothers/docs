# Installation per FTP

Für den Fall, dass das CRM auf einem Webpaket laufen soll statt lokal.
Dauer: rund 15 Minuten. Kommandozeile brauchst du nicht.

> **Vorher lesen:** In der Datenbank stehen Akquisedaten, Telefonnotizen und
> Gesprächsinhalte über Dritte. Auf einem Server heißt das: **Passwort setzen ist
> Pflicht**, und die Datenbankdatei darf nicht öffentlich abrufbar sein. Schritt 5
> prüft genau das. Wenn du dir unsicher bist, betreib das Tool lieber lokal —
> es läuft mit `php -S 127.0.0.1:8080 -t public` ohne jede Installation.

---

## Voraussetzungen beim Hoster

| | |
|---|---|
| PHP | **8.1 oder neuer** (bei den meisten Paketen in der Verwaltung umstellbar) |
| Erweiterungen | `zip`, `pdo_sqlite`, `mbstring` — fast immer vorhanden; `curl` nur für die Anreicherung |
| Datenbank | keine. SQLite steckt in PHP, du brauchst **kein** MySQL |
| Speicher | ~2 MB plus Datenbank |

---

## Schritt 1 — Hochladen

Lade den kompletten Ordner `pizza-support-crm` per FTP hoch.

**Am besten:** in ein Verzeichnis **außerhalb** des Webroots (z. B. `/pizza-crm`)
und richte eine Subdomain wie `crm.pizzasupport.de` ein, deren Document Root auf
`/pizza-crm/public` zeigt. Dann liegen Datenbank und Programmcode gar nicht erst
im abrufbaren Bereich.

**Wenn das nicht geht:** Ordner direkt in den Webroot legen, also z. B.
`httpdocs/pizza-crm/`. Erreichbar ist er dann unter
`https://deine-domain.de/pizza-crm/`. Die mitgelieferte `.htaccess` im
Hauptverzeichnis leitet auf `public/` weiter und sperrt `src/`, `bin/`, `data/`
und `templates/`. Schritt 5 sagt dir, ob das greift.

> **FileZilla & Co.:** `.htaccess` und `data/.htaccess` beginnen mit einem Punkt und
> werden von manchen FTP-Programmen ausgeblendet. In FileZilla: Server → *Versteckte
> Dateien anzeigen erzwingen*. Ohne diese beiden Dateien fehlt der Schutz.

## Schritt 2 — Schreibrechte setzen

Im FTP-Programm (FileZilla: Rechtsklick → Dateiattribute):

| Pfad | Rechte |
|---|---|
| `data/` | **755**, falls es später hakt: 775 |
| `config.local.php` | **666** |

`config.local.php` liegt leer bei (`return [];`) — sie muss nur beschreibbar sein,
damit der Assistent das Passwort hineinschreiben kann.

## Schritt 3 — Assistent freischalten

Lege per FTP im Ordner `data/` eine leere Datei mit dem Namen
`setup-erlaubt.txt` an.

Ohne diese Datei verweigert der Assistent den Dienst. Damit kann ihn nur jemand
starten, der FTP-Zugang hat — also du.

## Schritt 4 — Assistent aufrufen

Öffne im Browser:

```
https://deine-domain.de/pizza-crm/setup.php
```

(bei Subdomain-Variante: `https://crm.pizzasupport.de/setup.php`)

Der Assistent führt durch vier Punkte:

1. **Systemcheck** — PHP-Version, Erweiterungen, Schreibrechte. Alles grün? Weiter.
2. **Passwort setzen** — mindestens 8 Zeichen. Danach fragt das Tool bei jedem Aufruf danach.
3. **Absenderdaten** — Firma, Straße, PLZ/Ort, Telefon, E-Mail, Website. Die
   Telefonnummer ist der Call-to-Action im Anschreiben, die lass nicht leer.
4. **Akquiseliste importieren** — die Datei `PizzaSupport_Akquiseliste_Freiburg.xlsx`
   liegt schon in `data/` und steht in der Auswahlliste. Ein Klick, 55 Betriebe drin.

Falls PHP die Konfiguration nicht schreiben darf, zeigt der Assistent den fertigen
Dateiinhalt an — den kopierst du per FTP in `config.local.php`.

## Schritt 5 — Sicherheitsprüfung

Der Assistent prüft selbst, ob die Datenbank über das Web erreichbar ist. Prüf es
zur Sicherheit noch mal von Hand: Ruf im Browser auf

```
https://deine-domain.de/pizza-crm/data/crm.sqlite
```

- **Fehlermeldung 403 oder 404** → gut, so soll es sein.
- **Ein Download startet** → **sofort handeln.** Dann greift die `.htaccess`
  nicht (kein `mod_rewrite`, oder `AllowOverride` ist aus). Lösung: Document Root
  der Subdomain auf `public/` zeigen lassen oder den Ordner aus dem Webroot
  herausnehmen. Bis dahin die Datenbank per FTP löschen.

## Schritt 6 — Assistent wieder sperren

**Nicht vergessen:** `data/setup-erlaubt.txt` per FTP löschen. Danach ist
`setup.php` gesperrt. Wer ganz sichergehen will, löscht `setup.php` gleich mit —
für eine spätere Neueinrichtung lädt man sie einfach wieder hoch.

## Schritt 7 — Loslegen

```
https://deine-domain.de/pizza-crm/
```

Passwort eingeben, fertig.

---

## Danach

**Sicherung.** Deine komplette Datenbank ist die eine Datei `data/crm.sqlite`.
Lade sie regelmäßig per FTP herunter — nach jedem Akquisetag, das dauert Sekunden.
Ein Webpaket ist kein Backup.

**Datenanreicherung.** `bin/enrich.php` braucht die Kommandozeile. Hat dein Paket
SSH, läuft es dort direkt:

```bash
php bin/enrich.php --prio=A
```

Ohne SSH: lokal ausführen (PHP installieren, Ordner herunterladen, Skript laufen
lassen) und die erneuerte `data/crm.sqlite` wieder hochladen. Oder die offenen
Felder einfach beim Telefonieren mitpflegen — bei 55 Betrieben ist das kein
Drama, und du telefonierst sie ohnehin alle durch.

**E-Mail-Versand.** Trag Host, Benutzer und Absenderadresse deines Postfachs in
`config.local.php` unter `smtp` ein. Bis dahin bleibt der Senden-Knopf grau; Texte
lassen sich trotzdem vorbereiten und kopieren. Der Versand bleibt in jedem Fall
auf Einzelmails nach dokumentiertem Vorkontakt beschränkt — siehe README,
Abschnitt § 7 UWG.

**Aktualisieren.** Neue Version drüberladen, aber `config.local.php` und
`data/` dabei **auslassen** — dort stecken Konfiguration und alle Daten.

---

## Wenn etwas klemmt

| Symptom | Ursache | Lösung |
|---|---|---|
| Weiße Seite | PHP-Version zu alt | Beim Hoster auf 8.1+ stellen |
| „Kein Passwort gesetzt … nur von 127.0.0.1" | Schritt 4.2 fehlt | `setup.php` aufrufen und Passwort setzen |
| Verzeichnisliste statt Anmeldung | `mod_rewrite` fehlt | Document Root auf `public/` zeigen lassen |
| „unable to open database file" | `data/` nicht beschreibbar | Rechte auf 775 setzen |
| Import bricht ab | `zip` fehlt | Beim Hoster aktivieren, oder Liste als CSV importieren |
| Umlaute zerschossen im CSV | Excel-Import ohne UTF-8 | Der Export enthält ein BOM — beim Öffnen „UTF-8" wählen |
