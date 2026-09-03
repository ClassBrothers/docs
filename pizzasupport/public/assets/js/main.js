/*!
 * pizzasupport.de – Hauptskript
 *
 * Grundsatz: Alles funktioniert auch ohne JavaScript. Was hier passiert,
 * macht die Bedienung angenehmer, ist aber nie Voraussetzung. Geladen wird
 * mit defer, schwere Teile (Karte, Konfetti) erst bei Bedarf.
 */
(function () {
  'use strict';

  var wenigerBewegung = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------------ */
  /* Sicherheitsabfrage im Kontaktformular: Klick auf eine Option setzt  */
  /* das verborgene Feld. Ohne JavaScript bleibt die Textalternative im  */
  /* <details>-Element die einzige, aber vollständig funktionsfähige     */
  /* Möglichkeit - kein Nutzer wird ausgesperrt.                         */
  /* ------------------------------------------------------------------ */
  var captchaFeld = document.getElementById('k-captcha-klick');
  if (captchaFeld) {
    Array.prototype.forEach.call(document.querySelectorAll('[data-captcha-wert]'), function (knopf) {
      knopf.addEventListener('click', function () {
        captchaFeld.value = knopf.getAttribute('data-captcha-wert');
        var gruppe = knopf.closest('.captcha-optionen');
        Array.prototype.forEach.call(gruppe.querySelectorAll('[data-captcha-wert]'), function (k) {
          var gewaehlt = k === knopf;
          k.classList.toggle('ist-gewaehlt', gewaehlt);
          k.setAttribute('aria-pressed', gewaehlt ? 'true' : 'false');
        });
      });
    });
  }

  /* ------------------------------------------------------------------ */
  /* Ersparnisrechner: liest die Menge live aus dem Bestellformular       */
  /* mit (Felder "menge[...]"), rechnet ausschliesslich im Browser -      */
  /* nichts davon wird gesendet, solange niemand tatsaechlich bestellt.   */
  /* ------------------------------------------------------------------ */
  var rechnerBox = document.querySelector('[data-rechner]');
  if (rechnerBox) {
    var rPreis      = rechnerBox.querySelector('[data-rechner-preis]');
    var rMonat      = rechnerBox.querySelector('[data-rechner-monat]');
    var rMengeWert  = rechnerBox.querySelector('[data-rechner-menge-wert]');
    var rErgebnis   = rechnerBox.querySelector('[data-rechner-ergebnis]');
    var rZahl       = rechnerBox.querySelector('[data-rechner-zahl]');
    var rHinweis    = rechnerBox.querySelector('[data-rechner-hinweis]');
    var euroFormat  = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });

    function rechnerMenge() {
      var summe = 0;
      Array.prototype.forEach.call(document.querySelectorAll('input[name^="menge["]'), function (f) {
        var n = parseInt(f.value, 10);
        if (!isNaN(n) && n > 0) { summe += n; }
      });
      return summe;
    }

    function rechnerAktualisieren() {
      var menge = rechnerMenge();
      if (rMengeWert) { rMengeWert.textContent = menge > 0 ? menge.toLocaleString('de-DE') : '–'; }

      var preis = parseFloat((rPreis.value || '').replace(',', '.'));
      if (!menge || isNaN(preis) || preis <= 0) {
        rErgebnis.hidden = true;
        return;
      }

      rZahl.textContent = euroFormat.format(preis * menge);

      var monat = parseInt(rMonat.value, 10);
      if (!isNaN(monat) && monat > 0) {
        var monate = Math.round((menge / monat) * 10) / 10;
        rHinweis.textContent = 'Das deckt etwa ' + monate.toLocaleString('de-DE') + ' Monate Deines Bedarfs.';
      } else {
        rHinweis.textContent = '';
      }
      rErgebnis.hidden = false;
    }

    rPreis.addEventListener('input', rechnerAktualisieren);
    rMonat.addEventListener('input', rechnerAktualisieren);
    document.addEventListener('input', function (e) {
      if (e.target.matches('input[name^="menge["]')) { rechnerAktualisieren(); }
    });
    rechnerAktualisieren();
  }

  /* ------------------------------------------------------------------ */
  /* Navigation auf schmalen Bildschirmen                                */
  /* ------------------------------------------------------------------ */
  var navKnopf = document.querySelector('.kopf-toggle');
  var nav = document.getElementById('hauptnav');

  if (navKnopf && nav) {
    navKnopf.addEventListener('click', function () {
      var offen = nav.classList.toggle('ist-offen');
      navKnopf.setAttribute('aria-expanded', offen ? 'true' : 'false');
      navKnopf.setAttribute('aria-label', offen ? 'Menü schließen' : 'Menü öffnen');
    });

    // Nach einem Klick auf einen Anker wieder zumachen.
    nav.addEventListener('click', function (e) {
      if (e.target.tagName === 'A' && nav.classList.contains('ist-offen')) {
        nav.classList.remove('ist-offen');
        navKnopf.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* ------------------------------------------------------------------ */
  /* Modale Dialoge: Fokusfalle, Escape, Rückgabe des Fokus              */
  /* ------------------------------------------------------------------ */
  var letzterFokus = null;

  function fokussierbare(box) {
    return Array.prototype.filter.call(
      box.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])'),
      function (el) { return el.offsetParent !== null; }
    );
  }

  function modalOeffnen(id) {
    var modal = document.getElementById(id);
    if (!modal) { return; }
    letzterFokus = document.activeElement;
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    var ziele = fokussierbare(modal);
    if (ziele.length) { ziele[0].focus(); }
  }

  function modalSchliessen(modal) {
    modal.hidden = true;
    document.body.style.overflow = '';
    if (letzterFokus && letzterFokus.focus) { letzterFokus.focus(); }
  }

  document.addEventListener('click', function (e) {
    var oeffner = e.target.closest('[data-modal-oeffnen]');
    if (oeffner) {
      e.preventDefault();
      modalOeffnen(oeffner.getAttribute('data-modal-oeffnen'));
      return;
    }
    var schliesser = e.target.closest('[data-modal-schliessen]');
    if (schliesser) {
      var modal = schliesser.closest('.modal');
      if (modal) { modalSchliessen(modal); }
    }
  });

  document.addEventListener('keydown', function (e) {
    var offen = document.querySelector('.modal:not([hidden])');
    if (!offen) { return; }

    if (e.key === 'Escape') {
      modalSchliessen(offen);
      return;
    }
    // Tabulator im Dialog halten, sonst landet man hinter dem Overlay.
    if (e.key === 'Tab') {
      var ziele = fokussierbare(offen);
      if (!ziele.length) { return; }
      var erster = ziele[0];
      var letzter = ziele[ziele.length - 1];
      if (e.shiftKey && document.activeElement === erster) {
        e.preventDefault();
        letzter.focus();
      } else if (!e.shiftKey && document.activeElement === letzter) {
        e.preventDefault();
        erster.focus();
      }
    }
  });

  // Nach einem Fehler im Empfehlungsformular direkt wieder aufmachen.
  if (window.location.hash === '#modal-empfehlung') {
    modalOeffnen('modal-empfehlung');
  }

  /* ------------------------------------------------------------------ */
  /* Einwilligung. Entscheidung liegt lokal im Browser, nie auf dem Server. */
  /* ------------------------------------------------------------------ */
  var CONSENT_KEY = 'ps-consent-v1';

  function consentLesen() {
    try {
      var roh = window.localStorage.getItem(CONSENT_KEY);
      return roh ? JSON.parse(roh) : null;
    } catch (e) {
      return null;   // Privater Modus oder gesperrter Speicher: einfach fragen.
    }
  }

  function consentSchreiben(wert) {
    try {
      window.localStorage.setItem(CONSENT_KEY, JSON.stringify(wert));
    } catch (e) { /* nicht schlimm, dann fragen wir eben erneut */ }
    document.dispatchEvent(new CustomEvent('ps:consent', { detail: wert }));
  }

  window.psConsent = {
    erlaubt: function (bereich) {
      var c = consentLesen();
      return !!(c && c[bereich]);
    }
  };

  var consentBox = document.getElementById('consent');
  if (consentBox) {
    var karteCheckbox = document.getElementById('consent-karte');
    var analyseCheckbox = document.getElementById('consent-analyse');

    // Schließen (Kreuz, Klick daneben, Escape) zählt wie "Nur Notwendiges" -
    // ein Dialog, den man einfach zumacht, ist nie eine Zustimmung.
    function consentEntscheidung(art) {
      var wert = { notwendig: true, karte: false, analyse: false, stand: new Date().toISOString() };
      if (art === 'alle') { wert.karte = true; wert.analyse = true; }
      if (art === 'auswahl') {
        if (karteCheckbox) { wert.karte = karteCheckbox.checked; }
        if (analyseCheckbox) { wert.analyse = analyseCheckbox.checked; }
      }
      consentSchreiben(wert);
      consentBox.hidden = true;
      document.body.style.overflow = '';
    }

    if (!consentLesen()) {
      consentBox.hidden = false;
      document.body.style.overflow = 'hidden';
    }

    consentBox.addEventListener('click', function (e) {
      var knopf = e.target.closest('[data-consent]');
      if (!knopf) { return; }
      consentEntscheidung(knopf.getAttribute('data-consent'));
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !consentBox.hidden) {
        consentEntscheidung('ablehnen');
      }
    });

    document.addEventListener('click', function (e) {
      if (!e.target.closest('[data-consent-oeffnen]')) { return; }
      e.preventDefault();
      var c = consentLesen();
      if (karteCheckbox) { karteCheckbox.checked = !!(c && c.karte); }
      if (analyseCheckbox) { analyseCheckbox.checked = !!(c && c.analyse); }
      consentBox.hidden = false;
      document.body.style.overflow = 'hidden';
      consentBox.scrollIntoView({ block: 'nearest' });
    });
  }

  /* ------------------------------------------------------------------ */
  /* Google Analytics: laedt erst, wenn im Consent-Banner zugestimmt     */
  /* wurde. Vorher baut die Seite keinerlei Verbindung zu Google auf.    */
  /* ------------------------------------------------------------------ */
  var GA_MESSKENNUNG = 'G-H0S2ZPCEMT';
  var gaGeladen = false;

  function gaStarten() {
    if (gaGeladen) { return; }
    gaGeladen = true;

    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', GA_MESSKENNUNG);

    var skript = document.createElement('script');
    skript.async = true;
    skript.src = 'https://www.googletagmanager.com/gtag/js?id=' + GA_MESSKENNUNG;
    document.head.appendChild(skript);
  }

  if (window.psConsent.erlaubt('analyse')) {
    gaStarten();
  }
  document.addEventListener('ps:consent', function (e) {
    if (e.detail && e.detail.analyse) { gaStarten(); }
  });

  /* ------------------------------------------------------------------ */
  /* Mengen je Format im Bestellformular                                 */
  /* ------------------------------------------------------------------ */
  var formateListe = document.querySelector('[data-formate-liste]');
  if (formateListe) {
    var formatMin  = parseInt(formateListe.getAttribute('data-format-min'), 10) || 0;
    var gesamtMin  = parseInt(formateListe.getAttribute('data-gesamt-min'), 10) || 0;
    var gesamtMax  = parseInt(formateListe.getAttribute('data-gesamt-max'), 10) || 0;
    var summeBox   = document.querySelector('[data-mengen-summe]');
    var summeWertEl     = summeBox ? summeBox.querySelector('[data-mengen-summe-wert]') : null;
    var summeHinweisEl  = summeBox ? summeBox.querySelector('[data-mengen-summe-hinweis]') : null;

    function summeAktualisieren() {
      if (!summeWertEl) { return; }
      var gesamt = 0;
      Array.prototype.forEach.call(formateListe.querySelectorAll('input[type="number"]'), function (f) {
        var n = parseInt(f.value, 10);
        if (!isNaN(n) && n > 0) { gesamt += n; }
      });
      summeWertEl.textContent = gesamt.toLocaleString('de-DE') + ' Kartons';
      if (gesamt === 0) {
        summeHinweisEl.textContent = 'Trag oben ein, wie viele Kartons Du brauchst.';
      } else if (gesamt < gesamtMin) {
        summeHinweisEl.textContent = 'Noch ' + (gesamtMin - gesamt).toLocaleString('de-DE') + ' bis zur Mindestmenge von ' + gesamtMin.toLocaleString('de-DE') + '.';
      } else if (gesamt > gesamtMax) {
        summeHinweisEl.textContent = 'Für mehr als ' + gesamtMax.toLocaleString('de-DE') + ' melde Dich bitte direkt bei uns.';
      } else {
        summeHinweisEl.textContent = 'Menge passt.';
      }
    }

    Array.prototype.forEach.call(formateListe.querySelectorAll('[data-menge-gruppe]'), function (gruppe) {
      var feld     = gruppe.querySelector('input[type="number"]');
      var knoepfe  = gruppe.querySelectorAll('[data-menge-wert]');

      function spiegeln() {
        Array.prototype.forEach.call(knoepfe, function (k) {
          k.classList.toggle('ist-aktiv', k.getAttribute('data-menge-wert') === feld.value);
        });
      }
      function pruefen() {
        var n = parseInt(feld.value, 10);
        feld.setCustomValidity(
          feld.value !== '' && n > 0 && n < formatMin
            ? 'Mindestens ' + formatMin + ' Stück, sonst leer lassen.'
            : ''
        );
      }

      Array.prototype.forEach.call(knoepfe, function (k) {
        k.addEventListener('click', function () {
          feld.value = k.getAttribute('data-menge-wert');
          spiegeln();
          pruefen();
          summeAktualisieren();
        });
      });
      feld.addEventListener('input', function () {
        spiegeln();
        pruefen();
        summeAktualisieren();
      });
      spiegeln();
      pruefen();
    });

    summeAktualisieren();
  }

  /* ------------------------------------------------------------------ */
  /* Lieferart im Bestellformular: zeigt die Abrufmenge nur bei          */
  /* "Monatlicher Abruf" und rechnet vor, auf wie viele Monate sich die  */
  /* Gesamtmenge bei der gewuenschten Abrufmenge verteilt.               */
  /* ------------------------------------------------------------------ */
  var lieferartGruppe = document.querySelector('[data-lieferart-gruppe]');
  if (lieferartGruppe) {
    var lieferartFelder  = lieferartGruppe.querySelectorAll('[data-lieferart-wahl]');
    var abrufFeld        = lieferartGruppe.querySelector('[data-lieferart-feld="abruf"]');
    var abholungHinweis  = lieferartGruppe.querySelector('[data-lieferart-feld="abholung"]');
    var abrufMengeFeld   = document.getElementById('g-abrufmenge');
    var abrufHinweis     = document.getElementById('g-abrufmenge-hilfe');
    var abrufHinweisText = abrufHinweis ? abrufHinweis.textContent : '';

    function lieferartAnzeigen() {
      var gewaehlt = lieferartGruppe.querySelector('[data-lieferart-wahl]:checked');
      var wert = gewaehlt ? gewaehlt.value : 'gesamt';
      if (abrufFeld) { abrufFeld.hidden = wert !== 'abruf'; }
      if (abholungHinweis) { abholungHinweis.hidden = wert !== 'abholung'; }
    }

    function abrufMengeAnzeigen() {
      if (!abrufMengeFeld || !abrufHinweis) { return; }
      var abrufMenge = parseInt(abrufMengeFeld.value, 10);
      var gesamt = 0;
      Array.prototype.forEach.call(document.querySelectorAll('input[name^="menge["]'), function (f) {
        var n = parseInt(f.value, 10);
        if (!isNaN(n) && n > 0) { gesamt += n; }
      });
      if (abrufMenge > 0 && gesamt > 0) {
        var monate = Math.ceil(gesamt / abrufMenge);
        abrufHinweis.textContent = abrufHinweisText + ' Bei dieser Menge verteilt sich Deine Bestellung auf etwa '
          + monate + (monate === 1 ? ' Monat.' : ' Monate.');
      } else {
        abrufHinweis.textContent = abrufHinweisText;
      }
    }

    Array.prototype.forEach.call(lieferartFelder, function (f) {
      f.addEventListener('change', lieferartAnzeigen);
    });
    if (abrufMengeFeld) { abrufMengeFeld.addEventListener('input', abrufMengeAnzeigen); }
    document.addEventListener('input', function (e) {
      if (e.target.matches('input[name^="menge["]')) { abrufMengeAnzeigen(); }
    });
    lieferartAnzeigen();
    abrufMengeAnzeigen();
  }

  /* ------------------------------------------------------------------ */
  /* Versandzuschlag ausserhalb Freiburgs, anhand der eingetragenen PLZ  */
  /* ------------------------------------------------------------------ */
  var versandBox = document.querySelector('[data-versand-zuschlag]');
  var plzFeld    = document.getElementById('g-plz');
  var ortFeld    = document.getElementById('g-ort');
  if (versandBox && plzFeld) {
    var plzVon = parseInt(versandBox.getAttribute('data-plz-von'), 10);
    var plzBis = parseInt(versandBox.getAttribute('data-plz-bis'), 10);
    var freieOrte = [];
    try { freieOrte = JSON.parse(versandBox.getAttribute('data-freie-orte') || '[]'); } catch (eJson) { freieOrte = []; }
    var versandCheckbox = versandBox.querySelector('input[type="checkbox"]');

    function versandPruefen() {
      var plz = parseInt(plzFeld.value, 10);
      var plzAusserhalb = plzFeld.value.length === 5 && !isNaN(plz) && (plz < plzVon || plz > plzBis);
      var ortIstFrei = ortFeld && freieOrte.indexOf(ortFeld.value.trim().toLowerCase()) !== -1;
      var ausserhalb = plzAusserhalb && !ortIstFrei;
      versandBox.hidden = !ausserhalb;
      if (!ausserhalb && versandCheckbox) { versandCheckbox.checked = false; }
    }
    plzFeld.addEventListener('input', versandPruefen);
    if (ortFeld) { ortFeld.addEventListener('input', versandPruefen); }
    versandPruefen();
  }

  /* ------------------------------------------------------------------ */
  /* Buchen-Knopf beim Werbepartner: Button-Lösung nach § 312j BGB.      */
  /* Der statische Text im HTML ist die rechtssichere Vorgabe für         */
  /* Privatpersonen; nur bei erkennbarer Unternehmensbuchung wird auf     */
  /* den freundlicheren Text wechselt - das braucht kein JavaScript,      */
  /* also bleibt ohne Skript die sichere Formulierung stehen.             */
  /* ------------------------------------------------------------------ */
  var buchenKnopf = document.querySelector('[data-buchen-knopf]');
  if (buchenKnopf) {
    var artFelder = document.querySelectorAll('input[name="art"]');
    var knopfBeschriften = function () {
      var gewaehlt = document.querySelector('input[name="art"]:checked');
      var istUnternehmen = gewaehlt && gewaehlt.value === 'unternehmen';
      buchenKnopf.textContent = buchenKnopf.getAttribute(
        istUnternehmen ? 'data-label-unternehmen' : 'data-label-privat'
      );
    };
    Array.prototype.forEach.call(artFelder, function (f) {
      f.addEventListener('change', knopfBeschriften);
    });
    knopfBeschriften();
  }

  /* ------------------------------------------------------------------ */
  /* Auftragswert auf der Werbepartner-Seite                             */
  /*                                                                     */
  /* Reine Anzeigehilfe. Verbindlich ist ausschließlich die Berechnung    */
  /* auf dem Server - hier wird nichts übertragen, was zählt.            */
  /* ------------------------------------------------------------------ */
  var rechner = document.querySelector('[data-preisrechner]');
  var summeBox = document.querySelector('[data-summe]');

  if (rechner && summeBox) {
    var summeWert = summeBox.querySelector('[data-summe-wert]');
    var summeHinweis = summeBox.querySelector('[data-summe-hinweis]');
    var couponFeld = document.querySelector('[data-coupon]');
    var MWST = 19;
    var RABATT = 10;

    var euro = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });

    /* Fun Area: Preis haengt von der gewaehlten Flaeche ab, nicht von      */
    /* einem Festpreis. Das Ergebnis wird als data-preis auf die Checkbox   */
    /* geschrieben, danach rechnet neuRechnen() ganz normal weiter.         */
    var funareaBlock = document.querySelector('[data-funarea-block]');
    var funareaCheckbox = document.querySelector('[data-funarea-checkbox]');
    if (funareaBlock && funareaCheckbox) {
      var faBreiteFeld = funareaBlock.querySelector('[data-funarea-breite-feld]');
      var faHoeheFeld  = funareaBlock.querySelector('[data-funarea-hoehe-feld]');
      var faErgebnis   = funareaBlock.querySelector('[data-funarea-ergebnis]');
      var faErgebnisText = faErgebnis ? faErgebnis.textContent : '';
      var faPreisJeCm2 = parseInt(funareaBlock.getAttribute('data-preis-je-cm2'), 10) || 0;
      var faMindest    = parseFloat(funareaBlock.getAttribute('data-min-flaeche')) || 0;

      function funareaAktualisieren() {
        funareaBlock.hidden = !funareaCheckbox.checked;
        if (!funareaCheckbox.checked) { return; }

        var breite = parseFloat((faBreiteFeld.value || '').replace(',', '.'));
        var hoehe  = parseFloat((faHoeheFeld.value || '').replace(',', '.'));
        if (isNaN(breite) || isNaN(hoehe) || breite <= 0 || hoehe <= 0) {
          funareaCheckbox.setAttribute('data-preis', '0');
          if (faErgebnis) { faErgebnis.textContent = faErgebnisText; }
          return;
        }
        // Flaeche auf eine Nachkommastelle runden, danach multiplizieren.
        var flaeche = Math.round(breite * hoehe * 10) / 10;
        var preisCent = Math.round(flaeche * faPreisJeCm2);
        funareaCheckbox.setAttribute('data-preis', String(preisCent));

        if (faErgebnis) {
          if (flaeche < faMindest) {
            faErgebnis.textContent = 'Mindestens ' + faMindest.toLocaleString('de-DE') + ' cm² nötig - aktuell '
              + flaeche.toLocaleString('de-DE') + ' cm².';
          } else {
            faErgebnis.textContent = flaeche.toLocaleString('de-DE') + ' cm² · '
              + euro.format(preisCent / 100) + ' brutto.';
          }
        }
      }

      Array.prototype.forEach.call(funareaBlock.querySelectorAll('[data-funarea-breite]'), function (knopf) {
        knopf.addEventListener('click', function () {
          faBreiteFeld.value = knopf.getAttribute('data-funarea-breite');
          faHoeheFeld.value = knopf.getAttribute('data-funarea-hoehe');
          funareaAktualisieren();
          neuRechnenAusloesen();
        });
      });
      faBreiteFeld.addEventListener('input', function () { funareaAktualisieren(); neuRechnenAusloesen(); });
      faHoeheFeld.addEventListener('input', function () { funareaAktualisieren(); neuRechnenAusloesen(); });
      funareaCheckbox.addEventListener('change', funareaAktualisieren);
      funareaAktualisieren();
    }

    function neuRechnenAusloesen() {
      rechner.dispatchEvent(new Event('change'));
    }

    function neuRechnen() {
      var netto = 0;
      var anzahl = 0;
      Array.prototype.forEach.call(rechner.querySelectorAll('input[type="checkbox"]:checked'), function (cb) {
        var preis = parseInt(cb.getAttribute('data-preis'), 10) || 0;
        // Brutto-Preise auf netto zurückrechnen, wie es der Server auch tut.
        if (cb.getAttribute('data-brutto') === '1') {
          preis = Math.round(preis / (1 + MWST / 100));
        }
        netto += preis;
        anzahl++;
      });

      if (!anzahl) {
        summeBox.hidden = true;
        return;
      }

      var rabatt = 0;
      if (couponFeld && couponFeld.checked) {
        rabatt = Math.round(netto * RABATT / 100);
        netto -= rabatt;
      }
      var brutto = Math.round(netto * (1 + MWST / 100));

      summeBox.hidden = false;
      summeWert.textContent = euro.format(netto / 100) + ' netto';
      summeHinweis.textContent = euro.format(brutto / 100) + ' brutto'
        + (rabatt ? ' · Gutschein-Nachlass ' + euro.format(rabatt / 100) + ' berücksichtigt' : '')
        + ' · unverbindlich, verbindlich wird die Auftragsbestätigung';
    }

    /* Wunschflaeche: nur Positionen zeigen, die zu einer gerade gewaehlten */
    /* Flaeche passen - wer "Deckel groß" bucht, soll nicht zwischen Slots  */
    /* fuer "Deckel klein" oder Seitenflaechen waehlen muessen.             */
    var wunschBlock = document.querySelector('[data-wunschflaeche-block]');
    if (wunschBlock) {
      var wunschHinweis = wunschBlock.querySelector('[data-wunschflaeche-hinweis]');
      var wunschGruppen = wunschBlock.querySelectorAll('[data-wunschflaeche-gruppe]');
      var wunschLabels  = wunschBlock.querySelectorAll('[data-paket]');

      function wunschflaechenAktualisieren() {
        var gewaehltePakete = [];
        Array.prototype.forEach.call(rechner.querySelectorAll('input[type="checkbox"]:checked'), function (cb) {
          gewaehltePakete.push(cb.value);
        });

        Array.prototype.forEach.call(wunschLabels, function (label) {
          var passt = gewaehltePakete.indexOf(label.getAttribute('data-paket')) !== -1;
          label.hidden = !passt;
          if (!passt) {
            var eingabe = label.querySelector('input');
            if (eingabe) { eingabe.checked = false; }
          }
        });

        Array.prototype.forEach.call(wunschGruppen, function (gruppe) {
          gruppe.hidden = !gruppe.querySelector('[data-paket]:not([hidden])');
        });

        if (wunschHinweis) { wunschHinweis.hidden = gewaehltePakete.length > 0; }
      }

      rechner.addEventListener('change', wunschflaechenAktualisieren);
      wunschflaechenAktualisieren();
    }

    rechner.addEventListener('change', neuRechnen);
    if (couponFeld) { couponFeld.addEventListener('change', neuRechnen); }
    neuRechnen();
  }

  /* ------------------------------------------------------------------ */
  /* Konfetti-Danke. Läuft nur, wenn jemand Bewegung sehen will.         */
  /* ------------------------------------------------------------------ */
  var danke = document.querySelector('[data-konfetti]');
  if (danke) {
    danke.focus();
    danke.scrollIntoView({ behavior: wenigerBewegung ? 'auto' : 'smooth', block: 'center' });
    if (!wenigerBewegung) {
      konfettiStarten(danke.querySelector('canvas'));
    }
  }

  function konfettiStarten(canvas) {
    if (!canvas || !canvas.getContext) { return; }
    var ctx = canvas.getContext('2d');
    var farben = ['#f07e22', '#f7c94b', '#b5342a', '#3e6b43', '#16375b', '#ffffff'];
    var teilchen = [];
    var laeuft = true;
    var start = performance.now();

    function groesse() {
      var r = canvas.getBoundingClientRect();
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = r.width * dpr;
      canvas.height = r.height * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      return r;
    }

    var rect = groesse();

    for (var i = 0; i < 90; i++) {
      teilchen.push({
        x: rect.width / 2 + (Math.random() - 0.5) * 60,
        y: rect.height / 2 - 10,
        vx: (Math.random() - 0.5) * 7,
        vy: -Math.random() * 9 - 4,
        b: 5 + Math.random() * 5,
        h: 3 + Math.random() * 5,
        dreh: Math.random() * Math.PI,
        drehV: (Math.random() - 0.5) * 0.25,
        farbe: farben[(Math.random() * farben.length) | 0]
      });
    }

    function bild(jetzt) {
      if (!laeuft) { return; }
      ctx.clearRect(0, 0, rect.width, rect.height);

      for (var i = 0; i < teilchen.length; i++) {
        var t = teilchen[i];
        t.vy += 0.22;          // Schwerkraft
        t.vx *= 0.995;         // Luftwiderstand
        t.x += t.vx;
        t.y += t.vy;
        t.dreh += t.drehV;

        ctx.save();
        ctx.translate(t.x, t.y);
        ctx.rotate(t.dreh);
        ctx.fillStyle = t.farbe;
        ctx.fillRect(-t.b / 2, -t.h / 2, t.b, t.h);
        ctx.restore();
      }

      // Nach vier Sekunden ist gut - kein Dauerläufer im Hintergrund.
      if (jetzt - start > 4000) {
        laeuft = false;
        ctx.clearRect(0, 0, rect.width, rect.height);
        return;
      }
      requestAnimationFrame(bild);
    }

    requestAnimationFrame(bild);

    // Wer den Tab wechselt, braucht keine Animation mehr.
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) { laeuft = false; }
    });
  }

  /* ------------------------------------------------------------------ */
  /* Teilnehmerliste: suchen, sortieren, filtern                         */
  /* ------------------------------------------------------------------ */
  var liste = document.querySelector('[data-t-liste]');
  if (liste) {
    var eintraege = Array.prototype.slice.call(liste.querySelectorAll('.teilnehmer-eintrag'));
    var suchfeld = document.querySelector('[data-t-suche]');
    var zaehler = document.querySelector('[data-t-zaehler]');
    var keine = document.querySelector('[data-t-keine]');
    var filter = 'alle';
    var sortierung = 'plz';

    function anwenden() {
      var suche = (suchfeld && suchfeld.value || '').trim().toLowerCase();
      var sichtbar = 0;

      eintraege.forEach(function (li) {
        var typOk = filter === 'alle' || li.getAttribute('data-t-typ') === filter;
        var text = li.textContent.toLowerCase();
        var suchOk = !suche || text.indexOf(suche) !== -1;
        var zeigen = typOk && suchOk;
        li.hidden = !zeigen;
        if (zeigen) { sichtbar++; }
      });

      if (zaehler) {
        zaehler.textContent = sichtbar === 1 ? '1 Eintrag' : sichtbar + ' Einträge';
      }
      if (keine) { keine.hidden = sichtbar !== 0; }
    }

    function sortieren() {
      var sortiert = eintraege.slice().sort(function (a, b) {
        if (sortierung === 'plz') {
          var pa = a.getAttribute('data-t-plz') || '';
          var pb = b.getAttribute('data-t-plz') || '';
          if (pa !== pb) { return pa.localeCompare(pb, 'de'); }
        }
        return (a.getAttribute('data-t-name') || '')
          .localeCompare(b.getAttribute('data-t-name') || '', 'de', { sensitivity: 'base' });
      });
      sortiert.forEach(function (li) { liste.appendChild(li); });
    }

    if (suchfeld) { suchfeld.addEventListener('input', anwenden); }

    document.addEventListener('click', function (e) {
      var s = e.target.closest('[data-t-sortierung]');
      if (s) {
        sortierung = s.getAttribute('data-t-sortierung');
        s.parentNode.querySelectorAll('.segment-knopf').forEach(function (k) {
          k.classList.toggle('ist-aktiv', k === s);
        });
        sortieren();
        return;
      }
      var f = e.target.closest('[data-t-filter]');
      if (f) {
        filter = f.getAttribute('data-t-filter');
        f.parentNode.querySelectorAll('.segment-knopf').forEach(function (k) {
          k.classList.toggle('ist-aktiv', k === f);
        });
        anwenden();
      }
    });

    sortieren();
    anwenden();
  }

  /* ------------------------------------------------------------------ */
  /* Karte erst laden, wenn sie gebraucht und erlaubt ist                */
  /* ------------------------------------------------------------------ */
  var karteHalter = document.querySelector('[data-karte]');
  if (karteHalter) {
    var karteConsent = karteHalter.querySelector('[data-karte-consent]');
    var karteFlaeche = karteHalter.querySelector('.karte-flaeche');
    var karteGeladen = false;

    function karteStarten() {
      if (karteGeladen) { return; }
      karteGeladen = true;
      if (karteConsent) { karteConsent.hidden = true; }
      karteFlaeche.hidden = false;

      var skript = document.createElement('script');
      // Der versionierte Pfad kommt vom Server (siehe asset() in bootstrap.php) -
      // ohne das haengt jede kuenftige Aenderung an karte.js an einem Jahr
      // Browser-Cache fest, weil dieses Skript hier selbst per FTP-Upload und
      // nicht ueber PHP ausgeliefert wird.
      skript.src = karteHalter.getAttribute('data-skript') || '/assets/js/karte.js';
      skript.defer = true;
      skript.addEventListener('load', function () {
        if (window.psKarte) { window.psKarte.starten(karteHalter, karteFlaeche); }
      });
      skript.addEventListener('error', function () {
        karteFlaeche.textContent = 'Die Karte lässt sich gerade nicht laden. '
          + 'Die Liste daneben zeigt alle Teilnehmer.';
        karteFlaeche.className += ' karte-fehler';
      });
      document.body.appendChild(skript);
    }

    var ladeKnopf = karteHalter.querySelector('[data-karte-laden]');
    if (ladeKnopf) {
      ladeKnopf.addEventListener('click', function () {
        // Ein Klick hier ist die Einwilligung für die Kartenkacheln.
        consentSchreiben({ notwendig: true, karte: true, stand: new Date().toISOString() });
        karteStarten();
      });
    }

    if (window.psConsent.erlaubt('karte')) {
      karteStarten();
    }
    document.addEventListener('ps:consent', function (e) {
      if (e.detail && e.detail.karte) { karteStarten(); }
    });
  }

  /* ------------------------------------------------------------------ */
  /* Klick auf einen Listeneintrag springt zum passenden Punkt           */
  /* ------------------------------------------------------------------ */
  document.addEventListener('click', function (e) {
    var knopf = e.target.closest('[data-t-springen]');
    if (!knopf) { return; }
    var id = knopf.getAttribute('data-t-springen');
    if (window.psKarte && window.psKarte.zeigen) {
      window.psKarte.zeigen(id);
    } else if (karteHalter) {
      // Karte noch nicht da: erst darauf hinweisen, wo sie herkommt.
      karteHalter.scrollIntoView({ behavior: wenigerBewegung ? 'auto' : 'smooth', block: 'center' });
    }
  });

}());

/* ---------------------------------------------------------------------------
 * Ausgehende Links
 *
 * Öffnet jeden Link, der auf eine fremde Domain zeigt, in einem neuen Tab und
 * setzt die Sicherheitsattribute. Gilt automatisch auch für Links, die später
 * dazukommen – niemand muss daran denken.
 *
 * Steuerung über Attribute im HTML:
 *   data-intern   -> Link wird komplett in Ruhe gelassen
 *   data-follow   -> neuer Tab ja, aber ohne nofollow (für eigene Projekte)
 *
 * Einsetzen: Inhalt ans Ende der bestehenden Haupt-JS-Datei anhängen.
 * Nicht als <script> direkt in die Seite schreiben – die Content-Security-Policy
 * blockiert Inline-Skripte.
 * ------------------------------------------------------------------------- */

(function () {
  'use strict';

  function eigeneDomain(hostname) {
    return hostname.replace(/^www\./, '').toLowerCase();
  }

  function linksAufbereiten(bereich) {
    var eigen = eigeneDomain(window.location.hostname);
    var links = (bereich || document).querySelectorAll('a[href]');

    Array.prototype.forEach.call(links, function (a) {
      if (a.hasAttribute('data-intern')) return;
      if (a.hasAttribute('data-extern-geprueft')) return;

      var url;
      try {
        url = new URL(a.getAttribute('href'), window.location.href);
      } catch (e) {
        return;
      }

      // mailto:, tel:, javascript: und Sprungmarken bleiben unberührt
      if (url.protocol !== 'http:' && url.protocol !== 'https:') return;
      if (eigeneDomain(url.hostname) === eigen) return;

      a.setAttribute('target', '_blank');

      var rel = (a.getAttribute('rel') || '').split(/\s+/).filter(Boolean);
      var noetig = a.hasAttribute('data-follow')
        ? ['noopener', 'noreferrer']
        : ['nofollow', 'noopener', 'noreferrer'];

      noetig.forEach(function (wert) {
        if (rel.indexOf(wert) === -1) rel.push(wert);
      });
      a.setAttribute('rel', rel.join(' '));

      // Hinweis für Screenreader, damit der Tabwechsel nicht überrascht
      if (!a.hasAttribute('aria-label')) {
        var text = (a.textContent || '').trim();
        if (text) a.setAttribute('aria-label', text + ' (öffnet in neuem Tab)');
      }

      a.setAttribute('data-extern-geprueft', '');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { linksAufbereiten(); });
  } else {
    linksAufbereiten();
  }

  // Falls Inhalte nachgeladen werden, lässt sich das erneut anstoßen:
  window.externeLinksPruefen = linksAufbereiten;
})();
