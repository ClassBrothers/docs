<?php
declare(strict_types=1);
$meta['titel']        = 'Anmeldung bestätigt | Pizza Support';
$meta['beschreibung'] = 'Deine Newsletter-Anmeldung ist bestätigt.';
$meta['robots']       = 'noindex,follow';
$zustand = flash_get('newsletter_bestaetigt_zustand', 'ok');
?>
<section class="band">
  <div class="wrap schmal zentriert">
    <?php if ($zustand === 'ok'): ?>
      <h1>Passt, Du bist eingetragen</h1>
      <p>
        Wir melden uns, wenn es etwas zu berichten gibt: wenn die Schwelle fällt, wenn
        die Produktion startet, wenn die Kartons unterwegs sind. Sonst hörst Du
        nichts von uns.
      </p>
    <?php else: ?>
      <h1>Der Link hat nicht funktioniert</h1>
      <p>
        Der Bestätigungslink ist entweder abgelaufen oder war schon einmal in Gebrauch.
        Trag Dich am besten noch einmal ein – dann schicken wir einen frischen Link.
      </p>
    <?php endif; ?>
    <p>
      <a class="btn btn-primaer" href="/teilnehmer.html">Zum aktuellen Projektstand</a>
    </p>
  </div>
</section>
