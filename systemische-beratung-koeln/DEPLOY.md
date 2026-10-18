# Deploy: systemische-beratung-koeln.com

Reihenfolge einhalten. Nichts überspringen.

## 1. Vorbereitung im KAS (ALL-INKL)
- 2FA für den KAS-Login aktivieren
- Neuen FTP/SSH-Benutzer nur für dieses Domain-Verzeichnis anlegen (langes Zufallspasswort, Passwortmanager). SFTP nur, wenn der Tarif SSH bietet, sonst FTPS
- Let's-Encrypt-Zertifikat für `systemische-beratung-koeln.com` und `www.` aktivieren
- IP-Anonymisierung der Logfiles aktivieren, Speicherdauer notieren und in `src/content/pages/datenschutz.md` eintragen
- AVV mit ALL-INKL im KAS abschließen
- Alte FTP- und Datenbank-Benutzer aus der WordPress-Zeit löschen, ungenutzte Postfächer löschen

## 2. WordPress sichern und entfernen
1. Komplett-Backup (Dateien und Datenbank) herunterladen, **lokal und offline** ablegen, Backup auf Vollständigkeit prüfen
2. Webroot leeren (kein `wp-*`, keine `.php`, keine `*.zip`, `*.sql`, `*.bak`). Das erledigt `--delete` im Deploy, prüfen Sie es trotzdem per SFTP-Listing
3. WordPress-Datenbank im KAS erst löschen, wenn das Backup geprüft ist

## 3. Deploy
```bash
cp .env.example .env     # ausfüllen, nie committen
./scripts/deploy.sh
```
Das Skript führt `npm ci`, Textprüfung, `astro check`, Build, `dist/`-Prüfung und den Upload aus.

## 4. Sicherheitstests (nach dem Deploy)
```bash
D=https://systemische-beratung-koeln.com
for p in /wp-login.php /xmlrpc.php /wp-admin/ /feed/ /x-material-notizen/; do curl -sI $D$p | head -1; done   # 410
for p in /.env /.git/config /package.json /test.php;                    do curl -sI $D$p | head -1; done   # 403
curl -sI -X POST $D/ | head -1                                                                               # 403
curl -sI $D/arbeitsweise/ | grep -iE "^(HTTP|location)"                                                      # 301 -> /systemische-beratung/
curl -sI http://www.systemische-beratung-koeln.com/ | grep -iE "^(HTTP|location)"                           # 301 -> https ohne www
curl -sI $D/kosten | grep -iE "^(HTTP|location)"                                                             # 301 -> /kosten/
curl -sI $D/ | grep -iE "strict-transport|content-security|x-content-type|x-frame|referrer|permissions|cross-origin"
```
Dann securityheaders.com und Mozilla Observatory (Ziel A+). Externe Links prüfen: `npx linkinator https://systemische-beratung-koeln.com --recurse`.

Falls ALL-INKL einen Header doppelt oder gar nicht setzt: im KAS prüfen, ob `mod_headers` aktiv ist. HSTS-Preload erst nach vier Wochen stabilem Betrieb erwägen.

## 5. Nach dem Go-live
- Sitemap `https://systemische-beratung-koeln.com/sitemap-index.xml` in der Google Search Console einreichen
- UptimeRobot-Check auf die Startseite einrichten
- `security.txt`: Ablaufdatum jährlich erneuern
