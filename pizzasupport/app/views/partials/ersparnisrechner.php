<?php
/**
 * Ersparnisrechner: Einkaufspreis je Karton × gewuenschte Bestellmenge.
 * Die Menge liest live aus dem Bestellformular darunter mit - so haengen
 * beide zusammen, ohne dass man sie zweimal eintragen muss.
 *
 * Rechnet im Browser, damit das Ergebnis sofort da ist. Ohne JavaScript
 * bleiben die Felder nutzbar, es gibt nur keine Sofortanzeige - die
 * eigentliche Bestellung darunter funktioniert davon unabhaengig.
 */
declare(strict_types=1);

$er     = config('ersparnisrechner');
$fehler = $fehler ?? [];
$altw   = $altw   ?? [];
?>
<section class="band band-hell" id="rechner" aria-labelledby="rechner-titel">
  <div class="wrap schmal">
    <h2 id="rechner-titel">Was sparst Du mit kostenlosen Kartons?</h2>
    <p class="band-lead">
      Trag ein, was ein Karton Dich bisher im Einkauf kostet – das Ergebnis siehst Du sofort,
      mit der Menge aus dem Formular weiter unten.
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
          <label for="r-monat">Kartons pro Monat <span class="feld-optional">(nur zur Einordnung)</span></label>
          <input type="number" id="r-monat" name="kartons_monat" form="formular-bestellen" inputmode="numeric"
                 min="<?= (int) $er['kartons_monat_min'] ?>" max="<?= (int) $er['kartons_monat_max'] ?>"
                 value="<?= e(alt($altw, 'kartons_monat')) ?>" data-rechner-monat>
          <?php if (isset($fehler['kartons_monat'])): ?><p class="feld-meldung"><?= e($fehler['kartons_monat']) ?></p><?php endif; ?>
        </div>
        <div class="feld">
          <span class="feld-label">Gewünschte Bestellmenge</span>
          <p class="rechner-menge-anzeige"><strong data-rechner-menge-wert>–</strong> Kartons</p>
          <p class="feld-hilfe">Aus dem Formular weiter unten übernommen.</p>
        </div>
      </div>

      <div class="rechner-ergebnis" data-rechner-ergebnis aria-live="polite" hidden>
        <span class="rechner-label">Deine Ersparnis</span>
        <strong class="rechner-zahl" data-rechner-zahl>0 €</strong>
        <p class="rechner-hinweis" data-rechner-hinweis></p>
        <p class="rechner-steuer">Die Verpackungssteuer nimmt Dir niemand ab. Den Einkauf schon.</p>
      </div>
      <noscript><p class="feld-hilfe">Für die Sofortanzeige braucht es JavaScript – bestellen kannst Du auch so.</p></noscript>
    </div>
  </div>
</section>
