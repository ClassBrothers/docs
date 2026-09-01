<?php
/** Startseite: Hero, Story, Startschuss-Prinzip, Bestellung, Formate, FAQ. */
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Was kosten die Pizzakartons für meinen Betrieb?',
        'antwort' => '<p>Nichts. Kein Cent, keine versteckte Gebühr, keine Mindestabnahme gegen Rechnung.
                      Die Kartons sind vollständig durch die Werbeflächen finanziert, die Unternehmen und
                      Privatleute auf Deckel, Seite und Boden buchen. Du bezahlst weder Druck noch
                      Lieferung. Was Du einbringst, ist Dein Bedarf – je mehr Betriebe mitmachen,
                      desto attraktiver werden die Flächen für die
                      <a href="/werbepartner.html">Werbepartner</a>.</p>',
    ],
    [
        'frage'   => 'Wie läuft das Startschuss-Prinzip genau ab?',
        'antwort' => '<p>Erst sammeln, dann drucken. Betriebe tragen ihren Bedarf ein, Unternehmen buchen
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
                      drücken. Was die Steuer für einen Betrieb konkret bedeutet, haben wir auf der
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
$meta['beschreibung'] = 'Pizza Support liefert Freiburger Gastronomie kostenlose Pizzakartons, finanziert durch Werbung aus der Nachbarschaft. Unverbindlich eintragen und beim Startschuss dabei sein.';
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
      <p class="kicker">Für die Gastronomie in Freiburg und drumherum</p>
      <h1>Kostenlose Pizzakartons für <span class="akzent">Freiburg</span></h1>
      <p class="hero-lead">
        Kostenlose Pizzakartons für Freiburg klingen nach Haken – hier ist keiner.
        Wir drucken 32er-Kartons in Vierfarbdruck, Unternehmen und Privatleute aus der
        Stadt buchen die Flächen darauf, und die Rechnung ist damit bezahlt. Für Deinen
        Betrieb bleibt: null Einkaufspreis, kein Vertrag, keine Mindestlaufzeit. Wer in
        Freiburg Pizza aus dem Ofen holt, bekommt die Kartons umsonst –
        <a href="#bestellen">hier trägst Du Deinen Bedarf ein</a>.
      </p>
      <div class="hero-aktionen">
        <a class="btn btn-primaer btn-gross" href="#bestellen">Jetzt bestellen</a>
        <a class="btn btn-sekundaer btn-gross" href="/werbepartner.html">Ich will eine Werbefläche</a>
      </div>
      <p class="hero-fuss">
        Bestelle jetzt und sei dabei, wenn wir versenden. Unverbindlich bis zum Startschuss.
      </p>
    </div>

    <aside class="hero-karton" aria-label="Werbeflächen auf dem Karton">
      <div class="karton-skizze">
        <svg class="karton-illustration" viewBox="0 0 200 160" width="200" height="160" aria-hidden="true" focusable="false">
          <defs>
            <linearGradient id="karton-deckelfarbe" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#f7e5c4"/><stop offset="100%" stop-color="#efd4a4"/>
            </linearGradient>
          </defs>
          <ellipse cx="100" cy="152" rx="74" ry="7" fill="rgba(60,35,20,.08)"/>
          <rect x="18" y="80" width="164" height="64" rx="6" fill="#ebce9b"/>
          <rect x="18" y="122" width="164" height="10" rx="3" fill="#d3b071"/>
          <path d="M18,80 L182,80 L178,12 L22,12 Z" fill="url(#karton-deckelfarbe)"/>
          <ellipse cx="100" cy="108" rx="58" ry="30" fill="#e0a33c"/>
          <ellipse cx="100" cy="108" rx="46" ry="23" fill="#f2c568"/>
          <circle cx="82" cy="98" r="6" fill="#c1440e"/>
          <circle cx="116" cy="102" r="6" fill="#c1440e"/>
          <circle cx="100" cy="118" r="6" fill="#c1440e"/>
          <g stroke="rgba(255,255,255,.85)" stroke-width="2.4" stroke-linecap="round" fill="none">
            <path class="karton-dampf" d="M78,72 q0,-6 5,-11 t0,-11"/>
            <path class="karton-dampf" d="M100,68 q0,-6 5,-11 t0,-11"/>
            <path class="karton-dampf" d="M122,72 q0,-6 5,-11 t0,-11"/>
          </g>
        </svg>
        <div class="karton-deckel">
          <span class="flaeche flaeche-gross">Deckel groß<small>88 × 136 mm</small></span>
          <span class="flaeche flaeche-mittel">Deckel mittel<small>88 × 88 mm</small></span>
          <span class="flaeche flaeche-klein">Deckel klein<small>88 × 40 mm</small></span>
        </div>
        <div class="karton-seite">
          <span class="flaeche flaeche-seite">Seite · 93 × 23 mm</span>
        </div>
        <p class="karton-boden">Unten drunter: die <strong>Fun Area</strong> ab <?= e(preis(790)) ?> für alle, die keinen Betrieb, aber etwas zu sagen haben.</p>
      </div>
    </aside>
  </div>
</section>

<?php include APP_ROOT . '/app/views/partials/fortschritt.php'; ?>

<section class="band band-hell" id="story" aria-labelledby="story-titel">
  <div class="wrap schmal">
    <h2 id="story-titel">Warum verschenkt jemand Pizzakartons?</h2>
    <p>
      Weil wir seit Jahren mit Gastronomen zu tun haben und wissen, wie die Rechnung
      am Monatsende aussieht. Wareneinsatz rauf, Energie rauf, Personal schwer zu
      finden. Und dann kam in Freiburg die Verpackungssteuer dazu. Kein Weltuntergang,
      aber bei ein paar hundert Kartons in der Woche summiert sich das zu einer
      Position, die vorher nicht im Plan stand.
    </p>
    <p>
      Wir haben nicht vor, darüber zu streiten. Die Stadt hat ihre Gründe, und weniger
      Müll auf der Straße will hier niemand ernsthaft bekämpfen. Uns interessiert die
      andere Seite: Wenn der Karton sowieso Geld kostet, machen wir eben den Einkauf
      umsonst. Auf dem Deckel ist Platz, den ein Fliesenleger aus Haslach, eine
      Kanzlei in der Wiehre oder das Fitnessstudio in Zähringen gut gebrauchen kann.
      Diese Fläche bezahlt den Karton. Mehr ist der Trick nicht.
    </p>
    <p>
      Getragen wird das Ganze von der <?= e(config('firma.name')) ?> hier in Freiburg,
      gastronomisch beraten von der <?= e(config('partner_gastro')) ?> – Leuten, die
      selbst wissen, wie voll eine Küche am Freitagabend ist.
      <a href="/ueber-uns.html">Mehr über uns und was danach kommt.</a>
    </p>
  </div>
</section>

<section class="band" id="startschuss" aria-labelledby="startschuss-titel">
  <div class="wrap">
    <h2 id="startschuss-titel">Wie kommt der Karton in Deine Küche?</h2>
    <p class="band-lead">
      Wir nennen das Startschuss-Prinzip. Gedruckt wird erst, wenn beide Seiten stehen –
      genug Betriebe mit Bedarf und genug gebuchte Werbung. So zahlt niemand für eine
      Auflage, die halbleer bleibt.
    </p>

    <ol class="schritte">
      <li>
        <span class="schritt-nr" aria-hidden="true">1</span>
        <h3>Du trägst Dich ein</h3>
        <p>Betrieb, Adresse, Wunschmenge. Dauert zwei Minuten und kostet nichts.</p>
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
        <p>Auf dem Deckel steht kein Konzern, sondern der Betrieb zwei Straßen weiter. Das kommt an.</p>
      </article>
      <article class="karte">
        <h3>Keine Bindung</h3>
        <p>Kein Abo, keine Laufzeit. Läuft es gut, machen wir die nächste Auflage. Läuft es nicht, war es das.</p>
      </article>
      <article class="karte">
        <h3>Eine Antwort auf die Steuer</h3>
        <p>Die Abgabe bleibt, der Einkaufspreis fällt weg. <a href="/verpackungssteuer-freiburg.html">Was das rechnerisch bringt.</a></p>
      </article>
    </div>
  </div>
</section>

<section class="band band-dunkel" id="mission" aria-labelledby="mission-titel">
  <div class="wrap schmal">
    <h2 id="mission-titel">Wofür wir das aufziehen</h2>
    <p>
      Freiburg lebt von Läden, die jemandem gehören, den man kennt. Von der Pizzeria,
      in der der Chef noch selbst am Ofen steht, und vom Betrieb, der seit dreißig
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
      Für alle anderen die Fun Area auf der Unterseite.
    </p>
    <div class="preis-gitter">
      <?php foreach (config('werbeformate') as $wf): ?>
        <article class="preis-karte<?= $wf['id'] === 'fun-area' ? ' preis-karte-fun' : '' ?>">
          <h3><?= e($wf['label']) ?></h3>
          <p class="preis-masse"><?= e($wf['masse']) ?></p>
          <p class="preis-zahl">
            <?= e(preis($wf['preis'])) ?>
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
    <h2 id="cta-titel">Klingt das nach etwas für Deinen Betrieb?</h2>
    <p>
      Dann trag Dich ein. Es kostet nichts, bindet Dich an nichts und dauert
      keine drei Minuten. Bestelle jetzt und sei dabei, wenn wir versenden.
    </p>
    <a class="btn btn-primaer btn-gross" href="#bestellen">Jetzt bestellen</a>
  </div>
</section>
