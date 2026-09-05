<?php
/**
 * "Was ein Unternehmen wirklich kauft": Vorteilsliste, die sowohl auf
 * werbepartner.html (Verkaufsseite) als auch auf flaeche-buchen.html
 * (direkt vor dem Formular, als Vertrauensanker) erscheint. Ein einziger
 * Ort dafuer, damit beide Seiten nie auseinanderlaufen.
 */
declare(strict_types=1);

// Ein wiederverwendetes Icon (Haeckchen im Kreis) statt acht verschiedener
// Piktogramme - haelt den Abschnitt schlicht, ohne eine ganze Icon-
// Bibliothek einzubinden.
$kauftHaeckchen = '<svg class="kauft-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">'
  . '<circle cx="12" cy="12" r="11" fill="var(--akzent-waerme)"/>'
  . '<path d="M7 12.5l3 3 7-7" stroke="#241C18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
  . '</svg>';
$pnKauft = config('partnernachlass');
$kauftPunkte = [
  'Gemeinschaft unterstützen – Dein Budget hilft direkt der Freiburger Gastronomie.',
  'Coole, sympathische Werbung statt gewöhnlicher Anzeige.',
  'Viralität und Aufmerksamkeit: ein Pizzakarton wird gesehen, nicht weggeklickt.',
  'Dein Firmenprofil mit Logo und Verlinkung dauerhaft auf pizzasupport.de.',
  'Stammkunden-Rabatt bei zukünftigen Auflagen.',
  'Als Stammkunde künftig vor anderen Deine Fläche wählen.',
  'Bis zu ' . (int) $pnKauft['prozent'] . ' % Rabatt auf Aufträge bei unseren Aktionspartnern (Class Brothers, KI-Assistenz, Badische Entertainment und weitere).',
];
?>
<section class="band band-hell" aria-labelledby="kauft-titel">
  <div class="wrap">
    <h2 id="kauft-titel">Was ein Unternehmen wirklich kauft</h2>
    <ul class="kauft-liste">
      <?php foreach ($kauftPunkte as $punkt): ?>
        <li><?= $kauftHaeckchen ?><span><?= e($punkt) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <?php if (!empty($kauftMitCta)): ?>
      <p class="zwischen-cta">
        <a class="btn btn-primaer btn-gross" href="/flaeche-buchen.html">Fläche jetzt buchen</a>
      </p>
    <?php endif; ?>
  </div>
</section>
