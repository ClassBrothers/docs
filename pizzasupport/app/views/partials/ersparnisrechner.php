<?php
/**
 * Ersparnisrechner: Einkaufspreis je Karton × Kartons pro Monat.
 *
 * Preis und Menge werden mit dem Bestellformular darunter mitgeschickt
 * (form="formular-bestellen") und dort auch ausgewertet - sie fliessen in
 * die oeffentliche Ersparnis-Summe auf der Startseite ein (siehe
 * fortschritt() in app/lib/stats.php). Die Berechnung hier ist reine
 * Anzeige im Browser und rechnet live bei jeder Eingabe.
 *
 * Ohne JavaScript bleiben die Felder nutzbar und werden ganz normal mit
 * der Bestellung abgeschickt, es gibt nur keine Live-Anzeige - die
 * eigentliche Bestellung darunter funktioniert davon unabhaengig. Die
 * Vorbelegung zeigt serverseitig schon ein plausibles Ergebnis.
 */
declare(strict_types=1);

$er     = config('ersparnisrechner');
$fehler = $fehler ?? [];
$altw   = $altw   ?? [];

$vorbelegtPreisCent = 45;
$vorbelegtMonat     = 1300;
$angezeigtPreis     = alt($altw, 'einkaufspreis') ?: number_format($vorbelegtPreisCent / 100, 2, ',', '');
$angezeigtMonat     = alt($altw, 'kartons_monat') ?: (string) $vorbelegtMonat;

$vorschauPreisCent = (int) round((float) str_replace(',', '.', $angezeigtPreis) * 100);
$vorschauMonat     = (int) $angezeigtMonat;
$vorschauMonatCent = $vorschauPreisCent * $vorschauMonat;
$vorschauJahrCent  = $vorschauMonatCent * 12;
?>
<section class="band band-hell" id="rechner" aria-labelledby="rechner-titel">
  <div class="wrap schmal">
    <h2 id="rechner-titel">Was kannst Du durch kostenlose Pizzakartons sparen?</h2>
    <p class="band-lead">
      Trag ein, was ein Karton Dich bisher im Einkauf kostet und wie viele Du im Monat
      brauchst.
    </p>

    <div class="rechner" data-rechner
         data-preis-min="<?= (int) $er['einkaufspreis_min_cent'] ?>" data-preis-max="<?= (int) $er['einkaufspreis_max_cent'] ?>"
         data-monat-min="<?= (int) $er['kartons_monat_min'] ?>" data-monat-max="<?= (int) $er['kartons_monat_max'] ?>">
      <div class="feld-reihe">
        <div class="feld<?= isset($fehler['einkaufspreis']) ? ' feld-fehler' : '' ?>">
          <label for="r-preis">Einkaufspreis je Karton <span class="feld-optional">(Euro)</span></label>
          <input type="text" id="r-preis" name="einkaufspreis" form="formular-bestellen" inputmode="decimal"
                 placeholder="z. B. 0,45" value="<?= e($angezeigtPreis) ?>" data-rechner-preis>
          <?php if (isset($fehler['einkaufspreis'])): ?><p class="feld-meldung"><?= e($fehler['einkaufspreis']) ?></p><?php endif; ?>
        </div>
        <div class="feld<?= isset($fehler['kartons_monat']) ? ' feld-fehler' : '' ?>">
          <label for="r-monat">Kartons pro Monat</label>
          <input type="number" id="r-monat" name="kartons_monat" form="formular-bestellen" inputmode="numeric"
                 min="<?= (int) $er['kartons_monat_min'] ?>" max="<?= (int) $er['kartons_monat_max'] ?>"
                 value="<?= e($angezeigtMonat) ?>" data-rechner-monat>
          <?php if (isset($fehler['kartons_monat'])): ?><p class="feld-meldung"><?= e($fehler['kartons_monat']) ?></p><?php endif; ?>
        </div>
      </div>

      <div class="rechner-ergebnis rechner-kasse" data-rechner-ergebnis aria-live="polite">
        <p class="rechner-fliesstext">
          Das sind <strong class="rechner-kachel" data-rechner-monatssumme><?= e(preis($vorschauMonatCent)) ?> im Monat</strong>,
          die im Betrieb bleiben. Im Jahr:
          <strong class="rechner-kachel rechner-kachel-akzent" data-rechner-jahressumme><?= e(preis($vorschauJahrCent)) ?></strong>.
        </p>
        <p class="rechner-steuer">Die Verpackungssteuer nimmt Dir niemand ab. Den Einkauf schon.</p>
        <p class="zwischen-cta">
          <a class="btn btn-primaer btn-gross" href="#bestellen">Ich will kostenlose Pizzakartons bestellen!</a>
        </p>
      </div>
      <noscript><p class="feld-hilfe">Die Zahl oben zeigt das Beispiel mit den vorbelegten Werten. Für eine Live-Berechnung mit eigenen Zahlen braucht es JavaScript – bestellen kannst Du auch so.</p></noscript>
    </div>
  </div>
</section>
