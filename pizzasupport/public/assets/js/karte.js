/*!
 * pizzasupport.de – Kartenmodul
 *
 * Wird erst geladen, wenn jemand die Karte anfordert (siehe main.js).
 *
 * Warum kein fertiges Kartenpaket im Standardfall: Unsere Content-Security-
 * Policy lässt Skripte nur von der eigenen Domain zu, und ein Kartenpaket
 * nur für Marker und Kachelraster wäre für diese Seite zu viel Ballast.
 * Dieses Modul zeichnet OpenStreetMap-Kacheln, versteht Ziehen, Zoomen und
 * Marker – mehr braucht die Teilnehmerkarte nicht.
 *
 * Wer trotzdem Leaflet einsetzen will: Datei nach /assets/js/leaflet.js
 * legen und vor diesem Skript einbinden. Liegt dann window.L vor, nutzt
 * dieses Modul Leaflet und überspringt den eigenen Renderer.
 */
(function () {
  'use strict';

  var KACHEL = 256;
  var MIN_ZOOM = 8;
  var MAX_ZOOM = 18;
  var ATTRIBUTION = '© <a href="https://www.openstreetmap.org/copyright" '
    + 'rel="nofollow noopener" target="_blank">OpenStreetMap</a>-Mitwirkende';

  /* --- Rechnerei: Grad zu Weltpixeln und zurück ---------------------- */
  function lonZuX(lon, z) {
    return (lon + 180) / 360 * KACHEL * Math.pow(2, z);
  }
  function latZuY(lat, z) {
    var r = lat * Math.PI / 180;
    return (1 - Math.log(Math.tan(r) + 1 / Math.cos(r)) / Math.PI) / 2 * KACHEL * Math.pow(2, z);
  }

  function Karte(halter, flaeche) {
    this.halter = halter;
    this.el = flaeche;
    this.zoom = parseInt(halter.getAttribute('data-zoom'), 10) || 12;
    this.mitte = {
      lat: parseFloat(halter.getAttribute('data-zentrum-lat')) || 47.9959,
      lon: parseFloat(halter.getAttribute('data-zentrum-lon')) || 7.8522
    };
    this.punkte = [];
    this.marker = {};
    this.popup = null;
    this.kachelCache = {};

    this.aufbauen();
    this.datenHolen();
  }

  Karte.prototype.aufbauen = function () {
    var self = this;

    this.el.classList.add('psk-map');
    this.el.innerHTML = '';

    this.ebene = document.createElement('div');
    this.ebene.className = 'psk-kacheln';
    this.el.appendChild(this.ebene);

    // Zoomknöpfe – auch mit Tastatur bedienbar.
    var zoomBox = document.createElement('div');
    zoomBox.className = 'psk-zoom';
    zoomBox.innerHTML = '<button type="button" aria-label="Hineinzoomen">+</button>'
      + '<button type="button" aria-label="Herauszoomen">−</button>';
    zoomBox.children[0].addEventListener('click', function () { self.zoomen(1); });
    zoomBox.children[1].addEventListener('click', function () { self.zoomen(-1); });
    this.el.appendChild(zoomBox);

    var attr = document.createElement('div');
    attr.className = 'psk-attribution';
    attr.innerHTML = ATTRIBUTION;
    this.el.appendChild(attr);

    this.el.setAttribute('role', 'application');
    this.el.setAttribute('aria-label', 'Karte der teilnehmenden Betriebe. Die vollständige Liste steht daneben.');
    this.el.setAttribute('tabindex', '0');

    /* Ziehen mit Maus und Finger */
    var zieht = false, startX = 0, startY = 0, bewegt = false;

    this.el.addEventListener('pointerdown', function (e) {
      if (e.target.closest('.psk-zoom, .psk-popup, .psk-marker')) { return; }
      zieht = true; bewegt = false;
      startX = e.clientX; startY = e.clientY;
      self.el.classList.add('ist-gezogen');
      self.el.setPointerCapture(e.pointerId);
    });

    this.el.addEventListener('pointermove', function (e) {
      if (!zieht) { return; }
      var dx = e.clientX - startX;
      var dy = e.clientY - startY;
      if (Math.abs(dx) + Math.abs(dy) > 3) { bewegt = true; }
      startX = e.clientX; startY = e.clientY;
      self.verschieben(-dx, -dy);
    });

    function losLassen(e) {
      if (!zieht) { return; }
      zieht = false;
      self.el.classList.remove('ist-gezogen');
      try { self.el.releasePointerCapture(e.pointerId); } catch (err) { /* egal */ }
      if (bewegt) { self.zeichnen(); }
    }
    this.el.addEventListener('pointerup', losLassen);
    this.el.addEventListener('pointercancel', losLassen);

    /* Rad zoomt nur mit Strg – sonst scrollt die Seite weiter, wie erwartet */
    this.el.addEventListener('wheel', function (e) {
      if (!e.ctrlKey) { return; }
      e.preventDefault();
      self.zoomen(e.deltaY < 0 ? 1 : -1);
    }, { passive: false });

    /* Tastatur */
    this.el.addEventListener('keydown', function (e) {
      var schritt = 60;
      var taste = { ArrowLeft: [-schritt, 0], ArrowRight: [schritt, 0], ArrowUp: [0, -schritt], ArrowDown: [0, schritt] };
      if (taste[e.key]) {
        e.preventDefault();
        self.verschieben(taste[e.key][0], taste[e.key][1]);
        self.zeichnen();
      } else if (e.key === '+' || e.key === '=') {
        e.preventDefault(); self.zoomen(1);
      } else if (e.key === '-') {
        e.preventDefault(); self.zoomen(-1);
      }
    });

    window.addEventListener('resize', function () { self.zeichnen(); });
  };

  Karte.prototype.masse = function () {
    return { b: this.el.clientWidth, h: this.el.clientHeight };
  };

  /** Verschiebt die Mitte um eine Pixelstrecke. */
  Karte.prototype.verschieben = function (dx, dy) {
    var x = lonZuX(this.mitte.lon, this.zoom) + dx;
    var y = latZuY(this.mitte.lat, this.zoom) + dy;
    var welt = KACHEL * Math.pow(2, this.zoom);

    this.mitte.lon = x / welt * 360 - 180;
    var n = Math.PI - 2 * Math.PI * y / welt;
    this.mitte.lat = 180 / Math.PI * Math.atan(0.5 * (Math.exp(n) - Math.exp(-n)));

    this.ebene.style.transform = 'translate(' + (-dx) + 'px,' + (-dy) + 'px)';
    this.zeichnen();
  };

  Karte.prototype.zoomen = function (richtung) {
    var neu = Math.max(MIN_ZOOM, Math.min(MAX_ZOOM, this.zoom + richtung));
    if (neu === this.zoom) { return; }
    this.zoom = neu;
    this.zeichnen();
  };

  Karte.prototype.zeichnen = function () {
    var m = this.masse();
    if (!m.b || !m.h) { return; }

    var z = this.zoom;
    var mittelX = lonZuX(this.mitte.lon, z);
    var mittelY = latZuY(this.mitte.lat, z);
    var linksOben = { x: mittelX - m.b / 2, y: mittelY - m.h / 2 };

    this.ebene.style.transform = '';

    var vonX = Math.floor(linksOben.x / KACHEL);
    var bisX = Math.floor((linksOben.x + m.b) / KACHEL);
    var vonY = Math.floor(linksOben.y / KACHEL);
    var bisY = Math.floor((linksOben.y + m.h) / KACHEL);
    var max = Math.pow(2, z);
    var gebraucht = {};

    for (var tx = vonX; tx <= bisX; tx++) {
      for (var ty = vonY; ty <= bisY; ty++) {
        if (ty < 0 || ty >= max) { continue; }
        var wx = ((tx % max) + max) % max;   // Datumsgrenze sauber umschlagen
        var id = z + '/' + wx + '/' + ty;
        gebraucht[id] = true;

        var kachel = this.kachelCache[id];
        if (!kachel) {
          kachel = document.createElement('img');
          kachel.className = 'psk-kachel';
          kachel.width = KACHEL;
          kachel.height = KACHEL;
          kachel.alt = '';
          kachel.loading = 'lazy';
          kachel.decoding = 'async';
          kachel.referrerPolicy = 'no-referrer';
          kachel.addEventListener('load', function () { this.classList.add('ist-da'); });
          kachel.src = 'https://tile.openstreetmap.org/' + z + '/' + wx + '/' + ty + '.png';
          this.kachelCache[id] = kachel;
          this.ebene.appendChild(kachel);
        }
        kachel.style.left = Math.round(tx * KACHEL - linksOben.x) + 'px';
        kachel.style.top = Math.round(ty * KACHEL - linksOben.y) + 'px';
      }
    }

    // Kacheln aus anderen Zoomstufen wieder entfernen, damit der
    // Speicher nicht mit der Zeit vollläuft.
    for (var alt in this.kachelCache) {
      if (!gebraucht[alt]) {
        var el = this.kachelCache[alt];
        if (el.parentNode) { el.parentNode.removeChild(el); }
        delete this.kachelCache[alt];
      }
    }

    this.markerZeichnen(linksOben);
  };

  Karte.prototype.markerZeichnen = function (linksOben) {
    var self = this;

    this.punkte.forEach(function (p) {
      if (p.lat === null || p.lon === null) { return; }

      var el = self.marker[p.id];
      if (!el) {
        el = document.createElement('button');
        el.type = 'button';
        el.className = 'psk-marker psk-marker-' + p.typ;
        el.setAttribute('aria-label', p.name + ', ' + (p.ort || ''));
        el.addEventListener('click', function (e) {
          e.stopPropagation();
          self.popupZeigen(p);
        });
        self.marker[p.id] = el;
        self.ebene.appendChild(el);
      }
      el.style.left = Math.round(lonZuX(p.lon, self.zoom) - linksOben.x) + 'px';
      el.style.top = Math.round(latZuY(p.lat, self.zoom) - linksOben.y) + 'px';
    });

    if (this.popup && this.popupPunkt) {
      this.popup.style.left = Math.round(lonZuX(this.popupPunkt.lon, this.zoom) - linksOben.x) + 'px';
      this.popup.style.top = Math.round(latZuY(this.popupPunkt.lat, this.zoom) - linksOben.y) + 'px';
    }
  };

  Karte.prototype.popupZeigen = function (p) {
    var self = this;
    this.popupSchliessen();

    var box = document.createElement('div');
    box.className = 'psk-popup';
    box.setAttribute('role', 'dialog');

    var adresse = [p.strasse, [p.plz, p.ort].filter(Boolean).join(' ')].filter(Boolean).join(', ');
    var html = '<button type="button" class="psk-popup-zu" aria-label="Schließen">&times;</button>'
      + '<strong></strong><small></small>';
    box.innerHTML = html;
    // Inhalte als Text setzen, nie als HTML - die Namen kommen aus der Datenbank.
    box.querySelector('strong').textContent = p.name;
    // Kategorie immer als Text zeigen, nicht nur ueber Farbe/Form des Markers -
    // Gastronomien haben ihre Betriebsart, unterstuetzende Unternehmen sonst nichts.
    var kategorie = p.sparte || (p.typ === 'unternehmen' ? 'Unterstützendes Unternehmen' : '');
    box.querySelector('small').textContent = adresse + (kategorie ? ' · ' + kategorie : '');

    if (p.website) {
      var a = document.createElement('a');
      a.href = p.website;
      a.target = '_blank';
      a.rel = 'nofollow noopener';
      a.textContent = 'Website';
      box.appendChild(document.createElement('br'));
      box.appendChild(a);
    }

    box.querySelector('.psk-popup-zu').addEventListener('click', function () { self.popupSchliessen(); });

    this.popup = box;
    this.popupPunkt = p;
    this.ebene.appendChild(box);

    Object.keys(this.marker).forEach(function (id) {
      self.marker[id].classList.toggle('ist-aktiv', id === p.id);
    });

    this.zeichnen();
  };

  Karte.prototype.popupSchliessen = function () {
    if (this.popup && this.popup.parentNode) {
      this.popup.parentNode.removeChild(this.popup);
    }
    this.popup = null;
    this.popupPunkt = null;
  };

  /** Springt zu einem Eintrag und öffnet dessen Sprechblase. */
  Karte.prototype.zeigen = function (id) {
    var treffer = null;
    for (var i = 0; i < this.punkte.length; i++) {
      if (this.punkte[i].id === id) { treffer = this.punkte[i]; break; }
    }
    if (!treffer || treffer.lat === null) { return; }

    this.mitte = { lat: treffer.lat, lon: treffer.lon };
    if (this.zoom < 14) { this.zoom = 15; }
    this.zeichnen();
    this.popupZeigen(treffer);
    this.el.scrollIntoView({ block: 'center', behavior: 'smooth' });
  };

  Karte.prototype.datenHolen = function () {
    var self = this;
    var quelle = this.halter.getAttribute('data-endpunkt') || '/api/teilnehmer.json';

    fetch(quelle, { credentials: 'same-origin' })
      .then(function (a) { return a.ok ? a.json() : Promise.reject(new Error(a.status)); })
      .then(function (daten) {
        self.punkte = (daten.teilnehmer || []).filter(function (p) {
          return typeof p.lat === 'number' && typeof p.lon === 'number';
        });
        self.zeichnen();
      })
      .catch(function () {
        // Kein Drama: Die Liste neben der Karte ist die eigentliche Quelle.
        self.zeichnen();
      });
  };

  /* --- Öffentliche Schnittstelle ------------------------------------- */
  var aktiv = null;

  window.psKarte = {
    starten: function (halter, flaeche) {
      // Liegt ein vollwertiges Kartenpaket vor, hat das Vorrang.
      if (window.L && typeof window.L.map === 'function') {
        aktiv = leafletStarten(halter, flaeche);
        return aktiv;
      }
      aktiv = new Karte(halter, flaeche);
      aktiv.zeichnen();
      return aktiv;
    },
    zeigen: function (id) {
      if (aktiv && aktiv.zeigen) { aktiv.zeigen(id); }
    }
  };

  /** Wird nur benutzt, wenn Leaflet lokal eingebunden wurde. */
  function leafletStarten(halter, flaeche) {
    var karte = window.L.map(flaeche).setView([
      parseFloat(halter.getAttribute('data-zentrum-lat')) || 47.9959,
      parseFloat(halter.getAttribute('data-zentrum-lon')) || 7.8522
    ], parseInt(halter.getAttribute('data-zoom'), 10) || 12);

    window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: MAX_ZOOM, attribution: ATTRIBUTION
    }).addTo(karte);

    var marker = {};
    fetch(halter.getAttribute('data-endpunkt') || '/api/teilnehmer.json', { credentials: 'same-origin' })
      .then(function (a) { return a.json(); })
      .then(function (daten) {
        (daten.teilnehmer || []).forEach(function (p) {
          if (typeof p.lat !== 'number' || typeof p.lon !== 'number') { return; }
          var m = window.L.circleMarker([p.lat, p.lon], {
            radius: 8, weight: 2, color: '#fff',
            fillColor: p.typ === 'gastro' ? '#b5342a' : '#16375b', fillOpacity: 1
          }).addTo(karte);
          m.bindPopup(function () {
            var d = document.createElement('div');
            var s = document.createElement('strong');
            s.textContent = p.name;
            d.appendChild(s);
            return d;
          });
          marker[p.id] = m;
        });
      });

    return {
      zeigen: function (id) {
        var m = marker[id];
        if (m) { karte.setView(m.getLatLng(), 15); m.openPopup(); }
      }
    };
  }

}());
