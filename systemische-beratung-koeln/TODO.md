# Offene Punkte vor Veröffentlichung

Im Code als `TODO:` markiert (`grep -rn "TODO" src public`).

## Für Seb (inhaltlich/rechtlich)
- [ ] **Impressum**: Betreiberin festlegen (Nina oder Class Brothers GmbH), Anschrift, Kontakt, Register, USt-ID, Verantwortliche nach § 18 MStV (`src/content/pages/impressum.md`)
- [ ] **Datenschutzerklärung**: Speicherdauer der Logfiles aus dem KAS eintragen, AVV-Abschluss bestätigen, Aufsichtsbehörde prüfen, rechtlich gegenlesen lassen (`datenschutz.md`)
- [ ] **Kontakt-E-Mail** festlegen: `src/site.ts` (Platzhalter `kontakt@systemische-beratung-koeln.com`), außerdem fest eingetragen in `kontakt.md`, `impressum.md`, `public/.well-known/security.txt`
- [ ] **Ninas voller Name und Qualifikation** für die Redaktionszeile, nur nach Freigabe (`src/site.ts`, `ueber-mich.md`)
- [ ] **Betreiberin im Text**: `ueber-mich.md` sagt „eine systemische Beraterin baut eine Praxis auf“. Mit Nina abstimmen
- [ ] **Preisspannen** für Köln recherchieren (8 bis 10 Praxen) und in `kosten.md` mit Quelle und Datum eintragen. Es gibt bewusst keine erfundenen Zahlen
- [ ] **Echte Fotos** (Kölner Orte, Hände, Tee, Fensterlicht). Aktuell keine Bilder auf der Seite
- [ ] **Hilfe in Köln**: Direktlinks und Kontaktdaten der einzelnen Beratungsstellen (Caritas, Evangelische Beratungsstelle, Christliche Sozialhilfe, Kinderschutzbund, Diakonie, Frauenberatung, Sozialpsychiatrischer Dienst, KV Nordrhein) ergänzen. Im Umland statt der Startseiten die Seiten der Familienberatung der Kreise

## Für Seb (ALL-INKL, siehe DEPLOY.md)
- [ ] KAS: 2FA, neuer FTP/SSH-Benutzer, IP-Anonymisierung Logfiles, AVV, Let's Encrypt
- [ ] Alte FTP-/DB-Benutzer und ungenutzte Postfächer löschen
- [ ] WordPress-Komplett-Backup offline sichern, dann Webroot leeren

## Verifikation, die in dieser Umgebung nicht möglich war
Der Egress-Proxy dieser Sitzung sperrt externe Hosts (kein curl, kein WebFetch). Deshalb **nicht** geprüft:
- [ ] Alle externen Links einzeln aufrufen (`npx linkinator <live-url> --recurse` nach dem Deploy)
- [ ] Alle Telefonnummern auf der jeweiligen offiziellen Seite (112, 0800 111 0 111/222, 116 016, 116 111, 0800 111 0 550, 116 117)
- [ ] G-BA-Fakten: Erwachsene (Beschluss 22.11.2019, Abrechnung seit 01.07.2020), Kinder/Jugendliche (Beschluss 18.01.2024, in Kraft 12.04.2024). Per Websuche bestätigt, Versorgungsstand (EBM) vor Veröffentlichung auf g-ba.de prüfen und `beratung-oder-therapie.md` und `kosten.md` ggf. präzisieren
- [ ] Wikipedia-Seiten „Systemische Therapie“ und „Genogramm“ existieren; kein eigener Artikel „Systemische Beratung“ verlinkt
- [ ] Lighthouse, securityheaders.com, Mozilla Observatory gegen die Live-Seite
- [ ] `.htaccess`-Regeln auf dem echten Apache testen (nur syntaktisch geprüft, nicht ausgeführt)
- [ ] **Lighthouse mobil ≥ 95**: Lokal in der Sandbox gemessen (mit gzip): Performance 93 (LCP 1,4 s, CLS 0), Accessibility 100, Best Practices 100, SEO 100. TBT von rund 270 ms ist bei null JavaScript ein Messartefakt der Sandbox. Gegen die Live-Seite wiederholen
