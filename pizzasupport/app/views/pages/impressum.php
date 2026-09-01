<?php
declare(strict_types=1);
$meta['titel']        = 'Impressum | Pizza Support';
$meta['beschreibung'] = 'Anbieterkennzeichnung nach § 5 DDG.';
$meta['robots']       = 'noindex,nofollow';
$meta['stoerer']      = false;
?>
<section class="band band-recht">
  <div class="wrap schmal">
    <h1>Impressum</h1>

    <div class="hinweis hinweis-todo" role="note">
      <strong>Vor dem Livegang zu prüfen:</strong> Die mit PLATZHALTER markierten Angaben
      stammen noch nicht aus dem Handelsregister. Bitte durch die verbindlichen Daten der
      <?= e(config('firma.name')) ?> ersetzen (zentral in <code>app/config.php</code>) und
      den Text juristisch abnehmen lassen.
    </div>

    <h2>Angaben gemäß § 5 DDG</h2>
    <p>
      <?= e(config('firma.name')) ?><br>
      <?= e(config('firma.strasse')) ?><br>
      <?= e(config('firma.plz_ort')) ?><br>
      Deutschland
    </p>

    <h2>Vertreten durch</h2>
    <p><?= e(config('firma.gf')) ?></p>

    <h2>Kontakt</h2>
    <p>
      Telefon: <?= e(config('firma.telefon')) ?><br>
      E-Mail: <a href="mailto:<?= e(config('firma.email')) ?>"><?= e(config('firma.email')) ?></a>
    </p>

    <h2>Registereintrag</h2>
    <p>
      Eintragung im Handelsregister<br>
      Registergericht und Registernummer: <?= e(config('firma.hrb')) ?>
    </p>

    <h2>Umsatzsteuer-Identifikationsnummer</h2>
    <p>
      Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:<br>
      <?= e(config('firma.ustid')) ?>
    </p>

    <h2>Redaktionell verantwortlich</h2>
    <p>
      Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV:<br>
      <?= e(config('firma.gf')) ?>, Anschrift wie oben.
    </p>

    <h2>Projektpartner</h2>
    <p>
      Pizza Support ist ein Projekt der <?= e(config('firma.name')) ?> mit gastronomischer
      Unterstützung der <?= e(config('partner_gastro')) ?>. Vertragspartner für Bestellungen
      und Werbebuchungen ist ausschließlich die <?= e(config('firma.name')) ?>.
    </p>

    <h2>Verbraucherstreitbeilegung</h2>
    <p>
      Wir sind nicht bereit und nicht verpflichtet, an Streitbeilegungsverfahren vor einer
      Verbraucherschlichtungsstelle teilzunehmen.
    </p>

    <h2>Haftung für Inhalte</h2>
    <p>
      Als Diensteanbieter sind wir für eigene Inhalte auf diesen Seiten nach den allgemeinen
      Gesetzen verantwortlich. Wir sind jedoch nicht verpflichtet, übermittelte oder
      gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die
      auf eine rechtswidrige Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung
      der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt.
      Eine diesbezügliche Haftung ist erst ab dem Zeitpunkt der Kenntnis einer konkreten
      Rechtsverletzung möglich. Bei Bekanntwerden entsprechender Rechtsverletzungen werden
      wir diese Inhalte umgehend entfernen.
    </p>

    <h2>Haftung für Links</h2>
    <p>
      Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen
      Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen.
      Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber
      der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf
      mögliche Rechtsverstöße überprüft; rechtswidrige Inhalte waren nicht erkennbar. Eine
      permanente inhaltliche Kontrolle der verlinkten Seiten ist ohne konkrete Anhaltspunkte
      einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden
      wir derartige Links umgehend entfernen.
    </p>
    <p>
      Das gilt ausdrücklich auch für die Ziele von QR-Codes, die über pizzasupport.de
      weitergeleitet werden. Für den Inhalt der Zielseite ist der jeweilige Inserent
      verantwortlich. Bei Kenntnis rechtswidriger Inhalte schalten wir die Weiterleitung ab.
    </p>

    <h2>Urheberrecht</h2>
    <p>
      Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen
      dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art
      der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen
      Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind
      nur für den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf
      dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter
      beachtet. Insbesondere werden Inhalte Dritter als solche gekennzeichnet. Sollten Sie
      trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen
      entsprechenden Hinweis. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige
      Inhalte umgehend entfernen.
    </p>

    <h2>Kartenmaterial</h2>
    <p>
      Die Karte auf der Teilnehmerseite nutzt Kartendaten von OpenStreetMap.
      © <a href="https://www.openstreetmap.org/copyright" rel="nofollow noopener" target="_blank">OpenStreetMap-Mitwirkende</a>,
      veröffentlicht unter der Open Database License.
    </p>

    <p class="klein"><a href="/">Zurück zur Startseite</a></p>
  </div>
</section>
