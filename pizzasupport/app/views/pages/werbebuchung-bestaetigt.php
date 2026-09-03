<?php
declare(strict_types=1);
$meta['titel']        = 'Buchung bestätigt | Pizza Support';
$meta['beschreibung'] = 'Ihre Werbebuchung bei Pizza Support ist bestätigt.';
$meta['robots']       = 'noindex,follow';

$zustand  = flash_get('werbebuchung_bestaetigt_zustand', 'fehler');
$vergeben = flash_get('werbebuchung_bestaetigt_vergeben', []);
$verloren = flash_get('werbebuchung_bestaetigt_verloren', []);

$label = static function (string $kennung): string {
    $f = flaechenkatalog_eintrag($kennung);
    return $f ? $kennung . ' – ' . $f['bezeichnung'] . ' (' . $f['masse'] . ')' : $kennung;
};
?>
<section class="band">
  <div class="wrap schmal zentriert">
    <?php if ($zustand === 'ok'): ?>
      <h1>Bestätigt, danke!</h1>
      <p>
        Ihre Buchung ist jetzt bestätigt. Verbindlich wird sie erst mit der
        Auftragsbestätigung nach dem Startschuss – bis dahin ist alles kostenfrei.
      </p>
      <?php if ($vergeben): ?>
        <p class="hinweis hinweis-ok" role="status">
          Diese Wunschfläche gehört jetzt fest zu Ihrer Buchung:
          <strong><?= e(implode(', ', array_map($label, $vergeben))) ?></strong>
        </p>
      <?php endif; ?>
      <?php if ($verloren): ?>
        <p class="hinweis hinweis-fehler" role="alert">
          Eine andere Buchung hat vor Ihnen bestätigt: <strong><?= e(implode(', ', array_map($label, $verloren))) ?></strong>
          war deshalb schon vergeben. Wir melden uns bei Ihnen für eine Alternative –
          Ihre übrige Buchung bleibt davon unberührt.
        </p>
      <?php endif; ?>
    <?php else: ?>
      <h1>Der Link hat nicht funktioniert</h1>
      <p>
        Der Bestätigungslink ist entweder abgelaufen oder war schon einmal in Gebrauch.
        Schreiben Sie uns kurz, dann schauen wir gemeinsam nach:
        <a href="mailto:<?= e(firma_email_link()) ?>"><?= e(config('firma.email')) ?></a>.
      </p>
    <?php endif; ?>
    <p>
      <a class="btn btn-primaer" href="/teilnehmer.html">Zum aktuellen Projektstand</a>
    </p>
  </div>
</section>
