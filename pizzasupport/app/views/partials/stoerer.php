<?php declare(strict_types=1); ?>
<button class="stoerer" type="button" data-modal-oeffnen="modal-empfehlung" aria-label="Lieblings-Pizzeria vorschlagen">
  <span class="stoerer-icon" aria-hidden="true">🍕</span>
  <span class="stoerer-text">Unterstütze Deine<br><strong>Lieblings-Pizzeria!</strong></span>
</button>

<div class="modal" id="modal-empfehlung" hidden role="dialog" aria-modal="true" aria-labelledby="modal-empfehlung-titel">
  <div class="modal-hinter" data-modal-schliessen></div>
  <div class="modal-box" role="document">
    <button class="modal-zu" type="button" data-modal-schliessen aria-label="Schließen">&times;</button>

    <h2 id="modal-empfehlung-titel">Welche Pizzeria sollen wir ansprechen?</h2>
    <p class="modal-vorspann">
      Du hast einen Laden, der die Kartons gut gebrauchen könnte? Sag uns, welcher.
      Wir melden uns dort und erklären, wie das Ganze funktioniert. Kostet die
      Pizzeria nichts und Dich auch nicht.
    </p>

    <?php if ($erfolg = flash_get('empfehlung_ok')): ?>
      <p class="hinweis hinweis-ok" role="status"><?= e((string) $erfolg) ?></p>
    <?php endif; ?>

    <?php $fehler = flash_get('empfehlung_fehler', []); $altw = flash_get('empfehlung_alt', []); ?>
    <?php if ($fehler): ?>
      <p class="hinweis hinweis-fehler" role="alert">Da fehlt noch etwas – schau bitte unten.</p>
    <?php endif; ?>

    <form method="post" action="/senden/empfehlung" class="formular" novalidate>
      <?= csrf_field() ?>
      <?= honeypot_field() ?>

      <div class="feld<?= isset($fehler['name']) ? ' feld-fehler' : '' ?>">
        <label for="emp-name">Name der Pizzeria <span class="pflicht" aria-hidden="true">*</span></label>
        <input type="text" id="emp-name" name="name" required maxlength="120"
               value="<?= e(alt($altw, 'name')) ?>" autocomplete="off"
               <?= isset($fehler['name']) ? 'aria-invalid="true" aria-describedby="emp-name-fehler"' : '' ?>>
        <?php if (isset($fehler['name'])): ?><p class="feld-meldung" id="emp-name-fehler"><?= e($fehler['name']) ?></p><?php endif; ?>
      </div>

      <div class="feld">
        <label for="emp-strasse">Straße und Hausnummer</label>
        <input type="text" id="emp-strasse" name="strasse" maxlength="150" value="<?= e(alt($altw, 'strasse')) ?>">
      </div>

      <div class="feld-reihe">
        <div class="feld feld-klein<?= isset($fehler['plz']) ? ' feld-fehler' : '' ?>">
          <label for="emp-plz">PLZ</label>
          <input type="text" id="emp-plz" name="plz" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" value="<?= e(alt($altw, 'plz')) ?>">
          <?php if (isset($fehler['plz'])): ?><p class="feld-meldung"><?= e($fehler['plz']) ?></p><?php endif; ?>
        </div>
        <div class="feld">
          <label for="emp-ort">Ort</label>
          <input type="text" id="emp-ort" name="ort" maxlength="100" value="<?= e(alt($altw, 'ort', 'Freiburg im Breisgau')) ?>">
        </div>
      </div>

      <div class="feld">
        <label for="emp-hinweis">Warum gerade die? <span class="feld-optional">(freiwillig)</span></label>
        <textarea id="emp-hinweis" name="hinweis" rows="2" maxlength="500"><?= e(alt($altw, 'hinweis')) ?></textarea>
      </div>

      <div class="feld<?= isset($fehler['melder_email']) ? ' feld-fehler' : '' ?>">
        <label for="emp-email">Deine E-Mail <span class="feld-optional">(freiwillig, falls wir nachfragen wollen)</span></label>
        <input type="email" id="emp-email" name="melder_email" maxlength="254" value="<?= e(alt($altw, 'melder_email')) ?>" autocomplete="email">
        <?php if (isset($fehler['melder_email'])): ?><p class="feld-meldung"><?= e($fehler['melder_email']) ?></p><?php endif; ?>
      </div>

      <div class="feld feld-check<?= isset($fehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
        <label>
          <input type="checkbox" name="datenschutz_ok" value="1" required>
          <span>Ich habe die <a href="/datenschutz.html" target="_blank" rel="noopener">Datenschutzhinweise</a> gelesen. <span class="pflicht" aria-hidden="true">*</span></span>
        </label>
        <?php if (isset($fehler['datenschutz_ok'])): ?><p class="feld-meldung"><?= e($fehler['datenschutz_ok']) ?></p><?php endif; ?>
      </div>

      <button class="btn btn-primaer btn-block" type="submit">Pizzeria vorschlagen</button>
    </form>
  </div>
</div>
