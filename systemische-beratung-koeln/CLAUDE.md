# Briefing für Claude Code: Infoportal „Systemische Beratung Köln“

> Kopiere diese Datei als `CLAUDE.md` in einen leeren Projektordner und starte Claude Code dort mit:
> „Lies CLAUDE.md und setze das Projekt Schritt für Schritt um. Arbeite die Abnahme-Checkliste am Ende vollständig ab.“

---

## 1. Ausgangslage und Ziel

- Domain: `systemische-beratung-koeln.com`
- Bisher: WordPress (Theme Salient + WPBakery), halbfertig, mit Demo-Resten. Die Inhaberin, Nina (systemische Beraterin), startet ihre Praxis erst in ca. 12 Monaten.
- Neues Ziel: eine **unabhängige, hilfreiche Informationsseite** zum Thema systemische Beratung in Köln und Umgebung. Sie soll die Domain jetzt zum Thema ranken lassen. Beim Praxisstart werden die Leistungsseiten mit Ninas Angebot gefüllt.
- Die Seite verkauft **nichts**. Sie informiert, ordnet ein und zeigt, wo man in Köln Hilfe findet. Vertrauen schlägt Werbung.

**Prioritäten (in dieser Reihenfolge):**
1. Hilfreicher Inhalt (Helpful Content)
2. GEO-first: Antworten so aufbereiten, dass KI-Suchen (Google AI Overviews, ChatGPT, Perplexity) sie zitieren
3. Mobile first
4. Lokaler Fokus: Köln + 30 km
5. Datenschutz ohne Kompromisse

---

## 2. Tech-Stack (verbindlich)

- **Node.js 22 LTS**, Paketmanager npm
- **Astro** (aktuelle Version), Ausgabe `output: 'static'`, kein SSR
- Reines CSS mit Custom Properties (kein Tailwind, kein UI-Framework). Eine globale `global.css` plus komponentennahe Styles.
- Kein clientseitiges JavaScript, außer wenn zwingend nötig (z. B. Menü-Toggle; Lösung bevorzugt mit `<details>` oder CSS)
- Integrationen: `@astrojs/sitemap`
- Bilder: `astro:assets`, WebP/AVIF, `width`/`height` gesetzt, `loading="lazy"` außer im Hero
- Build-Ergebnis in `dist/`, Upload auf den bestehenden Webspace bei **ALL-INKL.COM** (Apache, daher `.htaccess` mitliefern). Übertragung nur verschlüsselt: SFTP, falls der Tarif SSH bietet, sonst FTPS (FTP über TLS). Niemals unverschlüsseltes FTP.
- SSL-Zertifikat: Let's Encrypt über das KAS (Kundenadministrationssystem) aktivieren

### Schriften: lokal, niemals von Google laden
- Pakete: `@fontsource-variable/fraunces` (Überschriften) und `@fontsource-variable/source-sans-3` (Fließtext)
- Nur Subset `latin`, nur benötigte Achsen/Schnitte
- `font-display: swap`, Preload nur für die Fließtextschrift
- Prüfen nach dem Build: In `dist/` darf **kein** Verweis auf `fonts.googleapis.com` oder `fonts.gstatic.com` stehen (per `grep -r` testen).

### Datenschutz (DSGVO/TDDDG)
- **Keine** externen Ressourcen: keine Google Fonts, kein Google Maps, keine YouTube-Embeds, kein CDN, keine Social-Plugins, kein reCAPTCHA
- **Kein Tracking, keine Cookies** → kein Cookie-Banner nötig. (Falls später Statistik gewünscht: nur cookielose, selbst gehostete Lösung, separat freigeben lassen.)
- **Kein Kontaktformular.** Kontakt nur per `mailto:`-Link. Keine Daten werden auf dem Server verarbeitet.
- Externe Links: `rel="noopener"`, kein `target="_blank"`-Zwang
- Karten: statt Embed ein Link „In Karte öffnen“ (OpenStreetMap)
- Datenschutzerklärung passend zu genau diesem Setup schreiben: nur Server-Logfiles, keine Cookies, keine Dritten. Hoster und Auftragsverarbeiter: **ALL-INKL.COM – Neue Medien Münnich**, Hauptstraße 68, 02742 Friedersdorf. Anschrift vor Veröffentlichung im aktuellen Impressum von all-inkl.com prüfen.
- Im KAS die **IP-Anonymisierung der Logfiles** aktivieren und die Speicherdauer in der Datenschutzerklärung korrekt angeben (Wert im KAS nachsehen, als TODO markieren).
- Mit ALL-INKL einen Vertrag zur Auftragsverarbeitung (AVV) im KAS abschließen, falls noch nicht geschehen (TODO für Seb).
- Security-Header: siehe Abschnitt 9a

---

## 3. Zielgruppe und Ansprache

- **Primär Frauen zwischen 22 und 62 Jahren** in Köln und Umgebung. Sie suchen für sich, ihre Partnerschaft oder ihre Familie Orientierung. Häufig sind sie diejenigen, die in der Familie den ersten Schritt machen.
- Typische Lebenslagen:
  - berufliche Neuorientierung, Erschöpfung
  - Trennung, Konflikte in der Partnerschaft
  - Patchwork, Pubertät der Kinder
  - Pflege der Eltern, Wechseljahre als Umbruchphase
  - Rückkehr nach der Elternzeit
- Diese Leserinnen sind informiert, haben wenig Zeit, lesen oft abends auf dem Handy und wollen ernst genommen werden.
- **Anrede: „Sie“.** Das Portal ist neutral und informierend. Zum Praxisstart kann auf „Du“ umgestellt werden; dafür alle Texte in `src/content/` halten.
- Sprache: gendersensibel, unaufgeregt (z. B. „Beraterinnen und Berater“, „Klientinnen und Klienten“; kein Sternchen-Stakkato in jedem Satz).

---

## 4. Schreibregeln (wichtig)

**So schreiben:**
- Wie eine erfahrene Beraterin, die einer Freundin etwas erklärt: ruhig, konkret, ehrlich
- Erst die Antwort, dann die Erklärung. Jeder Abschnitt beginnt mit 1–2 Sätzen, die die Frage direkt beantworten (GEO).
- Kurze Absätze (max. 3–4 Sätze), konkrete Beispiele aus dem Alltag, klare Zahlen, wo es sie gibt
- Ehrlich über Grenzen: Was Beratung leisten kann und was nicht
- Lösungsorientiert: Jeder Text endet mit einem konkreten nächsten Schritt für die Leserin

**Verbotene Formulierungen und Muster (typisch KI):**
- „In der heutigen schnelllebigen Zeit“, „Tauchen Sie ein“, „Entdecken Sie“, „Es ist wichtig zu beachten“, „Zusammenfassend lässt sich sagen“, „spielt eine entscheidende Rolle“, „ganzheitlicher Ansatz“ (höchstens einmal pro Seite), „Reise zu sich selbst“, „Schlüssel zu“, „nicht nur …, sondern auch“ als Dauerschablone
- Dreierlisten aus Adjektiven („einfühlsam, kompetent und individuell“)
- Gedankenstrich-Ketten, rhetorische Fragen als Absatzanfang in Serie, Emojis
- Leere Versprechen: „Wir lösen Ihre Probleme“, „Sie werden sich besser fühlen“

**Rechtlich sauber (Heilmittelwerbegesetz, Heilpraktikergesetz):**
- Keine Heilversprechen, keine Erfolgsgarantien, keine Diagnosen
- Klare Trennung: **Beratung** (ohne Krankheitswert) vs. **Psychotherapie** (Behandlung psychischer Erkrankungen durch approbierte Psychotherapeutinnen/-therapeuten oder Heilpraktiker für Psychotherapie)
- Bei Hinweisen auf psychische Erkrankungen immer auf ärztliche/psychotherapeutische Abklärung verweisen

**Ton-Referenz (so soll es klingen):**
> Systemische Beratung schaut nicht nur auf ein Problem, sondern auf die Menschen und Beziehungen drumherum. Wenn es zu Hause ständig kracht, liegt das selten an einer einzelnen Person. Oft haben sich Muster eingeschliffen, die alle Beteiligten kennen und trotzdem nicht loswerden. Eine Beraterin hilft, diese Muster sichtbar zu machen. Die Lösung entwickeln Sie selbst – mit Unterstützung von außen.

---

## 5. Design

### Farben (warm, ruhig, erwachsen, nicht verspielt)
Als Custom Properties in `:root` anlegen:

| Token | Wert | Verwendung |
|---|---|---|
| `--bg` | `#FBF7F3` | Seitenhintergrund (warmes Creme) |
| `--surface` | `#FFFFFF` | Karten, Infoboxen |
| `--surface-rose` | `#F3E6E1` | Hervorgehobene Bereiche (zartes Rosé) |
| `--surface-sage` | `#E6EDE8` | Hilfe-/Kontaktboxen (helles Salbei) |
| `--text` | `#2F2A2C` | Fließtext |
| `--text-muted` | `#5E5659` | Nebentext |
| `--primary` | `#4E6B5F` | Links, Buttons (Salbeigrün dunkel) |
| `--primary-hover` | `#3D564C` | Hover |
| `--accent` | `#A8584D` | Akzente, Zitate, kleine Highlights (Terrakotta-Rosé) |
| `--line` | `#E4D9D2` | Trennlinien |
| `--crisis` | `#8C2F39` | Rahmen der Krisen-Box |

- **Alle Kombinationen auf WCAG 2.2 AA prüfen** (Fließtext ≥ 4,5:1). Werte notfalls abdunkeln, Charakter beibehalten.
- Dark Mode: per `prefers-color-scheme` mit entsprechend abgeleiteten Tönen

### Typografie
- Überschriften: Fraunces, Gewicht 500–600, leicht reduzierter Zeilenabstand
- Fließtext: Source Sans 3, **mind. 18 px** mobil, Zeilenhöhe 1,65, max. 68 Zeichen Zeilenlänge
- Fluid Type mit `clamp()`

### Layout (mobile first)
- Basis 360 px, Seitenabstand 20 px, keine horizontale Scrollbar
- Touch-Ziele ≥ 44 × 44 px, Buttons volle Breite auf Mobil
- Großzügiger Weißraum, runde Ecken (12 px), keine harten Schatten
- Navigation: max. 5 Punkte, mobil als `<details>`-Menü
- Bilder: ruhige, echte Fotos (Kölner Orte, Hände, Tasse Tee, Fensterlicht). **Keine KI-generierten Personenbilder, keine Stock-Lachen.** Platzhalter klar als TODO kennzeichnen.
- Performance-Ziel: Lighthouse mobil ≥ 95 in allen vier Kategorien, LCP < 2 s

---

## 6. Seitenstruktur und URLs

**Wichtig:** Die alten WordPress-Slugs bleiben erhalten. Dadurch bleiben eventuell indexierte URLs gültig, und zum Praxisstart werden genau diese Seiten zu Ninas Leistungsseiten.

| URL | Titel (H1) | Inhalt / Suchintention |
|---|---|---|
| `/` | Systemische Beratung in Köln: Was sie ist, was sie kostet und wo Sie Hilfe finden | Pillar-Seite, Überblick, Sprungmarken, Kurzantworten, Krisen-Box |
| `/systemische-beratung/` | Was ist systemische Beratung? | Definition, Grundannahmen, Methoden (Genogramm, zirkuläre Fragen, Systemaufstellung, Reframing) in Alltagssprache, Herkunft, Wikipedia-Link |
| `/systemische-einzelberatung-koeln/` | Systemische Einzelberatung in Köln | Für wen, typische Anlässe (Beruf, Entscheidungen, Lebensumbrüche), Ablauf, Dauer, Kosten |
| `/systemische-paarberatung-koeln/` | Systemische Paarberatung in Köln | Wann sinnvoll, was in einer Sitzung passiert, was, wenn der Partner nicht mitkommt, Trennungsberatung |
| `/systemische-familienberatung-koeln/` | Systemische Familienberatung in Köln | Pubertät, Patchwork, Trennung mit Kindern, Pflege von Angehörigen; kostenlose Erziehungs- und Familienberatungsstellen |
| `/beratung-oder-therapie/` | Systemische Beratung oder Therapie – was ist der Unterschied? | Abgrenzung, wer was darf, Kassenleistung, Entscheidungshilfe |
| `/kosten/` | Was kostet systemische Beratung in Köln? | Preisspannen Selbstzahler (selbst recherchieren, Quelle nennen), kostenlose Angebote, Kasse bei Therapie, Steuer (nur als Hinweis, keine Steuerberatung) |
| `/hilfe-in-koeln/` | Beratungsstellen und Hilfe in Köln und Umgebung | Verzeichnis kostenloser und öffentlicher Anlaufstellen, nach Thema gegliedert, plus Umland |
| `/beraterin-finden/` | Gute systemische Beraterin finden: Darauf sollten Sie achten | Qualifikation (DGSF/SG-zertifiziert), Erstgespräch, Bauchgefühl, Fragen fürs Erstgespräch |
| `/ueber-mich/` | Über dieses Portal | Wer schreibt, warum, redaktionelle Grundsätze, Hinweis „Eigenes Beratungsangebot ab 2027“ |
| `/kontakt/` | Kontakt | mailto, Hinweis: keine Beratung per E-Mail, in Krisen → Krisen-Box |
| `/impressum/`, `/datenschutz/` | — | Pflichtseiten |

### Weiterleitungen (`.htaccess`)
- `/arbeitsweise/` → 301 auf `/systemische-beratung/`
- `/x-material-notizen/` → **410 Gone**
- WordPress-Pfade (`/wp-admin/`, `/wp-login.php`, `/feed/`, `/?p=…`) → 410
- Erzwingen: HTTPS, ohne `www`, mit Trailing Slash

---

## 7. Inhaltliche Leitplanken und Fakten

### Fakten (vor Veröffentlichung gegen die Quelle prüfen)
- Systemische Therapie ist für Erwachsene seit 2020 Leistung der gesetzlichen Krankenversicherung. Quelle: G-BA.
- Für Kinder und Jugendliche hat der G-BA im Januar 2024 die Aufnahme beschlossen; der Beschluss ist seit 12.04.2024 in Kraft. **Aktuellen Versorgungsstand (EBM) prüfen** und korrekt formulieren. Quelle: g-ba.de
- Systemische **Beratung** (ohne Krankheitswert) ist **keine** Kassenleistung und wird selbst bezahlt. Kostenlose Alternativen: kommunale und kirchliche Familien- und Erziehungsberatungsstellen.
- Die DGSF (Deutsche Gesellschaft für Systemische Therapie, Beratung und Familientherapie) hat ihren Sitz in Köln – guter lokaler Bezug.

### Lokaler Fokus Köln + 30 km
- Kölner Stadtbezirke natürlich einbauen, wo es passt (Innenstadt, Ehrenfeld, Nippes, Lindenthal, Rodenkirchen, Porz, Kalk, Mülheim, Chorweiler)
- Umland auf `/hilfe-in-koeln/` mit eigenem Abschnitt „Rund um Köln“:
  - Leverkusen
  - Bergisch Gladbach, Rösrath, Overath, Odenthal
  - Frechen, Hürth, Brühl, Wesseling, Pulheim, Bergheim, Kerpen, Erftstadt
  - Dormagen
  - Troisdorf, Niederkassel
  - Bonn
- Je Umland-Kreis: Link zur jeweiligen Familien-/Erziehungsberatung des Kreises bzw. der Stadt (Rhein-Erft-Kreis, Rheinisch-Bergischer Kreis, Rhein-Sieg-Kreis, Rhein-Kreis Neuss, Leverkusen, Bonn)
- **Keine Doorway-Seiten** pro Stadt. Ein guter Abschnitt statt 20 dünner Seiten.

### Linkverzeichnis (jeden Link vor dem Einbau aufrufen und prüfen; tote oder umgezogene Links ersetzen)

**Grundlagen**
- Wikipedia: „Systemische Therapie“ und „Systemische Beratung“ (falls eigener Artikel vorhanden)
- DGSF – dgsf.org (inkl. Suche nach zertifizierten Beraterinnen und Beratern)
- Systemische Gesellschaft (SG) – systemische-gesellschaft.de
- Gemeinsamer Bundesausschuss – g-ba.de (Psychotherapie-Richtlinie)
- Bundespsychotherapeutenkammer – bptk.de (Infos für Patientinnen)

**Köln, öffentlich und kostenlos**
- Stadt Köln: Übersicht Familienberatungsstellen – `stadt-koeln.de/leben-in-koeln/familie/familienberatungsstellen`
- Caritas Köln: Familien- und Erziehungsberatung (u. a. Porz, Südstadt, Internationale Familienberatung)
- pro familia Köln
- Kinderschutzbund Köln / Kinderschutz-Zentrum
- Familienberatung der Christlichen Sozialhilfe Köln
- Diakonie Köln: Ehe-, Familien- und Lebensberatung
- Sozialpsychiatrischer Dienst der Stadt Köln (Gesundheitsamt)
- Frauenberatungsstellen in Köln (z. B. für Frauen in Trennung oder bei Gewalt)

**Psychotherapie finden**
- 116 117 – Terminservicestelle der Kassenärztlichen Vereinigung (Psychotherapeutische Sprechstunde)
- KV Nordrhein: Arztsuche

**Krisen-Box (auf jeder Seite im Footer, auf der Startseite gut sichtbar)**
- Akute Gefahr: Notruf **112**
- TelefonSeelsorge: **0800 111 0 111** und **0800 111 0 222**, rund um die Uhr, kostenlos, anonym; Chat auf telefonseelsorge.de
- Hilfetelefon Gewalt gegen Frauen: **116 016** (rund um die Uhr, mehrsprachig)
- Nummer gegen Kummer: Kinder und Jugendliche **116 111**, Elterntelefon **0800 111 0 550**
- Ärztlicher Bereitschaftsdienst: **116 117**
- Nummern als `tel:`-Links. Alle Nummern vor Veröffentlichung auf der jeweiligen offiziellen Seite verifizieren.

---

## 8. GEO und SEO

**Struktur jeder Seite:**
1. H1 mit Hauptkeyword + Köln (wo passend)
2. „Kurz gesagt“-Box: 2–3 Sätze Direktantwort
3. Inhaltsverzeichnis mit Sprungmarken (ab 3 H2)
4. H2 als echte Fragen formulieren, wie Menschen sie stellen („Wie viele Sitzungen braucht man?“)
5. Jede H2 startet mit einer zitierfähigen Antwort in 40–60 Wörtern
6. FAQ-Block (4–6 Fragen) am Ende
7. „Stand: [Monat Jahr] · Redaktion: [Name]“ sichtbar, mit Datum der letzten inhaltlichen Prüfung
8. Quellenliste am Seitenende

**Strukturierte Daten (JSON-LD):**
- Global: `WebSite`, `Organization` (Betreiberin laut Impressum)
- Pro Seite: `WebPage` bzw. `Article` mit `author`, `datePublished`, `dateModified`, `about`, `areaServed` (Köln + Umland)
- `FAQPage` für FAQ-Blöcke, `BreadcrumbList`
- **Kein `LocalBusiness`**, solange es kein Angebot gibt

**Technik:**
- Title max. 60 Zeichen, Meta-Description 140–155 Zeichen, je Seite einzigartig
- Canonical, `lang="de"`, Open-Graph-Tags (eigenes OG-Bild 1200×630, lokal)
- `robots.txt` mit Sitemap-Verweis; KI-Crawler (GPTBot, ClaudeBot, PerplexityBot, Google-Extended) ausdrücklich **erlauben**
- `/llms.txt` mit Kurzbeschreibung des Portals und Liste der wichtigsten Seiten
- Interne Verlinkung: Jede Unterseite verlinkt auf die Startseite und 2–3 thematisch passende Seiten, mit beschreibenden Ankertexten
- Semantisches HTML, saubere Überschriftenhierarchie, Alt-Texte

**Keyword-Cluster (Ausgangspunkt; Volumen vor dem Schreiben prüfen):**
- systemische Beratung Köln
- systemische Therapie Köln / Kosten / Krankenkasse
- Paarberatung Köln, Eheberatung Köln, Trennungsberatung Köln
- Familienberatung Köln, Erziehungsberatung Köln kostenlos
- systemischer Coach Köln, Lebensberatung Köln
- Unterschied systemische Beratung und Therapie
- Was macht man in einer systemischen Beratung

---

## 9. Projektstruktur

```
/
├─ CLAUDE.md
├─ astro.config.mjs        # site: 'https://systemische-beratung-koeln.com', trailingSlash: 'always'
├─ public/
│  ├─ .htaccess
│  ├─ robots.txt
│  ├─ llms.txt
│  └─ favicon.svg
├─ src/
│  ├─ styles/global.css
│  ├─ layouts/BaseLayout.astro    # Head, SEO, JSON-LD, Header, Footer mit Krisen-Box
│  ├─ components/                  # KurzGesagt, Toc, Faq, LinkCard, CrisisBox, Breadcrumbs
│  ├─ content/                     # Seiteninhalte als Markdown/MDX mit Frontmatter
│  └─ pages/                       # Routen gemäß Tabelle in Abschnitt 6
└─ package.json                     # Scripts: dev, build, preview, check
```

Inhalte in Content Collections mit Frontmatter (`title`, `description`, `h1`, `updated`, `faq[]`, `sources[]`), damit Texte später ohne Code-Kenntnisse angepasst werden können.

---

## 9a. Sicherheit (Härtung gegen Angriffe)

Grundprinzip: **Was nicht da ist, kann nicht gehackt werden.** Eine statische Seite ohne PHP, Datenbank und Login hat fast keine Angriffsfläche. Die größten Risiken sind WordPress-Reste, schwache Zugangsdaten und eine falsch konfigurierte `.htaccess`.

### WordPress vollständig entfernen
- Vor dem Deploy: Komplett-Backup (Dateien + Datenbank) **lokal und offline** sichern, nicht im Webroot liegen lassen
- Webroot danach **vollständig leeren**: keine `wp-*`-Ordner, keine `xmlrpc.php`, keine `.php`-Dateien, keine alten Backups (`*.zip`, `*.sql`, `*.bak`)
- WordPress-Datenbank im KAS löschen oder exportieren und löschen, sobald das Backup geprüft ist
- Falls die WP-Version weiter erreichbar sein soll: eigene Subdomain (z. B. `dev.`), **htaccess-Passwortschutz** über das KAS, `noindex`-Header, WordPress, Theme und alle Plugins aktualisieren, nicht benötigte Plugins löschen. Empfehlung: lieber nur das Offline-Backup behalten und die Subdomain nicht betreiben.

### `.htaccess`: verbindliche Regeln
```apache
# Keine Verzeichnisauflistung, keine Server-Signatur
Options -Indexes -MultiViews
ServerSignature Off

# PHP und Skripte im Webroot komplett blockieren
<FilesMatch "\.(php|phtml|php\d|phar|cgi|pl|py|sh|asp|aspx|jsp)$">
  Require all denied
</FilesMatch>

# Versteckte Dateien und Projektdateien blockieren (.git, .env, .htpasswd …)
<FilesMatch "^\.">
  Require all denied
</FilesMatch>
<FilesMatch "\.(md|json|lock|yml|yaml|ini|log|sql|bak|zip|tar|gz|env|config)$">
  Require all denied
</FilesMatch>
# Ausnahme für llms.txt und security.txt (liegen als .txt vor, daher nicht betroffen)

# Typische WordPress-Angriffspfade sofort beenden
RewriteEngine On
RewriteRule ^(wp-admin|wp-includes|wp-content|wp-json)(/.*)?$ - [G,L]
RewriteRule ^(wp-login\.php|xmlrpc\.php|wp-config\.php)$ - [G,L]

# Nur GET und HEAD zulassen
RewriteCond %{REQUEST_METHOD} !^(GET|HEAD)$
RewriteRule .* - [F,L]
```
Reihenfolge der Regeln mit den Weiterleitungen aus Abschnitt 6 abstimmen und nach dem Deploy jede Regel testen (siehe Checkliste).

### Security-Header
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (Preload erst nach 4 Wochen stabilem Betrieb prüfen)
- `Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'none'; object-src 'none'; upgrade-insecure-requests`
  - Astro so konfigurieren, dass **keine Inline-Skripte und Inline-Styles** nötig sind (`build.inlineStylesheets: 'never'`). JSON-LD ist ein Datenblock und wird nicht ausgeführt; falls die CSP ihn dennoch meldet, per SHA-256-Hash freigeben, **niemals** `'unsafe-inline'`.
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()`
- `Cross-Origin-Opener-Policy: same-origin`, `Cross-Origin-Resource-Policy: same-origin`
- Header `X-Powered-By` entfernen
- Ziel: Note **A+** bei securityheaders.com und Mozilla Observatory

### Lieferkette (npm)
- Nur notwendige Pakete, `package-lock.json` committen, Installation per `npm ci`
- `npm audit --omit=dev` muss ohne High/Critical durchlaufen
- Keine Secrets im Repository; Zugangsdaten nur in einer lokalen `.env`, die in `.gitignore` steht und **nie** hochgeladen wird
- Falls GitHub genutzt wird: privates Repo, Dependabot-Alerts aktivieren

### Zugänge bei ALL-INKL (Aufgaben für Seb, als TODO ausgeben)
- KAS-Login mit **Zwei-Faktor-Authentifizierung** absichern
- Eigenen FTP/SSH-Benutzer nur für diese Domain anlegen, auf das Domain-Verzeichnis beschränkt, langes Zufallspasswort (Passwortmanager)
- Alte FTP-Benutzer und Datenbank-Benutzer aus der WordPress-Zeit löschen
- E-Mail-Postfächer der Domain: starke Passwörter, nicht genutzte Postfächer löschen

### Deploy-Script
- `scripts/deploy.sh` mit `lftp mirror --reverse --delete` über SFTP bzw. FTPS, Zugangsdaten aus `.env`
- Vorher automatisch: `npm ci`, `npm run build`, Linkcheck, Prüfung, dass `dist/` keine `.php`, `.env`, `.map`- oder `.md`-Dateien enthält
- `--delete` sorgt dafür, dass keine Altlasten auf dem Server liegen bleiben

### Weitere Maßnahmen
- `/.well-known/security.txt` (RFC 9116) mit Kontakt-E-Mail und Ablaufdatum
- Keine Source Maps im Produktions-Build
- Keine E-Mail-Adresse im Klartext im HTML (einfache Verschleierung per HTML-Entities, kein JavaScript)
- Monitoring: kostenloser Uptime-Check (z. B. UptimeRobot) auf Startseite; Google Search Console prüft zusätzlich auf Malware und manuelle Maßnahmen

---

## 10. Offene Punkte (als `TODO:` im Code markieren, nicht erfinden)

- Impressum: Betreiberin (Nina oder Class Brothers GmbH), Anschrift, Kontakt
- Ninas voller Name und Qualifikation für die Redaktionszeile (nur nach Freigabe)
- Speicherdauer der Logfiles bei ALL-INKL (im KAS nachsehen)
- AVV mit ALL-INKL, 2FA im KAS, neuer FTP/SSH-Benutzer (siehe 9a)
- Echte Fotos
- Kontakt-E-Mail-Adresse

Footer-Credit beibehalten: „Umsetzung: Class Brothers GmbH“ mit Link auf `https://class-brothers.com/seo-agentur`.

---

## 11. Abnahme-Checkliste (Claude Code arbeitet sie vor Abschluss ab)

- [ ] `npm run build` läuft fehlerfrei, `astro check` ohne Fehler
- [ ] `grep -ri "googleapis\|gstatic" dist/` liefert **nichts**
- [ ] Keine externen Requests außer Links (Netzwerk im Browser prüfen)
- [ ] Keine Cookies, kein localStorage
- [ ] Alle externen Links aufgerufen und erreichbar (Linkcheck-Script, z. B. `linkinator`)
- [ ] Alle Telefonnummern gegen die offizielle Quelle geprüft
- [ ] Lighthouse mobil ≥ 95 (Performance, Accessibility, Best Practices, SEO)
- [ ] Kontraste WCAG AA erfüllt
- [ ] Darstellung bei 360 px, 390 px, 768 px, 1280 px geprüft, keine horizontale Scrollbar
- [ ] JSON-LD validiert (Schema.org-Validator)
- [ ] Sitemap und robots.txt erreichbar, `llms.txt` vorhanden
- [ ] Texte gegen die Verbotsliste aus Abschnitt 4 gescannt (per Script) und bereinigt
- [ ] Keine Heilversprechen, Abgrenzung Beratung/Therapie auf jeder relevanten Seite
- [ ] Sicherheit nach dem Deploy getestet (per `curl -I`):
  - `/wp-login.php`, `/xmlrpc.php`, `/wp-admin/` → 410
  - `/.env`, `/.git/config`, `/package.json`, `/test.php` → 403
  - `POST /` → 403
  - Verzeichnis ohne `index.html` → keine Auflistung
  - Alle Header aus 9a vorhanden; securityheaders.com und Mozilla Observatory: A+
  - HTTP → HTTPS-Weiterleitung, `www` → ohne `www`
- [ ] `npm audit --omit=dev` ohne High/Critical
- [ ] Webroot enthält keine WordPress-Reste und keine `.php`-Dateien
- [ ] Alle `TODO:` in einer Liste ausgegeben
- [ ] Kurze `DEPLOY.md`: WordPress-Backup offline sichern → Webroot leeren → `scripts/deploy.sh` ausführen → Sicherheitstests → Sitemap in der Google Search Console einreichen
