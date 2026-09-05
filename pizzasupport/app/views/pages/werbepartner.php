<?php
/** Werbepartner-Landingpage: reine Verkaufs-/Infoseite, kein Formular. Ansprache: Du. */
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Wann wird die Werbefläche in Rechnung gestellt?',
        'antwort' => '<p>Erst nach dem Startschuss. Sobald genug Gastronomien und genug Buchungen zusammengekommen
                      sind, bekommst Du eine Auftragsbestätigung und eine Teilrechnung über '
                      . (int) config('startschuss.anzahlung') . ' % des Auftragswerts. Die Restsumme wird
                      mit Auslieferung fällig. Vor der Auftragsbestätigung entstehen Dir keine Kosten, und Du
                      kannst Deine Reservierung bis dahin formlos zurückziehen.</p>',
    ],
    [
        'frage'   => 'Wie viele Menschen sehen mein Motiv?',
        'antwort' => '<p>Ein Pizzakarton steht selten allein herum. Er kommt ins Haus, liegt eine
                      Mahlzeit lang auf dem Tisch und wird von mehreren Personen gesehen – anders als
                      eine Anzeige, die man wegklickt. Wir versprechen Dir keine Reichweitenzahlen,
                      die wir nicht belegen können. Was wir Dir liefern, ist die gedruckte Auflage
                      und, wenn Du einen QR-Code nutzt, die Zahl der tatsächlichen Scans.</p>',
    ],
    [
        'frage'   => 'Welche Motive lehnt ihr ab?',
        'antwort' => '<p>Essens-Lieferdienste, weil sie in direkter Konkurrenz zu den Gastronomien stehen,
                      die die Kartons ausgeben. Außerdem Politisches, Religiöses und alles, was ohne
                      fachliche Grundlage Meinung transportiert. Bei Motiven, die rechtlich heikel sind
                      oder dem Projekt schaden könnten, behalten wir uns die Ablehnung ebenfalls vor.
                      In dem Fall erstatten wir bereits geleistete Zahlungen vollständig.</p>',
    ],
    [
        'frage'   => 'Kann ich das Ziel meines QR-Codes später ändern?',
        'antwort' => '<p>Bis zur Druckfreigabe jederzeit. Danach ist die Ziel-Adresse fest. Technisch
                      führt jeder Code über pizzasupport.de, sodass wir die Weiterleitung im Notfall
                      abschalten können – etwa wenn eine Zielseite nicht mehr erreichbar ist.
                      Für den Inhalt der verlinkten Seite bist Du als Inserent verantwortlich.</p>',
    ],
    [
        'frage'   => 'Lohnt sich ein Gutschein auf dem Karton?',
        'antwort' => '<p>Nach unserer Erfahrung deutlich mehr als ein reines Logo. Ein Gutschein gibt
                      dem Karton einen Grund, aufgehoben zu werden, und macht Deinen Erfolg messbar –
                      Du siehst, wie viele Menschen tatsächlich zu Dir kommen. Deshalb geben wir auf
                      Gutscheinmotive ' . (int) config('coupon_rabatt_prozent') . ' % Nachlass auf den
                      Listenpreis.</p>',
    ],
    [
        'frage'   => 'Muss mein Unternehmen aus Freiburg kommen?',
        'antwort' => '<p>Nein, aber es sollte für Menschen in und um Freiburg relevant sein. Die Kartons
                      werden hier ausgegeben, also funktionieren Angebote am besten, die man von hier
                      aus erreichen kann. Bei überregionalen Marken schauen wir uns den Einzelfall an.</p>',
    ],
    [
        'frage'   => 'Welche Vorgaben an das Layout gelten?',
        'antwort' => '<p>Gastronomie muss Spaß machen und Werbung darf nicht stören. Damit unsere Aktion
                      noch viele Auflagen haben wird, erwarten wir bei den Motiven echte Kreativität und
                      lehnen langweilige Stockmotive auch gerne ab. Pizza Support braucht Anzeigenmotive,
                      die so cool sind, dass man sich jeden Pizzakarton am liebsten an den Kühlschrank
                      hängen würde! Wir erwarten, dass die Anzeigen in den Kontext „Pizza“ gebracht
                      werden – auch wenn das super ungelenk oder an den Haaren herbeigezogen ist. Es geht
                      nicht um Perfektion, es geht darum, gemeinsam wieder Spaß zu haben und sich nicht zu
                      ernst zu nehmen. Je witziger alle Motive sind, desto viraler wird die Aktion, was
                      wieder mehr Aufmerksamkeit für alle Werbepartner bringt. Unser Kreativ-Team
                      unterstützt Dich gerne bei der Findung und Umsetzung des perfekten Motivs.</p>',
    ],
];

$meta['titel']        = 'Werbung auf Pizzakartons in Freiburg buchen | Pizza Support';
$meta['beschreibung'] = 'Werbefläche auf Freiburger Pizzakartons: feste Einzelpreise ab 89 € netto, Gutscheinmotive mit 10 % Nachlass, Zahlung erst nach dem Startschuss. Jetzt Fläche sichern.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    jsonld_breadcrumb(['Start' => '/', 'Für Unternehmen' => '/werbepartner.html']),
];

$f = fortschritt_oeffentlich();
?>

<section class="hero hero-werbepartner">
  <div class="wrap hero-innen">
    <div class="hero-text">
      <p class="kicker">Für Unternehmen und Selbstständige</p>
      <h1>Werbung auf Pizzakartons in Freiburg buchen</h1>
      <p class="hero-lead">
        Werbung auf Pizzakartons in Freiburg erreicht Menschen beim Essen
        in sympathischem Kontext. Du buchst eine feste Fläche auf Deckel oder Seite,
        wir drucken sie in 4c, liefern sie aus und die Freiburger Gastronomie gibt
        die Kartons an tausende Gäste weiter.<br>
        Dein Budget bezahlt damit zwei Dinge gleichzeitig: die sympathische Wahrnehmung
        Deiner Marke und die Unterstützung von Gastro und Gästen.
      </p>
      <div class="hero-aktionen">
        <a class="btn btn-primaer btn-gross" href="/flaeche-buchen.html">Fläche jetzt buchen</a>
        <a class="btn btn-sekundaer btn-gross" href="#preise">Preise ansehen</a>
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

<?php $kauftMitCta = true; include APP_ROOT . '/app/views/partials/kauft.php'; ?>

<section class="band" id="preise" aria-labelledby="preise-titel">
  <div class="wrap">
    <h2 id="preise-titel">Welche Flächen gibt es und was kosten sie?</h2>
    <p class="band-lead">
      Die Auflage beträgt <?= zahl((int) config('auflage')) ?> Kartons. Jede Fläche ist ein fest
      benannter Platz auf dem Karton, den nur ein Unternehmen bekommt – wer zuerst bestätigt,
      sichert sich die Fläche. Du kannst mehrere Flächen gleichzeitig buchen.
    </p>

    <div class="tabelle-wrap">
      <table class="preistabelle">
        <caption class="sr-only">Werbeflächen auf dem Pizzakarton mit Maßen, Preisen und Verfügbarkeit</caption>
        <thead>
          <tr>
            <th scope="col">Fläche</th>
            <th scope="col">Position</th>
            <th scope="col">Maße</th>
            <th scope="col">Verfügbar</th>
            <th scope="col">Preis</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (flaechenkatalog_preisstufen() as $stufe): ?>
            <tr>
              <th scope="row"><?= e($stufe['bezeichnung']) ?><span class="tabelle-sub"><?= e(implode(', ', $stufe['codes'])) ?></span></th>
              <td><?= e(config('flaechenkatalog.gruppen')[$stufe['gruppe']] ?? $stufe['gruppe']) ?></td>
              <td><?= e($stufe['masse']) ?></td>
              <td><?= count($stufe['verfuegbare_codes']) ?></td>
              <td class="tabelle-preis">
                <?= e(preis($stufe['preis'])) ?>
                <small>zzgl. <?= (int) config('mwst_prozent') ?> % MwSt.</small>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="coupon-hinweis">
      <h3>Gewinne Kunden mit Coupons</h3>
      <p>
        Ein Logo wird gesehen. Ein Gutschein wird eingelöst. Wer auf seiner Fläche
        einen Coupon platziert, bekommt <strong><?= (int) config('coupon_rabatt_prozent') ?> % Nachlass</strong>
        auf den Listenpreis – und dazu etwas, das Werbung sonst selten liefert:
        eine Zahl am Monatsende, die zeigt, was angekommen ist.
      </p>
    </div>

    <p class="zwischen-cta">
      <a class="btn btn-primaer btn-gross" href="/flaeche-buchen.html">Fläche jetzt buchen</a>
    </p>
  </div>
</section>

<section class="band" aria-labelledby="ideen-titel">
  <div class="wrap">
    <h2 id="ideen-titel">Und was steht dann drauf?</h2>
    <p class="hero-lead">
      Die häufigste Frage, die uns Unternehmen stellen. Hier drei Antworten von Betrieben
      aus der Region.
    </p>

    <div class="ideen-raster ideen-teaser">

      <article class="idee">
        <p class="idee-claim">„Heißhunger auf nen neuen Job?“</p>
        <p>Ein Unternehmen sucht neue Mitarbeiter – mit QR-Code zu einem Formular mit drei Feldern.</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Jetzt ne Cola wär nice?“</p>
        <p>Ein Kino druckt einen Getränkegutschein, der im Kino gegen eine kostenlose Cola eingelöst werden kann.</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Iss auf und dann starte Deine Ausbildung!“</p>
        <p>Die Zielgruppe für Ausbildungsplätze sitzt abends direkt vor diesem Karton.</p>
      </article>

    </div>

    <p>
      <a href="/werbeideen.html" class="btn btn-sekundaer">Alle Ideen ansehen</a>
    </p>
  </div>
</section>

<section class="band band-hell" aria-labelledby="ablauf-titel">
  <div class="wrap">
    <h2 id="ablauf-titel">Wie buche ich eine Werbefläche?</h2>
    <ol class="schritte schritte-vier">
      <li>
        <span class="schritt-nr" aria-hidden="true">1</span>
        <h3>Fläche reservieren</h3>
        <p>Du wählst eine oder mehrere Flächen. Das ist eine Reservierung, ohne Zahlung und ohne
          Vertragsbindung – verbindlich wird sie erst mit der Auftragsbestätigung.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">2</span>
        <h3>Motiv einreichen</h3>
        <p>Sofort hochladen oder später nachreichen. Wir prüfen jedes Motiv vor dem Druck.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">3</span>
        <h3>Startschuss und Anzahlung</h3>
        <p>Auftragsbestätigung und Teilrechnung über <?= (int) config('startschuss.anzahlung') ?> %. Erst ab hier wird es verbindlich.</p>
      </li>
      <li>
        <span class="schritt-nr" aria-hidden="true">4</span>
        <h3>Druck und Auslieferung</h3>
        <p>Rund <?= e(config('startschuss.lieferwochen')) ?> Wochen später sind die Kartons in den Gastronomien.</p>
      </li>
    </ol>
    <p class="band-nachsatz">
      Feste Kalendertermine nennen wir bewusst nicht. Wir geben die Produktion frei, sobald
      beide Seiten stehen – den aktuellen Stand siehst Du auf der
      <a href="/teilnehmer.html">Teilnehmerseite</a>. Warum es dieses Projekt überhaupt gibt,
      steht auf der Seite zur <a href="/verpackungssteuer-freiburg.html">Freiburger Verpackungssteuer</a>.
    </p>
    <p class="zwischen-cta">
      <a class="btn btn-primaer btn-gross" href="/flaeche-buchen.html">Fläche jetzt buchen</a>
    </p>
  </div>
</section>

<?php $pn = config('partnernachlass'); ?>
<section class="band" aria-labelledby="vorteile-titel">
  <div class="wrap schmal">
    <h2 id="vorteile-titel">Auf einen Blick</h2>
    <ul class="liste-check">
      <li>Sympathischer Kontext: Dein Motiv liegt beim Essen auf dem Tisch, nicht zwischen Werbeanzeigen.</li>
      <li>Reservierung ohne Zahlung – verbindlich wird sie erst mit der Auftragsbestätigung.</li>
      <li><?= (int) config('coupon_rabatt_prozent') ?> % Nachlass auf den Listenpreis bei einem Gutscheinmotiv.</li>
      <li>
        <?= (int) $pn['prozent'] ?> % Nachlass auf Leistungen unserer eigenen Häuser
        (<a href="/ueber-uns.html#sonst-titel">Class Brothers, KI-Assistenz, SnackWorks, Badische
        Entertainment</a>), <?= (int) $pn['monate'] ?> Monate ab Deiner Buchung.
      </li>
    </ul>
  </div>
</section>

<?php include APP_ROOT . '/app/views/partials/fortschritt.php'; ?>

<section class="band band-cta band-bestellen" aria-labelledby="buchen-abschluss-titel">
  <div class="wrap schmal zentriert">
    <h2 id="buchen-abschluss-titel">Bereit für Deine Fläche?</h2>
    <p class="band-lead">
      Die Teilnehmer-Karte zeigt, wer schon dabei ist – als Beleg dafür, dass hier wirklich
      etwas entsteht. Die Buchung selbst dauert wenige Minuten.
    </p>
    <p>
      <a class="btn btn-primaer btn-gross" href="/flaeche-buchen.html">Fläche jetzt buchen</a>
    </p>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Was Werbepartner vorher wissen wollen') ?>
</div>
