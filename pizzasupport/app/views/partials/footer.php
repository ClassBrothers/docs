<?php declare(strict_types=1); ?>
<footer class="fuss">
  <div class="tricolore-bar" aria-hidden="true"></div>
  <div class="wrap fuss-innen">

    <div class="fuss-spalte fuss-marke">
      <?php $fussLogo = config('logo.src'); ?>
      <?php if (str_ends_with($fussLogo, '.png')): ?>
        <picture>
          <source srcset="<?= e(asset(substr($fussLogo, 0, -4) . '.webp')) ?>" type="image/webp">
          <img class="fuss-logo" src="<?= e(asset($fussLogo)) ?>" width="260" height="168" alt="Pizza Support" loading="lazy" decoding="async">
        </picture>
      <?php else: ?>
        <img class="fuss-logo" src="<?= e(asset($fussLogo)) ?>" width="260" height="168" alt="Pizza Support" loading="lazy" decoding="async">
      <?php endif; ?>
      <p>Kostenlose Pizzakartons für die Freiburger Gastronomie, finanziert durch Unternehmen aus der Nachbarschaft.</p>
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
        <li><a href="/werbeideen.html">Werbeideen ansehen</a></li>
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
        <a href="tel:<?= e(preg_replace('/[^+0-9]/', '', (string) config('firma.telefon'))) ?>"><?= e(config('firma.telefon')) ?></a><br>
        <?= email_link_html() ?><br>
        <?= e(config('firma.plz_ort')) ?>
      </p>
    </div>

  </div>

  <div class="wrap fuss-unten">
    <p class="fuss-services">
      Ein Projekt der <a href="https://class-brothers.com" rel="nofollow noopener">Class&nbsp;Brothers GmbH</a> – Agentur für
      <a href="https://class-brothers.com/seo" rel="nofollow noopener">SEO</a> und
      <a href="https://webdesign-freiburg.info" rel="nofollow noopener">Webdesign Freiburg</a>, in Zusammenarbeit mit der
      <a href="https://badische-entertainment.com" rel="nofollow noopener">Badische Entertainment GmbH – Eventagentur und Gastroconsulting</a>.
      <a href="https://class-brothers.com/was-kostet-diese-website/" data-follow>Was kostet diese Website?</a>
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
