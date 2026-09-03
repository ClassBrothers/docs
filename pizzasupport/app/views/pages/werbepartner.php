<?php
/** Werbepartner-Landingpage. Ansprache: Sie. Hoechste Seriositaet. */
declare(strict_types=1);

$faq = [
    [
        'frage'   => 'Wann wird die Werbefläche in Rechnung gestellt?',
        'antwort' => '<p>Erst nach dem Startschuss. Sobald genug Gastronomien und genug Buchungen zusammengekommen
                      sind, erhalten Sie eine Auftragsbestätigung und eine Teilrechnung über '
                      . (int) config('startschuss.anzahlung') . ' % des Auftragswerts. Die Restsumme wird
                      mit Auslieferung fällig. Vor dem Startschuss entstehen Ihnen keine Kosten, und Sie
                      können Ihre Buchung bis dahin formlos zurückziehen.</p>',
    ],
    [
        'frage'   => 'Wie viele Menschen sehen mein Motiv?',
        'antwort' => '<p>Ein Pizzakarton steht selten allein herum. Er kommt ins Haus, liegt eine
                      Mahlzeit lang auf dem Tisch und wird von mehreren Personen gesehen – anders als
                      eine Anzeige, die man wegklickt. Wir versprechen Ihnen keine Reichweitenzahlen,
                      die wir nicht belegen können. Was wir Ihnen liefern, ist die gedruckte Auflage
                      und, wenn Sie einen QR-Code nutzen, die Zahl der tatsächlichen Scans.</p>',
    ],
    [
        'frage'   => 'Welche Motive lehnen Sie ab?',
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
                      Für den Inhalt der verlinkten Seite sind Sie als Inserent verantwortlich.</p>',
    ],
    [
        'frage'   => 'Lohnt sich ein Gutschein auf dem Karton?',
        'antwort' => '<p>Nach unserer Erfahrung deutlich mehr als ein reines Logo. Ein Gutschein gibt
                      dem Karton einen Grund, aufgehoben zu werden, und macht Ihren Erfolg messbar –
                      Sie sehen, wie viele Menschen tatsächlich zu Ihnen kommen. Deshalb geben wir auf
                      Gutscheinmotive ' . (int) config('coupon_rabatt_prozent') . ' % Nachlass auf den
                      Listenpreis.</p>',
    ],
    [
        'frage'   => 'Muss mein Unternehmen aus Freiburg kommen?',
        'antwort' => '<p>Nein, aber es sollte für Menschen in und um Freiburg relevant sein. Die Kartons
                      werden hier ausgegeben, also funktionieren Angebote am besten, die man von hier
                      aus erreichen kann. Bei überregionalen Marken schauen wir uns den Einzelfall an.</p>',
    ],
];

$meta['titel']        = 'Werbung auf Pizzakartons in Freiburg buchen | Pizza Support';
$meta['beschreibung'] = 'Werbefläche auf Freiburger Pizzakartons: feste Paketpreise ab 699 € netto, Gutscheinmotive mit 10 % Nachlass, Zahlung erst nach dem Startschuss. Jetzt Fläche sichern.';
$meta['jsonld'] = [
    jsonld_faq($faq),
    jsonld_breadcrumb(['Start' => '/', 'Für Unternehmen' => '/werbepartner.html']),
];

$fehler = flash_get('werbung_fehler', []);
$altw   = flash_get('werbung_alt', []);
$erfolg = flash_get('werbung_ok');
$f      = fortschritt();
?>

<section class="seiten-hero seiten-hero-seriös">
  <div class="wrap schmal">
    <p class="kicker">Für Unternehmen und Selbstständige</p>
    <h1>Werbung auf Pizzakartons in Freiburg buchen</h1>
    <p class="hero-lead">
      Werbung auf Pizzakartons in Freiburg erreicht Menschen beim Essen
      in sympathischem Kontext. Sie buchen eine feste Fläche auf Deckel oder Seite,
      wir drucken sie in 4c, liefern sie aus und die Freiburger Gastronomie gibt
      die Kartons an tausende Gäste weiter.<br>
      Ihr Budget bezahlt damit zwei Dinge gleichzeitig: die sympathische Wahrnehmung
      Ihrer Marke und die Unterstützung von Gastro und Gästen. Wer in Freiburg wirbt
      und dabei etwas zurückgeben will, findet dafür kaum eine direktere Fläche als
      den Pizzakarton, der abends auf dem Tisch liegt.
    </p>
    <div class="hero-aktionen">
      <a class="btn btn-primaer btn-gross" href="#buchen">Fläche buchen</a>
      <a class="btn btn-sekundaer btn-gross" href="#preise">Preise ansehen</a>
    </div>
  </div>
</section>

<section class="band" id="preise" aria-labelledby="preise-titel">
  <div class="wrap">
    <h2 id="preise-titel">Welche Flächen gibt es und was kosten sie?</h2>
    <p class="band-lead">
      Die Auflage beträgt 42.000 Kartons. Aktuell wird die erste Auflage gebucht,
      deren Veröffentlichung für November 2026 geplant ist.
    </p>

    <div class="tabelle-wrap">
      <table class="preistabelle">
        <caption class="sr-only">Werbeformate auf dem Pizzakarton mit Maßen und Preisen</caption>
        <thead>
          <tr>
            <th scope="col">Fläche</th>
            <th scope="col">Position</th>
            <th scope="col">Maße</th>
            <th scope="col">Preis</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (config('werbeformate') as $wf): ?>
            <tr>
              <th scope="row"><?= e($wf['label']) ?><span class="tabelle-sub"><?= e($wf['text']) ?></span></th>
              <td><?= e($wf['gruppe']) ?></td>
              <td><?= e($wf['masse']) ?></td>
              <td class="tabelle-preis">
                <?= $wf['id'] === 'fun-area' ? 'ab ' : '' ?><?= e(preis($wf['preis'])) ?>
                <small><?= $wf['brutto'] ? 'inkl. ' . (int) config('mwst_prozent') . ' % MwSt.' : 'zzgl. ' . (int) config('mwst_prozent') . ' % MwSt.' ?></small>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="coupon-hinweis">
      <h3>Gewinnen Sie Kunden mit Coupons</h3>
      <p>
        Ein Logo wird gesehen. Ein Gutschein wird eingelöst. Wer auf seiner Fläche
        einen Coupon platziert, bekommt <strong><?= (int) config('coupon_rabatt_prozent') ?> % Nachlass</strong>
        auf den Listen-Mediapreis – und dazu etwas, das Werbung sonst selten liefert:
        eine Zahl am Monatsende, die zeigt, was angekommen ist. Kreuzen Sie im Formular
        einfach „Mein Motiv enthält einen Gutschein“ an, der Rabatt ist dann schon
        eingerechnet.
      </p>
    </div>
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
        <p>Sie wählen Format und Motivart. Unverbindlich, ohne Zahlung, ohne Vertragsbindung.</p>
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
      beide Seiten stehen – den aktuellen Stand sehen Sie auf der
      <a href="/teilnehmer.html">Teilnehmerseite</a>. Warum es dieses Projekt überhaupt gibt,
      steht auf der Seite zur <a href="/verpackungssteuer-freiburg.html">Freiburger Verpackungssteuer</a>.
    </p>
  </div>
</section>

<?php include APP_ROOT . '/app/views/partials/fortschritt.php'; ?>

<section class="band band-bestellen" id="buchen" aria-labelledby="buchen-titel">
  <div class="wrap">

    <?php if ($erfolg): ?>
      <div class="danke danke-ruhig" id="danke" tabindex="-1" role="status">
        <h2>Ihre Buchung ist bei uns eingegangen</h2>
        <p><?= e((string) $erfolg) ?></p>
        <p class="danke-weiter">
          Eine Bestätigung liegt in Ihrem Postfach. Kommt sie nicht an, schauen Sie bitte
          kurz im Spam-Ordner nach oder schreiben Sie uns an
          <a href="mailto:<?= e(firma_email_link()) ?>"><?= e(config('firma.email')) ?></a>.
        </p>
      </div>
    <?php endif; ?>

    <div class="bestellen-kopf">
      <h2 id="buchen-titel">Fläche buchen</h2>
      <p class="band-lead">
        Bis zum Startschuss ist alles unverbindlich. Erst danach gehen Auftragsbestätigung
        und Teilrechnung über <?= (int) config('startschuss.anzahlung') ?> % an Sie heraus.
      </p>
    </div>

    <?php if ($fehler): ?>
      <p class="hinweis hinweis-fehler" role="alert">
        Bitte prüfen Sie die markierten Felder – dann schicken wir das Formular gleich ab.
      </p>
    <?php endif; ?>

    <form method="post" action="/senden/werbepartner" class="formular formular-breit" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <?= honeypot_field() ?>

      <fieldset>
        <legend>Ihre Fläche</legend>

        <div class="feld<?= isset($fehler['formate']) ? ' feld-fehler' : '' ?>">
          <span class="feld-label">Welche Flächen möchten Sie buchen? <span class="pflicht" aria-hidden="true">*</span></span>
          <div class="wahl-gitter wahl-gitter-preis" data-preisrechner>
            <?php
            $gewaehlt = $altw['formate'] ?? [];
            $gewaehlt = is_array($gewaehlt) ? $gewaehlt : [];
            foreach (config('werbeformate') as $wf): ?>
              <label class="wahl">
                <input type="checkbox" name="formate[]" value="<?= e($wf['id']) ?>"
                       data-preis="<?= (int) $wf['preis'] ?>" data-brutto="<?= $wf['brutto'] ? '1' : '0' ?>"
                       <?= $wf['id'] === 'fun-area' ? 'data-funarea-checkbox' : '' ?>
                       <?= in_array($wf['id'], $gewaehlt, true) ? 'checked' : '' ?>>
                <span class="wahl-inhalt">
                  <strong><?= e($wf['label']) ?></strong>
                  <small><?= e($wf['masse']) ?></small>
                  <em class="wahl-preis"><?= e(preis($wf['preis'])) ?> <?= $wf['brutto'] ? 'brutto' : 'netto' ?></em>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($fehler['formate'])): ?><p class="feld-meldung"><?= e($fehler['formate']) ?></p><?php endif; ?>
        </div>

        <?php $fa = config('fun_area'); ?>
        <div class="feld feld-funarea" data-funarea-block
             data-preis-je-cm2="<?= (int) $fa['preis_je_cm2_cent'] ?>" data-min-flaeche="<?= e((string) $fa['mindestflaeche_cm2']) ?>" hidden>
          <span class="feld-label">Größe der Fun-Area-Fläche</span>
          <div class="menge-wahl">
            <?php foreach ($fa['schnellauswahl'] as $sa): ?>
              <button type="button" class="menge-knopf" data-funarea-breite="<?= e((string) $sa['breite_cm']) ?>" data-funarea-hoehe="<?= e((string) $sa['hoehe_cm']) ?>">
                <?= e($sa['label']) ?> (<?= e((string) $sa['breite_cm']) ?> × <?= e((string) $sa['hoehe_cm']) ?> cm)
              </button>
            <?php endforeach; ?>
          </div>
          <div class="feld-reihe">
            <div class="feld<?= isset($fehler['fun_breite']) ? ' feld-fehler' : '' ?>">
              <label for="w-fun-breite">Breite <span class="feld-optional">(cm)</span></label>
              <input type="text" id="w-fun-breite" name="fun_breite" inputmode="decimal"
                     data-funarea-breite-feld value="<?= e(alt($altw, 'fun_breite')) ?>">
            </div>
            <div class="feld<?= isset($fehler['fun_hoehe']) ? ' feld-fehler' : '' ?>">
              <label for="w-fun-hoehe">Höhe <span class="feld-optional">(cm)</span></label>
              <input type="text" id="w-fun-hoehe" name="fun_hoehe" inputmode="decimal"
                     data-funarea-hoehe-feld value="<?= e(alt($altw, 'fun_hoehe')) ?>">
            </div>
          </div>
          <p class="feld-hilfe" data-funarea-ergebnis>
            Mindestens <?= e((string) $fa['mindestflaeche_cm2']) ?> cm². Der Preis ist ein Bruttopreis.
          </p>
          <?php if (isset($fehler['fun_breite']) || isset($fehler['fun_hoehe'])): ?>
            <p class="feld-meldung"><?= e($fehler['fun_breite'] ?? $fehler['fun_hoehe']) ?></p>
          <?php endif; ?>
        </div>

        <?php
          $fk = config('flaechenkatalog');
          $gewaehlteWunschflaechen = $altw['wunschflaechen'] ?? [];
          $gewaehlteWunschflaechen = is_array($gewaehlteWunschflaechen) ? $gewaehlteWunschflaechen : [];
          $flaechenplanDatei = APP_ROOT . '/public/assets/img/flaechenplan.jpg';
          $flaechenplanGrossDatei = APP_ROOT . '/public/assets/img/flaechenplan-gross.jpg';
        ?>
        <div class="feld feld-wunschflaeche" data-wunschflaeche-block>
          <span class="feld-label">Wunschfläche <span class="feld-optional">(freiwillig)</span></span>
          <p class="feld-hilfe">
            Alle Maße und Preise beziehen sich auf das Kartonformat 32 × 32 cm. Bei kleineren
            oder größeren Kartons skalieren wir Ihre Fläche proportional mit; der Preis bleibt
            gleich. Die Auswahl unten ist ein <strong>Wunsch, keine Zusage</strong> – die Fläche
            ist begrenzt, siehe Hinweis weiter unten vor dem Buchen-Button.
          </p>

          <?php if (is_file($flaechenplanDatei)): ?>
            <figure class="flaechenplan-grafik">
              <a href="<?= e(asset(is_file($flaechenplanGrossDatei) ? '/assets/img/flaechenplan-gross.jpg' : '/assets/img/flaechenplan.jpg')) ?>"
                 target="_blank" rel="noopener">
                <picture>
                  <source srcset="<?= e(asset('/assets/img/flaechenplan.webp')) ?>" type="image/webp">
                  <img src="<?= e(asset('/assets/img/flaechenplan.jpg')) ?>"
                       alt="Flächenplan des Pizzakartons mit den benannten Werbeflächen auf Deckel, Boden und Seiten"
                       loading="lazy">
                </picture>
              </a>
              <figcaption>
                Lage und Kennung aller Werbeflächen auf dem Karton (Bezugsmaß 32 × 32 cm).
                Zum Vergrößern anklicken.
              </figcaption>
            </figure>
          <?php endif; ?>

          <p class="feld-hilfe" data-wunschflaeche-hinweis>
            Wählen Sie oben zuerst eine Fläche aus – wir zeigen dann die passenden Positionen.
          </p>

          <?php foreach ($fk['gruppen'] as $gruppeKey => $gruppeLabel):
            $flaechenInGruppe = array_values(array_filter(
                $fk['flaechen'],
                fn (array $f): bool => $f['gruppe'] === $gruppeKey && $f['buchbar']
            ));
            if (!$flaechenInGruppe) {
                continue;
            }
          ?>
            <fieldset class="wunschflaeche-gruppe" data-wunschflaeche-gruppe hidden>
              <legend><?= e($gruppeLabel) ?></legend>
              <div class="wahl-gitter">
                <?php foreach ($flaechenInGruppe as $flaeche): ?>
                  <label class="wahl wahl-schmal" data-paket="<?= e((string) $flaeche['paket']) ?>" hidden>
                    <input type="checkbox" name="wunschflaechen[]" value="<?= e($flaeche['id']) ?>"
                           <?= in_array($flaeche['id'], $gewaehlteWunschflaechen, true) ? 'checked' : '' ?>>
                    <span class="wahl-inhalt">
                      <strong><?= e($flaeche['id']) ?></strong>
                      <small><?= e($flaeche['bezeichnung']) ?> · <?= e($flaeche['masse']) ?></small>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>
          <?php endforeach; ?>

          <div class="feld<?= isset($fehler['wunschflaeche_notiz']) ? ' feld-fehler' : '' ?>">
            <label for="w-wunschflaeche-notiz">Anmerkungen zur Platzierung <span class="feld-optional">(freiwillig)</span></label>
            <textarea id="w-wunschflaeche-notiz" name="wunschflaeche_notiz" rows="2" maxlength="500"><?= e(alt($altw, 'wunschflaeche_notiz')) ?></textarea>
            <?php if (isset($fehler['wunschflaeche_notiz'])): ?><p class="feld-meldung"><?= e($fehler['wunschflaeche_notiz']) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="feld feld-check feld-coupon">
          <label>
            <input type="checkbox" name="coupon" value="1" data-coupon <?= alt($altw, 'coupon') ? 'checked' : '' ?>>
            <span>
              <strong>Mein Motiv enthält einen Gutschein.</strong>
              Dafür ziehen wir <?= (int) config('coupon_rabatt_prozent') ?> % vom Listen-Mediapreis ab.
            </span>
          </label>
        </div>

        <div class="summe" data-summe hidden aria-live="polite">
          <span class="summe-label">Ihr Auftragswert</span>
          <span class="summe-wert" data-summe-wert>–</span>
          <span class="summe-hinweis" data-summe-hinweis></span>
        </div>
      </fieldset>

      <fieldset>
        <legend>Ihr Motiv</legend>

        <div class="feld<?= isset($fehler['motiv']) ? ' feld-fehler' : '' ?>">
          <label for="w-motiv">Druckdatei hochladen <span class="feld-optional">(JPG, PNG, WebP oder PDF, max. 12 MB)</span></label>
          <input type="file" id="w-motiv" name="motiv" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf">
          <?php if (isset($fehler['motiv'])): ?><p class="feld-meldung"><?= e($fehler['motiv']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check">
          <label>
            <input type="checkbox" name="motiv_spaeter" value="1" <?= alt($altw, 'motiv_spaeter') ? 'checked' : '' ?>>
            <span>Ich reiche das Motiv später ein. Wir melden uns rechtzeitig vor der Druckfreigabe.</span>
          </label>
        </div>

        <div class="feld<?= isset($fehler['zielurl']) ? ' feld-fehler' : '' ?>">
          <label for="w-zielurl">Ziel für einen QR-Code <span class="feld-optional">(freiwillig)</span></label>
          <input type="text" id="w-zielurl" name="zielurl" maxlength="300" placeholder="ihre-firma.de/aktion"
                 value="<?= e(alt($altw, 'zielurl')) ?>">
          <p class="feld-hilfe">
            Der gedruckte Code führt technisch über pizzasupport.de auf Ihre Seite. So können wir
            Ihnen die Zahl der Scans nennen und die Weiterleitung notfalls abschalten. Nach der
            Druckfreigabe ist das Ziel fest; für den Inhalt der Zielseite sind Sie verantwortlich.
          </p>
          <?php if (isset($fehler['zielurl'])): ?><p class="feld-meldung"><?= e($fehler['zielurl']) ?></p><?php endif; ?>
        </div>

        <div class="motiv-vorbehalt">
          <h3>Motiv-Vorbehalt</h3>
          <p>
            Wir behalten uns vor, Motive abzulehnen. Ausgeschlossen sind Essens-Lieferdienste,
            da sie in direkter Konkurrenz zu den ausgebenden Gastronomien stehen, außerdem
            politische und religiöse Inhalte sowie Meinungsbeiträge ohne fachliche Grundlage.
            Lehnen wir ab, erstatten wir bereits gezahlte Beträge vollständig. Die Einzelheiten
            stehen in den <a href="/agb.html">AGB</a>.
          </p>
        </div>
      </fieldset>

      <fieldset>
        <legend>Ihr Unternehmen</legend>

        <div class="feld<?= isset($fehler['art']) ? ' feld-fehler' : '' ?>">
          <span class="feld-label">Sie buchen als <span class="pflicht" aria-hidden="true">*</span></span>
          <div class="wahl-reihe" role="radiogroup" aria-label="Art des Buchenden">
            <label class="wahl wahl-schmal">
              <input type="radio" name="art" value="unternehmen" required <?= alt($altw, 'art', 'unternehmen') === 'unternehmen' ? 'checked' : '' ?>>
              <span class="wahl-inhalt"><strong>Unternehmen</strong><small>Rechnung mit Ausweis der Umsatzsteuer</small></span>
            </label>
            <label class="wahl wahl-schmal">
              <input type="radio" name="art" value="privat" <?= alt($altw, 'art') === 'privat' ? 'checked' : '' ?>>
              <span class="wahl-inhalt"><strong>Privatperson</strong><small>für die Fun Area auf der Unterseite</small></span>
            </label>
          </div>
          <?php if (isset($fehler['art'])): ?><p class="feld-meldung"><?= e($fehler['art']) ?></p><?php endif; ?>
        </div>

        <div class="feld<?= isset($fehler['firma']) ? ' feld-fehler' : '' ?>">
          <label for="w-firma">Firma oder Name <span class="pflicht" aria-hidden="true">*</span></label>
          <input type="text" id="w-firma" name="firma" required maxlength="150"
                 value="<?= e(alt($altw, 'firma')) ?>" autocomplete="organization">
          <?php if (isset($fehler['firma'])): ?><p class="feld-meldung"><?= e($fehler['firma']) ?></p><?php endif; ?>
        </div>

        <div class="feld<?= isset($fehler['ansprechpartner']) ? ' feld-fehler' : '' ?>">
          <label for="w-person">Ansprechpartner <span class="pflicht" aria-hidden="true">*</span></label>
          <input type="text" id="w-person" name="ansprechpartner" required maxlength="120"
                 value="<?= e(alt($altw, 'ansprechpartner')) ?>" autocomplete="name">
          <?php if (isset($fehler['ansprechpartner'])): ?><p class="feld-meldung"><?= e($fehler['ansprechpartner']) ?></p><?php endif; ?>
        </div>

        <div class="feld-reihe">
          <div class="feld<?= isset($fehler['email']) ? ' feld-fehler' : '' ?>">
            <label for="w-email">E-Mail <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="email" id="w-email" name="email" required maxlength="254"
                   value="<?= e(alt($altw, 'email')) ?>" autocomplete="email">
            <?php if (isset($fehler['email'])): ?><p class="feld-meldung"><?= e($fehler['email']) ?></p><?php endif; ?>
          </div>
          <div class="feld<?= isset($fehler['telefon']) ? ' feld-fehler' : '' ?>">
            <label for="w-telefon">Telefon <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="tel" id="w-telefon" name="telefon" required maxlength="32"
                   value="<?= e(alt($altw, 'telefon')) ?>" autocomplete="tel">
            <?php if (isset($fehler['telefon'])): ?><p class="feld-meldung"><?= e($fehler['telefon']) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="feld<?= isset($fehler['rechnung']) ? ' feld-fehler' : '' ?>">
          <label for="w-rechnung">Rechnungsadresse <span class="feld-optional">(Straße und Hausnummer – PLZ und Ort stehen gleich unten)</span> <span class="pflicht" aria-hidden="true">*</span></label>
          <input type="text" id="w-rechnung" name="rechnung" required maxlength="200"
                 placeholder="Straße und Hausnummer" value="<?= e(alt($altw, 'rechnung')) ?>" autocomplete="street-address">
          <?php if (isset($fehler['rechnung'])): ?><p class="feld-meldung"><?= e($fehler['rechnung']) ?></p><?php endif; ?>
        </div>

        <div class="feld-reihe">
          <div class="feld feld-klein<?= isset($fehler['plz']) ? ' feld-fehler' : '' ?>">
            <label for="w-plz">PLZ <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="text" id="w-plz" name="plz" required inputmode="numeric" pattern="[0-9]{5}" maxlength="5"
                   value="<?= e(alt($altw, 'plz')) ?>" autocomplete="postal-code">
            <?php if (isset($fehler['plz'])): ?><p class="feld-meldung"><?= e($fehler['plz']) ?></p><?php endif; ?>
          </div>
          <div class="feld<?= isset($fehler['ort']) ? ' feld-fehler' : '' ?>">
            <label for="w-ort">Ort <span class="pflicht" aria-hidden="true">*</span></label>
            <input type="text" id="w-ort" name="ort" required maxlength="100"
                   value="<?= e(alt($altw, 'ort')) ?>" autocomplete="address-level2">
            <?php if (isset($fehler['ort'])): ?><p class="feld-meldung"><?= e($fehler['ort']) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="feld-reihe">
          <div class="feld<?= isset($fehler['ustid']) ? ' feld-fehler' : '' ?>">
            <label for="w-ustid">USt-IdNr. <span class="feld-optional">(bei Unternehmen)</span></label>
            <input type="text" id="w-ustid" name="ustid" maxlength="20" placeholder="DE123456789"
                   value="<?= e(alt($altw, 'ustid')) ?>">
            <?php if (isset($fehler['ustid'])): ?><p class="feld-meldung"><?= e($fehler['ustid']) ?></p><?php endif; ?>
          </div>
          <div class="feld<?= isset($fehler['website']) ? ' feld-fehler' : '' ?>">
            <label for="w-website">Website <span class="feld-optional">(freiwillig)</span></label>
            <input type="text" id="w-website" name="website" maxlength="300"
                   value="<?= e(alt($altw, 'website')) ?>" autocomplete="url">
            <?php if (isset($fehler['website'])): ?><p class="feld-meldung"><?= e($fehler['website']) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="feld">
          <label for="w-nachricht">Anmerkungen <span class="feld-optional">(freiwillig)</span></label>
          <textarea id="w-nachricht" name="nachricht" rows="3" maxlength="1500"><?= e(alt($altw, 'nachricht')) ?></textarea>
        </div>
      </fieldset>

      <fieldset>
        <legend>Bestätigungen</legend>

        <div class="feld feld-check<?= isset($fehler['agb_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="agb_ok" value="1" required>
            <span>Ich habe die <a href="/agb.html" target="_blank" rel="noopener">AGB</a> gelesen und akzeptiere sie. <span class="pflicht" aria-hidden="true">*</span></span>
          </label>
          <?php if (isset($fehler['agb_ok'])): ?><p class="feld-meldung"><?= e($fehler['agb_ok']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check<?= isset($fehler['motivvorbehalt_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="motivvorbehalt_ok" value="1" required>
            <span>Ich habe den Motiv-Vorbehalt zur Kenntnis genommen. <span class="pflicht" aria-hidden="true">*</span></span>
          </label>
          <?php if (isset($fehler['motivvorbehalt_ok'])): ?><p class="feld-meldung"><?= e($fehler['motivvorbehalt_ok']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check<?= isset($fehler['verbindlich_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="verbindlich_ok" value="1" required>
            <span>Ich buche verbindlich für den Fall, dass das Projekt zustande kommt. <span class="pflicht" aria-hidden="true">*</span></span>
          </label>
          <?php if (isset($fehler['verbindlich_ok'])): ?><p class="feld-meldung"><?= e($fehler['verbindlich_ok']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check">
          <label>
            <input type="checkbox" name="karte_ok" value="1" <?= alt($altw, 'karte_ok') ? 'checked' : '' ?>>
            <span>Wir dürfen als unterstützendes Unternehmen auf der <a href="/teilnehmer.html">Teilnehmerkarte</a> genannt werden.</span>
          </label>
        </div>

        <div class="feld feld-check<?= isset($fehler['datenschutz_ok']) ? ' feld-fehler' : '' ?>">
          <label>
            <input type="checkbox" name="datenschutz_ok" value="1" required>
            <span>Ich habe die <a href="/datenschutz.html" target="_blank" rel="noopener">Datenschutzhinweise</a> gelesen. <span class="pflicht" aria-hidden="true">*</span></span>
          </label>
          <?php if (isset($fehler['datenschutz_ok'])): ?><p class="feld-meldung"><?= e($fehler['datenschutz_ok']) ?></p><?php endif; ?>
        </div>

        <p class="formular-fuss">
          Die Fläche ist begrenzt. Wir berücksichtigen Buchungen in der Reihenfolge des
          Eingangs; ein Anspruch auf eine bestimmte Fläche besteht nicht. Kommt Ihre Buchung
          nicht zum Zug, sagen wir Ihnen umgehend Bescheid und berechnen nichts.
        </p>

        <?php /* Als Verbraucher-Buchung (Fun Area) sicherer Vorschlag ohne JavaScript:
                 die Button-Lösung nach § 312j BGB verlangt eine eindeutige
                 Zahlungspflicht-Formulierung. Nur wenn erkennbar ein Unternehmen bucht,
                 wechselt das Skript auf den freundlicheren Text - B2B braucht das nicht. */ ?>
        <button class="btn btn-primaer btn-gross btn-block" type="submit" data-buchen-knopf
                data-label-privat="Zahlungspflichtig buchen"
                data-label-unternehmen="Fläche verbindlich reservieren">Zahlungspflichtig buchen</button>
        <p class="formular-fuss">
          Die Reservierung ist bis zum Startschuss kostenfrei und jederzeit widerrufbar.
          Pflichtfelder sind mit <span class="pflicht" aria-hidden="true">*</span> markiert.
        </p>
      </fieldset>
    </form>
  </div>
</section>

<div class="wrap">
  <?= faq_block($faq, 'Was Werbepartner vorher wissen wollen') ?>
</div>
