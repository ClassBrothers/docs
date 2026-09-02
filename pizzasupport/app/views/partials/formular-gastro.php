<?php
/**
 * Bestellformular fuer die Gastronomie.
 * Ansprache: Du. Erwartet $formate, $mengen, $fehler, $altw, $erfolg.
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
    'Pizzeria', 'Restaurant', 'Imbiss', 'Lieferdienst mit eigener Küche',
    'Foodtruck', 'Bäckerei', 'Café', 'Bar mit Küche', 'Anderes',
];

$standardFormat = '32';
foreach ($formate as $fm) {
    if (!empty($fm['default'])) {
        $standardFormat = $fm['id'];
    }
}
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
      <h2 id="bestellen-titel">Trag Deinen Betrieb ein</h2>
      <p class="band-lead">
        Unverbindlich bis zum Startschuss. Wir melden uns, sobald die Produktion
        freigegeben ist – und vorher nur, wenn wir eine Rückfrage haben.
      </p>
    </div>

    <?php if ($fehler): ?>
      <p class="hinweis hinweis-fehler" role="alert">
        Ein paar Angaben fehlen oder passen noch nicht. Die betroffenen Felder sind markiert.
      </p>
    <?php endif; ?>

    <form method="post" action="/senden/gastro" id="formular-bestellen" class="formular formular-breit" novalidate>
      <?= csrf_field() ?>
      <?= honeypot_field() ?>

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
                   value="<?= e(alt($altw, 'ort', 'Freiburg im Breisgau')) ?>" autocomplete="address-level2">
            <?php if (isset($fehler['ort'])): ?><p class="feld-meldung"><?= e($fehler['ort']) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="feld<?= isset($fehler['betriebsart']) ? ' feld-fehler' : '' ?>">
          <label for="g-betriebsart">Was für ein Betrieb ist das? <span class="pflicht" aria-hidden="true">*</span></label>
          <select id="g-betriebsart" name="betriebsart" required>
            <option value="">Bitte auswählen</option>
            <?php foreach ($betriebsarten as $ba): ?>
              <option value="<?= e($ba) ?>"<?= alt($altw, 'betriebsart') === $ba ? ' selected' : '' ?>><?= e($ba) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($fehler['betriebsart'])): ?><p class="feld-meldung"><?= e($fehler['betriebsart']) ?></p><?php endif; ?>
        </div>
      </fieldset>

      <fieldset>
        <legend>So erreichen wir Dich</legend>

        <div class="feld-reihe">
          <div class="feld<?= isset($fehler['email']) ? ' feld-fehler' : '' ?>">
            <label for="g-email">E-Mail <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="email" id="g-email" name="email" required maxlength="254"
                   value="<?= e(alt($altw, 'email')) ?>" autocomplete="email">
            <?php if (isset($fehler['email'])): ?><p class="feld-meldung"><?= e($fehler['email']) ?></p><?php endif; ?>
          </div>
          <div class="feld<?= isset($fehler['telefon']) ? ' feld-fehler' : '' ?>">
            <label for="g-telefon">Telefon <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="tel" id="g-telefon" name="telefon" required maxlength="32"
                   value="<?= e(alt($altw, 'telefon')) ?>" autocomplete="tel">
            <?php if (isset($fehler['telefon'])): ?><p class="feld-meldung"><?= e($fehler['telefon']) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="feld<?= isset($fehler['website']) ? ' feld-fehler' : '' ?>">
          <label for="g-website">Website <span class="feld-optional">(freiwillig – wird auf der Karte verlinkt)</span></label>
          <input type="text" id="g-website" name="website" maxlength="300" placeholder="deine-pizzeria.de"
                 value="<?= e(alt($altw, 'website')) ?>" autocomplete="url">
          <?php if (isset($fehler['website'])): ?><p class="feld-meldung"><?= e($fehler['website']) ?></p><?php endif; ?>
        </div>
      </fieldset>

      <fieldset class="konfigurator">
        <legend>Format und Menge</legend>
        <p class="feld-hilfe">
          Trag bei jedem Format ein, wie viele Kartons Du davon brauchst. Du kannst auch
          mehrere Formate mischen – leer lassen heißt, Du bestellst davon keins.
          Die Erstauflage läuft auf <?= e($standardFormat) ?> × <?= e($standardFormat) ?> ×
          <?= (int) config('karton_hoehe_cm') ?> cm. Die übrigen Größen produzieren wir, sobald
          dafür genug Bedarf zusammenkommt.
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
                  <small><?= e($fm['hinweis']) ?><?= !empty($fm['sofort']) ? ' · läuft als Erstes' : '' ?></small>
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
          <p class="feld-hilfe">
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

        <div class="feld feld-check" data-versand-zuschlag
             data-plz-von="<?= e($porto['plz_von']) ?>" data-plz-bis="<?= e($porto['plz_bis']) ?>"
             data-freie-orte="<?= e(json_encode(array_map('mb_strtolower', $porto['freie_orte']), JSON_UNESCAPED_UNICODE)) ?>" hidden>
          <label>
            <input type="checkbox" id="g-versand-zuschlag" name="versand_zuschlag_ok" value="1"
                   <?= alt($altw, 'versand_zuschlag_ok') ? 'checked' : '' ?>>
            <span>
              Außerhalb <?= e($porto['frei_in']) ?> berechnen wir eine Portopauschale von
              <?= e(preis((int) $porto['pauschale_cent'], false)) ?> € netto zzgl.
              <?= (int) config('mwst_prozent') ?> % MwSt. je angefangene <?= zahl((int) $porto['je_kartons']) ?>
              Kartons. Damit bin ich einverstanden. <span class="pflicht" aria-hidden="true">*</span>
            </span>
          </label>
          <?php if (isset($fehler['versand_zuschlag_ok'])): ?><p class="feld-meldung"><?= e($fehler['versand_zuschlag_ok']) ?></p><?php endif; ?>
        </div>

        <div class="feld">
          <label for="g-anmerkung">Willst Du uns noch etwas mitgeben? <span class="feld-optional">(freiwillig)</span></label>
          <textarea id="g-anmerkung" name="anmerkung" rows="3" maxlength="1500"><?= e(alt($altw, 'anmerkung')) ?></textarea>
        </div>
      </fieldset>

      <fieldset class="konfigurator" data-lieferart-gruppe>
        <legend>Lieferung</legend>
        <p class="feld-hilfe">
          Wir lagern Deine Kartons und liefern ab, wenn Du sie brauchst. Eine Lieferung pro
          Monat ist für Dich kostenfrei, ab <?= zahl((int) $lieferung['abruf_min']) ?> Kartons
          je Abruf. Wir bündeln Fahrten, deshalb kann eine zusätzliche Lieferung im selben
          Monat bis zu <?= (int) $lieferung['zusatz_frist_werktage'] ?> Werktage dauern – länger
          nicht. Abholen kannst Du jederzeit, kostenlos und ohne Mengenbeschränkung.
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
              <span class="wahl-inhalt"><strong>Monatlicher Abruf</strong><small>Teilmengen über mehrere Monate</small></span>
            </label>
            <label class="wahl wahl-schmal">
              <input type="radio" name="lieferart" value="abholung" data-lieferart-wahl
                     <?= alt($altw, 'lieferart') === 'abholung' ? 'checked' : '' ?>>
              <span class="wahl-inhalt"><strong>Abholung</strong><small>Selbst abholen, jederzeit</small></span>
            </label>
          </div>
        </div>

        <div class="feld<?= isset($fehler['abruf_menge']) ? ' feld-fehler' : '' ?>" data-lieferart-feld="abruf" hidden>
          <label for="g-abrufmenge">Gewünschte Menge je Abruf</label>
          <input type="number" id="g-abrufmenge" name="abruf_menge" inputmode="numeric"
                 min="<?= (int) $lieferung['abruf_min'] ?>" value="<?= e(alt($altw, 'abruf_menge')) ?>"
                 aria-describedby="g-abrufmenge-hilfe">
          <p class="feld-hilfe" id="g-abrufmenge-hilfe" data-abrufmenge-hinweis>
            Mindestens <?= zahl((int) $lieferung['abruf_min']) ?> Kartons je Abruf.
          </p>
          <?php if (isset($fehler['abruf_menge'])): ?><p class="feld-meldung"><?= e($fehler['abruf_menge']) ?></p><?php endif; ?>
        </div>

        <p class="feld-hilfe" data-lieferart-feld="abholung" hidden>
          Wir rufen Dich unter der oben angegebenen Nummer zur Terminvereinbarung an.
          Abholorte: <?= e(implode(' oder ', array_column($lieferung['abholung_standorte'], 'ort'))) ?>,
          Montag bis Sonntag, kostenlos.
        </p>
      </fieldset>

      <fieldset>
        <legend>Fast fertig</legend>

        <div class="feld feld-check<?= isset($fehler['bestellung_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="bestellung_ok" value="1" required <?= alt($altw, 'bestellung_ok') ? 'checked' : '' ?>>
            <span>
              Ich bestelle verbindlich im Sinne des Startschuss-Prinzips: Sobald genug Betriebe
              und genug Werbebudget zusammen sind, geht meine Menge in die Produktion.
              Bis dahin kann ich jederzeit formlos absagen. Für mich fallen keine Kosten an.
              <span class="pflicht" aria-hidden="true">*</span>
            </span>
          </label>
          <?php if (isset($fehler['bestellung_ok'])): ?><p class="feld-meldung"><?= e($fehler['bestellung_ok']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check">
          <label>
            <input type="checkbox" name="karte_ok" value="1" <?= alt($altw, 'karte_ok') ? 'checked' : '' ?>>
            <span>
              Mein Betrieb darf mit Name, Adresse und Website auf der
              <a href="/teilnehmer.html">Teilnehmerkarte</a> erscheinen. Freiwillig,
              jederzeit widerrufbar, und wir schalten jeden Eintrag von Hand frei.
            </span>
          </label>
        </div>

        <div class="feld feld-check<?= isset($fehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="datenschutz_ok" value="1" required>
            <span>
              Ich habe die <a href="/datenschutz.html" target="_blank" rel="noopener">Datenschutzhinweise</a>
              gelesen und bin mit der Verarbeitung meiner Angaben zur Abwicklung dieser
              Bestellung einverstanden. <span class="pflicht" aria-hidden="true">*</span>
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
    </form>
  </div>
</section>
