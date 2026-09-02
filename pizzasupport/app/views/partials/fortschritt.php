<?php
/**
 * Fortschrittsanzeige zum Startschuss.
 * Erwartet $f aus fortschritt(); holt sich die Zahlen sonst selbst.
 */
declare(strict_types=1);
$f = $f ?? fortschritt();

// Die Balkenbreiten kommen aus der Datenbank und muessen deshalb pro Seite
// erzeugt werden. Als style-Attribut waeren sie unter unserer Content-
// Security-Policy wirkungslos - ein Nonce deckt nur <style>-Bloecke ab.
$nonce = $GLOBALS['csp_nonce'] ?? '';
?>
<style nonce="<?= e($nonce) ?>">
  #balken-betriebe{width:<?= (int) $f['betriebe_prozent'] ?>%}
  #balken-budget{width:<?= (int) $f['budget_prozent'] ?>%}
</style>
<section class="fortschritt" aria-labelledby="fortschritt-titel" data-fortschritt>
  <div class="wrap">
    <h2 id="fortschritt-titel" class="fortschritt-titel">
      <?php if ($f['ausgeloest']): ?>
        Der Startschuss ist gefallen – wir sind in Produktion.
      <?php else: ?>
        So weit sind wir bis zum Startschuss
      <?php endif; ?>
    </h2>

    <div class="fortschritt-zahlen">
      <p><strong data-zaehler="betriebe"><?= zahl($f['betriebe']) ?></strong> <span>Gastronomien dabei</span></p>
      <p><strong data-zaehler="unternehmen"><?= zahl($f['unternehmen']) ?></strong> <span>Unternehmen dabei</span></p>
      <p><strong data-zaehler="kartons"><?= zahl($f['kartons']) ?></strong> <span>Kartons vorgemerkt</span></p>
    </div>

    <div class="balken-gruppe">
      <div class="balken-zeile">
        <div class="balken-kopf">
          <span>Gastronomie</span>
          <span><?= zahl($f['betriebe']) ?> von <?= zahl($f['betriebe_ziel']) ?></span>
        </div>
        <div class="balken" role="progressbar" aria-valuenow="<?= $f['betriebe_prozent'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Fortschritt teilnehmende Gastronomien">
          <span class="balken-fuellung" id="balken-betriebe"></span>
        </div>
      </div>
      <div class="balken-zeile">
        <div class="balken-kopf">
          <span>Werbeflächen</span>
          <span><?= $f['budget_prozent'] ?> %</span>
        </div>
        <div class="balken" role="progressbar" aria-valuenow="<?= $f['budget_prozent'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Fortschritt gebuchte Werbeflächen">
          <span class="balken-fuellung balken-fuellung-zwei" id="balken-budget"></span>
        </div>
      </div>
    </div>

    <p class="fortschritt-hinweis">
      <?php if ($f['ausgeloest']): ?>
        Beide Schwellen sind erreicht. Die Kartons sind rund
        <?= e(config('startschuss.lieferwochen')) ?> Wochen nach Produktionsstart da.
      <?php else: ?>
        Gedruckt wird, sobald beide Balken voll sind. Bis dahin ist jede Eintragung
        unverbindlich und jede Buchung kostenfrei stornierbar.
        <a href="/teilnehmer.html">Wer schon dabei ist.</a>
      <?php endif; ?>
    </p>
  </div>
</section>
