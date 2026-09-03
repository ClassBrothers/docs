<?php
/** Landingpage fuer Gaeste: Abstimmung ueber die Werbemotive und die Aktion. Ansprache: Du. */
declare(strict_types=1);

$meta['titel']        = 'Für Gäste: Stimm über die Pizzakarton-Motive ab | Pizza Support';
$meta['beschreibung'] = 'Welches Werbemotiv auf dem Pizzakarton gefällt Dir am besten? Stimm ab und sag uns, was Du von der Aktion hältst.';
$meta['jsonld'] = [
    jsonld_breadcrumb(['Start' => '/', 'Für Gäste' => '/fuer-gaeste.html']),
];

$fehler = flash_get('abstimmung_fehler', []);
$altw   = flash_get('abstimmung_alt', []);
$erfolg = flash_get('abstimmung_ok');

$motive = db_all("SELECT id, firma FROM werbebuchungen WHERE status = 'freigegeben' ORDER BY firma");
$maxAuswahl = (int) config('abstimmung.max_auswahl');

$favoritGewaehlt = array_map('strval', is_array($altw['motiv_favorit'] ?? null) ? $altw['motiv_favorit'] : []);
$witzigGewaehlt  = array_map('strval', is_array($altw['motiv_witzig'] ?? null) ? $altw['motiv_witzig'] : []);

$bewertungOptionen = [
    'super'    => 'Super! Endlich tut jemand was für die Gastro.',
    'gut'      => 'Gute Idee, die Aufmerksamkeit schafft.',
    'ok'       => 'OK, ist mir aber eigentlich egal.',
    'schlecht' => 'Nicht gut, weil ich es blöd finde, wenn sich jemand für die Gemeinschaft einsetzt.',
];
?>

<section class="seiten-hero">
  <div class="wrap schmal">
    <p class="kicker">Deine Meinung zählt</p>
    <h1>Für Gäste: Wie findest Du die Pizzakartons?</h1>
    <p class="hero-lead">
      Du hast einen Pizzakarton mit einem Werbemotiv drauf in der Hand gehabt oder auf dem
      Tisch stehen sehen? Sag uns, welches Motiv Dir am besten gefällt und was Du von der
      ganzen Aktion hältst. Dauert zwei Minuten.
    </p>
  </div>
</section>

<section class="band" id="abstimmen">
  <div class="wrap schmal">
    <?php if ($erfolg): ?>
      <p class="hinweis hinweis-ok hinweis-gross" role="status"><?= e((string) $erfolg) ?></p>
    <?php endif; ?>
    <?php if ($fehler): ?>
      <p class="hinweis hinweis-fehler" role="alert">Bitte prüfe die markierten Felder.</p>
    <?php endif; ?>

    <?php if (!$motive): ?>
      <p class="feld-hilfe">
        Noch sind keine Motive freigegeben – schau bald wieder vorbei, sobald die ersten
        Kartons unterwegs sind.
      </p>
    <?php else: ?>
      <form method="post" action="/senden/abstimmung" class="formular" novalidate>
        <?= csrf_field() ?>
        <?= honeypot_field() ?>

        <fieldset class="feld<?= isset($fehler['motiv_favorit']) ? ' feld-fehler' : '' ?>">
          <legend>Welches Motiv gefällt Dir am besten? <span class="feld-hilfe-inline">(bis zu <?= $maxAuswahl ?>)</span></legend>
          <div class="wahl-gitter">
            <?php foreach ($motive as $m): ?>
              <label class="wahl wahl-schmal">
                <input type="checkbox" name="motiv_favorit[]" value="<?= e((string) $m['id']) ?>"
                       <?= in_array((string) $m['id'], $favoritGewaehlt, true) ? 'checked' : '' ?>>
                <span class="wahl-inhalt"><strong><?= e($m['firma']) ?></strong></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($fehler['motiv_favorit'])): ?><p class="feld-meldung"><?= e($fehler['motiv_favorit']) ?></p><?php endif; ?>
        </fieldset>

        <fieldset class="feld<?= isset($fehler['motiv_witzig']) ? ' feld-fehler' : '' ?>">
          <legend>Welches Motiv ist das witzigste? <span class="feld-hilfe-inline">(bis zu <?= $maxAuswahl ?>)</span></legend>
          <div class="wahl-gitter">
            <?php foreach ($motive as $m): ?>
              <label class="wahl wahl-schmal">
                <input type="checkbox" name="motiv_witzig[]" value="<?= e((string) $m['id']) ?>"
                       <?= in_array((string) $m['id'], $witzigGewaehlt, true) ? 'checked' : '' ?>>
                <span class="wahl-inhalt"><strong><?= e($m['firma']) ?></strong></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($fehler['motiv_witzig'])): ?><p class="feld-meldung"><?= e($fehler['motiv_witzig']) ?></p><?php endif; ?>
        </fieldset>

        <fieldset class="feld<?= isset($fehler['aktion_bewertung']) ? ' feld-fehler' : '' ?>">
          <legend>Wie gefällt Dir die Aktion generell? <span class="pflicht" aria-hidden="true">*</span></legend>
          <div class="wahl-reihe wahl-reihe-spalte" role="radiogroup" aria-label="Bewertung der Aktion">
            <?php foreach ($bewertungOptionen as $wert => $label): ?>
              <label class="wahl wahl-schmal">
                <input type="radio" name="aktion_bewertung" value="<?= e($wert) ?>" required
                       <?= alt($altw, 'aktion_bewertung') === $wert ? 'checked' : '' ?>>
                <span class="wahl-inhalt"><?= e($label) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($fehler['aktion_bewertung'])): ?><p class="feld-meldung"><?= e($fehler['aktion_bewertung']) ?></p><?php endif; ?>
        </fieldset>

        <div class="feld">
          <label for="a-feedback">Ideen, Anregungen, Kritik? <span class="feld-optional">(freiwillig)</span></label>
          <textarea id="a-feedback" name="feedback" rows="4" maxlength="2000"><?= e(alt($altw, 'feedback')) ?></textarea>
          <p class="feld-hilfe">Wir sind dankbar für jedes Feedback.</p>
        </div>

        <div class="feld-reihe">
          <div class="feld<?= isset($fehler['plz']) ? ' feld-fehler' : '' ?>">
            <label for="a-plz">Postleitzahl <span class="feld-optional">(freiwillig)</span></label>
            <input type="text" id="a-plz" name="plz" inputmode="numeric" maxlength="5" value="<?= e(alt($altw, 'plz')) ?>">
            <?php if (isset($fehler['plz'])): ?><p class="feld-meldung"><?= e($fehler['plz']) ?></p><?php endif; ?>
          </div>
          <div class="feld<?= isset($fehler['alter_jahre']) ? ' feld-fehler' : '' ?>">
            <label for="a-alter">Alter <span class="feld-optional">(freiwillig)</span></label>
            <input type="number" id="a-alter" name="alter_jahre" inputmode="numeric" min="1" max="120" value="<?= e(alt($altw, 'alter_jahre')) ?>">
            <?php if (isset($fehler['alter_jahre'])): ?><p class="feld-meldung"><?= e($fehler['alter_jahre']) ?></p><?php endif; ?>
          </div>
        </div>
        <p class="feld-hilfe">Für unsere Statistik dürfen wir Dich nach Deiner Postleitzahl und Deinem Alter fragen – ganz freiwillig.</p>

        <div class="feld<?= isset($fehler['name']) ? ' feld-fehler' : '' ?>">
          <label for="a-name">Name <span class="feld-optional">(freiwillig)</span></label>
          <input type="text" id="a-name" name="name" maxlength="150" value="<?= e(alt($altw, 'name')) ?>" autocomplete="name">
        </div>
        <div class="feld<?= isset($fehler['email']) ? ' feld-fehler' : '' ?>">
          <label for="a-email">E-Mail <span class="feld-optional">(freiwillig)</span></label>
          <input type="email" id="a-email" name="email" maxlength="254" value="<?= e(alt($altw, 'email')) ?>" autocomplete="email">
          <?php if (isset($fehler['email'])): ?><p class="feld-meldung"><?= e($fehler['email']) ?></p><?php endif; ?>
        </div>
        <p class="feld-hilfe">Wenn Du möchtest, dass wir Dir antworten, hinterlass bitte Deinen Namen und Deine Mailadresse.</p>

        <div class="feld feld-check<?= isset($fehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="datenschutz_ok" value="1" <?= alt($altw, 'datenschutz_ok') ? 'checked' : '' ?>>
            <span>Ich bin mit der Verarbeitung meines Namens und meiner E-Mail-Adresse laut
              <a href="/datenschutz.html" target="_blank" rel="noopener">Datenschutzhinweisen</a> einverstanden
              (nur nötig, wenn Du oben Name oder E-Mail angibst).</span>
          </label>
          <?php if (isset($fehler['datenschutz_ok'])): ?><p class="feld-meldung"><?= e($fehler['datenschutz_ok']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check<?= isset($fehler['newsletter_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="newsletter_ok" value="1" <?= alt($altw, 'newsletter_ok') ? 'checked' : '' ?>>
            <span>Schreibt mir News zum Projektstand von Pizza Support.</span>
          </label>
          <?php if (isset($fehler['newsletter_ok'])): ?><p class="feld-meldung"><?= e($fehler['newsletter_ok']) ?></p><?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primaer">Abstimmen</button>
      </form>
    <?php endif; ?>
  </div>
</section>
