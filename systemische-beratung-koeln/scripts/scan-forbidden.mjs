// Prüft Inhalte und Komponenten auf verbotene KI-Floskeln und Heilversprechen (Briefing Abschnitt 4).
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';

const patterns = [
  /in der heutigen schnelllebigen/i, /tauchen sie ein/i, /entdecken sie/i, /es ist wichtig zu beachten/i,
  /zusammenfassend lässt sich/i, /spielt eine entscheidende rolle/i, /reise zu sich selbst/i, /schlüssel zu/i,
  /nicht nur[^.\n]{0,80}sondern auch/i, /wir lösen ihre probleme/i, /sie werden sich besser fühlen/i,
  /heilt\b/i, /heilung garantiert/i, /garantiert/i, /\p{Extended_Pictographic}/u, /—|–{2,}/,
];
const ganzheitlich = /ganzheitlich/gi;

const walk = (d) => readdirSync(d).flatMap((f) => {
  const p = join(d, f);
  return statSync(p).isDirectory() ? walk(p) : [p];
});

let bad = 0;
for (const f of walk('src').filter((p) => /\.(md|astro|ts)$/.test(p))) {
  const t = readFileSync(f, 'utf8');
  // „garantiert“ ist in Verneinungen erlaubt (z. B. „keine Erfolgsgarantien“ trifft nicht)
  for (const re of patterns) {
    const m = t.match(re);
    if (m) { bad++; console.error(`${f}: verbotenes Muster ${re} -> "${m[0]}"`); }
  }
  const g = t.match(ganzheitlich);
  if (g && g.length > 1) { bad++; console.error(`${f}: "ganzheitlich" mehr als einmal`); }
}
console.log(bad ? `${bad} Treffer` : 'Keine verbotenen Muster gefunden.');
process.exit(bad ? 1 : 0);
