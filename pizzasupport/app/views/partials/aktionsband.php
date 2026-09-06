<?php
/**
 * Aktionsband: Bestellfrist der laufenden Auflage, auf allen Seiten direkt
 * unter dem Kopfbereich.
 *
 * Bewusst nicht mitscrollend (der Kopf ist sticky, dieses Band nicht):
 * ganz oben sieht es jeder, danach gibt es die Flaeche wieder frei. Die
 * Wiederholung an der Stelle, an der wirklich entschieden wird, steht
 * direkt ueber den beiden Formularen.
 *
 * Nach dem Fristtag verschwindet das Band ersatzlos - bestellen und buchen
 * bleibt moeglich, nur eben ohne Frist. Steht in der Konfiguration eine
 * Verlaengerung, laeuft das Band mit geaendertem Text bis dahin weiter.
 */
declare(strict_types=1);

$aktion = aktion();
if (!$aktion['aktiv']) {
    return;
}
?>
<div class="aktionsband<?= $aktion['verlaengert'] ? ' aktionsband-verlaengert' : '' ?>" role="status">
  <div class="wrap aktionsband-innen">
    <span class="aktionsband-marke" aria-hidden="true">
      <?= $aktion['tage'] === 0 ? 'Letzter Tag' : 'Noch ' . zahl($aktion['tage']) . ' Tag' . ($aktion['tage'] === 1 ? '' : 'e') ?>
    </span>
    <span class="aktionsband-text">
      <?php if ($aktion['verlaengert']): ?>
        <strong>Aktion verlängert bis <?= e($aktion['datum']) ?>!</strong>
        Pizzakartons und Werbeflächen weiterhin bestellbar.
      <?php else: ?>
        <strong>Bestellungen nur bis <?= e($aktion['datum']) ?> möglich!</strong>
        Gilt für Pizzakartons und Werbeflächen.
      <?php endif; ?>
    </span>
  </div>
</div>
