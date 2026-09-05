<?php
/**
 * Bestellformular fuer die Gastronomie, als Assistent in drei Schritten.
 * Ansprache: Du. Erwartet $formate, $mengen, $fehler, $altw, $erfolg.
 *
 * Die Schritt-Navigation ist reine Javascript-Verzierung: alle drei
 * Schritte stehen als durchgehende Seite im HTML, mit allen Feldern.
 * Erst .js-bereit auf <html> (siehe layout.php) schaltet main.css auf die
 * Ein-Schritt-Ansicht um und main.js uebernimmt Weiter/Zurueck. Ohne
 * JavaScript bleibt es bei der durchgehenden Seite - nichts fehlt, nichts
 * ist deaktiviert.
 */
declare(strict_types=1);

$formate    = $formate    ?? config('karton_formate');
$mengen     = $mengen     ?? config('mengen');
$porto      = $porto      ?? config('porto');
$lieferung  = $lieferung  ?? config('lieferung');
$fehler  = $fehler  ?? [];
$altw    = $altw    ?? [];
$erfolg  = $erfolg  ?? null;

$betriebsarten = [
    'pizzeria'     => 'Pizzeria',
    'restaurant'   => 'Restaurant',
    'imbiss'       => 'Imbiss',
    'lieferdienst' => 'Lieferdienst mit eigener Küche',
    'foodtruck'    => 'Foodtruck',
    'baeckerei'    => 'Bäckerei',
    'cafe'         => 'Café',
    'bar'          => 'Bar mit Küche',
    'anderes'      => 'Anderes',
];
$aktuelleGroessen = ['28' => '28 × 28 cm', '30' => '30 × 30 cm', '32' => '32 × 32 cm', '33' => '33 × 33 cm', 'andere' => 'Andere Größe'];

$altMengen = $altw['menge'] ?? [];
?>
<section class="band band-bestellen" id="bestellen" aria-labelledby="bestellen-titel">
  <div class="wrap">

    <?php if ($erfolg): ?>
      <?php /* Konfetti-Danke: der Karton klappt auf, es knallt. */ ?>
      <div class="danke" id="danke" tabindex="-1" role="status" data-konfetti>
        <div class="danke-karton" aria-hidden="true">
          <span class="danke-deckel"></span>
          <span class="danke-boden"></span>
        </div>
        <canvas class="danke-konfetti" aria-hidden="true"></canvas>
        <h2>Drin. Danke Dir!</h2>
        <p><?= e((string) $erfolg) ?></p>
        <p class="danke-weiter">
          Solange wir sammeln, hilft jede weitere Pizzeria.
          <button type="button" class="link-button" data-modal-oeffnen="modal-empfehlung">Kennst Du noch einen Laden?</button>
          Oder schau, <a href="/teilnehmer.html">wer schon dabei ist</a>.
        </p>
      </div>
    <?php endif; ?>

    <div class="bestellen-kopf">
      <h2 id="bestellen-titel">Trag Deine Gastronomie ein</h2>
      <p class="band-lead">
        Du trägst Deine Gastro unverbindlich ein. Wir drucken erst, wenn wir 50 Gastronomien
        in Freiburg im Boot haben. Wir melden uns, sobald diese Zahl erreicht ist, damit Du
        weißt, wann die Kartons ankommen und Du planen kannst.
      </p>
    </div>

    <?php if ($fehler): ?>
      <p class="hinweis hinweis-fehler" id="gastro-fehler" role="alert">
        Ein paar Angaben fehlen oder passen noch nicht. Die betroffenen Felder sind markiert.
      </p>
    <?php endif; ?>

    <form method="post" action="/senden/gastro" id="formular-bestellen" class="formular formular-breit assistent" data-assistent novalidate>
      <?= csrf_field() ?>
      <?= honeypot_field() ?>

      <p class="assistent-fortschritt" data-assistent-fortschritt hidden aria-live="polite">Schritt 1 von 3</p>

      <div class="assistent-schritt ist-aktiv" data-schritt="1">

        <fieldset>
          <legend>Dein Betrieb</legend>

          <div class="feld<?= isset($fehler['betrieb']) ? ' feld-fehler' : '' ?>">
            <label for="g-betrieb">Name der Gastronomie <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="text" id="g-betrieb" name="betrieb" required maxlength="150"
                   value="<?= e(alt($altw, 'betrieb')) ?>" autocomplete="organization"
                   <?= isset($fehler['betrieb']) ? 'aria-invalid="true"' : '' ?>>
            <?php if (isset($fehler['betrieb'])): ?><p class="feld-meldung"><?= e($fehler['betrieb']) ?></p><?php endif; ?>
          </div>

          <div class="feld-reihe">
            <div class="feld<?= isset($fehler['vorname']) ? ' feld-fehler' : '' ?>">
              <label for="g-vorname">Vorname <span class="pflicht" aria-hidden="true">*</span></label>
              <input type="text" id="g-vorname" name="vorname" required maxlength="80"
                     value="<?= e(alt($altw, 'vorname')) ?>" autocomplete="given-name">
              <?php if (isset($fehler['vorname'])): ?><p class="feld-meldung"><?= e($fehler['vorname']) ?></p><?php endif; ?>
            </div>
            <div class="feld<?= isset($fehler['nachname']) ? ' feld-fehler' : '' ?>">
              <label for="g-nachname">Nachname <span class="pflicht" aria-hidden="true">*</span></label>
              <input type="text" id="g-nachname" name="nachname" required maxlength="80"
                     value="<?= e(alt($altw, 'nachname')) ?>" autocomplete="family-name">
              <?php if (isset($fehler['nachname'])): ?><p class="feld-meldung"><?= e($fehler['nachname']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="feld<?= isset($fehler['strasse']) ? ' feld-fehler' : '' ?>">
            <label for="g-strasse">Straße und Hausnummer <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="text" id="g-strasse" name="strasse" required maxlength="150"
                   value="<?= e(alt($altw, 'strasse')) ?>" autocomplete="street-address">
            <?php if (isset($fehler['strasse'])): ?><p class="feld-meldung"><?= e($fehler['strasse']) ?></p><?php endif; ?>
          </div>

          <div class="feld-reihe">
            <div class="feld feld-klein<?= isset($fehler['plz']) ? ' feld-fehler' : '' ?>">
              <label for="g-plz">PLZ <span class="pflicht" aria-hidden="true">*</span></label>
              <input type="text" id="g-plz" name="plz" required inputmode="numeric" pattern="[0-9]{5}" maxlength="5"
                     value="<?= e(alt($altw, 'plz')) ?>" autocomplete="postal-code">
              <?php if (isset($fehler['plz'])): ?><p class="feld-meldung"><?= e($fehler['plz']) ?></p><?php endif; ?>
            </div>
            <div class="feld<?= isset($fehler['ort']) ? ' feld-fehler' : '' ?>">
              <label for="g-ort">Ort <span class="pflicht" aria-hidden="true">*</span></label>
              <input type="text" id="g-ort" name="ort" required maxlength="100"
                     value="<?= e(alt($altw, 'ort', 'Freiburg')) ?>" autocomplete="address-level2">
              <?php if (isset($fehler['ort'])): ?><p class="feld-meldung"><?= e($fehler['ort']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="feld<?= isset($fehler['betriebsart']) ? ' feld-fehler' : '' ?>">
            <label for="g-betriebsart">Wir sind… <span class="pflicht" aria-hidden="true">*</span></label>
            <select id="g-betriebsart" name="betriebsart" required data-betriebsart-feld>
              <option value="">Bitte auswählen</option>
              <?php foreach ($betriebsarten as $baId => $baLabel): ?>
                <option value="<?= e($baLabel) ?>" data-betriebsart-wahl="<?= e($baId) ?>"
                        <?= alt($altw, 'betriebsart') === $baLabel ? 'selected' : '' ?>><?= e($baLabel) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($fehler['betriebsart'])): ?><p class="feld-meldung"><?= e($fehler['betriebsart']) ?></p><?php endif; ?>
          </div>

          <div class="feld<?= isset($fehler['betriebsart_frei']) ? ' feld-fehler' : '' ?>" data-betriebsart-frei>
            <label for="g-betriebsart-frei">Was macht Ihr?</label>
            <input type="text" id="g-betriebsart-frei" name="betriebsart_frei" maxlength="150"
                   value="<?= e(alt($altw, 'betriebsart_frei')) ?>">
            <?php if (isset($fehler['betriebsart_frei'])): ?><p class="feld-meldung"><?= e($fehler['betriebsart_frei']) ?></p><?php endif; ?>
          </div>

          <p class="hinweis hinweis-todo" data-betriebsart-lieferdienst-hinweis>
            Bei Lieferung fällt die Verpackungssteuer in der Regel nicht an. Der Einkaufspreis
            für den Karton schon – und den nehmen wir raus.
          </p>

          <div class="feld-reihe">
            <div class="feld<?= isset($fehler['email']) ? ' feld-fehler' : '' ?>">
              <label for="g-email">E-Mail <span class="pflicht" aria-hidden="true">*</span></label>
              <input type="email" id="g-email" name="email" required maxlength="254"
                     value="<?= e(alt($altw, 'email')) ?>" autocomplete="email">
              <p class="feld-hilfe">Wird NICHT veröffentlicht.</p>
              <?php if (isset($fehler['email'])): ?><p class="feld-meldung"><?= e($fehler['email']) ?></p><?php endif; ?>
            </div>
            <div class="feld<?= isset($fehler['telefon']) ? ' feld-fehler' : '' ?>">
              <label for="g-telefon">Telefon <span class="pflicht" aria-hidden="true">*</span></label>
              <input type="tel" id="g-telefon" name="telefon" required maxlength="32"
                     value="<?= e(alt($altw, 'telefon')) ?>" autocomplete="tel">
              <p class="feld-hilfe">Wird NICHT veröffentlicht.</p>
              <?php if (isset($fehler['telefon'])): ?><p class="feld-meldung"><?= e($fehler['telefon']) ?></p><?php endif; ?>
            </div>
          </div>
        </fieldset>

        <button type="button" class="btn btn-primaer assistent-weiter" data-assistent-weiter hidden>Weiter</button>
      </div>

      <div class="assistent-schritt" data-schritt="2">

        <fieldset class="konfigurator">
          <legend>Format und Menge</legend>
          <p class="feld-hilfe">
            Wir liefern zum Aktionsbeginn im November aus, die nächste Lieferung nach ca. 4
            Wochen im Dezember. Wie viele Kartons möchtest Du im November kostenlos erhalten?
            Trag bei jedem Format ein, wie viele Kartons Du brauchst. Du kannst verschiedene
            Formate mischen.
          </p>

          <div class="feld<?= isset($fehler['menge']) ? ' feld-fehler' : '' ?>">
            <div class="formate-liste" data-formate-liste
                 data-format-min="<?= (int) $mengen['format_min'] ?>"
                 data-gesamt-min="<?= (int) $mengen['min'] ?>"
                 data-gesamt-max="<?= (int) $mengen['max'] ?>">
              <?php foreach ($formate as $fm): ?>
                <div class="format-zeile">
                  <div class="format-zeile-kopf">
                    <strong><?= e($fm['label']) ?></strong>
                    <small><?= e($fm['hinweis']) ?></small>
                  </div>
                  <div class="menge-wahl" data-menge-gruppe="<?= e($fm['id']) ?>">
                    <?php foreach ($mengen['presets'] as $p): ?>
                      <button type="button" class="menge-knopf" data-menge-wert="<?= (int) $p ?>">
                        <?= zahl((int) $p) ?>
                      </button>
                    <?php endforeach; ?>
                    <label class="feld-optional" for="g-menge-<?= e($fm['id']) ?>">Anzahl</label>
                    <input type="number" id="g-menge-<?= e($fm['id']) ?>" name="menge[<?= e($fm['id']) ?>]"
                           min="0" max="<?= (int) $mengen['max'] ?>" step="<?= (int) $mengen['step'] ?>"
                           value="<?= e(alt($altMengen, $fm['id'])) ?>" inputmode="numeric" placeholder="0">
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="feld-hilfe" data-mengenregeln>
              Je Format mindestens <?= zahl((int) $mengen['format_min']) ?> Stück, in Schritten von
              <?= (int) $mengen['step'] ?>. Insgesamt braucht es mindestens <?= zahl((int) $mengen['min']) ?>
              Kartons; mehr als <?= zahl((int) $mengen['max']) ?> geht auch – dann schreib uns kurz direkt.
            </p>
            <?php if (isset($fehler['menge'])): ?><p class="feld-meldung"><?= e($fehler['menge']) ?></p><?php endif; ?>
            <div class="summe" data-mengen-summe aria-live="polite">
              <span class="summe-label">Gesamt</span>
              <span class="summe-wert" data-mengen-summe-wert>0 Kartons</span>
              <span class="summe-hinweis" data-mengen-summe-hinweis></span>
            </div>
          </div>

          <p class="feld-hilfe" data-versand-zuschlag
             data-plz-von="<?= e($porto['plz_von']) ?>" data-plz-bis="<?= e($porto['plz_bis']) ?>"
             data-freie-orte="<?= e(json_encode(array_map('mb_strtolower', $porto['freie_orte']), JSON_UNESCAPED_UNICODE)) ?>" hidden>
            Außerhalb <?= e($porto['frei_in']) ?>: Abholung oder Lieferung nach Aufwand, wir
            melden uns bei Dir dazu.
          </p>

          <div class="feld">
            <label for="g-anmerkung">Willst Du uns noch etwas mitgeben? <span class="feld-optional">(freiwillig)</span></label>
            <textarea id="g-anmerkung" name="anmerkung" rows="3" maxlength="1500"><?= e(alt($altw, 'anmerkung')) ?></textarea>
          </div>
        </fieldset>

        <fieldset class="konfigurator" data-lieferart-gruppe>
          <legend>Lieferung</legend>
          <p class="feld-hilfe feld-hilfe-abstand">
            Wir liefern Kartons einmal pro Monat kostenlos aus. Die Mindestmenge sind 300
            Kartons. Brauchst Du im selben Monat weitere Kartons, werden mindestens 300 Stück
            per Hermes geliefert und die Zustellung kostet 10 Euro zzgl. 19% MwSt. je
            Lieferung. Bei größeren Mengen stimmen wir uns vorher mit Dir ab. Du kannst
            Kartons in Tiengen und auf der Haid nach vorheriger Absprache kostenlos abholen.
          </p>

          <div class="feld">
            <span class="feld-label">Wie möchtest Du Deine Kartons bekommen?</span>
            <div class="wahl-reihe" role="radiogroup" aria-label="Lieferart">
              <label class="wahl wahl-schmal">
                <input type="radio" name="lieferart" value="gesamt" data-lieferart-wahl
                       <?= alt($altw, 'lieferart', 'gesamt') === 'gesamt' ? 'checked' : '' ?>>
                <span class="wahl-inhalt"><strong>Alles auf einmal</strong><small>Die gesamte Menge in einer Lieferung</small></span>
              </label>
              <label class="wahl wahl-schmal">
                <input type="radio" name="lieferart" value="abruf" data-lieferart-wahl
                       <?= alt($altw, 'lieferart') === 'abruf' ? 'checked' : '' ?>>
                <span class="wahl-inhalt"><strong>Monatlicher Abruf</strong><small>Anzahl jeden Monat bis auf Widerruf</small></span>
              </label>
              <label class="wahl wahl-schmal">
                <input type="radio" name="lieferart" value="abholung" data-lieferart-wahl
                       <?= alt($altw, 'lieferart') === 'abholung' ? 'checked' : '' ?>>
                <span class="wahl-inhalt"><strong>Abholung</strong><small>Selbst abholen, jederzeit</small></span>
              </label>
            </div>
          </div>

          <div class="feld<?= isset($fehler['abruf_menge']) ? ' feld-fehler' : '' ?>" data-lieferart-feld="abruf" hidden>
            <label for="g-abrufmenge">Menge pro Monat</label>
            <input type="number" id="g-abrufmenge" name="abruf_menge" inputmode="numeric"
                   min="<?= (int) $lieferung['abruf_min'] ?>" value="<?= e(alt($altw, 'abruf_menge')) ?>"
                   aria-describedby="g-abrufmenge-hilfe">
            <p class="feld-hilfe" id="g-abrufmenge-hilfe" data-abrufmenge-hinweis>
              Mindestens <?= zahl((int) $lieferung['abruf_min']) ?> Kartons pro Monat.
            </p>
            <?php if (isset($fehler['abruf_menge'])): ?><p class="feld-meldung"><?= e($fehler['abruf_menge']) ?></p><?php endif; ?>
          </div>

          <p class="feld-hilfe" data-lieferart-feld="abholung" hidden>
            Wir rufen Dich unter der oben angegebenen Nummer zur Terminvereinbarung an.
            Abholorte: <?= e(implode(' oder ', array_column($lieferung['abholung_standorte'], 'ort'))) ?>,
            Montag bis Sonntag, kostenlos.
          </p>

          <p class="feld-hilfe" data-lieferart-feld="lager-hinweis" hidden>
            Das ist einiges auf einmal. Sag uns kurz per Anmerkung Bescheid, falls bei Dir die
            Lagerfläche knapp werden könnte – dann teilen wir die Lieferung sinnvoll auf.
          </p>
        </fieldset>

        <div class="assistent-nav">
          <button type="button" class="btn btn-sekundaer assistent-zurueck" data-assistent-zurueck hidden>Zurück</button>
          <button type="button" class="btn btn-primaer assistent-weiter" data-assistent-weiter hidden>Weiter</button>
        </div>
      </div>

      <div class="assistent-schritt" data-schritt="3">

        <fieldset>
          <legend>Dein Bedarf</legend>
          <p class="feld-hilfe feld-hilfe-abstand">
            Bitte hilf uns, Preise und Mengen besser einschätzen zu können. Was hast Du bisher
            bezahlt und welche Formate verwendest Du?
          </p>

          <div class="feld-reihe">
            <div class="feld<?= isset($fehler['kartons_monat_bedarf']) ? ' feld-fehler' : '' ?>">
              <label for="g-kartons-monat-bedarf">Bedarf Pizzakartons pro Monat (ungefähr) <span class="feld-optional">(freiwillig)</span></label>
              <input type="number" id="g-kartons-monat-bedarf" name="kartons_monat_bedarf" inputmode="numeric" min="1"
                     value="<?= e(alt($altw, 'kartons_monat_bedarf')) ?>" data-bedarf-kartons-monat>
              <?php if (isset($fehler['kartons_monat_bedarf'])): ?><p class="feld-meldung"><?= e($fehler['kartons_monat_bedarf']) ?></p><?php endif; ?>
            </div>
            <div class="feld<?= isset($fehler['aktueller_einkaufspreis']) ? ' feld-fehler' : '' ?>">
              <label for="g-aktueller-einkaufspreis">Aktueller Einkaufspreis je Karton <span class="feld-optional">(Euro, netto ohne Mehrwertsteuer, freiwillig)</span></label>
              <input type="text" id="g-aktueller-einkaufspreis" name="aktueller_einkaufspreis" inputmode="decimal"
                     placeholder="z. B. 0,45" value="<?= e(alt($altw, 'aktueller_einkaufspreis')) ?>" data-bedarf-einkaufspreis>
              <?php if (isset($fehler['aktueller_einkaufspreis'])): ?><p class="feld-meldung"><?= e($fehler['aktueller_einkaufspreis']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="feld-reihe">
            <div class="feld<?= isset($fehler['aktuelle_groesse']) ? ' feld-fehler' : '' ?>">
              <label for="g-aktuelle-groesse">Welche Größe verkaufst Du am häufigsten? <span class="feld-optional">(freiwillig)</span></label>
              <select id="g-aktuelle-groesse" name="aktuelle_groesse" class="feld-select-hoch">
                <option value="">Bitte auswählen</option>
                <?php foreach ($aktuelleGroessen as $agId => $agLabel): $agId = (string) $agId; ?>
                  <option value="<?= e($agId) ?>"<?= alt($altw, 'aktuelle_groesse') === $agId ? ' selected' : '' ?>><?= e($agLabel) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($fehler['aktuelle_groesse'])): ?><p class="feld-meldung"><?= e($fehler['aktuelle_groesse']) ?></p><?php endif; ?>
            </div>
            <div class="feld">
              <label for="g-aktueller-lieferant">Bei wem kaufst Du aktuell ein? <span class="feld-optional">(freiwillig)</span></label>
              <input type="text" id="g-aktueller-lieferant" name="aktueller_lieferant" maxlength="150"
                     value="<?= e(alt($altw, 'aktueller_lieferant')) ?>">
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Fast fertig</legend>

          <div class="feld feld-check">
            <label>
              <?php /* Default: angehakt. Bei einer echten Neuanzeige (frischer
                       Besuch, $altw leer) ist das schlicht die Voreinstellung;
                       bei einer Wiederanzeige nach einem Fehler zaehlt dagegen
                       ausschliesslich, was zuletzt tatsaechlich abgeschickt
                       wurde - ein bewusst abgewaehltes Haekchen bleibt also
                       abgewaehlt, statt durch die Voreinstellung ueberschrieben
                       zu werden (nicht angehakte Checkboxen fehlen im POST
                       komplett, "nicht vorhanden" ist hier also nicht dasselbe
                       wie "frischer Besuch"). */ ?>
              <input type="checkbox" name="karte_ok" value="1" data-karte-ok
                     <?= (empty($altw) || alt($altw, 'karte_ok')) ? 'checked' : '' ?>>
              <span>
                Mein Betrieb darf mit Name, Adresse und Website auf der
                <a href="/teilnehmer.html">Teilnehmerkarte</a> erscheinen. Kostenfrei und
                jederzeit durch beide Seiten widerrufbar. Ich verlinke nach Möglichkeit einen
                Link von meiner Website zu https://pizzasupport.de
              </span>
            </label>
          </div>

          <div class="feld<?= isset($fehler['website']) ? ' feld-fehler' : '' ?>" data-website-feld>
            <label for="g-website">Website <span class="feld-optional">(freiwillig – wird auf der Karte verlinkt)</span></label>
            <input type="text" id="g-website" name="website" maxlength="300" placeholder="deine-pizzeria.de"
                   value="<?= e(alt($altw, 'website')) ?>" autocomplete="url">
            <?php if (isset($fehler['website'])): ?><p class="feld-meldung"><?= e($fehler['website']) ?></p><?php endif; ?>
          </div>

          <div class="feld feld-check<?= isset($fehler['bestellung_ok']) ? ' feld-fehler' : '' ?>">
            <label>
              <input type="checkbox" name="bestellung_ok" value="1" required <?= alt($altw, 'bestellung_ok') ? 'checked' : '' ?>>
              <span>
                Ich bestelle verbindlich gemäß der <a href="/agb.html" target="_blank" rel="noopener">AGB</a>
                und akzeptiere diese hiermit im Sinne des Startschuss-Prinzips: Sobald genug
                Betriebe und genug Werbebudget zusammen sind, geht meine Menge in die Produktion.
                Bis dahin kann ich jederzeit formlos absagen. Für mich fallen keine Kosten an.
                <span class="pflicht" aria-hidden="true">*</span>
              </span>
            </label>
            <?php if (isset($fehler['bestellung_ok'])): ?><p class="feld-meldung"><?= e($fehler['bestellung_ok']) ?></p><?php endif; ?>
          </div>

          <div class="feld feld-check<?= isset($fehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
            <label>
              <input type="checkbox" name="datenschutz_ok" value="1" required>
              <span>
                Ich habe die <a href="/datenschutz.html" target="_blank" rel="noopener">Datenschutzhinweise</a>
                gelesen und bin mit der Verarbeitung meiner Angaben zur Abwicklung dieser
                Bestellung einverstanden, ich stimme zu, per Mail über den Projektverlauf
                informiert zu werden und kann mich davon jederzeit abmelden.
                <span class="pflicht" aria-hidden="true">*</span>
              </span>
            </label>
            <?php if (isset($fehler['datenschutz_ok'])): ?><p class="feld-meldung"><?= e($fehler['datenschutz_ok']) ?></p><?php endif; ?>
          </div>

          <button class="btn btn-primaer btn-gross btn-block" type="submit">Jetzt bestellen</button>
          <p class="formular-fuss">
            Bestelle jetzt und sei dabei, wenn wir versenden. Pflichtfelder sind mit
            <span class="pflicht" aria-hidden="true">*</span> markiert.
          </p>
        </fieldset>

        <div class="assistent-nav">
          <button type="button" class="btn btn-sekundaer assistent-zurueck" data-assistent-zurueck hidden>Zurück</button>
        </div>
      </div>
    </form>
  </div>
</section>
