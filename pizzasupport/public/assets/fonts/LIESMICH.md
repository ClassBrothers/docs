# Schriften

Hier gehören die selbst gehosteten WOFF2-Dateien hinein. Es werden **keine**
Google Fonts über deren CDN geladen – die Seite baut zu keinem fremden Server
eine Verbindung auf.

## Was gebraucht wird

Für die Überschriften ist **Fraunces** vorgesehen (SIL Open Font License):

    fraunces-variable.woff2      Variable Font, Gewichte 400–900
    fraunces-700.woff2           Fallback für ältere Browser (optional)

Bezugsquelle: <https://fonts.google.com/specimen/Fraunces> → „Download family",
darin die statischen bzw. variablen TTF-Dateien mit einem Werkzeug wie
`fonttools` oder <https://transfonter.org> nach WOFF2 wandeln und hier ablegen.

Die Einbindung steht bereits in `public/assets/css/critical.css` und muss nicht
angefasst werden.

## Solange die Dateien fehlen

Nichts geht kaputt. Die `@font-face`-Regel greift ins Leere, und der Browser
nimmt die nächste Schrift aus der Kette: Georgia, ersatzweise Times New Roman,
ersatzweise die Standard-Serifenschrift. Die Seite sieht damit etwas nüchterner
aus, funktioniert aber vollständig.

## Lizenz

Fraunces steht unter der SIL Open Font License 1.1 und darf mitgeliefert werden.
Legen Sie die Datei `OFL.txt` aus dem Download mit in dieses Verzeichnis.
