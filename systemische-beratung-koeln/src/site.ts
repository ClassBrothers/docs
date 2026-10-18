// Zentrale Stammdaten. Alles mit TODO vor Veröffentlichung klären.
export const site = {
  name: 'Systemische Beratung Köln',
  url: 'https://systemische-beratung-koeln.com',
  // TODO: Betreiberin laut Impressum (Nina oder Class Brothers GmbH)
  operator: 'TODO Betreiberin laut Impressum',
  // TODO: Ninas voller Name und Qualifikation, nur nach Freigabe
  editor: 'Systemische Beratung Köln',
  // TODO: echte Kontakt-E-Mail eintragen
  email: 'kontakt@systemische-beratung-koeln.com',
  published: '2026-10-18',
  locale: 'de-DE',
} as const;

export const nav = [
  { href: '/systemische-beratung/', label: 'Was ist systemische Beratung?' },
  { href: '/beratung-oder-therapie/', label: 'Beratung oder Therapie' },
  { href: '/kosten/', label: 'Kosten' },
  { href: '/hilfe-in-koeln/', label: 'Hilfe in Köln' },
  { href: '/beraterin-finden/', label: 'Beraterin finden' },
];

export const crisis = [
  { label: 'Akute Gefahr', num: '112', tel: '112', note: 'Notruf' },
  { label: 'TelefonSeelsorge', num: '0800 111 0 111', tel: '08001110111', note: 'rund um die Uhr, kostenlos, anonym' },
  { label: 'TelefonSeelsorge', num: '0800 111 0 222', tel: '08001110222', note: 'rund um die Uhr, kostenlos, anonym' },
  { label: 'Hilfetelefon Gewalt gegen Frauen', num: '116 016', tel: '116016', note: 'rund um die Uhr, mehrsprachig' },
  { label: 'Nummer gegen Kummer (Kinder und Jugendliche)', num: '116 111', tel: '116111', note: 'kostenlos' },
  { label: 'Elterntelefon', num: '0800 111 0 550', tel: '08001110550', note: 'kostenlos' },
  { label: 'Ärztlicher Bereitschaftsdienst', num: '116 117', tel: '116117', note: 'außerhalb der Praxiszeiten' },
];
