// WCAG-Kontrastprüfung der Farbtokens (Hell und Dunkel). Aufruf: node scripts/contrast.mjs
const lum = (hex) => {
  const c = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255).map((v) => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4));
  return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
};
const ratio = (a, b) => { const [x, y] = [lum(a), lum(b)].sort((p, q) => q - p); return (x + 0.05) / (y + 0.05); };
const light = { bg: '#fbf7f3', surface: '#ffffff', rose: '#f3e6e1', sage: '#e6ede8', text: '#2f2a2c', muted: '#5e5659', primary: '#4e6b5f', hover: '#3d564c', onPrimary: '#ffffff', accent: '#9a4e43', crisis: '#8c2f39' };
const dark = { bg: '#1c1819', surface: '#262122', rose: '#33282a', sage: '#222b27', text: '#efe8e4', muted: '#bfb4b0', primary: '#8fb5a4', hover: '#aecbbd', onPrimary: '#14201b', accent: '#e09a8e', crisis: '#e58a94' };
const pairs = [
  ['text', 'bg'], ['text', 'surface'], ['text', 'rose'], ['text', 'sage'], ['muted', 'bg'], ['muted', 'surface'], ['muted', 'rose'],
  ['primary', 'bg'], ['primary', 'surface'], ['primary', 'sage'], ['hover', 'bg'], ['onPrimary', 'primary'], ['onPrimary', 'hover'],
  ['accent', 'bg'], ['accent', 'rose'], ['crisis', 'surface'], ['crisis', 'bg'],
];
let fail = 0;
for (const [name, set] of [['hell', light], ['dunkel', dark]]) {
  for (const [f, b] of pairs) {
    const r = ratio(set[f], set[b]);
    const ok = r >= 4.5;
    if (!ok) fail++;
    console.log(`${name.padEnd(6)} ${f.padEnd(9)} auf ${b.padEnd(8)} ${r.toFixed(2)} ${ok ? 'ok' : 'ZU NIEDRIG'}`);
  }
}
process.exit(fail ? 1 : 0);
