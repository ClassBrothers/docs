<?php
/**
 * Bestellformular fuer die Gastronomie.
 * Ansprache: Du. Erwartet $formate, $mengen, $fehler, $altw, $erfolg.
 */
declare(strict_types=1);

$formate = $formate ?? config('karton_formate');
$mengen  = $mengen  ?? config('mengen');
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
$gewaehltesFormat = alt($altw, 'format', $standardFormat);
$gewaehlteMenge   = alt($altw, 'menge', (string) $mengen['presets'][0]);
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

    <form method="post" action="/senden/gastro" class="formular formular-breit" novalidate>
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

        <div class="feld<?= isset($fehler['format']) ? ' feld-fehler' : '' ?>">
          <span class="feld-label">Kartongröße <span class="pflicht" aria-hidden="true">*</span></span>
          <div class="wahl-gitter" role="radiogroup" aria-label="Kartongröße">
            <?php foreach ($formate as $fm): ?>
              <label class="wahl">
                <input type="radio" name="format" value="<?= e($fm['id']) ?>" required
                       <?= $gewaehltesFormat === $fm['id'] ? 'checked' : '' ?>>
                <span class="wahl-inhalt">
                  <strong><?= e($fm['label']) ?></strong>
                  <small><?= e($fm['hinweis']) ?></small>
                  <?php if (!empty($fm['sofort'])): ?>
                    <em class="wahl-badge">läuft als Erstes</em>
                  <?php endif; ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="feld-hilfe">
            Die Erstauflage läuft auf <?= e($standardFormat) ?> × <?= e($standardFormat) ?> × <?= (int) config('karton_hoehe_cm') ?> cm.
            Die übrigen Größen produzieren wir, sobald dafür genug Bedarf zusammenkommt –
            trag ruhig ein, was Du wirklich brauchst.
          </p>
          <?php if (isset($fehler['format'])): ?><p class="feld-meldung"><?= e($fehler['format']) ?></p><?php endif; ?>
        </div>

        <div class="feld<?= isset($fehler['menge']) ? ' feld-fehler' : '' ?>">
          <label for="g-menge">Wie viele Kartons brauchst Du? <span class="pflicht" aria-hidden="true">*</span></label>
          <div class="menge-wahl" data-menge>
            <?php foreach ($mengen['presets'] as $p): ?>
              <button type="button" class="menge-knopf<?= $gewaehlteMenge === (string) $p ? ' ist-aktiv' : '' ?>" data-menge-wert="<?= (int) $p ?>">
                <?= zahl((int) $p) ?>
              </button>
            <?php endforeach; ?>
            <input type="number" id="g-menge" name="menge" required
                   min="<?= (int) $mengen['min'] ?>" max="<?= (int) $mengen['max'] ?>" step="<?= (int) $mengen['step'] ?>"
                   value="<?= e($gewaehlteMenge) ?>" inputmode="numeric" aria-describedby="g-menge-hilfe">
          </div>
          <p class="feld-hilfe" id="g-menge-hilfe">
            Ab <?= zahl((int) $mengen['min']) ?> Stück, in Schritten von <?= (int) $mengen['step'] ?>.
            Mehr als <?= zahl((int) $mengen['max']) ?> geht auch – dann schreib uns kurz direkt.
            Je mehr Du abnimmst, desto schneller ist die Schwelle erreicht.
          </p>
          <?php if (isset($fehler['menge'])): ?><p class="feld-meldung"><?= e($fehler['menge']) ?></p><?php endif; ?>
        </div>

        <div class="feld">
          <label for="g-anmerkung">Willst Du uns noch etwas mitgeben? <span class="feld-optional">(freiwillig)</span></label>
          <textarea id="g-anmerkung" name="anmerkung" rows="3" maxlength="1500"><?= e(alt($altw, 'anmerkung')) ?></textarea>
        </div>
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
