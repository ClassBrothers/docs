<?php
/** Startseite: Hero, Story, Startschuss-Prinzip, Bestellung, Formate, FAQ. */
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Was kosten die Pizzakartons für meinen Betrieb?',
        'antwort' => '<p>Nichts. Kein Cent, keine versteckte Gebühr, keine Mindestabnahme gegen Rechnung.
                      Die Kartons sind vollständig durch die Werbeflächen finanziert, die Unternehmen und
                      Privatleute auf Deckel, Seite und Boden buchen. Du bezahlst weder Druck noch
                      Lieferung. Was Du einbringst, ist Dein Bedarf – je mehr Gastronomien mitmachen,
                      desto attraktiver werden die Flächen für die
                      <a href="/werbepartner.html">Werbepartner</a>.</p>',
    ],
    [
        'frage'   => 'Wie läuft das Startschuss-Prinzip genau ab?',
        'antwort' => '<p>Erst sammeln, dann drucken. Gastronomien tragen ihren Bedarf ein, Unternehmen buchen
                      Flächen. Wenn beides zusammenkommt – genug Kartons abgenommen und genug Werbebudget
                      gebucht – geben wir die Produktion frei. Danach dauert es rund '
                      . e(config('startschuss.lieferwochen')) . ' Wochen, bis die Paletten hier stehen und
                      wir ausliefern. Vorher passiert nichts, und niemand zahlt etwas an.</p>',
    ],
    [
        'frage'   => 'Welche Werbung landet auf meinen Kartons?',
        'antwort' => '<p>Handwerk aus dem Viertel, der Steuerberater um die Ecke, das Fitnessstudio,
                      der Heiratsantrag auf der Fun Area. Was nicht draufkommt: Essens-Lieferdienste,
                      also Deine direkte Konkurrenz. Dazu nichts Politisches, nichts Religiöses und
                      nichts, was ohne Sachkenntnis Meinung macht. Wir behalten uns bei jedem Motiv
                      das letzte Wort vor.</p>',
    ],
    [
        'frage'   => 'Hat das etwas mit der Freiburger Verpackungssteuer zu tun?',
        'antwort' => '<p>Es ist der Anlass. Seit die Steuer auf Einwegverpackungen erhoben wird, schlägt
                      jeder Karton zusätzlich zu Buche. Wir können die Steuer nicht abschaffen und wollen
                      das auch gar nicht – aber wir können den Einkaufspreis der Verpackung auf null
                      drücken. Was die Steuer für eine Gastronomie konkret bedeutet, haben wir auf der
                      Seite <a href="/verpackungssteuer-freiburg.html">Verpackungssteuer Freiburg</a>
                      durchgerechnet.</p>',
    ],
    [
        'frage'   => 'Muss ich meinen Namen auf der Teilnehmerkarte zeigen?',
        'antwort' => '<p>Nur wenn Du willst. Im Formular gibt es dafür ein eigenes Häkchen, und
                      wir schalten jeden Eintrag von Hand frei. Ohne Deine Zustimmung erscheint
                      auf der <a href="/teilnehmer.html">Teilnehmerseite</a> weder Dein Name noch
                      Deine Adresse. Zurückziehen kannst Du das jederzeit mit einer kurzen Mail.</p>',
    ],
    [
        'frage'   => 'Kann ich mitmachen, wenn ich keine Pizzeria bin?',
        'antwort' => '<p>Ja. Der Karton eignet sich für alles, was flach und heiß aus dem Ofen kommt –
                      Flammkuchen, Pide, Focaccia, belegte Fladen. Trag im Formular einfach ein,
                      was Du machst. Wir melden uns, wenn das Format zu Deinem Betrieb passt.</p>',
    ],
];

$meta['titel']        = 'Kostenlose Pizzakartons für Freiburg | Pizza Support';
$meta['beschreibung'] = 'Pizza Support liefert der Freiburger Gastronomie kostenlose Pizzakartons, finanziert durch Werbung auf Pizzakartons. Unverbindlich eintragen und beim Startschuss dabei sein.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    [
        '@type'       => 'WebSite',
        '@id'         => url('/#website'),
        'url'         => url('/'),
        'name'        => 'Pizza Support',
        'inLanguage'  => 'de-DE',
        'publisher'   => ['@id' => url('/#organisation')],
    ],
];

$formate = config('karton_formate');
$mengen  = config('mengen');
$fehler  = flash_get('gastro_fehler', []);
$altw    = flash_get('gastro_alt', []);
$erfolg  = flash_get('gastro_ok');
$f       = fortschritt();
?>

<section class="hero">
  <div class="wrap hero-innen">
    <div class="hero-text">
      <p class="kicker">Unterstützung für Gastronomie und Gäste</p>
      <h1>Kostenlose Pizzakartons für Freiburg</h1>
      <p class="hero-lead">
        Seit die Verpackungssteuer gilt, zahlt man bei jeder Bestellung drauf.
        Wir ändern was: Unternehmen aus der Region buchen Werbeflächen auf
        Pizzakartons und die Gastronomie bekommt sie umsonst. Lebensmittelecht,
        hygienisch zugelassen und eine Aktion, über die man spricht.<br>
        <a href="#bestellen">Hier bekommst Du Pizzakartons kostenlos.</a>.
      </p>
      <div class="hero-aktionen">
        <a class="btn btn-primaer btn-gross" href="#bestellen">Kartons bestellen</a>
        <a class="btn btn-sekundaer btn-gross" href="/werbepartner.html">Werbefläche sichern</a>
      </div>
      <p class="hero-fuss">
        Bestelle jetzt und sei dabei, wenn wir versenden. Unverbindlich bis zum Startschuss.
      </p>
    </div>

    <aside class="hero-karton" aria-label="Werbeflächen auf dem Karton">
      <figure class="hero-bild">
        <picture>
          <source srcset="<?= e(asset('/assets/img/pizzakarton-mit-werbung.webp')) ?>" type="image/webp">
          <img src="<?= e(asset('/assets/img/pizzakarton-mit-werbung.png')) ?>"
               alt="Pizzakarton mit Werbefläche auf dem Deckel"
               width="472" height="529"
               fetchpriority="high" decoding="async">
        </picture>
      </figure>
    </aside>
  </div>
</section>

<?php include APP_ROOT . '/app/views/partials/fortschritt.php'; ?>

<section class="band band-hell" id="story" aria-labelledby="story-titel">
  <div class="wrap schmal">
    <h2 id="story-titel">Warum sind die Pizzakartons kostenlos?</h2>
    <p>
      Kostenlose Pizzakartons bringen Pizzerien und Restaurants finanzielle Unterstützung.
      Die 50ct, die man in Freiburg für die Verpackungssteuer abgeben muss, werden durch
      den Wegfall des Einkaufspreises des Pizzakartons reduziert.
      Als Kreativagentur lieben wir die Gastro, arbeiten mit Eventagenturen und wissen,
      wie die finanzielle Belastung in der Gastro aussieht. Und dann kam in Freiburg die
      Verpackungssteuer dazu. Bei ein paar hundert Kartons in der Woche summiert sich
      das schnell zu einer Position, die vorher nicht im Plan stand und die für Gast und
      Gastro erstmal Mehrkosten bedeutet.
    </p>
    <p>
      Wie mit unseren Werbemotiven auf Pizzakartons halten wir es auch hier: Keine Politik.
      Wir haben ein Problem gesehen und eine Lösung gefunden. Der Deckel des Pizzakartons ist
      eine super Werbefläche, die einfach ungenutzt ist. Sympathische Werbefläche auf Pizzakartons,
      mit der regionale Unternehmen die Gastronomie vor Ort unterstützen können.
      Eine Win-Win Situation für alle. Die Werbung bezahlt den Karton und macht eine
      Einsparung für Gast und Gastro möglich.
    </p>
    <p>
      Initiiert wird die Aktion "PizzaSupport" von der <?= e(config('firma.name')) ?> hier in Freiburg
      in Kooperation mit der Eventagentur <?= e(config('partner_gastro')) ?>. Von der Gastro für die Gastro.
      <a href="/ueber-uns.html">Mehr dazu hier.</a>
    </p>
  </div>
</section>

<section class="band" id="startschuss" aria-labelledby="startschuss-titel">
  <div class="wrap">
    <h2 id="startschuss-titel">Wie bekomme ich kostenlose Pizzakartons?</h2>
    <p class="band-lead">
      Gedruckt wird erst, wenn beide Seiten stehen. Sobald sich genug Pizzerien und Restaurants
      mit Bedarf an Pizzakartons eingetragen haben und wir entsprechende Unternehmen als
      Werbepartner gewonnen haben, geht es los. Wir glauben fest dran, aber Du kennst ja Einsteins
      Spruch mit dem Universum.
    </p>

    <ol class="schritte">
      <li>
        <span class="schritt-nr" aria-hidden="true">1</span>
        <h3>Du trägst Dich ein</h3>
        <p>Gastronomie, Adresse, Wunschmenge. Dauert zwei Minuten und kostet nichts.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">2</span>
        <h3>Unternehmen buchen Flächen</h3>
        <p>Handwerk, Dienstleister, Handel aus der Region – und Privatleute auf der Fun Area.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">3</span>
        <h3>Die Schwelle fällt</h3>
        <p>Genug Kartons abgenommen, genug Budget gebucht. Wir geben die Produktion frei.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">4</span>
        <h3>Die Werbepartner zahlen an</h3>
        <p>Auftragsbestätigung und Teilrechnung über <?= (int) config('startschuss.anzahlung') ?> % gehen raus. An Dich nichts.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">5</span>
        <h3>Wir liefern</h3>
        <p>Rund <?= e(config('startschuss.lieferwochen')) ?> Wochen nach Produktionsstart stehen die Kartons bei Dir.</p>
      </li>
    </ol>

    <p class="band-nachsatz">
      Keine festen Termine, keine Versprechen ins Blaue. Sobald genug zusammenkommt,
      geben wir die Produktion frei – die Kartons sind dann rund
      <?= e(config('startschuss.lieferwochen')) ?> Wochen später da. Den aktuellen Stand
      siehst Du jederzeit auf der <a href="/teilnehmer.html">Teilnehmerseite</a>.
    </p>
  </div>
</section>

<?php include APP_ROOT . '/app/views/partials/ersparnisrechner.php'; ?>

<?php include APP_ROOT . '/app/views/partials/formular-gastro.php'; ?>

<section class="band band-hell" id="vorteile" aria-labelledby="vorteile-titel">
  <div class="wrap">
    <h2 id="vorteile-titel">Was hast Du davon?</h2>
    <div class="karten">
      <article class="karte">
        <h3>Der Karton kostet nichts</h3>
        <p>Kein Einkaufspreis, keine Liefergebühr. Was Du bisher für Verpackung ausgegeben hast, bleibt im Betrieb.</p>
      </article>
      <article class="karte">
        <h3>Vierfarbdruck statt Braunware</h3>
        <p>32 × 32 × 3 cm, sauber bedruckt. Sieht auf dem Beifahrersitz besser aus als der Standardkarton.</p>
      </article>
      <article class="karte">
        <h3>Du stehst auf der Karte</h3>
        <p>Wenn Du magst: Eintrag auf der <a href="/teilnehmer.html">Teilnehmerkarte</a>, mit Link auf Deine Seite.</p>
      </article>
      <article class="karte">
        <h3>Nachbarn werben für Nachbarn</h3>
        <p>Auf dem Deckel steht kein Konzern, sondern die Gastronomie zwei Straßen weiter. Das kommt an.</p>
      </article>
      <article class="karte">
        <h3>Keine Bindung</h3>
        <p>Kein Abo, keine Laufzeit. Läuft es gut, machen wir die nächste Auflage. Läuft es nicht, war es das.</p>
      </article>
      <article class="karte">
        <h3>Eine Antwort auf die Steuer</h3>
        <p>Die Abgabe bleibt, der Einkaufspreis fällt weg. <a href="/verpackungssteuer-freiburg.html">Was das rechnerisch bringt.</a></p>
      </article>
      <article class="karte">
        <h3>Du musst nichts stapeln</h3>
        <p>Dreitausend Kartons passen in keine Pizzeria. Wir lagern sie für Dich und liefern jeden Monat nach, was Du brauchst.</p>
      </article>
    </div>
  </div>
</section>

<section class="band band-dunkel" id="mission" aria-labelledby="mission-titel">
  <div class="wrap schmal">
    <h2 id="mission-titel">Wofür wir das aufziehen</h2>
    <p>
      Freiburg lebt von Läden, die jemandem gehören, den man kennt. Von der Pizzeria,
      in der der Chef noch selbst am Ofen steht, und von der Gastronomie, die seit dreißig
      Jahren dieselbe Ecke hält. Diese Vielfalt verschwindet nicht mit einem Knall,
      sondern leise, ein Laden nach dem anderen.
    </p>
    <p>
      Wir haben keinen Rettungsplan für die Gastronomie. Wir haben einen Karton, ein
      bisschen Druckerei-Erfahrung und die Überzeugung, dass eine Stadt sich selbst
      helfen kann, wenn jemand die Fäden zusammenhält. Wenn das hier funktioniert,
      lässt sich dasselbe Prinzip auf anderes übertragen – auf Kultur, auf Vereine,
      auf das, was eine Nachbarschaft sonst noch zusammenhält.
    </p>
    <p class="mission-cta">
      <a class="btn btn-hell" href="/werbepartner.html">Werbefläche buchen und mitfinanzieren</a>
    </p>
  </div>
</section>

<section class="band" id="flaechen" aria-labelledby="flaechen-titel">
  <div class="wrap">
    <h2 id="flaechen-titel">Was kostet eine Fläche auf dem Karton?</h2>
    <p class="band-lead">
      Für Unternehmen gibt es feste Pakete, keine Rechnerei nach Quadratzentimetern.
      Für alle anderen die Fun Area auf der Unterseite. Unser Lagerbestand: 42.000 Kartons,
      bereit für die erste Auflage.
    </p>
    <div class="preis-gitter">
      <?php foreach (config('werbeformate') as $wf): ?>
        <article class="preis-karte<?= $wf['id'] === 'fun-area' ? ' preis-karte-fun' : '' ?>">
          <h3><?= e($wf['label']) ?></h3>
          <p class="preis-masse"><?= e($wf['masse']) ?></p>
          <p class="preis-zahl">
            <?= $wf['id'] === 'fun-area' ? 'ab ' : '' ?><?= e(preis($wf['preis'])) ?>
            <small><?= $wf['brutto'] ? 'inkl. ' . (int) config('mwst_prozent') . ' % MwSt.' : 'netto' ?></small>
          </p>
          <p class="preis-text"><?= e($wf['text']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="band-nachsatz">
      Gutscheinmotive bekommen <?= (int) config('coupon_rabatt_prozent') ?> % Nachlass auf den Listenpreis.
      Alle Details, Bedingungen und das Buchungsformular stehen auf der Seite
      <a href="/werbepartner.html">für Werbepartner</a>.
    </p>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Was Gastronomen uns am häufigsten fragen') ?>
</div>

<section class="band band-cta" aria-labelledby="cta-titel">
  <div class="wrap schmal zentriert">
    <h2 id="cta-titel">Willst Du mit Deinem Betrieb dabei sein?</h2>
    <p>
      Dann trag Dich ein. Es kostet nichts, bindet Dich an nichts und dauert
      keine drei Minuten. Bestelle jetzt und sei dabei, wenn wir versenden.
    </p>
    <a class="btn btn-primaer btn-gross" href="#bestellen">Jetzt bestellen</a>
  </div>
</section>
