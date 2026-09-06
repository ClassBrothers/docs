<?php
/** Ueber uns: Story, Initiatoren, Ausblick. */
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Wer steckt hinter Pizza Support?',
        'antwort' => '<p>Die ' . e(config('firma.name')) . ' aus Freiburg, gastronomisch unterstützt von der
                      ' . e(config('partner_gastro')) . '. Wir verkaufen hier kein Konzept, das jemand
                      anderes umsetzt. Wir bestellen die Kartons, verkaufen die Werbung und nehmen am
                      Ende die Paletten an.</p>',
    ],
    [
        'frage'   => 'Verdient ihr daran?',
        'antwort' => '<p>Ja, sonst gäbe es das Projekt nicht. Aus den Werbeeinnahmen werden Druck, Lagerung,
                      Logistik und unsere Arbeitszeit bezahlt. Was übrig bleibt, ist unsere Marge – wir
                      sind ein Unternehmen, keine Stiftung. Für die Gastronomie bleibt der Karton trotzdem
                      kostenlos, weil er über die Fläche und nicht über den Betrieb finanziert wird.</p>',
    ],
    [
        'frage'   => 'Warum nennt ihr es nicht Crowdfunding?',
        'antwort' => '<p>Weil es keins ist. Niemand gibt Geld für ein Versprechen, niemand erwirbt einen
                      Anteil, niemand trägt ein Ausfallrisiko. Werbepartner buchen eine Leistung und
                      zahlen erst an, wenn die Produktion feststeht. Gastronomien zahlen gar nichts. Wir
                      nennen das Startschuss-Prinzip, weil es genau das beschreibt: Wir starten, wenn
                      genug zusammen ist.</p>',
    ],
    [
        'frage'   => 'Was kommt nach den Pizzakartons?',
        'antwort' => '<p>Erst einmal nichts – wir wollen dieses Projekt sauber zu Ende bringen, bevor wir
                      das nächste anfangen. Wenn es trägt, lässt sich das Prinzip übertragen: auf Kultur,
                      auf Vereine, auf alles, was eine Stadt zusammenhält und dem ein bisschen
                      Finanzierung fehlt.</p>',
    ],
];

$meta['titel']        = 'Über uns: Wer hinter Pizza Support steht | Pizza Support';
$meta['beschreibung'] = 'Pizza Support ist ein Freiburger Projekt der Class Brothers GmbH mit gastronomischer Unterstützung der Badischen Entertainment GmbH. Was uns antreibt und was danach kommt.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    jsonld_breadcrumb(['Start' => '/', 'Über uns' => '/ueber-uns.html']),
];
?>

<section class="hero hero-ueber-uns">
  <div class="wrap hero-innen">
    <div class="hero-text">
      <p class="kicker">Wer wir sind</p>
      <h1>Über uns: Warum ein Freiburger Projekt für Pizzakartons?</h1>
      <p class="hero-lead">
        Ein Freiburger Projekt für Pizzakartons entsteht nicht am Reißbrett, sondern an einem
        Tresen. Wir haben zu oft gehört, wie sich die Rechnung für kleine Gastronomien verschiebt –
        und irgendwann gemerkt, dass wir eine der Stellschrauben tatsächlich drehen können.
        Was daraus geworden ist, steht hier.
      </p>
      <div class="hero-aktionen">
        <a class="btn btn-primaer btn-gross" href="/kontakt.html">Kontakt aufnehmen</a>
      </div>
    </div>
    <aside class="hero-karton" aria-label="Pizzakarton mit Werbefläche">
      <figure class="hero-bild">
        <picture>
          <source srcset="<?= e(asset('/assets/img/pizzakarton-mit-werbung.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('/assets/img/pizzakarton-mit-werbung.png')) ?>"
               alt="Pizzakarton mit Werbefläche auf dem Deckel"
               width="472" height="529" fetchpriority="high" decoding="async">
        </picture>
      </figure>
    </aside>
  </div>
</section>

<section class="band" aria-labelledby="story-titel">
  <div class="wrap schmal">
    <h2 id="story-titel">Wie das angefangen hat</h2>
    <p>
      Wir arbeiten seit Jahren mit Unternehmen in und um Freiburg, viele davon aus der
      Gastronomie. Man bekommt dabei mit, was in den Küchen los ist: Der Wareneinsatz zieht
      an, die Energiekosten haben sich eingependelt, aber auf einem anderen Niveau als früher,
      und Personal zu finden ist zu einer eigenen Disziplin geworden.
    </p>
    <p>
      Als die Verpackungssteuer kam, war das für sich genommen keine Katastrophe. Es war der
      Tropfen, über den plötzlich alle sprachen. In einem dieser Gespräche fiel der Satz, um
      den sich seitdem alles dreht: „Der Karton kostet mich mittlerweile mehr als der Käse
      drauf." Das ist zugespitzt, aber der Punkt saß.
    </p>
    <p>
      Wir kennen uns mit Druck aus, mit Werbung sowieso, und wir wissen, wie man lokale
      Unternehmen zusammenbringt. Die Rechnung dahinter ist simpel: Auf einem Pizzakarton ist
      Platz. Platz, für den jemand bezahlt. Wenn genug Gastronomien mitmachen, wird die Auflage
      groß genug, dass die Fläche für Werbekunden interessant wird – und dann trägt sich das
      Ganze selbst.
    </p>
  </div>
</section>

<section class="band band-hell" aria-labelledby="initiatoren-titel">
  <div class="wrap">
    <h2 id="initiatoren-titel">Wer das trägt</h2>
    <div class="karten karten-zwei">
      <article class="karte karte-gross">
        <h3><?= e(config('firma.name')) ?></h3>
        <p>
          Initiator und Betreiber. Wir kümmern uns um Druck, Werbepartner, Logistik und
          diese Website. Im Hauptgeschäft machen wir Sichtbarkeit für Unternehmen –
          Suchmaschinen, KI-Assistenz, Websites, Coaching. Dieses Projekt ist die
          Anwendung dessen, was wir sonst für andere tun, auf eine Sache, die uns
          selbst am Herzen liegt.
        </p>
      </article>
      <article class="karte karte-gross">
        <h3><?= e(config('partner_gastro')) ?></h3>
        <p>
          Unsere gastronomische Rückendeckung. Sie bringt mit, was uns fehlen würde:
          das Gefühl dafür, was in einem Betrieb an einem Freitagabend tatsächlich
          funktioniert und was nur auf dem Papier gut aussieht. Wenn wir uns über
          Mengen, Formate oder Abläufe streiten, gewinnt meistens ihre Erfahrung.
        </p>
      </article>
    </div>
  </div>
</section>

<section class="band" aria-labelledby="haltung-titel">
  <div class="wrap schmal">
    <h2 id="haltung-titel">Wie wir zur Verpackungssteuer stehen</h2>
    <p>
      Wir haben keine Kampagne gegen die Stadt vor. Der Gedanke hinter der Steuer ist
      richtig: Weniger Einwegmüll ist ein Ziel, das man teilen kann, auch wenn man in der
      Gastronomie zu Hause ist. Wer samstagabends durch die Innenstadt läuft, versteht,
      warum das Thema auf dem Tisch liegt.
    </p>
    <p>
      Unser Punkt ist ein anderer: Zwischen der Absicht und dem Betrieb, der sie umsetzen
      muss, liegt eine Lücke. Die füllen wir. Nicht mit einer Forderung, sondern mit einem
      Karton, der nichts kostet. Wenn die Stadt irgendwann Lust hat, sich das anzuschauen –
      wir sind gesprächsbereit, und ein Projekt, das lokale Gastronomien entlastet und lokale
      Werbung stärkt, passt eigentlich ganz gut in die Gegend.
      <a href="/verpackungssteuer-freiburg.html">Unsere Einordnung der Steuer.</a>
    </p>
  </div>
</section>

<section class="band" aria-labelledby="sonst-titel">
  <div class="wrap schmal">
    <h2 id="sonst-titel">Was wir sonst so können</h2>
    <p>
      Pizza Support ist ein Projekt, kein Unternehmen. Dahinter stehen Leute, die den ganzen
      Tag etwas anderes machen – und genau deshalb wissen, wie man so etwas aufzieht. Falls
      Du über die Kartons hinaus etwas brauchst: Hier sind wir zu Hause.
    </p>
    <div class="partner-eintrag">
      <a class="partner-logo-box" href="https://class-brothers.com" target="_blank" rel="noopener" data-follow>
        <picture>
          <source srcset="<?= e(asset('/assets/img/logo-class-brothers.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('/assets/img/logo-class-brothers.png')) ?>" alt="Logo Class Brothers GmbH" class="partner-logo" width="139" height="48" loading="lazy" decoding="async">
        </picture>
      </a>
      <p>
        <strong><a href="https://class-brothers.com" target="_blank" rel="noopener" data-follow>Class Brothers GmbH</a></strong>
        — Digitale Agentur aus Freiburg, seit 2014. Suchmaschinenoptimierung, Websites,
        Markenberatung und Coaching für Betriebe, die im Netz gefunden werden wollen und nicht
        wissen, wo sie anfangen sollen.
      </p>
    </div>
    <div class="partner-eintrag">
      <a class="partner-logo-box" href="https://ki-assistenz.com" target="_blank" rel="noopener" data-follow>
        <picture>
          <source srcset="<?= e(asset('/assets/img/logo-ki-assistenz.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('/assets/img/logo-ki-assistenz.png')) ?>" alt="Logo KI-Assistenz" class="partner-logo" width="100" height="48" loading="lazy" decoding="async">
        </picture>
      </a>
      <p>
        <strong><a href="https://ki-assistenz.com" target="_blank" rel="noopener" data-follow>KI-Assistenz</a></strong>
        — Beratung und Umsetzung rund um künstliche Intelligenz im
        Arbeitsalltag. Für Unternehmen, die ahnen, dass sich hier etwas verändert, und dabei
        lieber jemanden neben sich haben.
      </p>
    </div>
    <div class="partner-eintrag">
      <a class="partner-logo-box" href="https://snackworks.de" target="_blank" rel="noopener" data-follow>
        <picture>
          <source srcset="<?= e(asset('/assets/img/logo-snackworks.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('/assets/img/logo-snackworks.png')) ?>" alt="Logo SnackWorks" class="partner-logo" width="124" height="48" loading="lazy" decoding="async">
        </picture>
      </a>
      <p>
        <strong><a href="https://snackworks.de" target="_blank" rel="noopener" data-follow>SnackWorks</a></strong>
        — Snackautomaten für Unternehmen, Hotels, Schulen, Bildungseinrichtungen und
        Fitnessstudios: überall dort, wo ein Snack im richtigen Moment die Welt verändern kann.
        Seit 2026 versorgt SnackWorks Freiburg mit Automaten in jeder Größe.
      </p>
    </div>
    <div class="partner-eintrag">
      <a class="partner-logo-box" href="https://badische-entertainment.com" target="_blank" rel="noopener" data-follow>
        <picture>
          <source srcset="<?= e(asset('/assets/img/logo-badische-entertainment.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('/assets/img/logo-badische-entertainment.png')) ?>" alt="Logo Badische Entertainment GmbH" class="partner-logo" width="72" height="48" loading="lazy" decoding="async">
        </picture>
      </a>
      <p>
        <strong><a href="https://badische-entertainment.com" target="_blank" rel="noopener" data-follow>Badische Entertainment GmbH</a></strong>
        — Veranstaltungen, Livemusik und Bühnenprogramm in der Region. Von der Firmenfeier bis
        zum Stadtfest. Ohne die Gastro-Erfahrung von hier gäbe es Pizza Support nicht.
      </p>
    </div>
    <?php $pn = config('partnernachlass'); ?>
    <p>
      Und weil Du uns unterstützt, unterstützen wir Dich zurück: Wer als Werbepartner eine
      Fläche bucht, bekommt <?= (int) $pn['prozent'] ?> % Nachlass auf Leistungen dieser vier
      Häuser, <?= (int) $pn['monate'] ?> Monate ab der Buchung.
    </p>
  </div>
</section>

<section class="band band-dunkel" aria-labelledby="ausblick-titel">
  <div class="wrap schmal">
    <h2 id="ausblick-titel">Und danach?</h2>
    <p>
      Jetzt helfen wir der Gastronomie. Das ist der erste Schritt und für die nächste Zeit
      der einzige, den wir versprechen.
    </p>
    <p>
      Wenn es funktioniert, ist das Prinzip übertragbar. Eine Fläche, die ohnehin da ist,
      finanziert etwas, das der Stadt guttut. Das muss kein Karton sein und es muss nicht
      bei Essen bleiben. Kultur, Vereine, Nachbarschaftsprojekte – überall gibt es Ideen,
      denen nur der letzte Rest Finanzierung fehlt. Wir schauen uns das an, wenn die ersten
      Paletten ausgeliefert sind. Vorher wäre es Gerede.
    </p>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Was man uns oft fragt') ?>
</div>

<section class="band band-cta" aria-labelledby="ueber-cta">
  <div class="wrap schmal zentriert">
    <h2 id="ueber-cta">Überzeugt? Dann jetzt Platz sichern!</h2>
    <p>
      Als Betrieb kostenlos Kartons bekommen oder als Unternehmen eine Fläche buchen –
      beides dauert keine fünf Minuten.
    </p>
    <div class="hero-aktionen zentriert">
      <a class="btn btn-primaer btn-gross" href="/#bestellen">Jetzt bestellen</a>
      <a class="btn btn-sekundaer btn-gross" href="/werbepartner.html">Werbefläche buchen</a>
    </div>
  </div>
</section>
