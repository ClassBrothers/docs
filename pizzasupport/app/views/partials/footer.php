<?php declare(strict_types=1); ?>
<footer class="fuss">
  <div class="wrap fuss-innen">

    <div class="fuss-spalte fuss-marke">
      <img class="fuss-logo" src="<?= e(asset(config('logo.src'))) ?>" width="1000" height="649" alt="Pizza Support" loading="lazy" decoding="async">
      <p>Kostenlose Pizzakartons für die Freiburger Gastronomie, bezahlt von Werbung aus der Nachbarschaft.</p>
      <p class="fuss-initiator">
        Ein Projekt der <?= e(config('firma.name')) ?> mit gastronomischer Unterstützung der
        <?= e(config('partner_gastro')) ?>.
      </p>
    </div>

    <nav class="fuss-spalte" aria-labelledby="fuss-nav-1">
      <h2 id="fuss-nav-1">Mitmachen</h2>
      <ul>
        <li><a href="/#bestellen">Kartons bestellen</a></li>
        <li><a href="/werbepartner.html">Werbefläche buchen</a></li>
        <li><a href="/teilnehmer.html">Wer schon dabei ist</a></li>
        <li><a href="/teilnehmer.html#newsletter">Auf dem Laufenden bleiben</a></li>
      </ul>
    </nav>

    <nav class="fuss-spalte" aria-labelledby="fuss-nav-2">
      <h2 id="fuss-nav-2">Hintergrund</h2>
      <ul>
        <li><a href="/verpackungssteuer-freiburg.html">Verpackungssteuer Freiburg</a></li>
        <li><a href="/ueber-uns.html">Über uns</a></li>
        <li><a href="/kontakt.html">Kontakt</a></li>
        <li><a href="/agb.html">AGB</a></li>
      </ul>
    </nav>

    <div class="fuss-spalte">
      <h2>Direkt erreichbar</h2>
      <p>
        <a href="mailto:<?= e(config('firma.email')) ?>"><?= e(config('firma.email')) ?></a><br>
        <?= e(config('firma.plz_ort')) ?>
      </p>
    </div>

  </div>

  <div class="wrap fuss-unten">
    <p class="fuss-services">
      Aus der gleichen Werkstatt: <a href="https://class-brothers.com" rel="nofollow noopener">Class&nbsp;Brothers</a> für
      <a href="https://class-brothers.com" rel="nofollow noopener">SEO</a>,
      <a href="https://class-brothers.com" rel="nofollow noopener">KI-Assistenz</a> und
      <a href="https://class-brothers.com" rel="nofollow noopener">Coaching</a>,
      <a href="https://webdesign-freiburg.info" rel="nofollow noopener">Webdesign Freiburg</a> für Websites,
      <a href="https://badische-entertainment.de" rel="nofollow noopener">Badische Entertainment</a> für Events.
    </p>
    <p class="fuss-recht">
      <a href="/impressum.html">Impressum</a>
      <span aria-hidden="true">·</span>
      <a href="/datenschutz.html">Datenschutz</a>
      <span aria-hidden="true">·</span>
      <button type="button" class="link-button" data-consent-oeffnen>Cookie-Einstellungen</button>
      <span aria-hidden="true">·</span>
      <span>&copy; <?= date('Y') ?> <?= e(config('firma.name')) ?></span>
    </p>
  </div>
</footer>
