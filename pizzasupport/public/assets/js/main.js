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
  /* Ersparnisrechner: Preis je Karton x Kartons pro Monat, live bei      */
  /* jeder Eingabe. Preis und Menge haengen per form="formular-bestellen" */
  /* (siehe ersparnisrechner.php) am echten Bestellformular und werden    */
  /* mit der Bestellung mitgeschickt; das Rechnen hier ist reine Anzeige  */
  /* im Browser und beeinflusst die Bestellung selbst nicht.              */
  /*                                                                      */
  /* Beide Felder liegen bewusst NICHT in einem eigenen <form> - ein      */
  /* Enter im Feld wuerde sonst, weil form="formular-bestellen" sie an    */
  /* das entfernte Bestellformular bindet, dessen Absenden ausloesen.     */
  /* Ein eigener keydown-Handler faengt Enter ab, ohne etwas abzuschicken.*/
  /* ------------------------------------------------------------------ */
  var rechnerBox = document.querySelector('[data-rechner]');
  if (rechnerBox) {
    var rPreis      = rechnerBox.querySelector('[data-rechner-preis]');
    var rMonat      = rechnerBox.querySelector('[data-rechner-monat]');
    var rMonatssumme = rechnerBox.querySelector('[data-rechner-monatssumme]');
    var rJahressumme = rechnerBox.querySelector('[data-rechner-jahressumme]');
    var euroFormat  = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });

    function rechnerBerechnen() {
      var preis = parseFloat((rPreis.value || '').replace(',', '.'));
      var monat = parseInt(rMonat.value, 10);

      if (isNaN(preis) || preis <= 0 || isNaN(monat) || monat <= 0) {
        return;
      }

      var summe1 = preis * monat;
      var summe2 = summe1 * 12;
      rMonatssumme.textContent = euroFormat.format(summe1) + ' im Monat';
      rJahressumme.textContent = euroFormat.format(summe2);
    }

    [rPreis, rMonat].forEach(function (feld) {
      feld.addEventListener('input', rechnerBerechnen);
      feld.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          rechnerBerechnen();
        }
      });
    });
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
  /* Lagerhinweis: "Alles auf einmal" bei mehr als 1000 Kartons.  Steht  */
  /* hier oben als benannte function-Deklaration (nicht var = function), */
  /* damit sie schon existiert, bevor die Bloecke weiter unten sie beim  */
  /* Definieren ihrer eigenen Handler aufrufen - function-Deklarationen  */
  /* werden im gesamten umschliessenden Funktionskoerper gehoisted.      */
  /* ------------------------------------------------------------------ */
  function lagerHinweisAktualisieren() {
    var hinweis = document.querySelector('[data-lieferart-feld="lager-hinweis"]');
    if (!hinweis) { return; }
    var gewaehlt = document.querySelector('[data-lieferart-wahl]:checked');
    var wert = gewaehlt ? gewaehlt.value : 'gesamt';
    var gesamt = 0;
    Array.prototype.forEach.call(document.querySelectorAll('input[name^="menge["]'), function (f) {
      var n = parseInt(f.value, 10);
      if (!isNaN(n) && n > 0) { gesamt += n; }
    });
    hinweis.hidden = !(wert === 'gesamt' && gesamt > 1000);
  }

  /* ------------------------------------------------------------------ */
  /* Mengen je Format im Bestellformular                                 */
  /* ------------------------------------------------------------------ */
  var formateListe = document.querySelector('[data-formate-liste]');
  if (formateListe) {
    var formatMin  = parseInt(formateListe.getAttribute('data-format-min'), 10) || 0;
    var gesamtMin  = parseInt(formateListe.getAttribute('data-gesamt-min'), 10) || 0;
    var gesamtMax  = parseInt(formateListe.getAttribute('data-gesamt-max'), 10) || 0;
    // Eigener Name statt "summeBox": weiter unten im selben Skript gibt es
    // fuer den Auftragswert auf der Buchungsseite eine gleichnamige
    // var-Deklaration - var ist funktionsweit gueltig, eine zweite
    // Zuweisung haette die hier gefundene Referenz stillschweigend
    // ueberschrieben, sobald jener Codeabschnitt beim Laden ausgefuehrt wird.
    var mengenSummeBox  = document.querySelector('[data-mengen-summe]');
    var summeWertEl     = mengenSummeBox ? mengenSummeBox.querySelector('[data-mengen-summe-wert]') : null;
    var summeHinweisEl  = mengenSummeBox ? mengenSummeBox.querySelector('[data-mengen-summe-hinweis]') : null;
    var mengenregelnEl  = document.querySelector('[data-mengenregeln]');

    // Mengenregeln und Gesamtsumme stehen erst da, wenn eine Menge
    // eingetragen wurde - ohne JavaScript bleiben beide immer sichtbar.
    if (mengenregelnEl) { mengenregelnEl.hidden = true; }
    if (mengenSummeBox) { mengenSummeBox.hidden = true; }

    function summeAktualisieren() {
      if (!summeWertEl) { return; }
      var gesamt = 0;
      Array.prototype.forEach.call(formateListe.querySelectorAll('input[type="number"]'), function (f) {
        var n = parseInt(f.value, 10);
        if (!isNaN(n) && n > 0) { gesamt += n; }
      });
      if (mengenregelnEl) { mengenregelnEl.hidden = gesamt === 0; }
      if (mengenSummeBox) { mengenSummeBox.hidden = gesamt === 0; }
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
      lagerHinweisAktualisieren();
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
      lagerHinweisAktualisieren();
    }

    var abrufMindest = abrufMengeFeld ? (parseInt(abrufMengeFeld.getAttribute('min'), 10) || 0) : 0;

    function abrufMengeAnzeigen() {
      if (!abrufMengeFeld || !abrufHinweis) { return; }
      var abrufMenge = parseInt(abrufMengeFeld.value, 10);
      var gesamt = 0;
      Array.prototype.forEach.call(document.querySelectorAll('input[name^="menge["]'), function (f) {
        var n = parseInt(f.value, 10);
        if (!isNaN(n) && n > 0) { gesamt += n; }
      });

      // Unter der Mindestmenge ist eine Monatsverteilung Unsinn - stattdessen
      // klar sagen, dass die Menge zu klein ist.
      if (abrufMengeFeld.value !== '' && abrufMenge > 0 && abrufMenge < abrufMindest) {
        var fehlertext = 'Mindestliefermenge sind ' + abrufMindest.toLocaleString('de-DE') + ' Stück.';
        abrufHinweis.textContent = fehlertext;
        abrufMengeFeld.setCustomValidity(fehlertext);
        return;
      }
      abrufMengeFeld.setCustomValidity('');

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

    function versandPruefen() {
      var plz = parseInt(plzFeld.value, 10);
      var plzAusserhalb = plzFeld.value.length === 5 && !isNaN(plz) && (plz < plzVon || plz > plzBis);
      var ortIstFrei = ortFeld && freieOrte.indexOf(ortFeld.value.trim().toLowerCase()) !== -1;
      var ausserhalb = plzAusserhalb && !ortIstFrei;
      versandBox.hidden = !ausserhalb;
    }
    plzFeld.addEventListener('input', versandPruefen);
    if (ortFeld) { ortFeld.addEventListener('input', versandPruefen); }
    versandPruefen();
  }

  /* ------------------------------------------------------------------ */
  /* Betriebsart im Bestellformular: "Anderes" zeigt ein Freitextfeld,   */
  /* "Lieferdienst mit eigener Küche" einen Hinweis zur Verpackungssteuer.*/
  /* Beide Elemente stehen im HTML immer da (ohne JavaScript bleiben sie */
  /* sichtbar) - erst hier werden sie erst versteckt und dann gezielt    */
  /* wieder eingeblendet.                                                */
  /* ------------------------------------------------------------------ */
  var betriebsartFeld = document.querySelector('[data-betriebsart-feld]');
  if (betriebsartFeld) {
    var betriebsartWahlen   = betriebsartFeld.querySelectorAll('[data-betriebsart-wahl]');
    var betriebsartFreiFeld = document.querySelector('[data-betriebsart-frei]');
    var lieferdienstHinweis = document.querySelector('[data-betriebsart-lieferdienst-hinweis]');

    function betriebsartAnzeigen() {
      var gewaehlt = betriebsartFeld.querySelector('[data-betriebsart-wahl]:checked');
      var wert = gewaehlt ? gewaehlt.getAttribute('data-betriebsart-wahl') : '';
      if (betriebsartFreiFeld) { betriebsartFreiFeld.hidden = wert !== 'anderes'; }
      if (lieferdienstHinweis) { lieferdienstHinweis.hidden = wert !== 'lieferdienst'; }
    }

    Array.prototype.forEach.call(betriebsartWahlen, function (f) {
      f.addEventListener('change', betriebsartAnzeigen);
    });
    betriebsartAnzeigen();
  }

  /* ------------------------------------------------------------------ */
  /* Teilnehmerkarte im Bestellformular: das Website-Feld ist nur dann   */
  /* sinnvoll, wenn der Betrieb auf der Karte erscheinen soll. Die       */
  /* Checkbox ist per Voreinstellung angehakt, das Feld bleibt darum     */
  /* ohne JavaScript sichtbar - konsistent mit der Voreinstellung.       */
  /* ------------------------------------------------------------------ */
  var karteOkFeld = document.querySelector('[data-karte-ok]');
  var websiteFeld = document.querySelector('[data-website-feld]');
  if (karteOkFeld && websiteFeld) {
    function websiteAnzeigen() { websiteFeld.hidden = !karteOkFeld.checked; }
    karteOkFeld.addEventListener('change', websiteAnzeigen);
    websiteAnzeigen();
  }

  /* ------------------------------------------------------------------ */
  /* Bestellassistent in drei Schritten. Ohne JavaScript ist das Formular */
  /* eine einzige durchgehende Seite mit allen drei Schritten - erst hier */
  /* werden Fortschritt und Weiter/Zurück eingeblendet und die Schritte   */
  /* nacheinander gezeigt. Beim Wechsel wandert der Fokus auf die         */
  /* Schrittüberschrift, damit Screenreader den neuen Abschnitt ansagen.  */
  /* ------------------------------------------------------------------ */
  var assistent = document.querySelector('[data-assistent]');
  if (assistent) {
    var schritte = Array.prototype.slice.call(assistent.querySelectorAll('.assistent-schritt'));
    var fortschritt = assistent.querySelector('[data-assistent-fortschritt]');
    var anzahl = schritte.length;
    var aktuell = 0;

    function schrittZeigen(index) {
      aktuell = index;
      schritte.forEach(function (schritt, i) {
        schritt.hidden = i !== index;
        schritt.classList.toggle('ist-aktiv', i === index);
      });
      if (fortschritt) { fortschritt.textContent = 'Schritt ' + (index + 1) + ' von ' + anzahl; }
      var ueberschrift = schritte[index].querySelector('legend');
      if (ueberschrift) {
        ueberschrift.setAttribute('tabindex', '-1');
        ueberschrift.focus({ preventScroll: true });
      }
      schritte[index].scrollIntoView({ behavior: wenigerBewegung ? 'auto' : 'smooth', block: 'start' });
    }

    Array.prototype.forEach.call(assistent.querySelectorAll('[data-assistent-weiter]'), function (knopf) {
      knopf.hidden = false;
      knopf.addEventListener('click', function () {
        var inhalt = schritte[aktuell];
        // Native Browser-Validierung fuer die Felder des aktuellen Schritts:
        // erst weiter, wenn dort alles ausgefuellt ist.
        var ungueltig = inhalt.querySelector(':invalid');
        if (ungueltig) {
          inhalt.querySelectorAll('input, select, textarea').forEach(function (f) { f.reportValidity && f.checkValidity(); });
          if (typeof ungueltig.reportValidity === 'function') { ungueltig.reportValidity(); }
          return;
        }
        if (aktuell < anzahl - 1) { schrittZeigen(aktuell + 1); }
      });
    });
    Array.prototype.forEach.call(assistent.querySelectorAll('[data-assistent-zurueck]'), function (knopf) {
      knopf.hidden = false;
      knopf.addEventListener('click', function () {
        if (aktuell > 0) { schrittZeigen(aktuell - 1); }
      });
    });

    if (fortschritt) { fortschritt.hidden = false; }
    schrittZeigen(0);
  }

  /* ------------------------------------------------------------------ */
  /* Schritt "Dein Bedarf" speist automatisch den Ersparnisrechner oben  */
  /* auf der Seite: Wochenbedarf wird auf einen Monat hochgerechnet.     */
  /* ------------------------------------------------------------------ */
  var bedarfKartonsWoche  = document.querySelector('[data-bedarf-kartons-woche]');
  var bedarfEinkaufspreis = document.querySelector('[data-bedarf-einkaufspreis]');
  if (bedarfKartonsWoche && bedarfEinkaufspreis) {
    function bedarfInRechnerUebernehmen() {
      if (!rPreis || !rMonat) { return; }
      var woche = parseInt(bedarfKartonsWoche.value, 10);
      if (!isNaN(woche) && woche > 0) {
        rMonat.value = String(Math.round(woche * 4.33));
        // Ereignis statt Direktaufruf: rechnerBerechnen() ist im eigenen
        // Block deklariert (strict mode = blockscoped) und von hier aus
        // nicht erreichbar - der bestehende input-Listener uebernimmt das.
        rMonat.dispatchEvent(new Event('input', { bubbles: true }));
      }
      if (bedarfEinkaufspreis.value.trim() !== '') {
        rPreis.value = bedarfEinkaufspreis.value;
        rPreis.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
    bedarfKartonsWoche.addEventListener('input', bedarfInRechnerUebernehmen);
    bedarfEinkaufspreis.addEventListener('input', bedarfInRechnerUebernehmen);
  }

  /* ------------------------------------------------------------------ */
  /* Auftragswert auf der Buchungsseite                                   */
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

    function neuRechnen() {
      var netto = 0;
      var anzahl = 0;
      Array.prototype.forEach.call(rechner.querySelectorAll('input[type="checkbox"]:checked'), function (cb) {
        netto += parseInt(cb.getAttribute('data-preis'), 10) || 0;
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

    /* Beim Ueberfahren einer Formatauswahl die passenden Umrandungen auf     */
    /* dem Flaechenplan rechts aufleuchten lassen - reine Orientierungshilfe, */
    /* wirkt sich auf nichts Verbindliches aus.                              */
    var alleUmrandungen = document.querySelectorAll('[data-paket-umrandung]');
    if (alleUmrandungen.length) {
      Array.prototype.forEach.call(rechner.querySelectorAll('label.wahl'), function (label) {
        var eingabe = label.querySelector('input[type="checkbox"]');
        if (!eingabe) { return; }
        var passende = document.querySelectorAll('[data-paket-umrandung="' + eingabe.value + '"]');
        if (!passende.length) { return; }

        label.addEventListener('mouseenter', function () {
          Array.prototype.forEach.call(passende, function (kasten) { kasten.classList.add('ist-aktiv'); });
        });
        label.addEventListener('mouseleave', function () {
          Array.prototype.forEach.call(passende, function (kasten) { kasten.classList.remove('ist-aktiv'); });
        });
      });
    }

    rechner.addEventListener('change', neuRechnen);
    if (couponFeld) { couponFeld.addEventListener('change', neuRechnen); }
    neuRechnen();
  }

  /* ------------------------------------------------------------------ */
  /* Flächenplan: Mouse-Over-Lupe. Zeigt an der Cursorposition einen      */
  /* vergrößerten Ausschnitt aus der hochaufgelösten Grafik, damit auch   */
  /* die kleine Beschriftung lesbar wird. Nur mit echter Maus - auf       */
  /* Touch-Geräten bleibt es beim einfachen, anklickbaren Bild.           */
  /* ------------------------------------------------------------------ */
  var lupenHalter = document.querySelector('[data-flaechenplan-lupe]');
  if (lupenHalter && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    var lupenBild = lupenHalter.querySelector('.flaechenplan-bild');
    var lupenglas = lupenHalter.querySelector('[data-lupenglas]');
    var lupenQuelle = lupenHalter.getAttribute('data-lupe-quelle');

    if (lupenBild && lupenglas && lupenQuelle) {
      var ZOOM = 4;
      lupenglas.style.backgroundImage = 'url(' + lupenQuelle + ')';

      // Hochaufgeloeste Datei schon beim Laden der Seite anfordern, damit sie
      // beim ersten Hover bereits im Cache liegt statt sichtbar nachzuladen.
      new Image().src = lupenQuelle;

      lupenHalter.addEventListener('mousemove', function (e) {
        var rect = lupenBild.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        if (x < 0 || y < 0 || x > rect.width || y > rect.height) {
          lupenglas.hidden = true;
          return;
        }
        lupenglas.hidden = false;

        var groesse = lupenglas.offsetWidth;
        var halb = groesse / 2;
        // Lupe darf bis zu 10% ihrer eigenen Groesse ueber den Bildrand
        // hinausragen - sonst laesst sich eine duenne Randflaeche (z.B. eine
        // Seitenflaeche direkt am Rand) nie mittig vergroessern, weil die
        // Lupe dafuer weiter nach aussen muesste, als eine strikte Klemmung
        // am Bildrand erlaubt.
        var ueberhang = groesse * 0.1;
        var lupeX = Math.max(-ueberhang, Math.min(x - halb, rect.width - groesse + ueberhang));
        var lupeY = Math.max(-ueberhang, Math.min(y - halb, rect.height - groesse + ueberhang));
        lupenglas.style.left = lupeX + 'px';
        lupenglas.style.top = lupeY + 'px';

        // Bildausschnitt anhand der tatsaechlichen (eingeklemmten) Lupenmitte
        // berechnen, damit der Ausschnitt zur sichtbaren Lupenposition passt.
        lupenglas.style.backgroundSize = (rect.width * ZOOM) + 'px ' + (rect.height * ZOOM) + 'px';
        var mitteX = lupeX + halb;
        var mitteY = lupeY + halb;
        lupenglas.style.backgroundPosition = (-(mitteX * ZOOM - halb)) + 'px ' + (-(mitteY * ZOOM - halb)) + 'px';
      });

      lupenHalter.addEventListener('mouseleave', function () {
        lupenglas.hidden = true;
      });
    }

    // Umrandungen je Paket: Position/Groesse kommen als Prozentwerte vom
    // Server (data-Attribute), werden hier in Pixel auf dem tatsaechlich
    // gerenderten Bild umgerechnet - direkt als CSS-Prozent im Markup bleiben
    // sie 0x0, weil das Bild "loading=lazy" traegt und beim ersten Skriptlauf
    // noch nicht geladen ist (clientWidth/-Height dann 0, kein Fehler, nur
    // ein leeres Bild ohne bekannte Groesse).
    var umrandungen = lupenHalter.querySelectorAll('[data-paket-umrandung]');
    if (umrandungen.length && lupenBild) {
      var umrandungenPositionieren = function () {
        var breite = lupenBild.clientWidth;
        var hoehe = lupenBild.clientHeight;
        if (!breite || !hoehe) { return; }
        Array.prototype.forEach.call(umrandungen, function (kasten) {
          kasten.style.left   = (parseFloat(kasten.getAttribute('data-left'))   / 100 * breite) + 'px';
          kasten.style.top    = (parseFloat(kasten.getAttribute('data-top'))    / 100 * hoehe)  + 'px';
          kasten.style.width  = (parseFloat(kasten.getAttribute('data-width'))  / 100 * breite) + 'px';
          kasten.style.height = (parseFloat(kasten.getAttribute('data-height')) / 100 * hoehe)  + 'px';
        });
      };
      if (lupenBild.complete) {
        umrandungenPositionieren();
      } else {
        lupenBild.addEventListener('load', umrandungenPositionieren);
      }
      window.addEventListener('resize', umrandungenPositionieren);
    }
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

(function () {
  'use strict';

  // Baut aus den umgekehrt abgelegten Teilen (siehe email_link_html() in
  // app/bootstrap.php) die echte Mailadresse zusammen und setzt sie erst
  // hier ein - im ausgelieferten HTML steht sie nirgends im Klartext.
  function mailAdressenAufloesen() {
    var links = document.querySelectorAll('[data-mail-nutzer]');
    Array.prototype.forEach.call(links, function (a) {
      var nutzer = (a.getAttribute('data-mail-nutzer') || '').split('').reverse().join('');
      var domain = (a.getAttribute('data-mail-domain') || '').split('').reverse().join('');
      if (!nutzer || !domain) return;
      var adresse = nutzer + '@' + domain;
      a.setAttribute('href', 'mailto:' + adresse);
      a.textContent = adresse;
      a.removeAttribute('data-mail-nutzer');
      a.removeAttribute('data-mail-domain');
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mailAdressenAufloesen);
  } else {
    mailAdressenAufloesen();
  }
})();
