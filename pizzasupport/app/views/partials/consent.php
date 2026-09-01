<?php
/**
 * Einwilligungsbanner nach TTDSG und DSGVO.
 *
 * Wir setzen von uns aus keine Marketing-Cookies. Notwendig ist allein das
 * Sitzungs-Cookie fuer den CSRF-Schutz. Zustimmungspflichtig sind bei uns
 * genau zwei Dinge: die Kartenkacheln von OpenStreetMap und Google Analytics.
 * Beides bleibt aus, bis jemand aktiv zustimmt - siehe main.js.
 *
 * Ablehnen ist genauso einfach wie Zustimmen – gleiche Groesse, gleiche
 * Ebene, kein Dark Pattern. Schließen (Kreuz, Klick daneben, Escape) zählt
 * ebenfalls als Ablehnen, nie als Zustimmung - siehe main.js.
 */
declare(strict_types=1);
?>
<div class="consent" id="consent" hidden role="dialog" aria-modal="true" aria-labelledby="consent-titel" aria-describedby="consent-text">
  <div class="consent-hinter" data-consent="ablehnen"></div>
  <div class="consent-box" role="document">
    <button class="modal-zu" type="button" data-consent="ablehnen" aria-label="Schließen (nur Notwendiges akzeptieren)">&times;</button>

    <div class="consent-inhalt">
      <h2 id="consent-titel">Kurz gefragt, bevor wir etwas nachladen</h2>
      <p id="consent-text">
        Diese Seite kommt ohne Werbe-Tracker aus. Was wir messen – Seitenaufrufe und
        Klicks auf QR-Codes – läuft anonym auf unserem eigenen Server, ohne Cookie
        und ohne Deine IP-Adresse zu speichern. Zustimmung brauchen wir nur für
        Inhalte, die von fremden Servern kommen: die Kartenkacheln von
        OpenStreetMap auf der Teilnehmerseite und Google Analytics. Details stehen
        in den <a href="/datenschutz.html">Datenschutzhinweisen</a>.
      </p>

      <form class="consent-optionen" id="consent-form">
        <div class="consent-option">
          <label>
            <input type="checkbox" checked disabled>
            <span><strong>Notwendig</strong> – Sitzung und Formularschutz. Ohne das funktioniert kein Formular.</span>
          </label>
        </div>
        <div class="consent-option">
          <label>
            <input type="checkbox" name="karte" id="consent-karte">
            <span><strong>Kartenkacheln</strong> – lädt die Karte von openstreetmap.org. Dabei erfährt deren Server Deine IP-Adresse.</span>
          </label>
        </div>
        <div class="consent-option">
          <label>
            <input type="checkbox" name="analyse" id="consent-analyse">
            <span><strong>Statistik (Google Analytics)</strong> – hilft uns zu verstehen, welche Seiten gelesen werden. Dabei setzt Google Cookies und erfährt Deine (gekürzte) IP-Adresse.</span>
          </label>
        </div>
      </form>

      <div class="consent-knoepfe">
        <button type="button" class="btn btn-sekundaer" data-consent="ablehnen">Nur Notwendiges</button>
        <button type="button" class="btn btn-sekundaer" data-consent="auswahl">Auswahl speichern</button>
        <button type="button" class="btn btn-primaer" data-consent="alle">Alles erlauben</button>
      </div>
      <p class="consent-fuss">
        Schließen zählt wie „Nur Notwendiges“. Deine Entscheidung liegt im lokalen
        Speicher Deines Browsers und lässt sich jederzeit über „Cookie-Einstellungen“
        im Fußbereich ändern.
      </p>
    </div>
  </div>
</div>
