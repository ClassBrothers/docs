# Schriften

Alle Schriften liegen hier selbst gehostet als WOFF2. Es wird **keine**
Google-Fonts-CDN geladen – die Seite baut zu keinem fremden Server eine
Verbindung auf, um eine Schrift zu bekommen.

## Was hier liegt

| Datei | Familie | Rolle | Achsen |
|---|---|---|---|
| `fraunces-variable.woff2` | Fraunces | Überschriften | `wght` 300–900, `opsz` 9–144 |
| `outfit-variable.woff2` | Outfit | Navigation, Buttons, Badges, Labels | `wght` 100–900 |
| `plusjakartasans-variable.woff2` | Plus Jakarta Sans | Fließtext | `wght` 200–800 |
| `plusjakartasans-italic.woff2` | Plus Jakarta Sans, kursiv | Betonungen im Fließtext | statisch, 400 |

Jede Datei ist auf den Zeichensatz zugeschnitten, den deutsche Texte
brauchen (Basis-Latein, Ä/Ö/Ü/ß, Anführungszeichen, Gedankenstrich,
Auslassungspunkte, Euro-Zeichen). Zusammen sind das rund 120 KB – deutlich
weniger, als ein ungekürzter Download aller vier Familien wiegen würde.

Bei Fraunces sind die beiden Achsen `SOFT` und `WONK` fest auf die Werte
eingebrannt, die `critical.css` für Überschriften verwendet (`SOFT 20`,
`WONK 1`). Das ist kein Verlust: Diese beiden Werte werden im ganzen Projekt
nirgends anders gebraucht, und ihr Wegfall spart ein gutes Stück Dateigröße.
Wer künftig einen anderen `SOFT`- oder `WONK`-Wert braucht, muss die Schrift
neu zuschneiden (siehe unten).

## Woher

Alle drei Familien stammen von Google Fonts und stehen unter der SIL Open
Font License 1.1 (Lizenztexte liegen daneben: `OFL-Fraunces.txt`,
`OFL-Outfit.txt`, `OFL-PlusJakartaSans.txt`). Diese Lizenz erlaubt
ausdrücklich, die Schriften mit einer eigenen Website auszuliefern.

## Neu zuschneiden

Falls weitere Zeichen gebraucht werden (z. B. für eine fremdsprachige
Seite) oder ein Achsenwert bei Fraunces geändert werden soll:

```bash
pip install fonttools brotli

# Fraunces mit anderen SOFT/WONK-Werten:
python3 -c "
from fontTools.varLib.instancer import instantiateVariableFont
from fontTools.ttLib import TTFont
f = TTFont('fraunces-quelldatei.woff2')
instantiateVariableFont(f, {'SOFT': 20, 'WONK': 1, 'wght': (300, 900)}, inplace=True)
f.save('fraunces-instanced.ttf')
"
fonttools subset fraunces-instanced.ttf --output-file=fraunces-variable.woff2 \
  --flavor=woff2 --unicodes="U+0020-00FF,U+2013,U+2014,U+2018-201E,U+2026,U+20AC" \
  --layout-features=kern,liga,calt,locl --no-hinting --desubroutinize
```

Die Ausgangsdateien (volle Zeichensätze, alle Achsen) lassen sich über
`fonts.googleapis.com/css2?family=…` beziehen; welche Achsen eine Familie
mitbringt, verrät `fonttools ttx -t fvar datei.woff2`.

## Solange die Dateien fehlen

Nichts geht kaputt. Die `@font-face`-Regeln greifen ins Leere, und der
Browser nimmt die nächste Schrift aus der jeweiligen Kette (Georgia bzw.
System-UI). Die Seite sieht dann etwas nüchterner aus, funktioniert aber
vollständig.
