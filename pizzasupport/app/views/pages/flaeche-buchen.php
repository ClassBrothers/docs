<?php
/** Reine Buchungsseite fuer Werbeflaechen. Ansprache: Sie. */
declare(strict_types=1);

$meta['titel']        = 'Werbefläche auf Pizzakartons buchen | Pizza Support';
$meta['beschreibung'] = 'Wählen Sie eine oder mehrere Werbeflächen auf dem Pizzakarton und buchen Sie unverbindlich bis zum Startschuss.';
$meta['jsonld'] = [
    jsonld_breadcrumb(['Start' => '/', 'Für Unternehmen' => '/werbepartner.html', 'Fläche buchen' => '/flaeche-buchen.html']),
];

$fehler = flash_get('werbung_fehler', []);
$altw   = flash_get('werbung_alt', []);
$erfolg = flash_get('werbung_ok');

$flaechenplanDatei      = APP_ROOT . '/public/assets/img/flaechenplan.jpg';
$flaechenplanGrossDatei = APP_ROOT . '/public/assets/img/flaechenplan-gross.jpg';
$flaechenplanVorhanden  = is_file($flaechenplanDatei);

// Faengt eine fehlende Tabelle ab: Beim FTP-Deploy kommen neue Dateien vor
// der Migration an (die Migration laeuft erst per Knopf im Adminpanel) -
// ohne Abfangen wuerde das die ganze Seite mitten im Rendern abbrechen,
// noch bevor das Layout (Kopf, CSS, Navigation) ueberhaupt zum Zug kommt.
try {
    $vergebeneKennungen = array_column(db_all('SELECT kennung FROM flaechen_vergabe'), 'kennung');
} catch (PDOException $e) {
    $vergebeneKennungen = [];
}

$gewaehlteFlaechen = $altw['flaechen'] ?? [];
$gewaehlteFlaechen = is_array($gewaehlteFlaechen) ? $gewaehlteFlaechen : [];
?>

<section class="seiten-hero seiten-hero-seriös">
  <div class="wrap">
    <p class="kicker">Für Unternehmen und Selbstständige</p>
    <h1>Fläche buchen</h1>
    <p class="hero-lead">
      Noch unentschlossen? Auf der Seite <a href="/werbepartner.html">Für Unternehmen</a> stehen
      Preise, Beispiele und alles, was Sie vorher wissen sollten. Hier geht es direkt zur Buchung.
    </p>
  </div>
</section>

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
      <h2 id="buchen-titel">Ihre Fläche(n)</h2>
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

    <div class="<?= $flaechenplanVorhanden ? 'bestellen-grid' : '' ?>">
    <div class="bestellen-formular-spalte">
    <form method="post" action="/senden/werbepartner" class="formular formular-breit" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <?= honeypot_field() ?>

      <fieldset>
        <legend>Ihre Fläche</legend>

        <div class="feld<?= isset($fehler['flaechen']) ? ' feld-fehler' : '' ?>" data-preisrechner>
          <span class="feld-label">Welche Flächen möchten Sie buchen? <span class="pflicht" aria-hidden="true">*</span></span>
          <p class="feld-hilfe">
            Jede Fläche ist ein fest benannter, einzeln bepreister Platz. Sie können beliebig
            viele auswählen – wer zuerst bestätigt, bekommt die Fläche.
            <?php if ($flaechenplanVorhanden): ?>
              Den Flächenplan mit allen Kennungen sehen Sie rechts daneben.
            <?php endif; ?>
          </p>

          <?php
            $fk = config('flaechenkatalog');
            foreach ($fk['gruppen'] as $gruppeKey => $gruppeLabel):
              $flaechenInGruppe = array_values(array_filter(
                  $fk['flaechen'],
                  fn (array $f): bool => $f['gruppe'] === $gruppeKey && $f['buchbar']
              ));
              if (!$flaechenInGruppe) {
                  continue;
              }
          ?>
            <fieldset class="flaechen-gruppe" id="gruppe-<?= e($gruppeKey) ?>">
              <legend><?= e($gruppeLabel) ?></legend>
              <div class="wahl-gitter wahl-gitter-preis">
                <?php foreach ($flaechenInGruppe as $flaeche):
                  $istVergeben = in_array($flaeche['id'], $vergebeneKennungen, true);
                ?>
                  <label class="wahl wahl-schmal<?= $istVergeben ? ' wahl-vergeben' : '' ?>">
                    <input type="checkbox" name="flaechen[]" value="<?= e($flaeche['id']) ?>"
                           data-preis="<?= (int) $flaeche['preis'] ?>"
                           <?= $istVergeben ? 'disabled' : '' ?>
                           <?= in_array($flaeche['id'], $gewaehlteFlaechen, true) ? 'checked' : '' ?>>
                    <span class="wahl-inhalt">
                      <strong><?= e($flaeche['id']) ?></strong>
                      <small><?= e($flaeche['bezeichnung']) ?> · <?= e($flaeche['masse']) ?></small>
                      <?php if ($istVergeben): ?>
                        <em class="wahl-vergeben-marke">bereits verkauft</em>
                      <?php else: ?>
                        <em class="wahl-preis"><?= e(preis((int) $flaeche['preis'])) ?> netto</em>
                      <?php endif; ?>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>
          <?php endforeach; ?>

          <?php if (isset($fehler['flaechen'])): ?><p class="feld-meldung"><?= e($fehler['flaechen']) ?></p><?php endif; ?>
        </div>

        <div class="feld<?= isset($fehler['notiz']) ? ' feld-fehler' : '' ?>">
          <label for="w-notiz">Anmerkungen zur Platzierung <span class="feld-optional">(freiwillig)</span></label>
          <textarea id="w-notiz" name="notiz" rows="2" maxlength="500"><?= e(alt($altw, 'notiz')) ?></textarea>
          <?php if (isset($fehler['notiz'])): ?><p class="feld-meldung"><?= e($fehler['notiz']) ?></p><?php endif; ?>
        </div>

        <div class="feld feld-check feld-coupon">
          <label>
            <input type="checkbox" name="coupon" value="1" data-coupon <?= alt($altw, 'coupon') ? 'checked' : '' ?>>
            <span>
              Zieht mir 10 % ab! Mein Motiv enthält einen Gutschein für die Gäste, mit dem sie bei
              meinem Unternehmen einen Vorteil erhalten. Dafür erhalte ich 10 % Rabatt auf den Listenpreis.
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

        <div class="feld<?= isset($fehler['firma']) ? ' feld-fehler' : '' ?>">
          <label for="w-firma">Firma <span class="pflicht" aria-hidden="true">*</span></label>
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
            <label for="w-ustid">USt-IdNr. <span class="feld-optional">(soweit vorhanden)</span></label>
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

        <div class="feld feld-check">
          <label>
            <input type="checkbox" name="naechste_auflage_bevorzugt" value="1" <?= alt($altw, 'naechste_auflage_bevorzugt') ? 'checked' : '' ?>>
            <span>Bei der nächsten Auflage bevorzugt kontaktieren. Ich möchte meine Fläche früher auswählen.</span>
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
          Die Fläche ist begrenzt. Wir vergeben in der Reihenfolge der Bestätigungen; ein Anspruch
          auf eine bestimmte Fläche besteht nicht. Kommt Ihre Buchung nicht zum Zug, sagen wir
          Ihnen umgehend Bescheid und berechnen nichts.
        </p>

        <button class="btn btn-primaer btn-gross btn-block" type="submit">Fläche verbindlich reservieren</button>
        <p class="formular-fuss">
          Die Reservierung ist bis zum Startschuss kostenfrei und jederzeit widerrufbar.
          Pflichtfelder sind mit <span class="pflicht" aria-hidden="true">*</span> markiert.
        </p>
      </fieldset>
    </form>
    </div>

    <?php if ($flaechenplanVorhanden): ?>
      <?php
        // Einzelkoordinaten je Kennung, von Hand aus der Originalgrafik
        // (2000 x 4545 px) abgemessen und in Prozent der Bildmasse
        // umgerechnet. Fehlt eine Kennung hier (z.B. die neuen StartUp-
        // Felder SU-S/SU-M, die im Plan noch nicht einzeln eingezeichnet
        // sind), bleibt sie einfach ohne Hervorhebung buchbar - kein Fehler.
        $flaechenplanKennungKoordinaten = [
            'D2'   => ['left' => 18.2, 'top' => 17.7, 'width' => 20.0, 'height' => 13.6],
            'D3'   => ['left' => 18.2, 'top' => 31.7, 'width' => 20.0, 'height' => 4.4],
            'D4'   => ['left' => 40.0, 'top' => 8.0,  'width' => 20.0, 'height' => 13.6],
            'D5'   => ['left' => 40.0, 'top' => 22.1, 'width' => 20.0, 'height' => 4.4],
            'D6'   => ['left' => 40.0, 'top' => 27.0, 'width' => 20.0, 'height' => 8.8],
            'D7'   => ['left' => 61.9, 'top' => 8.0,  'width' => 20.0, 'height' => 4.4],
            'D8'   => ['left' => 61.9, 'top' => 13.3, 'width' => 20.0, 'height' => 8.8],
            'D9'   => ['left' => 61.9, 'top' => 22.5, 'width' => 20.0, 'height' => 13.6],
            'DIN1' => ['left' => 16.0, 'top' => 2.5,  'width' => 18.5, 'height' => 3.0],
            'DIN2' => ['left' => 65.5, 'top' => 2.5,  'width' => 18.5, 'height' => 3.0],
            'DSL1' => ['left' => 6.0,  'top' => 7.0,  'width' => 6.9,  'height' => 8.5],
            'DSL2' => ['left' => 6.0,  'top' => 29.0, 'width' => 6.9,  'height' => 8.3],
            'DSR1' => ['left' => 87.1, 'top' => 7.0,  'width' => 6.9,  'height' => 8.5],
            'DSR2' => ['left' => 87.1, 'top' => 29.0, 'width' => 6.9,  'height' => 8.3],
            'BH1'  => ['left' => 15.5, 'top' => 38.5, 'width' => 22.0, 'height' => 3.0],
            'BH2'  => ['left' => 40.0, 'top' => 38.5, 'width' => 21.0, 'height' => 3.0],
            'BH3'  => ['left' => 63.5, 'top' => 38.5, 'width' => 21.5, 'height' => 3.0],
            'BL1'  => ['left' => 6.0,  'top' => 44.0, 'width' => 6.0,  'height' => 8.0],
            'BL2'  => ['left' => 6.0,  'top' => 54.0, 'width' => 6.0,  'height' => 9.0],
            'BL3'  => ['left' => 6.0,  'top' => 65.0, 'width' => 6.0,  'height' => 8.5],
            'BR1'  => ['left' => 86.0, 'top' => 44.0, 'width' => 8.0,  'height' => 8.0],
            'BR2'  => ['left' => 86.0, 'top' => 54.0, 'width' => 8.0,  'height' => 9.0],
            'BR3'  => ['left' => 86.0, 'top' => 65.0, 'width' => 8.0,  'height' => 8.5],
            'BF1'  => ['left' => 16.7, 'top' => 74.7, 'width' => 22.1, 'height' => 5.0],
            'BF2'  => ['left' => 38.8, 'top' => 74.7, 'width' => 23.3, 'height' => 5.0],
            'BF3'  => ['left' => 62.1, 'top' => 74.7, 'width' => 24.3, 'height' => 5.0],
        ];

        $verkauftMitKoordinaten = array_filter(
            $vergebeneKennungen,
            fn (string $kennung): bool => isset($flaechenplanKennungKoordinaten[$kennung])
        );
      ?>
      <aside class="bestellen-grafik-spalte" aria-label="Flächenplan des Pizzakartons">
        <div class="flaechenplan-lupe" data-flaechenplan-lupe
             data-lupe-quelle="<?= e(asset(is_file($flaechenplanGrossDatei) ? '/assets/img/flaechenplan-gross.jpg' : '/assets/img/flaechenplan.jpg')) ?>">
          <a href="<?= e(asset(is_file($flaechenplanGrossDatei) ? '/assets/img/flaechenplan-gross.jpg' : '/assets/img/flaechenplan.jpg')) ?>"
             target="_blank" rel="noopener">
            <picture>
              <source srcset="<?= e(asset('/assets/img/flaechenplan.webp')) ?>" type="image/webp">
              <img class="flaechenplan-bild" src="<?= e(asset('/assets/img/flaechenplan.jpg')) ?>"
                   alt="Flächenplan des Pizzakartons mit den benannten Werbeflächen auf Deckel, Boden und Seiten"
                   loading="lazy">
            </picture>
          </a>
          <?php foreach ($flaechenplanKennungKoordinaten as $kennung => $kasten): ?>
            <div class="flaechenplan-umrandung" data-paket-umrandung="<?= e($kennung) ?>"
                 data-left="<?= e((string) $kasten['left']) ?>" data-top="<?= e((string) $kasten['top']) ?>"
                 data-width="<?= e((string) $kasten['width']) ?>" data-height="<?= e((string) $kasten['height']) ?>"></div>
          <?php endforeach; ?>
          <?php
            // Position per <style nonce>-Regel statt per style="" -Attribut:
            // Die CSP dieser Seite erlaubt keine Inline-style-Attribute.
          ?>
          <?php if ($verkauftMitKoordinaten): ?>
            <style nonce="<?= e($GLOBALS['csp_nonce'] ?? '') ?>">
              <?php foreach ($verkauftMitKoordinaten as $i => $kennung): $k = $flaechenplanKennungKoordinaten[$kennung]; ?>
                .flaechenplan-verkauft-<?= (int) $i ?>{left:<?= e((string) $k['left']) ?>%;top:<?= e((string) $k['top']) ?>%;width:<?= e((string) $k['width']) ?>%;height:<?= e((string) $k['height']) ?>%}
              <?php endforeach; ?>
            </style>
            <?php foreach (array_values($verkauftMitKoordinaten) as $i => $kennung): ?>
              <div class="flaechenplan-verkauft flaechenplan-verkauft-<?= (int) $i ?>" title="<?= e($kennung) ?> bereits verkauft">
                <span>verkauft</span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
          <div class="flaechenplan-lupenglas" data-lupenglas hidden aria-hidden="true"></div>
        </div>
        <p class="flaechenplan-bildunterschrift">
          Lage und Kennung aller Werbeflächen auf dem Karton (Bezugsmaß 32 × 32 cm). Mit der
          Maus über den Plan fahren zum Vergrößern, anklicken für die volle Größe. Bewegen Sie
          die Maus über eine Fläche oben im Formular, um sie hier orange hervorzuheben.
        </p>
      </aside>
    <?php endif; ?>
    </div>
  </div>
</section>
