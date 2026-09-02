<?php
/** Teilnehmerkarte, Liste nach PLZ und A-Z, Fortschritt, Newsletter. */
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Wann startet das Projekt wirklich?',
        'antwort' => '<p>Wenn genug zusammenkommt. Wir brauchen ' . zahl((int) config('startschuss.betriebe')) . '
                      teilnehmende Gastronomien und genug gebuchtes Werbevolumen, um eine Auflage sinnvoll
                      zu drucken. Beides sehen Sie oben in Echtzeit. Erst wenn beide Balken voll sind,
                      geben wir die Produktion frei – danach dauert es rund '
                      . e(config('startschuss.lieferwochen')) . ' Wochen bis zur Auslieferung.
                      Feste Kalendertermine nennen wir nicht, solange wir sie nicht halten können.</p>',
    ],
    [
        'frage'   => 'Warum stehen hier noch nicht alle Gastronomien?',
        'antwort' => '<p>Weil jeder Eintrag von Hand freigegeben wird und nur erscheint, wenn die Gastronomie
                      dem ausdrücklich zugestimmt hat. Viele machen mit, wollen aber nicht öffentlich
                      genannt werden – das respektieren wir. Die Zahlen oben zählen alle Teilnehmer,
                      die Karte zeigt nur die, die einverstanden sind.</p>',
    ],
    [
        'frage'   => 'Wie komme ich auf diese Karte?',
        'antwort' => '<p>Über das <a href="/#bestellen">Bestellformular</a> auf der Startseite. Dort gibt es
                      ein Häkchen für die Anzeige auf der Karte. Sie können es jederzeit widerrufen –
                      eine kurze Mail genügt, dann verschwindet der Eintrag.</p>',
    ],
    [
        'frage'   => 'Woher kommen die Kartendaten?',
        'antwort' => '<p>Von OpenStreetMap, einem offenen Kartenprojekt. Wir binden weder Google Maps noch
                      andere Dienste ein, die Nutzerprofile bilden. Die Kartenkacheln werden erst geladen,
                      wenn Sie dem zustimmen – bis dahin sehen Sie die Liste, die auch ohne Karte
                      vollständig funktioniert.</p>',
    ],
];

$meta['titel']        = 'Wer macht bei Pizza Support mit? Karte und Liste | Pizza Support';
$meta['beschreibung'] = 'Alle teilnehmenden Gastronomien und unterstützenden Unternehmen auf einer Karte, sortierbar nach PLZ und Alphabet. Aktueller Stand bis zum Startschuss.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    jsonld_breadcrumb(['Start' => '/', 'Wer ist dabei' => '/teilnehmer.html']),
];

$f           = fortschritt();
$teilnehmer  = teilnehmer_liste();
$nlFehler    = flash_get('newsletter_fehler', []);
$nlErfolg    = flash_get('newsletter_ok');
?>

<section class="seiten-hero">
  <div class="wrap schmal">
    <p class="kicker">Der aktuelle Stand</p>
    <h1>Wer ist bei Pizza Support dabei?</h1>
    <p class="hero-lead">
      Hier sehen Sie, wer bei Pizza Support dabei ist: die Gastronomien, die Kartons abnehmen,
      und die Unternehmen, die sie finanzieren. Die Liste wächst mit jeder Eintragung, und
      der Startschuss fällt, sobald beide Seiten stehen. Wer noch fehlt, kann jederzeit
      dazukommen – <a href="/#bestellen">als Gastronomie hier</a> oder
      <a href="/werbepartner.html">als Werbepartner hier</a>.
    </p>
  </div>
</section>

<?php include APP_ROOT . '/app/views/partials/fortschritt.php'; ?>

<section class="band" aria-labelledby="karte-titel">
  <div class="wrap">
    <h2 id="karte-titel">Karte und Liste</h2>

    <div class="teilnehmer-steuerung">
      <div class="such-feld">
        <label for="t-suche" class="sr-only">Teilnehmer suchen</label>
        <input type="search" id="t-suche" placeholder="Name, Ort oder PLZ suchen" data-t-suche autocomplete="off">
      </div>

      <div class="segment" role="group" aria-label="Sortierung">
        <button type="button" class="segment-knopf ist-aktiv" data-t-sortierung="plz">nach PLZ</button>
        <button type="button" class="segment-knopf" data-t-sortierung="az">A–Z</button>
      </div>

      <div class="segment" role="group" aria-label="Filter nach Art">
        <button type="button" class="segment-knopf ist-aktiv" data-t-filter="alle">Alle</button>
        <button type="button" class="segment-knopf" data-t-filter="gastro">Gastronomie</button>
        <button type="button" class="segment-knopf" data-t-filter="unternehmen">Unternehmen</button>
      </div>
    </div>

    <div class="teilnehmer-raster">
      <div class="karten-spalte">
        <?php /* Die Karte wird erst nach Einwilligung geladen. */ ?>
        <div class="karte-halter" data-karte
             data-endpunkt="/api/teilnehmer.json"
             data-skript="<?= e(asset('/assets/js/karte.js')) ?>"
             data-zentrum-lat="47.9959" data-zentrum-lon="7.8522" data-zoom="12">
          <div class="karte-consent" data-karte-consent>
            <h3>Karte von OpenStreetMap laden?</h3>
            <p>
              Die Kartenkacheln kommen von openstreetmap.org. Beim Laden erfährt deren Server
              Ihre IP-Adresse. Deshalb fragen wir vorher. Alles Weitere steht in den
              <a href="/datenschutz.html">Datenschutzhinweisen</a>.
            </p>
            <button type="button" class="btn btn-primaer" data-karte-laden>Karte laden</button>
            <p class="karte-consent-fuss">Die Liste rechts funktioniert auch ohne Karte.</p>
          </div>
          <div class="karte-flaeche" id="karte" hidden></div>
        </div>
        <p class="karte-legende">
          <span class="legende-punkt legende-gastro" aria-hidden="true"></span> Gastronomie
          <span class="legende-punkt legende-unternehmen" aria-hidden="true"></span> Unterstützende Unternehmen
        </p>
      </div>

      <div class="liste-spalte">
        <?php if (!$teilnehmer): ?>
          <div class="leer-zustand">
            <h3>Noch ist die Karte leer</h3>
            <p>
              Wir haben gerade erst angefangen. Sobald die ersten Gastronomien ihre Freigabe
              erteilen, erscheinen sie hier – mit Name, Adresse und Link.
            </p>
            <p>
              <a class="btn btn-primaer" href="/#bestellen">Der erste sein</a>
            </p>
            <p class="leer-hinweis">
              Wir zeigen hier ausschließlich Gastronomien, die tatsächlich zugesagt und der
              Veröffentlichung zugestimmt haben. Beispieleinträge, die es in Wirklichkeit
              nicht gibt, würden weder Ihnen noch uns helfen.
            </p>
          </div>
        <?php else: ?>
          <p class="liste-zaehler" data-t-zaehler aria-live="polite">
            <?= zahl(count($teilnehmer)) ?> Einträge
          </p>
          <ul class="teilnehmer-liste" data-t-liste>
            <?php foreach ($teilnehmer as $t): ?>
              <?php
                // Fuer Screenreader-Nutzer: der Punkt daneben ist aria-hidden, also
                // braucht jede Zeile eine sichtbare Kategorie im Text - nicht nur
                // Farbe oder Form. Gastronomien haben ihre Betriebsart, unterstützende
                // Unternehmen bekommen hier einen eigenen Text.
                $kategorie = $t['sparte'] ?: ($t['typ'] === 'unternehmen' ? 'Unterstützendes Unternehmen' : null);
              ?>
              <li class="teilnehmer-eintrag" data-t-id="<?= e($t['id']) ?>" data-t-typ="<?= e($t['typ']) ?>"
                  data-t-plz="<?= e((string) $t['plz']) ?>" data-t-name="<?= e($t['name']) ?>">
                <button type="button" class="teilnehmer-knopf" data-t-springen="<?= e($t['id']) ?>">
                  <span class="teilnehmer-punkt teilnehmer-punkt-<?= e($t['typ']) ?>" aria-hidden="true"></span>
                  <span class="teilnehmer-text">
                    <strong><?= e($t['name']) ?></strong>
                    <small>
                      <?= $t['strasse'] ? e($t['strasse']) . ', ' : '' ?><?= e((string) $t['plz']) ?> <?= e((string) $t['ort']) ?>
                      <?= $kategorie ? ' · ' . e($kategorie) : '' ?>
                    </small>
                  </span>
                </button>
                <?php if ($t['website']): ?>
                  <a class="teilnehmer-link" href="<?= e($t['website']) ?>" rel="nofollow noopener" target="_blank">Website</a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="liste-leer" data-t-keine hidden>Kein Treffer. Anderer Suchbegriff?</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="band band-hell" id="newsletter" aria-labelledby="newsletter-titel">
  <div class="wrap schmal">
    <h2 id="newsletter-titel">Sollen wir Bescheid geben, wenn es losgeht?</h2>
    <p>
      Wir schreiben selten und nur, wenn es etwas zu sagen gibt: wenn die Schwelle fällt,
      wenn die Produktion startet, wenn die Kartons unterwegs sind. Keine Werbung, kein
      Weiterverkauf Ihrer Adresse, Abmeldung mit einem Klick.
    </p>

    <?php if ($nlErfolg): ?>
      <p class="hinweis hinweis-ok" role="status"><?= e((string) $nlErfolg) ?></p>
    <?php endif; ?>

    <form method="post" action="/senden/newsletter" class="formular formular-inline" novalidate>
      <?= csrf_field() ?>
      <?= honeypot_field() ?>
      <div class="feld<?= isset($nlFehler['email']) ? ' feld-fehler' : '' ?>">
        <label for="nl-email">E-Mail-Adresse</label>
        <input type="email" id="nl-email" name="email" required maxlength="254" autocomplete="email" placeholder="name@beispiel.de">
        <?php if (isset($nlFehler['email'])): ?><p class="feld-meldung"><?= e($nlFehler['email']) ?></p><?php endif; ?>
      </div>
      <div class="feld feld-check<?= isset($nlFehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
        <label>
          <input type="checkbox" name="datenschutz_ok" value="1" required>
          <span>Ich möchte Nachrichten zum Projektstand erhalten und habe die <a href="/datenschutz.html">Datenschutzhinweise</a> gelesen.</span>
        </label>
        <?php if (isset($nlFehler['datenschutz_ok'])): ?><p class="feld-meldung"><?= e($nlFehler['datenschutz_ok']) ?></p><?php endif; ?>
      </div>
      <button class="btn btn-primaer" type="submit">Eintragen</button>
    </form>
    <p class="formular-fuss">
      Sie bekommen zuerst eine Mail mit einem Bestätigungslink. Erst nach dem Klick
      sind Sie eingetragen – so landet niemand ungefragt in der Liste.
    </p>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Fragen zum Projektstand') ?>
</div>
