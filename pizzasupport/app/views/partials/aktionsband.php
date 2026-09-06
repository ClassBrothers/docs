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
 * Datum und Restlaufzeit kommen aus aktion() (siehe bootstrap.php), damit
 * ueberall auf der Seite dieselbe Zahl steht.
 */
declare(strict_types=1);

$aktion = aktion();
if (!$aktion['aktiv'] && !$aktion['abgelaufen']) {
    return;
}
?>
<div class="aktionsband<?= $aktion['abgelaufen'] ? ' aktionsband-vorbei' : '' ?>" role="status">
  <div class="wrap aktionsband-innen">
    <?php if ($aktion['abgelaufen']): ?>
      <span class="aktionsband-text">
        <strong>Die Bestellfrist ist abgelaufen.</strong>
        Schreib uns trotzdem – wir sagen Dir, wann die nächste Auflage startet.
      </span>
    <?php else: ?>
      <span class="aktionsband-marke" aria-hidden="true">
        <?= $aktion['tage'] === 0 ? 'Letzter Tag' : 'Noch ' . zahl($aktion['tage']) . ' Tag' . ($aktion['tage'] === 1 ? '' : 'e') ?>
      </span>
      <span class="aktionsband-text">
        <strong>Bestellungen nur bis <?= e($aktion['datum']) ?> möglich!</strong>
        Gilt für Pizzakartons und Werbeflächen.
      </span>
    <?php endif; ?>
  </div>
</div>
