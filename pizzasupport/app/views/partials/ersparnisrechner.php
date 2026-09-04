<?php
/**
 * Ersparnisrechner: Einkaufspreis je Karton × Kartons pro Monat.
 *
 * Preis und Menge werden mit dem Bestellformular darunter mitgeschickt
 * (form="formular-bestellen") und dort auch ausgewertet - sie fliessen in
 * die oeffentliche Ersparnis-Summe auf der Startseite ein (siehe
 * fortschritt() in app/lib/stats.php). Die Berechnung hier ist reine
 * Anzeige im Browser, erst nach einem Klick auf "Berechnen" - nicht mehr
 * live bei jeder Eingabe, damit klar ist, wann ein Ergebnis dasteht.
 *
 * Ohne JavaScript bleiben die Felder nutzbar und werden ganz normal mit
 * der Bestellung abgeschickt, es gibt nur keine Sofortanzeige - die
 * eigentliche Bestellung darunter funktioniert davon unabhaengig.
 */
declare(strict_types=1);

$er     = config('ersparnisrechner');
$fehler = $fehler ?? [];
$altw   = $altw   ?? [];
?>
<section class="band band-hell" id="rechner" aria-labelledby="rechner-titel">
  <div class="wrap schmal">
    <h2 id="rechner-titel">Was kannst Du durch kostenlose Pizzakartons sparen?</h2>
    <p class="band-lead">
      Trag ein, was ein Karton Dich bisher im Einkauf kostet und wie viele Du im Monat
      brauchst, und klick auf „Berechnen“.
    </p>

    <div class="rechner" data-rechner
         data-preis-min="<?= (int) $er['einkaufspreis_min_cent'] ?>" data-preis-max="<?= (int) $er['einkaufspreis_max_cent'] ?>"
         data-monat-min="<?= (int) $er['kartons_monat_min'] ?>" data-monat-max="<?= (int) $er['kartons_monat_max'] ?>">
      <div class="feld-reihe">
        <div class="feld<?= isset($fehler['einkaufspreis']) ? ' feld-fehler' : '' ?>">
          <label for="r-preis">Einkaufspreis je Karton <span class="feld-optional">(Euro)</span></label>
          <input type="text" id="r-preis" name="einkaufspreis" form="formular-bestellen" inputmode="decimal"
                 placeholder="z. B. 0,45" value="<?= e(alt($altw, 'einkaufspreis')) ?>" data-rechner-preis>
          <?php if (isset($fehler['einkaufspreis'])): ?><p class="feld-meldung"><?= e($fehler['einkaufspreis']) ?></p><?php endif; ?>
        </div>
        <div class="feld<?= isset($fehler['kartons_monat']) ? ' feld-fehler' : '' ?>">
          <label for="r-monat">Kartons pro Monat</label>
          <input type="number" id="r-monat" name="kartons_monat" form="formular-bestellen" inputmode="numeric"
                 min="<?= (int) $er['kartons_monat_min'] ?>" max="<?= (int) $er['kartons_monat_max'] ?>"
                 value="<?= e(alt($altw, 'kartons_monat')) ?>" data-rechner-monat>
          <?php if (isset($fehler['kartons_monat'])): ?><p class="feld-meldung"><?= e($fehler['kartons_monat']) ?></p><?php endif; ?>
        </div>
      </div>

      <button type="button" class="btn btn-primaer" data-rechner-berechnen>Berechnen</button>
      <p class="feld-meldung" data-rechner-eingabe-hinweis hidden>Trag oben Preis und Menge ein, dann klappt's.</p>

      <div class="rechner-ergebnis rechner-kasse" data-rechner-ergebnis aria-live="polite" hidden>
        <p class="rechner-fliesstext">
          Du zahlst jeden Monat
          <strong class="rechner-kachel" data-rechner-monatssumme>0 €</strong>
          für Pizzakartons. Das sind pro Jahr ca.
          <strong class="rechner-kachel rechner-kachel-akzent" data-rechner-jahressumme>0 €</strong>.
        </p>
        <p class="rechner-steuer">Die Verpackungssteuer nimmt Dir niemand ab. Den Einkauf schon.</p>
        <p class="zwischen-cta">
          <a class="btn btn-primaer btn-gross" href="#bestellen">Ich will kostenlose Pizzakartons bestellen!</a>
        </p>
      </div>
      <noscript><p class="feld-hilfe">Für die Berechnung braucht es JavaScript – bestellen kannst Du auch so.</p></noscript>
    </div>
  </div>
</section>
