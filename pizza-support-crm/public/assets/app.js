/* Pizza Support — Akquise-CRM. Vanilla JS, kein Framework. */
'use strict';

const Zustand = {
  meta: null,
  betriebe: [],
  filter: { prioritaet: [], stadtteil: '', pizza_fokus: '', tag: '', suche: '', followup_faellig: false, ohne_kontakt: false },
  ansicht: 'kanban',
  offen: null,
  sortierung: { feld: 'prioritaet', richtung: 1 },
  vorlageAktiv: null,
};

const SPALTEN_ALLE = [
  { feld: 'name', titel: 'Name', standard: true },
  { feld: 'prioritaet', titel: 'Prio', standard: true },
  { feld: 'stage_label', titel: 'Stufe', standard: true },
  { feld: 'stadtteil', titel: 'Stadtteil', standard: true },
  { feld: 'strasse', titel: 'Straße', standard: false },
  { feld: 'telefon', titel: 'Telefon', standard: true },
  { feld: 'email', titel: 'E-Mail', standard: false },
  { feld: 'inhaber', titel: 'Inhaber', standard: false },
  { feld: 'art_gastronomie', titel: 'Art', standard: false },
  { feld: 'pizza_fokus', titel: 'Fokus', standard: false },
  { feld: 'google_bewertung', titel: '★', standard: false },
  { feld: 'anzahl_bewertungen', titel: 'Bew.', standard: false },
  { feld: 'volumen_klasse', titel: 'Volumen', standard: true },
  { feld: 'kontaktversuche', titel: 'Kontakte', standard: true },
  { feld: 'letzter_kontakt', titel: 'Letzter Kontakt', standard: true },
  { feld: 'naechster_follow_up', titel: 'Wiedervorlage', standard: true },
  { feld: 'tags', titel: 'Tags', standard: false },
];
let sichtbareSpalten = SPALTEN_ALLE.filter(s => s.standard).map(s => s.feld);

/* ---------------- Hilfsmittel ---------------- */

const $ = (wahl, wurzel = document) => wurzel.querySelector(wahl);
const $$ = (wahl, wurzel = document) => Array.from(wurzel.querySelectorAll(wahl));

function el(tag, attribute = {}, ...kinder) {
  const knoten = document.createElement(tag);
  for (const [schluessel, wert] of Object.entries(attribute)) {
    if (wert === null || wert === undefined || wert === false) continue;
    if (schluessel === 'class') knoten.className = wert;
    else if (schluessel === 'text') knoten.textContent = wert;
    else if (schluessel === 'html') knoten.innerHTML = wert;
    else if (schluessel.startsWith('on')) knoten.addEventListener(schluessel.slice(2), wert);
    else knoten.setAttribute(schluessel, wert === true ? '' : wert);
  }
  for (const kind of kinder.flat()) {
    if (kind === null || kind === undefined || kind === false) continue;
    knoten.append(kind.nodeType ? kind : document.createTextNode(String(kind)));
  }
  return knoten;
}

function melden(text, art = '') {
  const knoten = el('div', { class: 'meldung ' + art, text });
  $('#meldungen').append(knoten);
  setTimeout(() => knoten.remove(), art === 'fehler' ? 7000 : 3200);
}

function datumDe(iso) {
  if (!iso) return '—';
  const teil = String(iso).slice(0, 10).split('-');
  return teil.length === 3 ? `${teil[2]}.${teil[1]}.${teil[0]}` : iso;
}

function heute() {
  return new Date().toISOString().slice(0, 10);
}

function inTagen(tage) {
  const d = new Date();
  d.setDate(d.getDate() + tage);
  return d.toISOString().slice(0, 10);
}

async function api(aktion, { methode = 'GET', daten = null, params = {} } = {}) {
  const suche = new URLSearchParams({ aktion, ...params });
  const optionen = { method: methode, headers: {} };
  if (methode === 'POST') {
    optionen.headers['Content-Type'] = 'application/json';
    optionen.headers['X-CSRF-Token'] = Zustand.meta ? Zustand.meta.csrf : '';
    optionen.body = JSON.stringify(daten || {});
  }
  const antwort = await fetch('api.php?' + suche.toString(), optionen);
  const inhalt = await antwort.json().catch(() => ({ fehler: 'Antwort nicht lesbar.' }));
  if (!antwort.ok) throw new Error(inhalt.fehler || ('Fehler ' + antwort.status));
  return inhalt;
}

/** Aktuelle Filter als Query-Parameter (für API und Export). */
function filterParams() {
  const f = Zustand.filter;
  const p = {};
  if (f.prioritaet.length) p.prioritaet = f.prioritaet.join(',');
  for (const feld of ['stadtteil', 'pizza_fokus', 'tag', 'suche']) if (f[feld]) p[feld] = f[feld];
  if (f.followup_faellig) p.followup_faellig = '1';
  if (f.ohne_kontakt) p.ohne_kontakt = '1';
  return p;
}

/* ---------------- Laden & Rendern ---------------- */

async function betriebeLaden() {
  const { betriebe } = await api('betriebe', { params: filterParams() });
  Zustand.betriebe = betriebe;
  $('#treffer').textContent = `${betriebe.length} Betrieb${betriebe.length === 1 ? '' : 'e'}`;
  if (Zustand.ansicht === 'kanban') kanbanZeichnen();
  if (Zustand.ansicht === 'liste') tabelleZeichnen();
}

async function zielAktualisieren() {
  const { dashboard } = await api('dashboard');
  $('#ziel-ist').textContent = dashboard.gewonnen;
  $('#ziel-soll').textContent = dashboard.ziel;
  $('#ziel-balken').style.width = dashboard.fortschritt + '%';
  return dashboard;
}

function ansichtWechseln(ansicht) {
  Zustand.ansicht = ansicht;
  $$('#reiter button').forEach(b => b.classList.toggle('aktiv', b.dataset.ansicht === ansicht));
  for (const name of ['kanban', 'liste', 'dashboard', 'vorlagen']) {
    $('#ansicht-' + name).hidden = name !== ansicht;
  }
  // Filter und Export beziehen sich nur auf die Datenansichten.
  $('#filterleiste').hidden = ansicht === 'dashboard' || ansicht === 'vorlagen';
  if (ansicht === 'kanban') kanbanZeichnen();
  if (ansicht === 'liste') tabelleZeichnen();
  if (ansicht === 'dashboard') dashboardZeichnen();
  if (ansicht === 'vorlagen') vorlagenZeichnen();
}

/* ---------------- Kanban ---------------- */

function fokusZeichen(betrieb) {
  return betrieb.pizza_fokus === 'Hauptfokus' ? '🍕' : '🍽';
}

function kanbanZeichnen() {
  const tafel = $('#tafel');
  tafel.textContent = '';
  const stages = Zustand.meta ? Zustand.meta.stages : {};

  for (const [schluessel, titel] of Object.entries(stages)) {
    const treffer = Zustand.betriebe.filter(b => b.pipeline_stage === schluessel);
    const spalte = el('div', { class: 'spalte' + (schluessel === 'gewonnen' ? ' ziel' : ''), 'data-stage': schluessel },
      el('div', { class: 'spalte-kopf' }, el('h3', { text: titel }), el('span', { class: 'zahl', text: treffer.length })),
      el('div', { class: 'spalte-inhalt' }, treffer.map(karteZeichnen))
    );
    spalte.addEventListener('dragover', ereignis => { ereignis.preventDefault(); spalte.style.background = '#e9e0d0'; });
    spalte.addEventListener('dragleave', () => { spalte.style.background = ''; });
    spalte.addEventListener('drop', async ereignis => {
      ereignis.preventDefault();
      spalte.style.background = '';
      const id = Number(ereignis.dataTransfer.getData('text/plain'));
      if (!id) return;
      await stageSetzen(id, schluessel);
    });
    tafel.append(spalte);
  }
}

function karteZeichnen(betrieb) {
  const meta = [el('span', { class: 'punkt ' + betrieb.prioritaet }), betrieb.stadtteil || '—',
    el('span', { title: betrieb.pizza_fokus, text: fokusZeichen(betrieb) })];

  if (betrieb.followup_faellig) {
    meta.push(el('span', { class: 'marker faellig', text: 'WV ' + datumDe(betrieb.naechster_follow_up) }));
  } else if (betrieb.naechster_follow_up) {
    meta.push(el('span', { class: 'marker', text: 'WV ' + datumDe(betrieb.naechster_follow_up) }));
  }
  if (betrieb.ueberfaellig) {
    meta.push(el('span', { class: 'marker kalt', text: betrieb.tage_seit_kontakt + ' T ohne Kontakt' }));
  } else if (betrieb.tage_seit_kontakt !== null) {
    meta.push(el('span', { class: 'marker', text: 'vor ' + betrieb.tage_seit_kontakt + ' T' }));
  }
  for (const tag of betrieb.tags_liste) meta.push(el('span', { class: 'marker', text: tag }));

  const karte = el('div', {
    class: 'karte', draggable: 'true', 'data-prio': betrieb.prioritaet, 'data-id': betrieb.id,
    onclick: () => detailOeffnen(betrieb.id),
  }, el('h4', { text: betrieb.name }), el('div', { class: 'karte-meta' }, meta));

  karte.addEventListener('dragstart', ereignis => {
    ereignis.dataTransfer.setData('text/plain', String(betrieb.id));
    karte.classList.add('zieht');
  });
  karte.addEventListener('dragend', () => karte.classList.remove('zieht'));
  return karte;
}

async function stageSetzen(id, stage) {
  try {
    await api('stage', { methode: 'POST', daten: { id, stage } });
    await betriebeLaden();
    await zielAktualisieren();
    if (Zustand.offen === id) await detailOeffnen(id);
    melden('Stufe aktualisiert: ' + Zustand.meta.stages[stage], 'gut');
  } catch (fehler) {
    melden(fehler.message, 'fehler');
  }
}

/* ---------------- Tabelle ---------------- */

function tabelleZeichnen() {
  const wahl = $('#spaltenwahl');
  if (!wahl.dataset.fertig) {
    wahl.append(el('span', { text: 'Spalten:' }));
    for (const spalte of SPALTEN_ALLE) {
      const kasten = el('input', { type: 'checkbox', checked: sichtbareSpalten.includes(spalte.feld) });
      kasten.addEventListener('change', () => {
        sichtbareSpalten = kasten.checked
          ? [...sichtbareSpalten, spalte.feld]
          : sichtbareSpalten.filter(f => f !== spalte.feld);
        tabelleZeichnen();
      });
      wahl.append(el('label', {}, kasten, spalte.titel));
    }
    wahl.dataset.fertig = '1';
  }

  const spalten = SPALTEN_ALLE.filter(s => sichtbareSpalten.includes(s.feld));
  const { feld, richtung } = Zustand.sortierung;
  const reihen = [...Zustand.betriebe].sort((a, b) => {
    const x = a[feld] ?? '', y = b[feld] ?? '';
    if (typeof x === 'number' && typeof y === 'number') return (x - y) * richtung;
    return String(x).localeCompare(String(y), 'de', { numeric: true }) * richtung;
  });

  const kopf = el('tr', {}, spalten.map(spalte => {
    const zelle = el('th', { text: spalte.titel + (feld === spalte.feld ? (richtung > 0 ? ' ▲' : ' ▼') : '') });
    zelle.addEventListener('click', () => {
      Zustand.sortierung = { feld: spalte.feld, richtung: feld === spalte.feld ? -richtung : 1 };
      tabelleZeichnen();
    });
    return zelle;
  }));
  $('#tabelle thead').replaceChildren(kopf);

  const koerper = reihen.map(betrieb => {
    const zeile = el('tr', { onclick: () => detailOeffnen(betrieb.id) },
      spalten.map(spalte => {
        let wert = betrieb[spalte.feld];
        if (spalte.feld === 'letzter_kontakt' || spalte.feld === 'naechster_follow_up') wert = datumDe(wert);
        if (wert === null || wert === undefined || wert === '') wert = '—';
        const zelle = el('td', { text: String(wert) });
        if (spalte.feld === 'name') zelle.style.fontWeight = '600';
        if (spalte.feld === 'naechster_follow_up' && betrieb.followup_faellig) zelle.style.color = 'var(--warnung)';
        return zelle;
      }));
    if (betrieb.ueberfaellig) zeile.style.background = '#fdf6f3';
    return zeile;
  });
  $('#tabelle tbody').replaceChildren(...(koerper.length ? koerper : [el('tr', {}, el('td', { colspan: spalten.length, class: 'leer', text: 'Keine Betriebe im aktuellen Filter.' }))]));
}

/* ---------------- Detailkarte ---------------- */

function schliessen() {
  Zustand.offen = null;
  $('#schublade-huelle').textContent = '';
}

async function detailOeffnen(id) {
  try {
    const { betrieb, aktivitaeten } = await api('betrieb', { params: { id } });
    Zustand.offen = id;
    detailZeichnen(betrieb, aktivitaeten);
  } catch (fehler) {
    melden(fehler.message, 'fehler');
  }
}

function feld(bezeichnung, name, wert, art = 'text', extra = {}) {
  const eingabe = art === 'textarea'
    ? el('textarea', { name, ...extra }, wert ?? '')
    : el('input', { type: art, name, value: wert ?? '', ...extra });
  return el('div', { class: 'feld' + (extra.breit ? ' breit' : '') }, el('label', { text: bezeichnung }), eingabe);
}

function auswahl(bezeichnung, name, wert, optionen, extra = {}) {
  const knoten = el('select', { name, ...extra },
    optionen.map(([w, t]) => el('option', { value: w, selected: String(w) === String(wert ?? '') }, t)));
  return el('div', { class: 'feld' + (extra.breit ? ' breit' : '') }, el('label', { text: bezeichnung }), knoten);
}

function detailZeichnen(betrieb, aktivitaeten) {
  const stages = Object.entries(Zustand.meta.stages);
  const formular = el('form', { class: 'block', id: 'stammdaten' },
    el('h3', {}, 'Stammdaten', el('span', { class: 'leise', text: betrieb.art_gastronomie || '' })),
    el('div', { class: 'feldgitter' },
      feld('Name', 'name', betrieb.name, 'text', { breit: true }),
      feld('Inhaber/Ansprechpartner', 'inhaber', betrieb.inhaber),
      feld('Telefon', 'telefon', betrieb.telefon),
      feld('Straße', 'strasse', betrieb.strasse),
      feld('PLZ', 'plz', betrieb.plz),
      feld('Stadtteil', 'stadtteil', betrieb.stadtteil),
      feld('Ort', 'ort', betrieb.ort),
      feld('E-Mail', 'email', betrieb.email, 'email'),
      feld('Website', 'website', betrieb.website),
      feld('Art der Gastronomie', 'art_gastronomie', betrieb.art_gastronomie),
      auswahl('Pizza-Fokus', 'pizza_fokus', betrieb.pizza_fokus, [['Hauptfokus', 'Hauptfokus'], ['Teilweise', 'Teilweise']]),
      auswahl('Priorität', 'prioritaet', betrieb.prioritaet, [['A', 'A'], ['B', 'B'], ['C', 'C']]),
      auswahl('Pipeline-Stufe', 'pipeline_stage', betrieb.pipeline_stage, stages),
      feld('Google-Bewertung', 'google_bewertung', betrieb.google_bewertung, 'number', { step: '0.1', min: '0', max: '5' }),
      feld('Anzahl Bewertungen', 'anzahl_bewertungen', betrieb.anzahl_bewertungen, 'number'),
      feld('Wiedervorlage', 'naechster_follow_up', (betrieb.naechster_follow_up || '').slice(0, 10), 'date'),
      feld('Vertragsdatum', 'vertragsdatum', (betrieb.vertragsdatum || '').slice(0, 10), 'date'),
      feld('Gebuchte Flächen', 'anzahl_flaechen_gebucht', betrieb.anzahl_flaechen_gebucht, 'number', { min: '0' }),
      feld('Tags (kommagetrennt)', 'tags', betrieb.tags, 'text', { breit: true }),
      feld('Notizen', 'notizen', betrieb.notizen, 'textarea', { breit: true }),
      el('div', { class: 'feld breit' },
        el('label', { text: 'Geschätztes Pizzenvolumen/Monat' }),
        el('div', { text: betrieb.pizzenvolumen_text || '—' }),
        el('div', { class: 'hinweis', text: 'Schätzwert auf Basis der Google-Bewertungszahl — im Gespräch verifizieren.' })),
      betrieb.notiz_quelle ? el('div', { class: 'feld breit' }, el('label', { text: 'Notiz aus der Quellliste' }), el('div', { class: 'leise', text: betrieb.notiz_quelle })) : null,
    ),
    el('div', { class: 'zeile ende', style: 'margin-top:12px' },
      el('button', { class: 'knopf klein', type: 'button', onclick: () => betriebLoeschen(betrieb.id) }, 'Löschen'),
      el('button', { class: 'knopf haupt', type: 'submit' }, 'Speichern'))
  );
  formular.addEventListener('submit', async ereignis => {
    ereignis.preventDefault();
    const daten = Object.fromEntries(new FormData(formular).entries());
    daten.id = betrieb.id;
    try {
      await api('speichern', { methode: 'POST', daten });
      melden('Gespeichert.', 'gut');
      await betriebeLaden();
      await zielAktualisieren();
      await detailOeffnen(betrieb.id);
    } catch (fehler) { melden(fehler.message, 'fehler'); }
  });

  const schublade = el('div', { class: 'schublade' },
    el('div', { class: 'schublade-kopf' },
      el('div', {},
        el('h2', { text: betrieb.name }),
        el('div', { class: 'unter', text: [betrieb.strasse, [betrieb.plz, betrieb.ort].filter(Boolean).join(' '), betrieb.stadtteil].filter(Boolean).join(' · ') }),
        el('div', { class: 'unter' },
          `Prio ${betrieb.prioritaet} · ${betrieb.stage_label} · ${betrieb.kontaktversuche} Kontaktversuch${betrieb.kontaktversuche === 1 ? '' : 'e'}`,
          betrieb.telefon ? el('span', {}, ' · ', el('a', { href: 'tel:' + betrieb.telefon.replace(/\s/g, '') }, betrieb.telefon)) : ' · keine Telefonnummer')),
      el('button', { class: 'knopf klein', style: 'margin-left:auto', onclick: schliessen }, '✕')),
    el('div', { class: 'schnellleiste' },
      el('button', { class: 'knopf klein gruen', onclick: () => anrufDialog(betrieb) }, '☎ Anruf protokollieren'),
      el('button', { class: 'knopf klein', onclick: () => followUpDialog(betrieb) }, '⏰ Wiedervorlage'),
      el('button', { class: 'knopf klein', onclick: () => briefFuerBetrieb(betrieb) }, '📄 Brief erzeugen'),
      el('button', { class: 'knopf klein', onclick: () => notizDialog(betrieb) }, '✎ Notiz'),
      el('button', { class: 'knopf klein', onclick: () => mailPanel(betrieb) }, '✉ E-Mail'),
      el('a', { class: 'knopf klein', href: 'export.php?art=etiketten&ids=' + betrieb.id, target: '_blank' }, '🏷 Etikett')),
    el('div', { class: 'schublade-inhalt' }, formular, zeitstrahlBlock(betrieb, aktivitaeten))
  );

  $('#schublade-huelle').replaceChildren(el('div', { class: 'schleier', onclick: schliessen }), schublade);
}

function zeitstrahlBlock(betrieb, aktivitaeten) {
  const typen = Zustand.meta.typen;
  return el('div', { class: 'block' },
    el('h3', {}, 'Aktivitäten', el('span', { class: 'leise', text: aktivitaeten.length + ' Einträge' })),
    aktivitaeten.length === 0
      ? el('p', { class: 'leise', text: 'Noch nichts protokolliert. Erster Schritt: anrufen oder Brief erzeugen.' })
      : el('ul', { class: 'zeitstrahl' }, aktivitaeten.map(eintrag =>
        el('li', { class: eintrag.typ },
          el('div', { class: 'kopfzeile' },
            el('b', { text: typen[eintrag.typ] || eintrag.typ }),
            datumDe(eintrag.datum) + (String(eintrag.datum).length > 10 ? ' ' + String(eintrag.datum).slice(11, 16) : ''),
            eintrag.ergebnis ? el('span', { class: 'marker', text: eintrag.ergebnis }) : null,
            el('button', {
              class: 'knopf klein', style: 'margin-left:auto;padding:0 6px',
              onclick: async () => {
                await api('aktivitaet_loeschen', { methode: 'POST', daten: { id: eintrag.id } });
                await detailOeffnen(betrieb.id);
              }
            }, '✕')),
          eintrag.notiz ? el('p', { text: eintrag.notiz }) : null)))
  );
}

/* ---------------- Schnellaktionen ---------------- */

function dialog(titel, inhalt, aufSpeichern) {
  const formular = el('form', { class: 'block', style: 'width:min(480px,94vw);margin:0' },
    el('h3', { text: titel }), inhalt,
    el('div', { class: 'zeile ende', style: 'margin-top:12px' },
      el('button', { class: 'knopf', type: 'button', onclick: () => huelle.remove() }, 'Abbrechen'),
      el('button', { class: 'knopf haupt', type: 'submit' }, 'Speichern')));

  const huelle = el('div', { class: 'schleier', style: 'display:grid;place-items:center;z-index:60' }, formular);
  huelle.addEventListener('click', ereignis => { if (ereignis.target === huelle) huelle.remove(); });
  formular.addEventListener('submit', async ereignis => {
    ereignis.preventDefault();
    try {
      await aufSpeichern(Object.fromEntries(new FormData(formular).entries()));
      huelle.remove();
    } catch (fehler) { melden(fehler.message, 'fehler'); }
  });
  document.body.append(huelle);
  const erstes = formular.querySelector('input, select, textarea');
  if (erstes) erstes.focus();
}

function anrufDialog(betrieb) {
  const inhalt = el('div', { class: 'feldgitter' },
    auswahl('Ergebnis', 'ergebnis', 'nicht_erreicht', [
      ['nicht_erreicht', 'Nicht erreicht'], ['erreicht', 'Erreicht'], ['rueckruf', 'Rückruf vereinbart'],
      ['interessiert', 'Interessiert'], ['abgelehnt', 'Abgelehnt']], { breit: true }),
    feld('Notiz', 'notiz', '', 'textarea', { breit: true, placeholder: 'Was wurde besprochen?' }),
    feld('Wiedervorlage am', 'naechster_follow_up', inTagen(7), 'date'),
    auswahl('Stufe danach', 'stage', '', [['', '— unverändert —'], ...Object.entries(Zustand.meta.stages)]));

  dialog('Anruf protokollieren — ' + betrieb.name, inhalt, async daten => {
    await api('aktivitaet', { methode: 'POST', daten: { betrieb_id: betrieb.id, typ: 'anruf', ...daten } });
    if (daten.stage) await api('stage', { methode: 'POST', daten: { id: betrieb.id, stage: daten.stage } });
    melden('Anruf protokolliert.', 'gut');
    await betriebeLaden(); await zielAktualisieren(); await detailOeffnen(betrieb.id);
  });
}

function followUpDialog(betrieb) {
  const inhalt = el('div', { class: 'feldgitter' },
    feld('Wiedervorlage am', 'naechster_follow_up', (betrieb.naechster_follow_up || inTagen(7)).slice(0, 10), 'date', { breit: true }),
    feld('Notiz', 'notiz', '', 'textarea', { breit: true, placeholder: 'Woran erinnern?' }));

  dialog('Wiedervorlage setzen — ' + betrieb.name, inhalt, async daten => {
    await api('speichern', { methode: 'POST', daten: { id: betrieb.id, naechster_follow_up: daten.naechster_follow_up } });
    if (daten.notiz) {
      await api('aktivitaet', { methode: 'POST', daten: { betrieb_id: betrieb.id, typ: 'sonstiges', notiz: 'Wiedervorlage ' + datumDe(daten.naechster_follow_up) + ': ' + daten.notiz } });
    }
    melden('Wiedervorlage gesetzt.', 'gut');
    await betriebeLaden(); await detailOeffnen(betrieb.id);
  });
}

function notizDialog(betrieb) {
  const inhalt = el('div', { class: 'feldgitter' },
    auswahl('Typ', 'typ', 'sonstiges', Object.entries(Zustand.meta.typen).filter(([k]) => k !== 'stage')),
    feld('Datum', 'datum', heute(), 'date'),
    feld('Notiz', 'notiz', '', 'textarea', { breit: true, required: true }));

  dialog('Notiz hinzufügen — ' + betrieb.name, inhalt, async daten => {
    await api('aktivitaet', { methode: 'POST', daten: { betrieb_id: betrieb.id, ...daten } });
    melden('Notiz gespeichert.', 'gut');
    await betriebeLaden(); await detailOeffnen(betrieb.id);
  });
}

async function betriebLoeschen(id) {
  if (!confirm('Diesen Betrieb mitsamt Aktivitäten löschen?')) return;
  await api('loeschen', { methode: 'POST', daten: { id } });
  schliessen();
  await betriebeLaden();
  melden('Betrieb gelöscht.');
}

function neuerBetrieb() {
  const inhalt = el('div', { class: 'feldgitter' },
    feld('Name', 'name', '', 'text', { breit: true, required: true }),
    feld('Straße', 'strasse', ''), feld('PLZ', 'plz', '79098'),
    feld('Stadtteil', 'stadtteil', ''), feld('Telefon', 'telefon', ''),
    auswahl('Priorität', 'prioritaet', 'B', [['A', 'A'], ['B', 'B'], ['C', 'C']]),
    auswahl('Pizza-Fokus', 'pizza_fokus', 'Hauptfokus', [['Hauptfokus', 'Hauptfokus'], ['Teilweise', 'Teilweise']]));

  dialog('Neuer Betrieb', inhalt, async daten => {
    const { betrieb } = await api('speichern', { methode: 'POST', daten });
    melden('Betrieb angelegt.', 'gut');
    await betriebeLaden();
    detailOeffnen(betrieb.id);
  });
}

/* ---------------- Export ---------------- */

async function exportDialog(art) {
  const anzahl = Zustand.betriebe.length;
  if (anzahl === 0) { melden('Keine Betriebe im aktuellen Filter.', 'fehler'); return; }
  const { vorlagen } = await api('vorlagen', { params: { art: 'brief' } });

  const inhalt = art === 'etiketten'
    ? el('div', { class: 'feldgitter' },
      auswahl('Etikettenbogen', 'format', Zustand.meta.etikett_std, Object.entries(Zustand.meta.etiketten), { breit: true }),
      feld('Erste freie Position auf dem Bogen', 'start', '1', 'number', { min: '1' }),
      el('div', { class: 'feld' }, el('label', { text: 'Hilfslinien' }),
        el('label', { class: 'leise' }, el('input', { type: 'checkbox', name: 'hilfslinien' }), ' zum Testdruck einzeichnen')))
    : el('div', { class: 'feldgitter' },
      auswahl('Vorlage', 'vorlage', '', vorlagen.map(v => [v.id, v.name]), { breit: true }),
      el('div', { class: 'feld breit' }, el('label', { text: 'Protokollieren' }),
        el('label', { class: 'leise' }, el('input', { type: 'checkbox', name: 'protokollieren', checked: true }),
          ' Brief als Kontaktversuch protokollieren und Stufe „Neu" auf „Kontaktversuch 1" setzen')));

  dialog(`${art === 'etiketten' ? 'Etiketten' : 'Anschreiben'} für ${anzahl} Betrieb${anzahl === 1 ? '' : 'e'} erzeugen`, inhalt, async daten => {
    const params = new URLSearchParams({ art: art === 'etiketten' ? 'etiketten' : 'briefe', ...filterParams() });
    for (const [schluessel, wert] of Object.entries(daten)) if (wert) params.set(schluessel, wert === 'on' ? '1' : wert);
    window.open('export.php?' + params.toString(), '_blank');
    if (art !== 'etiketten' && daten.protokollieren) {
      setTimeout(async () => { await betriebeLaden(); await zielAktualisieren(); }, 1200);
    }
  });
}

function briefFuerBetrieb(betrieb) {
  window.open('export.php?art=briefe&ids=' + betrieb.id + '&protokollieren=1', '_blank');
  setTimeout(() => detailOeffnen(betrieb.id), 1200);
}

/* ---------------- E-Mail (Einzelversand) ---------------- */

async function mailPanel(betrieb) {
  const { vorlagen } = await api('vorlagen', { params: { art: 'mail' } });
  const standard = vorlagen[0] || { betreff: '', koerper: '' };
  const gesperrt = betrieb.kontaktversuche < 1;
  const keineAdresse = !betrieb.email || !betrieb.email.includes('@');

  const banner = el('div', { class: 'warnbanner' }, el('div', {},
    el('b', {}, '⚠ Kein Massenversand ohne vorherigen Kontakt. '),
    'Kalt-Werbemails an Gewerbetreibende sind nach § 7 UWG unzulässig — auch B2B. Diese Funktion ist bewusst auf Einzelversand nach dokumentiertem Telefonat, Brief oder Besuch beschränkt.'));

  const felder = el('div', { class: 'feldgitter' },
    feld('An', 'an', betrieb.email || '(keine Adresse hinterlegt)', 'text', { breit: true, readonly: true }),
    feld('Betreff', 'betreff', Vorlagenfuellen(standard.betreff, betrieb), 'text', { breit: true }),
    feld('Text', 'koerper', Vorlagenfuellen(standard.koerper, betrieb), 'textarea', { breit: true, style: 'min-height:230px' }));

  const inhalt = el('div', {}, banner,
    gesperrt ? el('p', { class: 'sperrhinweis', text: '🔒 Gesperrt: für diesen Betrieb ist noch kein Kontaktversuch protokolliert. Erst anrufen oder anschreiben.' }) : null,
    keineAdresse ? el('p', { class: 'sperrhinweis', text: '🔒 Gesperrt: keine E-Mail-Adresse hinterlegt.' }) : null,
    !Zustand.meta.mail_aktiv ? el('p', { class: 'sperrhinweis', text: '🔒 SMTP ist nicht konfiguriert (config.local.php) — der Text lässt sich trotzdem vorbereiten und kopieren.' }) : null,
    felder);

  const formular = el('form', { class: 'block', style: 'width:min(680px,94vw);margin:0;max-height:88vh;overflow:auto' },
    el('h3', { text: 'E-Mail an ' + betrieb.name }), inhalt,
    el('div', { class: 'zeile ende', style: 'margin-top:12px' },
      el('button', { class: 'knopf', type: 'button', onclick: () => huelle.remove() }, 'Schließen'),
      el('button', {
        class: 'knopf', type: 'button', onclick: () => {
          navigator.clipboard.writeText(formular.koerper.value);
          melden('Text kopiert.', 'gut');
        }
      }, 'Text kopieren'),
      el('button', { class: 'knopf haupt', type: 'submit', disabled: gesperrt || keineAdresse || !Zustand.meta.mail_aktiv }, 'Einzeln senden')));

  const huelle = el('div', { class: 'schleier', style: 'display:grid;place-items:center;z-index:60' }, formular);
  huelle.addEventListener('click', ereignis => { if (ereignis.target === huelle) huelle.remove(); });
  formular.addEventListener('submit', async ereignis => {
    ereignis.preventDefault();
    try {
      const daten = { id: betrieb.id, betreff: formular.betreff.value, koerper: formular.koerper.value };
      const { gesendet } = await api('mail_senden', { methode: 'POST', daten });
      melden('Gesendet an ' + gesendet.empfaenger, 'gut');
      huelle.remove();
      await betriebeLaden();
      await detailOeffnen(betrieb.id);
    } catch (fehler) { melden(fehler.message, 'fehler'); }
  });
  document.body.append(huelle);
}

/** Platzhalter clientseitig für die Vorschau ersetzen (Serverwert ist maßgeblich). */
function Vorlagenfuellen(text, betrieb) {
  if (!text) return '';
  const werte = {
    Name: betrieb.name,
    Anrede: betrieb.inhaber && !/recherchieren/i.test(betrieb.inhaber)
      ? `Sehr geehrte Frau ${betrieb.inhaber.split(' ').pop()}, sehr geehrter Herr ${betrieb.inhaber.split(' ').pop()}`
      : 'Sehr geehrte Damen und Herren',
    'Straße': betrieb.strasse, Strasse: betrieb.strasse,
    PLZ_Ort: [betrieb.plz, betrieb.ort].filter(Boolean).join(' '),
    PLZ: betrieb.plz, Ort: betrieb.ort, Stadtteil: betrieb.stadtteil,
    Inhaber: betrieb.inhaber, Telefon: betrieb.telefon,
    Datum: datumDe(heute()),
  };
  return text.replace(/\{\{\s*([\wÄÖÜäöüß_]+)\s*\}\}/g, (treffer, name) => werte[name] ?? treffer);
}

/* ---------------- Dashboard ---------------- */

function balken(name, wert, maximum, klasse = '') {
  return el('div', { class: 'balkenreihe ' + klasse },
    el('span', { class: 'name', text: name }),
    el('span', { class: 'spur' }, el('i', { style: 'width:' + (maximum > 0 ? Math.round(wert / maximum * 100) : 0) + '%' })),
    el('span', { class: 'wert', text: wert }));
}

async function dashboardZeichnen() {
  const dash = $('#dash');
  dash.replaceChildren(el('div', { class: 'leer voll', text: 'Lade …' }));
  const { dashboard: d, faellig } = await api('dashboard', { params: { bis: 'woche' } });
  $('#ziel-ist').textContent = d.gewonnen;
  $('#ziel-balken').style.width = d.fortschritt + '%';

  const maximumTrichter = d.funnel[0].anzahl || 1;
  const maximumStage = Math.max(...Object.values(d.pro_stage).map(s => s.anzahl), 1);

  dash.replaceChildren(
    el('div', { class: 'ziel-gross voll' },
      el('h2', { text: 'Startschuss-Prinzip' }),
      el('p', { class: 'leise', style: 'margin:4px 0 0' }, `Gedruckt wird, sobald ${d.ziel} Betriebe unterschrieben haben.`),
      el('div', { style: 'display:flex;align-items:baseline;gap:10px;margin-top:12px' },
        el('span', { class: 'zahl', text: d.gewonnen }),
        el('span', { class: 'leise', text: `von ${d.ziel} gewonnen — noch ${d.fehlend} bis zum Start` })),
      el('div', { class: 'balken' }, el('i', { style: 'width:' + d.fortschritt + '%' })),
      el('div', { class: 'leise', text: `${d.fortschritt} % erreicht · ${d.volumen_gewonnen.toLocaleString('de-DE')} Pizzen/Monat aus gewonnenen Betrieben (Schätzwert)` })),

    el('div', { class: 'kennzahlen voll' },
      kennzahl(d.gesamt, 'Betriebe in der Liste'),
      kennzahl(d.ohne_kontakt, 'noch nie kontaktiert', d.ohne_kontakt > 0 ? 'warnung' : ''),
      kennzahl(d.faellig_heute, 'Wiedervorlage heute fällig', d.faellig_heute > 0 ? 'warnung' : ''),
      kennzahl(d.kalt, '> 7 Tage ohne Kontakt & ohne Termin', d.kalt > 0 ? 'warnung' : ''),
      kennzahl(d.aktivitaeten_7t, 'Aktivitäten letzte 7 Tage', 'gut'),
      kennzahl(d.anreicherung.mit_telefon + '/' + d.gesamt, 'mit Telefonnummer')),

    el('div', { class: 'block trichter' }, el('h3', { text: 'Conversion-Funnel' }),
      d.funnel.map(stufe => balken(stufe.stufe, stufe.anzahl, maximumTrichter))),

    el('div', { class: 'block' }, el('h3', { text: 'Verteilung nach Pipeline-Stufe' }),
      Object.values(d.pro_stage).map(s => balken(s.label, s.anzahl, maximumStage))),

    el('div', { class: 'block' }, el('h3', { text: 'Priorität A/B/C' }),
      Object.entries(d.pro_prioritaet).map(([prio, werte]) =>
        balken(`Prio ${prio} (${werte.offen} offen)`, werte.gesamt, d.gesamt))),

    el('div', { class: 'block' }, el('h3', { text: 'Datenanreicherung' }),
      balken('Vollständig', d.anreicherung.gefunden, d.gesamt),
      balken('Offen', d.anreicherung.offen, d.gesamt),
      balken('Kein Impressum', d.anreicherung.kein_impressum, d.gesamt),
      balken('Mit E-Mail', d.anreicherung.mit_mail, d.gesamt),
      el('p', { class: 'leise', style: 'margin:10px 0 0' }, 'Anreicherung starten: ', el('code', {}, 'php bin/enrich.php --report'))),

    el('div', { class: 'block voll' },
      el('h3', {}, 'Fällige Wiedervorlagen', el('span', { class: 'leise', text: `heute: ${d.faellig_heute} · diese Woche: ${d.faellig_woche}` })),
      faellig.length === 0
        ? el('p', { class: 'leise', text: 'Nichts fällig. Gut — dann steht Neukontakt an.' })
        : el('table', { class: 'liste' },
          el('thead', {}, el('tr', {}, ['Betrieb', 'Stufe', 'Fällig', 'Telefon', ''].map(t => el('th', { text: t })))),
          el('tbody', {}, faellig.map(b => el('tr', { onclick: () => detailOeffnen(b.id) },
            el('td', { text: b.name, style: 'font-weight:600' }),
            el('td', { text: b.stage_label }),
            el('td', { text: datumDe(b.naechster_follow_up), style: 'color:var(--warnung);font-weight:600' }),
            el('td', { text: b.telefon || '—' }),
            el('td', {}, el('button', { class: 'knopf klein', onclick: ereignis => { ereignis.stopPropagation(); anrufDialog(b); } }, '☎')))))))
  );
}

function kennzahl(wert, beschriftung, klasse = '') {
  return el('div', { class: 'kennzahl ' + klasse }, el('b', { text: String(wert) }), el('span', { text: beschriftung }));
}

/* ---------------- Vorlagen ---------------- */

async function vorlagenZeichnen() {
  const { vorlagen } = await api('vorlagen');
  const behaelter = $('#vorlagen');
  const aktiv = vorlagen.find(v => v.id === Zustand.vorlageAktiv) || vorlagen[0];
  Zustand.vorlageAktiv = aktiv ? aktiv.id : null;

  const liste = el('div', { class: 'vorlagen-liste' },
    vorlagen.map(v => el('button', { class: v.id === Zustand.vorlageAktiv ? 'aktiv' : '', onclick: () => { Zustand.vorlageAktiv = v.id; vorlagenZeichnen(); } },
      v.name, el('small', { text: (v.art === 'brief' ? 'Brief' : 'E-Mail') + (Number(v.ist_standard) ? ' · Standard' : '') }))),
    el('button', {
      onclick: async () => {
        const { vorlage } = await api('vorlage_speichern', { methode: 'POST', daten: { art: 'brief', name: 'Neue Vorlage', koerper: '{{Anrede}},\n\n' } });
        Zustand.vorlageAktiv = vorlage.id;
        vorlagenZeichnen();
      }
    }, '+ Neue Vorlage'));

  if (!aktiv) { behaelter.replaceChildren(liste); return; }

  const formular = el('form', { class: 'block', style: 'margin:0' },
    el('h3', {}, 'Vorlage bearbeiten', el('span', { class: 'leise', text: 'Platzhalter anklicken zum Einfügen' })),
    el('div', { class: 'feldgitter' },
      feld('Name', 'name', aktiv.name, 'text'),
      auswahl('Art', 'art', aktiv.art, [['brief', 'Brief (PDF)'], ['mail', 'E-Mail']]),
      feld('Betreff', 'betreff', aktiv.betreff, 'text', { breit: true, placeholder: 'Bei Briefen optional — sonst erste Zeile „Betreff: …" im Text' }),
      feld('Text', 'koerper', aktiv.koerper, 'textarea', { breit: true, style: 'min-height:320px;font-family:ui-monospace,monospace;font-size:12.5px' })),
    el('div', { class: 'platzhalter-hilfe' },
      ['{{Anrede}}', '{{Name}}', '{{Straße}}', '{{PLZ_Ort}}', '{{Stadtteil}}', '{{Inhaber}}', '{{Telefon}}', '{{Datum}}', '{{Absender_firma}}', '{{Absender_telefon}}', '{{Absender_web}}']
        .map(p => el('code', {
          onclick: () => {
            const feldKnoten = formular.koerper;
            const pos = feldKnoten.selectionStart ?? feldKnoten.value.length;
            feldKnoten.value = feldKnoten.value.slice(0, pos) + p + feldKnoten.value.slice(feldKnoten.selectionEnd ?? pos);
            feldKnoten.focus();
          }
        }, p))),
    el('div', { class: 'zeile ende', style: 'margin-top:12px' },
      Number(aktiv.ist_standard) ? null : el('button', {
        class: 'knopf klein', type: 'button', onclick: async () => {
          if (!confirm('Vorlage löschen?')) return;
          await api('vorlage_loeschen', { methode: 'POST', daten: { id: aktiv.id } });
          Zustand.vorlageAktiv = null; vorlagenZeichnen();
        }
      }, 'Löschen'),
      el('button', { class: 'knopf', type: 'button', onclick: () => vorschauZeigen(aktiv.id) }, 'Vorschau am ersten Betrieb'),
      el('button', { class: 'knopf haupt', type: 'submit' }, 'Speichern')));

  formular.addEventListener('submit', async ereignis => {
    ereignis.preventDefault();
    const daten = Object.fromEntries(new FormData(formular).entries());
    daten.id = aktiv.id;
    await api('vorlage_speichern', { methode: 'POST', daten });
    melden('Vorlage gespeichert.', 'gut');
    vorlagenZeichnen();
  });

  const rechts = el('div', {},
    el('div', { class: 'warnbanner' }, el('div', {},
      el('b', {}, '⚠ Kein Massenversand ohne vorherigen Kontakt. '),
      'Briefvorlagen sind für Post und Einzelgespräch gedacht. E-Mail-Vorlagen dürfen nur einzeln versendet werden, nachdem ein Kontaktversuch dokumentiert ist (§ 7 UWG).')),
    formular);

  behaelter.replaceChildren(liste, rechts);
}

async function vorschauZeigen(vorlageId) {
  if (!Zustand.betriebe.length) { melden('Erst Betriebe laden.', 'fehler'); return; }
  const betrieb = Zustand.betriebe[0];
  const { betreff, koerper, adresse } = await api('vorschau', { params: { id: betrieb.id, vorlage: vorlageId } });
  const huelle = el('div', { class: 'schleier', style: 'display:grid;place-items:center;z-index:60' },
    el('div', { class: 'block', style: 'width:min(680px,94vw);max-height:88vh;overflow:auto;margin:0' },
      el('h3', { text: 'Vorschau — ' + betrieb.name }),
      el('p', { class: 'leise', text: adresse.join(' · ') }),
      betreff ? el('p', {}, el('b', { text: betreff })) : null,
      el('div', { class: 'vorschau', text: koerper }),
      el('div', { class: 'zeile ende', style: 'margin-top:12px' },
        el('button', { class: 'knopf', onclick: () => huelle.remove() }, 'Schließen'))));
  huelle.addEventListener('click', e => { if (e.target === huelle) huelle.remove(); });
  document.body.append(huelle);
}

/* ---------------- Start ---------------- */

function filterVerdrahten() {
  $$('#prio-gruppe button').forEach(knopf => knopf.addEventListener('click', () => {
    const prio = knopf.dataset.prio;
    const liste = Zustand.filter.prioritaet;
    Zustand.filter.prioritaet = liste.includes(prio) ? liste.filter(p => p !== prio) : [...liste, prio];
    knopf.classList.toggle('aktiv');
    betriebeLaden();
  }));

  const anbinden = (wahl, feld) => $(wahl).addEventListener('change', ereignis => {
    Zustand.filter[feld] = ereignis.target.value;
    betriebeLaden();
  });
  anbinden('#f-stadtteil', 'stadtteil');
  anbinden('#f-fokus', 'pizza_fokus');
  anbinden('#f-tag', 'tag');

  let bremse;
  $('#f-suche').addEventListener('input', ereignis => {
    clearTimeout(bremse);
    bremse = setTimeout(() => { Zustand.filter.suche = ereignis.target.value.trim(); betriebeLaden(); }, 250);
  });

  for (const [wahl, feld] of [['#f-faellig', 'followup_faellig'], ['#f-ohnekontakt', 'ohne_kontakt']]) {
    $(wahl).addEventListener('click', () => {
      Zustand.filter[feld] = !Zustand.filter[feld];
      $(wahl).classList.toggle('aktiv', Zustand.filter[feld]);
      betriebeLaden();
    });
  }

  $('#btn-neu').addEventListener('click', neuerBetrieb);
  $('#btn-brief').addEventListener('click', () => exportDialog('briefe'));
  $('#btn-etiketten').addEventListener('click', () => exportDialog('etiketten'));
  $('#btn-csv').addEventListener('click', () => {
    window.open('export.php?' + new URLSearchParams({ art: 'csv', ...filterParams() }).toString(), '_blank');
  });
  $$('#reiter button').forEach(b => b.addEventListener('click', () => ansichtWechseln(b.dataset.ansicht)));
  document.addEventListener('keydown', ereignis => { if (ereignis.key === 'Escape') schliessen(); });
}

async function start() {
  try {
    Zustand.meta = await api('start');
  } catch (fehler) {
    document.body.innerHTML = '<div class="leer">Nicht angemeldet. <a href="login.php">Zur Anmeldung</a></div>';
    return;
  }
  const fuellen = (wahl, werte) => {
    const knoten = $(wahl);
    for (const wert of werte) knoten.append(el('option', { value: wert }, wert));
  };
  fuellen('#f-stadtteil', Zustand.meta.filter.stadtteile);
  fuellen('#f-fokus', Zustand.meta.filter.pizza_fokus);
  fuellen('#f-tag', Zustand.meta.filter.tags);
  $('#ziel-soll').textContent = Zustand.meta.ziel;

  filterVerdrahten();
  await betriebeLaden();
  await zielAktualisieren();
}

start();
