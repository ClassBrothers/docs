<?php
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Wie schnell antwortet ihr?',
        'antwort' => '<p>Werktags in der Regel am selben oder am nächsten Tag. Wenn es dringend ist,
                      greifen Sie besser zum Telefon – Mails können in einer vollen Woche auch mal
                      einen Tag liegen bleiben.</p>',
    ],
    [
        'frage'   => 'Ich habe eine Pizzeria und will einfach loslegen. Muss ich erst schreiben?',
        'antwort' => '<p>Nein. Das <a href="/#bestellen">Bestellformular</a> reicht völlig. Wir melden uns
                      dann bei Ihnen, wenn wir eine Rückfrage haben oder wenn es losgeht.</p>',
    ],
    [
        'frage'   => 'Ich möchte über das Projekt berichten. An wen wende ich mich?',
        'antwort' => '<p>Schreiben Sie uns über dieses Formular mit dem Betreff „Presse“. Wir schicken
                      Bildmaterial, Hintergründe und stellen den Kontakt zu teilnehmenden Betrieben her,
                      soweit diese einverstanden sind.</p>',
    ],
];

$meta['titel']        = 'Kontakt zu Pizza Support Freiburg | Pizza Support';
$meta['beschreibung'] = 'Fragen zu kostenlosen Pizzakartons, zu Werbeflächen oder zum Projekt? Schreiben Sie uns – wir antworten werktags meist am selben Tag.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    jsonld_breadcrumb(['Start' => '/', 'Kontakt' => '/kontakt.html']),
];

$fehler = flash_get('kontakt_fehler', []);
$altw   = flash_get('kontakt_alt', []);
$erfolg = flash_get('kontakt_ok');
?>

<section class="seiten-hero">
  <div class="wrap schmal">
    <p class="kicker">Direkter Draht</p>
    <h1>Kontakt zu Pizza Support</h1>
    <p class="hero-lead">
      Der Kontakt zu Pizza Support läuft ohne Ticketsystem und ohne Warteschleife – hier
      schreiben Sie direkt an die Leute, die das Projekt machen. Ob Rückfrage zur Bestellung,
      Interesse an einer Werbefläche oder ein Vorschlag, an den wir noch nicht gedacht haben:
      Schreiben Sie uns, wir antworten.
    </p>
  </div>
</section>

<section class="band">
  <div class="wrap kontakt-raster">

    <div class="kontakt-formular">
      <?php if ($erfolg): ?>
        <p class="hinweis hinweis-ok" role="status"><?= e((string) $erfolg) ?></p>
      <?php endif; ?>
      <?php if ($fehler): ?>
        <p class="hinweis hinweis-fehler" role="alert">Bitte prüfen Sie die markierten Felder.</p>
      <?php endif; ?>

      <h2>Schreiben Sie uns</h2>
      <form method="post" action="/senden/kontakt" class="formular" novalidate>
        <?= csrf_field() ?>
        <?= honeypot_field() ?>

        <div class="feld<?= isset($fehler['name']) ? ' feld-fehler' : '' ?>">
          <label for="k-name">Name <span class="pflicht" aria-hidden="true">*</span></label>
          <input type="text" id="k-name" name="name" required maxlength="120" value="<?= e(alt($altw, 'name')) ?>" autocomplete="name">
          <?php if (isset($fehler['name'])): ?><p class="feld-meldung"><?= e($fehler['name']) ?></p><?php endif; ?>
        </div>

        <div class="feld<?= isset($fehler['email']) ? ' feld-fehler' : '' ?>">
          <label for="k-email">E-Mail <span class="pflicht" aria-hidden="true">*</span></label>
          <input type="email" id="k-email" name="email" required maxlength="254" value="<?= e(alt($altw, 'email')) ?>" autocomplete="email">
          <?php if (isset($fehler['email'])): ?><p class="feld-meldung"><?= e($fehler['email']) ?></p><?php endif; ?>
        </div>

        <div class="feld">
          <label for="k-betreff">Betreff</label>
          <input type="text" id="k-betreff" name="betreff" maxlength="150" value="<?= e(alt($altw, 'betreff')) ?>">
        </div>

        <div class="feld<?= isset($fehler['nachricht']) ? ' feld-fehler' : '' ?>">
          <label for="k-nachricht">Ihre Nachricht <span class="pflicht" aria-hidden="true">*</span></label>
          <textarea id="k-nachricht" name="nachricht" rows="7" required maxlength="4000"><?= e(alt($altw, 'nachricht')) ?></textarea>
          <?php if (isset($fehler['nachricht'])): ?><p class="feld-meldung"><?= e($fehler['nachricht']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check<?= isset($fehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="datenschutz_ok" value="1" required>
            <span>Ich habe die <a href="/datenschutz.html" target="_blank" rel="noopener">Datenschutzhinweise</a> gelesen. <span class="pflicht" aria-hidden="true">*</span></span>
          </label>
          <?php if (isset($fehler['datenschutz_ok'])): ?><p class="feld-meldung"><?= e($fehler['datenschutz_ok']) ?></p><?php endif; ?>
        </div>

        <button class="btn btn-primaer btn-gross" type="submit">Nachricht senden</button>
      </form>
    </div>

    <aside class="kontakt-daten">
      <h2>Auch ohne Formular</h2>
      <p>
        <strong><?= e(config('firma.name')) ?></strong><br>
        <?= e(config('firma.strasse')) ?><br>
        <?= e(config('firma.plz_ort')) ?>
      </p>
      <p>
        <a href="mailto:<?= e(config('firma.email')) ?>"><?= e(config('firma.email')) ?></a><br>
        <a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) config('firma.telefon'))) ?>"><?= e(config('firma.telefon')) ?></a>
      </p>

      <h3>Worum geht es?</h3>
      <ul class="liste-links">
        <li><a href="/#bestellen">Kartons für meinen Betrieb</a></li>
        <li><a href="/werbepartner.html">Werbefläche buchen</a></li>
        <li><a href="/teilnehmer.html">Aktueller Projektstand</a></li>
        <li><a href="/verpackungssteuer-freiburg.html">Fragen zur Verpackungssteuer</a></li>
      </ul>

      <p class="klein">
        Für rechtliche Angaben siehe <a href="/impressum.html">Impressum</a>.
        Wie wir mit Ihren Daten umgehen, steht im <a href="/datenschutz.html">Datenschutz</a>.
      </p>
    </aside>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Bevor Sie schreiben') ?>
</div>
