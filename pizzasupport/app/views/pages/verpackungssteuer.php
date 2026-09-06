<?php
/** Ratgeberseite zur Verpackungssteuer. Konstruktiv, nie gegen die Stadt. */
declare(strict_types=1);

$s        = config('steuer');
$proKarton = $s['karton_cent'];

$faq = [
    [
        'frage'   => 'Was ist die Verpackungssteuer in Freiburg?',
        'antwort' => '<p>Eine kommunale Steuer auf Einwegverpackungen, die für Speisen und Getränke zum
                      sofortigen Verzehr ausgegeben werden. Sie wird nicht beim Gast erhoben, sondern
                      bei der Gastronomie, die die Verpackung ausgibt. Das Vorbild ist die Tübinger Satzung,
                      deren Rechtmäßigkeit das Bundesverfassungsgericht Ende 2024 entschieden und im
                      Januar 2025 veröffentlicht hat. Maßgeblich ist immer die aktuelle Satzung der
                      Stadt Freiburg.</p>',
    ],
    [
        'frage'   => 'Wer muss die Steuer zahlen?',
        'antwort' => '<p>Die Gastronomie, die die Verpackung an den Endkunden ausgibt – also Pizzerien,
                      Imbisse, Cafés, Bäckereien, Foodtrucks. Nicht der Hersteller, nicht der Großhandel
                      und nicht der Gast. Ausgenommen sind reine Lieferdienste ohne eigenen Verkauf vor
                      Ort: Der Verpackungsmüll fällt bei ihnen im Privathaushalt an, nicht im
                      öffentlichen Raum. Besteuert wird der Verzehr außer Haus im Sinne von Take-away.
                      Für die betroffene Gastronomie bedeutet das: eine zusätzliche Position
                      in der Kalkulation, die vorher nicht da war.</p>',
    ],
    [
        'frage'   => 'Wie viel kostet ein Pizzakarton mit Steuer?',
        'antwort' => '<p>Zum Einkaufspreis des Kartons kommt der Steuerbetrag je ausgegebener
                      Einwegverpackung. Bei ' . number_format($proKarton, 0, ',', '.') . ' Cent je
                      Verpackung und 400 Kartons in der Woche sind das rund '
                      . preis((int) round($proKarton * 400 * 4.33)) . ' im Monat, allein an Steuer.
                      Der Einkauf des Kartons kommt oben drauf. Genau da setzen wir an:
                      <a href="/#bestellen">Unsere Kartons kosten den Betrieb nichts.</a></p>',
    ],
    [
        'frage'   => 'Kann ich die Steuer an meine Gäste weitergeben?',
        'antwort' => '<p>Rechtlich steht Dir die Preisgestaltung frei. Praktisch ist es eine
                      Abwägung: Ein sichtbarer Aufschlag auf der Rechnung sorgt für Diskussionen an
                      der Theke, ein eingepreister Aufschlag drückt die Marge. Die meisten Gastronomien,
                      mit denen wir sprechen, machen ein bisschen von beidem. Wer den Einkaufspreis
                      der Verpackung auf null bekommt, hat in dieser Rechnung mehr Luft.</p>',
    ],
    [
        'frage'   => 'Fällt die Steuer bei Mehrweg weg?',
        'antwort' => '<p>Ja. Die Steuer knüpft an Einwegverpackungen an – Mehrwegsysteme sind nicht
                      betroffen. Für viele Speisen funktioniert Mehrweg gut. Für eine Pizza, die
                      heiß und flach transportiert werden muss, ist der Karton bislang schwer zu
                      ersetzen. Deshalb konzentrieren wir uns auf genau dieses Produkt.</p>',
    ],
    [
        'frage'   => 'Wie hilft Pizza Support konkret gegen die Mehrkosten?',
        'antwort' => '<p>Wir nehmen den Einkaufspreis aus der Rechnung. Die Steuer bleibt, weil sie
                      am Ausgeben der Verpackung hängt und nicht am Preis. Aber der Karton selbst
                      kostet den Betrieb nichts mehr, weil Werbeflächen darauf ihn bezahlen. Bei
                      400 Kartons pro Woche ist das eine spürbare Entlastung – ohne Vertrag und
                      ohne Mindestlaufzeit. <a href="/#bestellen">So trägst Du Dich ein.</a></p>',
    ],
    [
        'frage'   => 'Ich liefere überwiegend aus. Bringt mir das etwas?',
        'antwort' => '<p>Ja, nur anders als der Betrieb an der Theke. Die Verpackungssteuer fällt bei
                      Lieferung in der Regel nicht an, da bist Du ohnehin außen vor. Der Einkaufspreis
                      für den Karton fällt bei Dir aber genauso an wie überall sonst, und der ist bei
                      Lieferdiensten wegen der Mengen oft die größere Zahl. Genau den nehmen wir raus.</p>',
    ],
];

$meta['titel']        = 'Verpackungssteuer Freiburg: Was sie für die Gastronomie bedeutet | Pizza Support';
$meta['beschreibung'] = 'Verpackungssteuer Freiburg verständlich erklärt: wer sie zahlt, wie hoch sie ausfällt, eine Beispielrechnung für Pizzerien und was Gastronomien jetzt tun können.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    jsonld_breadcrumb(['Start' => '/', 'Verpackungssteuer Freiburg' => '/verpackungssteuer-freiburg.html']),
    [
        '@type'         => 'Article',
        'headline'      => 'Verpackungssteuer Freiburg: Was sie für die Gastronomie bedeutet',
        'description'   => 'Ratgeber zur kommunalen Verpackungssteuer in Freiburg mit Beispielrechnung für Gastronomien.',
        'inLanguage'    => 'de-DE',
        'author'        => ['@id' => url('/#organisation')],
        'publisher'     => ['@id' => url('/#organisation')],
        'mainEntityOfPage' => url('/verpackungssteuer-freiburg.html'),
        'about'         => ['@type' => 'Thing', 'name' => 'Kommunale Verpackungssteuer'],
    ],
];

/**
 * Beispielrechnung nach Vertriebsweg statt nach Betriebsgroesse: so wird
 * sichtbar, dass die Steuer nur beim Verzehr an Ort und Theke in voller
 * Hoehe greift, waehrend der Einkaufspreis des Kartons immer anfaellt -
 * unabhaengig davon, ob der Gast abholt oder beliefert wird.
 */
$einkaufCent = 45;   // realistischer Einkaufspreis eines bedruckten 32er-Kartons
$beispiele = [
    [
        'betrieb' => 'Abholung und Theke', 'kartons' => 300, 'steuerpflichtig' => 'alle',
        'steuer_cent' => 64950, 'einkauf_cent' => 58455, 'zusammen_cent' => 123405,
        'mitps_cent' => 64950, 'praefix' => '',
    ],
    [
        'betrieb' => 'gemischt', 'kartons' => 400, 'steuerpflichtig' => 'etwa die Hälfte',
        'steuer_cent' => 43300, 'einkauf_cent' => 77940, 'zusammen_cent' => 121240,
        'mitps_cent' => 43300, 'praefix' => '',
    ],
    [
        'betrieb' => 'überwiegend Lieferung', 'kartons' => 800, 'steuerpflichtig' => 'wenige',
        'steuer_cent' => null, 'einkauf_cent' => 155880, 'zusammen_cent' => 155880,
        'mitps_cent' => 0, 'praefix' => 'ab ',
    ],
];
?>

<section class="seiten-hero">
  <div class="wrap schmal">
    <p class="kicker">Ratgeber für Gastronomen</p>
    <h1>Verpackungssteuer Freiburg: Was bedeutet sie für die Gastronomie?</h1>
    <p class="hero-lead">
      Die Verpackungssteuer in Freiburg trifft nicht den Gast, sondern die Gastronomie, die die
      Verpackung ausgibt. Das ist der ganze Unterschied, und er erklärt, warum sich für viele
      Küchen die Kalkulation verschoben hat. Auf dieser Seite steht, was besteuert wird, wie
      viel bei welchem Volumen zusammenkommt und welche Stellschrauben Gastronomien bleiben.
      Wer die Verpackungssteuer in Freiburg abfedern will, findet am Ende einen Weg, der beim
      Einkaufspreis ansetzt statt beim Preisschild für den Gast.
    </p>
  </div>
</section>

<section class="band" aria-labelledby="was-titel">
  <div class="wrap schmal">
    <h2 id="was-titel">Worum geht es genau?</h2>
    <p>
      Freiburg erhebt eine kommunale Steuer auf Einwegverpackungen für Speisen und Getränke,
      die zum sofortigen Verzehr abgegeben werden. Rechtlich ist das eine örtliche
      Verbrauchsteuer. Vorbild ist die Tübinger Satzung, die das Bundesverfassungsgericht Ende
      2024 entschieden und im Januar 2025 veröffentlicht hat – seitdem haben mehrere Städte
      nachgezogen.
    </p>
    <p>
      Das Ziel dahinter ist nachvollziehbar: weniger Einwegmüll in der Innenstadt, weniger
      überfüllte Papierkörbe am Wochenende. Darüber streiten wir nicht. Uns geht es um die
      betriebswirtschaftliche Seite, über die in der Debatte weniger gesprochen wird.
    </p>

    <div class="info-box">
      <h3>Was besteuert wird</h3>
      <ul class="liste-check">
        <li><strong><?= number_format($s['karton_cent'], 0, ',', '.') ?> Cent</strong> je Einwegverpackung – dazu zählt der Pizzakarton</li>
        <li><strong><?= number_format($s['geschirr_cent'], 0, ',', '.') ?> Cent</strong> je Einweggeschirr</li>
        <li><strong><?= number_format($s['besteck_cent'], 0, ',', '.') ?> Cent</strong> je Einwegbesteck-Set</li>
      </ul>
      <p class="info-box-fuss">
        Alle Angaben ohne Gewähr. Verbindlich ist immer die aktuelle Fassung der
        Verpackungssteuersatzung – bitte lies sie auf den offiziellen Seiten der Stadt
        Freiburg nach.
      </p>
    </div>
  </div>
</section>

<section class="band band-hell" aria-labelledby="rechnung-titel">
  <div class="wrap">
    <h2 id="rechnung-titel">Was kostet das im Monat?</h2>
    <p class="band-lead">
      Gerechnet mit <?= number_format($s['karton_cent'], 0, ',', '.') ?> Cent Steuer je
      steuerpflichtiger Verpackung und einem Einkaufspreis von <?= e(preis($einkaufCent)) ?> für
      einen bedruckten 32er-Karton. Ein Monat entspricht 4,33 Wochen. Wie viel davon tatsächlich
      steuerpflichtig ist, hängt vom Vertriebsweg ab – Abholung und Theke immer, Lieferung nach
      Hause in der Regel nicht.
    </p>

    <div class="tabelle-wrap">
      <table class="preistabelle">
        <caption class="sr-only">Beispielrechnung Verpackungssteuer und Kartoneinkauf je Monat nach Vertriebsweg</caption>
        <thead>
          <tr>
            <th scope="col">Betriebstyp</th>
            <th scope="col">Kartons pro Woche</th>
            <th scope="col">davon steuerpflichtig</th>
            <th scope="col">Steuer im Monat</th>
            <th scope="col">Kartoneinkauf im Monat</th>
            <th scope="col">Zusammen</th>
            <th scope="col">Mit Pizza Support</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($beispiele as $b): ?>
            <tr>
              <th scope="row"><?= e($b['betrieb']) ?></th>
              <td><?= zahl($b['kartons']) ?></td>
              <td><?= e($b['steuerpflichtig']) ?></td>
              <td><?= $b['steuer_cent'] === null ? 'gering' : e(preis($b['steuer_cent'])) ?></td>
              <td><?= e(preis($b['einkauf_cent'])) ?></td>
              <td><strong><?= e($b['praefix']) ?><?= e(preis($b['zusammen_cent'])) ?></strong></td>
              <td class="tabelle-gut"><?= e($b['praefix']) ?><?= e(preis($b['mitps_cent'])) ?><span class="tabelle-sub">Einkauf entfällt</span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <p class="band-nachsatz">
      Bei Lieferung nach Hause fällt die Verpackungssteuer nach den Auslegungshinweisen der
      Stadt in der Regel nicht an. Der Einkaufspreis fällt trotzdem an – und genau den nehmen
      wir raus. Was für Deinen Betrieb gilt, klärst Du am besten mit Deiner Steuerberatung.
    </p>
  </div>
</section>

<section class="band" aria-labelledby="tun-titel">
  <div class="wrap">
    <h2 id="tun-titel">Was können Gastronomien jetzt tun?</h2>
    <div class="karten">
      <article class="karte">
        <h3>Sauber erfassen</h3>
        <p>Zähl mit, wie viele Einwegverpackungen wirklich rausgehen. Ohne belastbare Zahl ist jede Anmeldung und jede Kalkulation Schätzung.</p>
      </article>
      <article class="karte">
        <h3>Mehrweg prüfen, wo es passt</h3>
        <p>Bei Schalen und Bechern funktioniert Mehrweg oft gut. Beim Pizzakarton bleibt es schwierig – der muss flach, stabil und atmungsaktiv sein.</p>
      </article>
      <article class="karte">
        <h3>Einkaufspreis angreifen</h3>
        <p>An der Steuer lässt sich nicht drehen, am Einkauf schon. Genau dafür gibt es uns: <a href="/#bestellen">kostenlose Kartons, finanziert durch Werbung</a>.</p>
      </article>
      <article class="karte">
        <h3>Nicht still schlucken</h3>
        <p>Rede mit Kollegen, mit dem Verband, mit der Stadt. Wer die Zahlen kennt, kann bei Ausnahmen und Übergangsregeln mitreden.</p>
      </article>
      <article class="karte">
        <h3>Frist im Kalender eintragen</h3>
        <p>Die Steueranmeldung muss bis zum 30. Januar für das abgelaufene Jahr bei der Stadt sein, auf amtlichem Vordruck. Eine Erinnerung kommt nicht. Belege über Warenbezug und Verkauf musst Du vier Jahre aufbewahren.</p>
      </article>
    </div>
  </div>
</section>

<section class="band band-hell" aria-labelledby="quellen-titel">
  <div class="wrap schmal">
    <h2 id="quellen-titel">Wo steht das offiziell?</h2>
    <p>
      Wir geben hier unsere Einordnung wieder, keine Rechtsberatung. Verbindlich sind
      ausschließlich die Veröffentlichungen der Stadt:
    </p>
    <ul class="liste-links">
      <li><a href="https://www.freiburg.de/pb/2485964.html?QUERYSTRING=verpackungssteuer" rel="nofollow noopener" target="_blank">Stadt Freiburg im Breisgau – Verpackungssteuer, amtliche Auskunft</a></li>
      <li><a href="https://www.bundesverfassungsgericht.de/SharedDocs/Entscheidungen/DE/2024/11/rs20241127_1bvr172623.html" rel="nofollow noopener" target="_blank">Bundesverfassungsgericht – Entscheidung zur kommunalen Verpackungssteuer</a></li>
      <li><a href="https://www.dehogabw.de" rel="nofollow noopener" target="_blank">DEHOGA Baden-Württemberg – Hinweise für Gastronomien</a></li>
      <li><a href="https://de.wikipedia.org/wiki/Verpackungssteuer_(T%C3%BCbingen)" rel="nofollow noopener" target="_blank">Wikipedia – Verpackungssteuer (Tübingen)</a></li>
    </ul>
    <p class="klein">
      Bei steuerlichen Fragen zur eigenen Gastronomie hilft die Steuerberatung weiter.
      Wir können sagen, was ein Karton kostet – nicht, wie Deine Anmeldung auszusehen hat.
    </p>
  </div>
</section>

<section class="band" aria-labelledby="ausnahmen-titel">
  <div class="wrap schmal">
    <h2 id="ausnahmen-titel">Worauf fällt die Steuer nicht an?</h2>
    <p>
      Auf Mehrweg, auf Lieferung nach Hause, auf mitgebrachte Gefäße, auf eingepackte Reste aus
      dem Restaurant, auf Servietten, auf essbare Waffeln und Becher, auf Kleinstverpackungen bis
      25 Gramm und auf besonders kleine Besteckteile bis 10 Zentimeter. Wer im Jahr an höchstens
      zehn Tagen auf Festen und Märkten verkauft, ist ebenfalls befreit. Verbindlich ist die
      Satzung der Stadt – bitte lies dort nach.
    </p>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Häufige Fragen zur Verpackungssteuer') ?>
</div>

<section class="band band-cta" aria-labelledby="steuer-cta">
  <div class="wrap schmal zentriert">
    <h2 id="steuer-cta">Den Einkaufspreis auf null bringen</h2>
    <p>
      Die Steuer bleibt. Der Karton muss trotzdem nicht Dein Geld kosten.
      Bestelle jetzt und sei dabei, wenn wir versenden.
    </p>
    <a class="btn btn-primaer btn-gross" href="/#bestellen">Jetzt bestellen</a>
  </div>
</section>
