// Prüft das Build-Ergebnis in dist/ gegen die Abnahme-Checkliste.
import { readdirSync, readFileSync, statSync, existsSync } from 'node:fs';
import { join, extname } from 'node:path';

const root = 'dist';
const walk = (d) => readdirSync(d).flatMap((f) => {
  const p = join(d, f);
  return statSync(p).isDirectory() ? walk(p) : [p];
});
const files = walk(root);
const errors = [];
const err = (m) => errors.push(m);

for (const f of files) {
  if (/\.(php|env|map|md|sql|bak|zip)$/i.test(f)) err(`Unerlaubte Datei: ${f}`);
}
const text = files.filter((f) => /\.(html|css|js|xml|txt|svg)$/.test(f));
for (const f of text) {
  const t = readFileSync(f, 'utf8');
  if (/googleapis|gstatic/i.test(t)) err(`Google-Verweis in ${f}`);
}

const exists = (p) => {
  const clean = p.split('#')[0].split('?')[0];
  if (clean === '/') return true;
  const rel = join(root, clean);
  return existsSync(rel) && (statSync(rel).isFile() || existsSync(join(rel, 'index.html')));
};

for (const f of files.filter((f) => f.endsWith('.html'))) {
  const t = readFileSync(f, 'utf8');
  const name = f.replace(root, '');
  if (/ style=/i.test(t)) err(`Inline-Style in ${name}`);
  if (/\son[a-z]+=/i.test(t)) err(`Inline-Eventhandler in ${name}`);
  if (/<script(?![^>]*application\/ld\+json)/i.test(t)) err(`Script-Tag in ${name}`);
  if (/<style/i.test(t)) err(`<style>-Block in ${name}`);
  if (/target="_blank"/i.test(t)) err(`target=_blank in ${name}`);
  if (name !== '/404.html' && (t.match(/<h1[ >]/g) || []).length !== 1) err(`H1-Anzahl != 1 in ${name}`);
  const title = (t.match(/<title>([^<]*)<\/title>/) || [])[1] || '';
  if (!title || title.length > 70) err(`Title-Länge ${title.length} in ${name}`);
  const desc = (t.match(/<meta name="description" content="([^"]*)"/) || [])[1] || '';
  if (name !== '/404.html' && (desc.length < 120 || desc.length > 160)) err(`Description-Länge ${desc.length} in ${name}`);
  for (const m of t.matchAll(/(?:href|src)="([^"]+)"/g)) {
    const u = m[1];
    if (/^(https?:|mailto:|tel:|#|&#)/.test(u)) continue;
    if (u.startsWith('/') && !exists(u)) err(`Defekter interner Link ${u} in ${name}`);
  }
  for (const m of t.matchAll(/<a [^>]*href="(https?:\/\/[^"]+)"[^>]*>/g)) {
    if (!/rel="noopener"/.test(m[0]) && !m[1].includes('systemische-beratung-koeln.com')) err(`Externer Link ohne rel=noopener: ${m[1]} in ${name}`);
  }
  const ld = [...t.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)];
  if (ld.length !== 1) err(`JSON-LD-Block fehlt/mehrfach in ${name}`);
  else { try { JSON.parse(ld[0][1]); } catch { err(`JSON-LD ungültig in ${name}`); } }
  if (!t.includes('<html lang="de">')) err(`lang=de fehlt in ${name}`);
}
for (const must of ['robots.txt', 'llms.txt', '.htaccess', 'sitemap-index.xml', '.well-known/security.txt', 'og-image.png', 'favicon.svg', '404.html']) {
  if (!existsSync(join(root, must))) err(`Fehlt in dist: ${must}`);
}
if (errors.length) { console.error(errors.join('\n')); console.error(`\n${errors.length} Fehler`); process.exit(1); }
console.log(`dist/ ok (${files.length} Dateien).`);
