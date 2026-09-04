<?php
/**
 * Fortschrittsanzeige zum Startschuss.
 * Erwartet $f aus fortschritt_oeffentlich(); holt es sich sonst selbst.
 * Die Balken laufen immer mit dem echten Prozentwert, die konkreten
 * Zahlen (Betriebe/Kartons/Unternehmen/Budget) bleiben verborgen, bis das
 * jeweilige Ziel erreicht ist - siehe fortschritt_oeffentlich() in stats.php.
 */
declare(strict_types=1);
$f = $f ?? fortschritt_oeffentlich();

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

    <?php if ($f['betriebe_erreicht'] || $f['budget_erreicht']): ?>
      <div class="fortschritt-zahlen">
        <?php if ($f['betriebe_erreicht']): ?>
          <p><strong data-zaehler="betriebe"><?= zahl($f['betriebe']) ?></strong> <span>Gastronomien dabei</span></p>
          <p><strong data-zaehler="kartons"><?= zahl($f['kartons']) ?></strong> <span>Kartons vorgemerkt</span></p>
        <?php endif; ?>
        <?php if ($f['budget_erreicht']): ?>
          <p><strong data-zaehler="unternehmen"><?= zahl($f['unternehmen']) ?></strong> <span>Unternehmen dabei</span></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="balken-gruppe">
      <div class="balken-zeile">
        <div class="balken-kopf">
          <span>Gastronomie</span>
          <?php if ($f['betriebe_erreicht']): ?>
            <span><?= zahl($f['betriebe']) ?> von <?= zahl($f['betriebe_ziel']) ?></span>
          <?php endif; ?>
        </div>
        <div class="balken" role="progressbar" aria-valuenow="<?= $f['betriebe_prozent'] ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Fortschritt teilnehmende Gastronomien">
          <span class="balken-fuellung" id="balken-betriebe"></span>
        </div>
      </div>
      <div class="balken-zeile">
        <div class="balken-kopf">
          <span>Werbeflächen</span>
          <?php if ($f['budget_erreicht']): ?>
            <span><?= e(preis($f['budget_cent'])) ?> von <?= e(preis($f['budget_ziel_cent'])) ?></span>
          <?php endif; ?>
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

    <p class="fortschritt-hinweis">
      Den Anfang haben wir selbst gemacht: Die ersten Flächen gehen an vier Unternehmen aus
      unserem eigenen Umfeld. Wer andere um Vertrauen bittet, sollte selbst vorangehen.
    </p>

    <?php if ($f['ersparnis_cent'] > 0): ?>
      <p class="fortschritt-ersparnis">
        So viel hat Pizza Support der Freiburger Gastronomie bisher erspart:
        <strong><?= e(preis($f['ersparnis_cent'])) ?></strong>
        <span class="fortschritt-ersparnis-klein">Grundlage sind die von den Betrieben selbst angegebenen Einkaufspreise.</span>
      </p>
    <?php endif; ?>
  </div>
</section>
