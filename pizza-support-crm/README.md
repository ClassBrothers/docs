# Pizza Support — Akquise-CRM

Lokales, visuelles CRM für die Akquise-Phase von **Pizza Support** (Kampagne der Class Brothers GmbH,
Freiburg). Ziel: 50 teilnehmende Pizzerien für den Startschuss.

Kein Framework, keine Composer-Abhängigkeiten: PHP 8.1+ mit SQLite, Vanilla JS im Frontend,
PDF-Erzeugung selbst geschrieben.

---

## Schnellstart

```bash
# 1. Konfiguration anlegen
cp config.example.php config.local.php

# 2. Passwort setzen (Hash in config.local.php eintragen)
php bin/passwort.php "DeinPasswort"

# 3. Akquiseliste importieren
php bin/import.php data/PizzaSupport_Akquiseliste_Freiburg.xlsx

# 4. Starten
php -S 127.0.0.1:8080 -t public
```

Danach im Browser: <http://127.0.0.1:8080>

Ohne gesetzten `passwort_hash` ist das Tool **nur von 127.0.0.1 aus** erreichbar — Absicht,
siehe Abschnitt „Hosting".

---

## Die vier Ansichten

| Ansicht | Wofür |
|---|---|
| **Kanban** | Tagesarbeit. Neun Spalten von „Neu" bis „Gewonnen", Karten per Drag & Drop verschieben (loggt den Wechsel automatisch). |
| **Liste** | Schneller Überblick, sortierbar, Spalten frei zuschaltbar, Basis für den CSV-Export. |
| **Dashboard** | Fortschritt Richtung 50, Conversion-Funnel, Verteilung nach Stufe und Priorität, fällige Wiedervorlagen. |
| **Vorlagen** | Brief- und Mail-Texte bearbeiten, Platzhalter einfügen, Vorschau am ersten Betrieb der Liste. |

**Kartenmarker:** farbiger Punkt = Priorität (A grün, B gold, C rot), 🍕 = Pizza-Hauptfokus,
🍽 = Pizza als Teilangebot. Rot markiert: mehr als 7 Tage kein Kontakt **und** kein Wiedervorlage-Termin.
Gelb markiert: Wiedervorlage ist fällig.

**Detailkarte** (Klick auf eine Karte): alle Stammdaten editierbar, Aktivitäten-Zeitstrahl,
Schnellbuttons für Anruf, Wiedervorlage, Brief, Notiz, E-Mail und Einzeletikett.

---

## Rechtlicher Rahmen — warum es keinen Massenversand gibt

Automatisierte Kalt-E-Mails an Gewerbetreibende ohne bestehende Geschäftsbeziehung sind nach
**§ 7 UWG** unzulässige Werbung — B2B genauso wie B2C. Das Tool ist deshalb bewusst so gebaut:

- **Primärkanal ist Telefon und Brief.** Dafür gibt es Anschreiben-Generator und Etikettendruck.
- **Kein Sammelversand-Knopf.** Es existiert keine API-Aktion, die mehr als eine Mail auf einmal verschickt.
- **Einzelversand erst nach Vorkontakt.** `Mailer::anBetrieb()` bricht ab, wenn `kontaktversuche < 1` —
  das lässt sich im UI nicht umgehen, weil die Prüfung serverseitig sitzt.
- **Sichtbarer Hinweis** in jeder E-Mail-Ansicht und über dem Vorlagen-Editor.

Der Zähler `kontaktversuche` steigt bei protokollierten Anrufen, Briefen, Besuchen und Mails.

---

## Export

**Anschreiben (PDF)** — Kopfleiste „Briefe". Erzeugt für alle Betriebe im aktuellen Filter je eine
DIN-5008-Seite (Form A: Anschriftfeld ab 45 mm, passt in Fensterumschläge DIN lang). Auf Wunsch wird
der Export als Kontaktversuch protokolliert und die Stufe „Neu" auf „Kontaktversuch 1" gesetzt.

**Etiketten (PDF)** — Kopfleiste „Etiketten". Voreingestellt **Avery L7160** (3×7, 63,5 × 38,1 mm).
Ebenfalls hinterlegt: L7163 (2×7) und L7165 (2×4). Weitere Bögen ergänzt man in `PS_ETIKETTEN`
(`src/bootstrap.php`) — reine Millimeterangaben. „Erste freie Position" erlaubt es, angebrochene
Bögen weiterzuverwenden; „Hilfslinien" zeichnet Rahmen für den Testdruck auf Normalpapier.

**CSV** — alle Felder des aktuellen Filters, Semikolon-getrennt mit BOM, öffnet sauber in Excel.

Platzhalter in Vorlagen: `{{Anrede}}`, `{{Name}}`, `{{Straße}}`, `{{PLZ_Ort}}`, `{{PLZ}}`, `{{Ort}}`,
`{{Stadtteil}}`, `{{Inhaber}}`, `{{Telefon}}`, `{{Datum}}` sowie `{{Absender_firma}}`,
`{{Absender_telefon}}`, `{{Absender_web}}` usw. aus der Konfiguration.

`{{Anrede}}` wird aus dem Inhaber-Feld abgeleitet. Ist dort „zu recherchieren", ein Firmenname oder
stehen mehrere Personen drin, greift automatisch „Sehr geehrte Damen und Herren". Bei einem einzelnen
Nachnamen ohne bekanntes Geschlecht wird die Doppelanrede gesetzt — ein falsches „Herr" wäre der
teurere Fehler.

---

## Datenanreicherung

Die Quellliste hat 55 Betriebe, davon 55 ohne E-Mail und 54 ohne Inhaber (Google Places liefert
diese Felder nicht). Das Skript sucht Website und Impressum und liest aus, was öffentlich dasteht:

```bash
php bin/enrich.php --report          # zeigt nur, was offen ist (kein Netzzugriff)
php bin/enrich.php --prio=A          # nur Priorität A
php bin/enrich.php --limit=10        # in Häppchen
php bin/enrich.php --probelauf       # recherchieren, nichts speichern
php bin/enrich.php --id=7            # einzelner Betrieb
```

Regeln: 2 Sekunden Pause zwischen Abrufen, nichts wird geraten. Findet sich nur ein
Lieferplattform-Eintrag (Lieferando, Wolt, Uber Eats) und keine eigene Website, wird der Betrieb als
`kein_impressum` markiert und in der Quellnotiz mit „telefonisch erfragen" vermerkt.

**Realistische Erwartung:** Bei kleinen Pizzerien ist eine öffentliche E-Mail die Ausnahme. Der
bereits geprüfte Beispielfall (Beatzzeria, Wasserstraße 10 — Inhaber über Lieferando-Impressum
verifiziert, aber keine Mailadresse, nur Kontaktformular) ist der Normalfall. Rechne mit einer
Trefferquote um ein Drittel; der Rest läuft über das Telefon, wofür das Tool ohnehin gebaut ist.

---

## Import

```bash
php bin/import.php <datei.xlsx|datei.csv> [--blatt=Akquiseliste] [--kampagne=pizzasupport] [--probelauf]
```

Der Import ist wiederholbar: Betriebe werden über Name + Straße erkannt. Bei bereits vorhandenen
Einträgen werden nur die Stammdaten aufgefrischt — Pipeline-Stufe, Kontaktversuche, Notizen und
Aktivitäten bleiben unangetastet.

Übernommen wird 1:1 die Spaltenstruktur des Blatts „Akquiseliste". `zu recherchieren` wird als
leeres Feld gespeichert und der Betrieb auf `anreicherung_status = offen` gesetzt. Die Textspalte
„Geschätztes Pizzenvolumen/Monat" landet unverändert in `pizzenvolumen_text`; zusätzlich wird ein
Zahlenwert für Sortierung und Summen abgeleitet (Bereich → Mittelwert, „<250" → 150, „3.000+" → 3450)
und die Klasse (Sehr hoch/Hoch/Mittel/…) separat gespeichert.

---

## Konfiguration (`config.local.php`)

| Schlüssel | Bedeutung |
|---|---|
| `passwort_hash` | Aus `php bin/passwort.php "…"`. Leer = nur 127.0.0.1. |
| `db_pfad` | Pfad zur SQLite-Datei. Standard `data/crm.sqlite`. |
| `ziel_gewonnen` | Startschuss-Schwelle, Standard 50. |
| `etikettenformat` | `L7160`, `L7163` oder `L7165`. |
| `absender` | Firma, Straße, PLZ/Ort, Telefon, E-Mail, Web — für Briefkopf, Fußzeile und Platzhalter. |
| `smtp` | Host, Port, Benutzer, Passwort, Sicherheit, Absenderadresse. |

**Vor dem ersten Briefdruck ausfüllen:** `absender.strasse`, `absender.plz_ort`, `absender.telefon`
und `absender.email`. Die Briefvorlage nennt die Telefonnummer als Call-to-Action — bleibt sie leer,
steht im Brief eine leere Zeile.

Alle SMTP-Werte lassen sich auch per Umgebungsvariable setzen (`PS_SMTP_HOST`, `PS_SMTP_USER`,
`PS_SMTP_PASS`, …) — Zugangsdaten müssen nicht in die Datei.

---

## Getroffene Annahmen (offene Fragen aus dem Brief)

**1. Hosting.** Rein lokal, nicht auf dem Webspace von pizzasupport.de. Die Datei enthält
Akquisedaten, Telefonnotizen und Gesprächsinhalte über Dritte — die haben auf einem öffentlich
erreichbaren Server nichts verloren. Umgesetzt: Datenbank außerhalb des Webroots, `data/.htaccess`
sperrt den Ordner zusätzlich, `noindex`-Header auf allen Seiten, Session-Login mit CSRF-Schutz auf
schreibenden Aktionen. Ohne gesetztes Passwort läuft das Tool nur auf 127.0.0.1. Soll es doch auf
den Server: Passwort setzen, HTTPS erzwingen, `data/` außerhalb des Webroots legen.

**2. Etikettenformat.** Avery **L7160** als Standard (3×7, 63,5 × 38,1 mm — der gängigste Bogen).
L7163 und L7165 sind mit hinterlegt, Wechsel im Export-Dialog. Falls du einen anderen Bogen zu Hause
hast: Maße in `PS_ETIKETTEN` ergänzen, das sind fünf Zahlen.

**3. SMTP-Postfach.** Nichts fest hinterlegt. Bis Host und Absenderadresse konfiguriert sind, ist der
Sende-Knopf deaktiviert — Text lässt sich trotzdem vorbereiten und in die Zwischenablage kopieren.
Empfehlung: eine Adresse unter der Kampagnendomain, damit Antworten nicht im Agenturpostfach
untergehen.

**4. Wiederverwendbarkeit für andere Ventures.** Ja, vorbereitet. Jeder Betrieb trägt ein Feld
`kampagne` (Standard `pizzasupport`). Für BVMW- oder Kreishandwerkerschafts-Kontakte genügt
`php bin/import.php liste.csv --kampagne=bvmw` — dieselbe Datenbank, dieselben Vorlagen, saubere
Trennung. Der Kampagnenwechsel im UI ist noch nicht verdrahtet (aktuell fest `pizzasupport`); das ist
eine Auswahlliste in der Kopfzeile, sobald die zweite Kampagne wirklich ansteht.

---

## Aufbau

```
pizza-support-crm/
├── bin/
│   ├── import.php          Excel-/CSV-Import
│   ├── enrich.php          Datenanreicherung (Impressum-Recherche)
│   └── passwort.php        Passwort-Hash erzeugen
├── public/
│   ├── index.php           App-Rahmen
│   ├── login.php           Anmeldung
│   ├── api.php             JSON-API
│   ├── export.php          PDF- und CSV-Export
│   └── assets/             app.css, app.js
├── src/
│   ├── bootstrap.php       Konfiguration, Konstanten, Autoload
│   ├── Db.php              PDO/SQLite
│   ├── schema.sql          Tabellen
│   ├── Repo.php            Betriebe und Aktivitäten
│   ├── Importer.php        Spaltenzuordnung, Volumen-Auswertung
│   ├── Xlsx.php            XLSX-Leser (ZipArchive + SimpleXML)
│   ├── Vorlagen.php        Vorlagen und Platzhalter
│   ├── Pdf.php             PDF-Schreiber
│   ├── BriefRenderer.php   DIN-5008-Anschreiben
│   ├── EtikettenRenderer.php
│   ├── Mailer.php          SMTP-Einzelversand
│   ├── Anreicherung.php    Impressum-Recherche
│   ├── Statistik.php       Dashboard-Kennzahlen
│   └── Auth.php            Login, Session, CSRF
├── templates/              Ausgangstexte für Brief und Mail
└── data/                   SQLite-Datei, Quell-Excel (nicht im Repo)
```

## Design

Trattoria-Designsystem wie pizzasupport.de: Fraunces für Überschriften (über Google Fonts, Fallback
Georgia), San-Marzano-Rot `#a62a24`, Basilikum-Grün `#4a7c42`, Olivgold `#b08d3e` auf Papierweiß.
Bewusst nüchterner als die Kampagnenseite — es ist ein Arbeitswerkzeug.

Die PDFs nutzen Helvetica: Fraunces ließe sich nur mit eingebetteter Schriftdatei erzeugen, und die
Standard-14-Schriften halten die PDF-Erzeugung abhängigkeitsfrei. Der Briefkopf trägt die
Kampagnenfarben, das genügt für ein Anschreiben.
