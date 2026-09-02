<?php
declare(strict_types=1);
$meta['titel']        = 'Datenschutzerklärung | Pizza Support';
$meta['beschreibung'] = 'Welche Daten wir verarbeiten, warum, wie lange und welche Rechte Sie haben.';
$meta['robots']       = 'noindex,nofollow';
$meta['stoerer']      = false;
$fr = config('aufbewahrung');
?>
<section class="band band-recht">
  <div class="wrap schmal">
    <h1>Datenschutzerklärung</h1>

    <h2>1. Verantwortlicher</h2>
    <p>
      Verantwortlich für die Datenverarbeitung auf dieser Website ist:<br>
      <?= e(config('firma.name')) ?><br>
      <?= e(config('firma.strasse')) ?><br>
      <?= e(config('firma.plz_ort')) ?><br>
      Vertreten durch: <?= e(config('firma.gf')) ?><br>
      E-Mail: <a href="mailto:<?= e(firma_email_link()) ?>"><?= e(config('firma.email')) ?></a><br>
      Telefon: <?= e(config('firma.telefon')) ?>
    </p>
    <p>
      Einen Datenschutzbeauftragten haben wir nicht bestellt, da die gesetzlichen
      Voraussetzungen dafür bei uns nicht vorliegen. Für alle Fragen zum Datenschutz
      erreichen Sie uns unter der oben genannten Adresse.
    </p>

    <h2>2. Grundsätzliches</h2>
    <p>
      Wir verarbeiten personenbezogene Daten nur, soweit das für den Betrieb dieser Website
      und die angebotenen Leistungen erforderlich ist. Wir verkaufen keine Daten, wir betreiben
      kein Profiling und wir setzen keine Werbe- oder Tracking-Dienste Dritter ein.
      Rechtsgrundlagen sind Art. 6 Abs. 1 lit. a DSGVO (Einwilligung),
      lit. b (Vertrag oder vorvertragliche Maßnahmen) und lit. f (berechtigtes Interesse).
    </p>

    <h2>3. Hosting</h2>
    <p>
      Diese Website wird gehostet von ALL-INKL.COM – Neue Medien Münnich,
      Inhaber: René Münnich, Hauptstraße 68, 02742 Friedersdorf, Deutschland. Der Anbieter
      verarbeitet in unserem Auftrag die Daten, die beim Aufruf der Website anfallen, und
      stellt Speicherplatz, Datenbank und E-Mail-Versand bereit. Die Server stehen in
      Deutschland. Mit dem Anbieter besteht ein Vertrag über Auftragsverarbeitung nach
      Art. 28 DSGVO. Rechtsgrundlage ist unser berechtigtes Interesse an einem sicheren und
      zuverlässigen Betrieb der Website (Art. 6 Abs. 1 lit. f DSGVO).
    </p>

    <h2>4. Server-Logfiles</h2>
    <p>
      Beim Aufruf dieser Website erhebt unser Hosting-Anbieter technisch notwendige
      Zugriffsdaten: aufgerufene Adresse, Datum und Uhrzeit, übertragene Datenmenge,
      Statusmeldung, Browsertyp und Betriebssystem sowie die IP-Adresse. Diese Daten sind
      für die Auslieferung der Seite und für die Abwehr von Angriffen erforderlich. Sie
      werden nicht mit anderen Datenquellen zusammengeführt und nach spätestens sieben
      Tagen gelöscht oder gekürzt. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO.
    </p>

    <h2>5. Cookies und ähnliche Techniken</h2>
    <p>
      Wir setzen ein einziges Cookie: <code>ps_session</code>. Es enthält eine zufällige
      Sitzungskennung, dient dem Schutz unserer Formulare gegen Fälschungen von fremden
      Seiten und wird beim Schließen des Browsers gelöscht. Es ist für den Betrieb der
      Formulare technisch erforderlich im Sinne des § 25 Abs. 2 Nr. 2 TDDDG und benötigt
      daher keine Einwilligung.
    </p>
    <p>
      Ihre Entscheidung zum Einwilligungsbanner speichern wir im lokalen Speicher Ihres
      Browsers, damit wir nicht bei jedem Seitenaufruf erneut fragen müssen. Diese
      Information verlässt Ihren Browser nicht. Werbe- oder Marketing-Cookies setzen wir
      selbst nicht. Stimmen Sie im Banner Google Analytics zu, setzt ausschließlich Google
      eigene Cookies zur Reichweitenmessung – siehe Nummer 7.
    </p>

    <h2>6. Reichweitenmessung</h2>
    <p>
      Wir zählen Seitenaufrufe selbst, auf unserem eigenen Server, ohne Cookie und ohne
      Dienste Dritter. Gespeichert werden dabei: die aufgerufene Adresse, Datum und Stunde,
      der Hostname einer verweisenden Seite (nicht die vollständige Adresse) sowie eine
      Kennung, die aus Ihrer IP-Adresse, einem geheimen Zusatzwert und dem aktuellen Datum
      berechnet wird.
    </p>
    <p>
      Diese Kennung ist eine Einwegberechnung: Aus ihr lässt sich Ihre IP-Adresse nicht
      zurückgewinnen, und weil das Datum einfließt, ändert sie sich jede Nacht. Eine
      Wiedererkennung über mehrere Tage ist damit ausgeschlossen. Ihre IP-Adresse selbst
      speichern wir zu keinem Zeitpunkt. Nach <?= (int) $fr['analytics_roh'] ?> Tagen werden
      auch diese Einzelwerte gelöscht; übrig bleiben reine Tagessummen ohne jeden
      Personenbezug. Rechtsgrundlage ist unser berechtigtes Interesse an einer
      datensparsamen Erfolgskontrolle (Art. 6 Abs. 1 lit. f DSGVO). Sendet Ihr Browser das
      Signal „Do Not Track“ oder „Global Privacy Control“, zählen wir Sie gar nicht.
    </p>

    <h2>7. Google Analytics</h2>
    <p>
      Zusätzlich zur eigenen, cookiefreien Zählung aus Nummer 6 können Sie der Nutzung von
      Google Analytics zustimmen, einem Reichweitenmessungsdienst der Google Ireland
      Limited, Gordon House, Barrow Street, Dublin 4, Irland. Google Analytics setzt dazu
      Cookies auf Ihrem Gerät und verarbeitet unter anderem Ihre (von Google gekürzte)
      IP-Adresse, besuchte Seiten, Verweildauer, groben Standort sowie Geräte- und
      Browserinformationen.
    </p>
    <p>
      Wir binden Google Analytics erst ein, nachdem Sie im Einwilligungsbanner aktiv
      zugestimmt haben; vorher baut Ihr Browser keine Verbindung zu Google auf und es
      werden keine entsprechenden Cookies gesetzt. Rechtsgrundlage ist Ihre Einwilligung
      (Art. 6 Abs. 1 lit. a DSGVO, § 25 Abs. 1 TDDDG), die Sie jederzeit über
      „Cookie-Einstellungen“ im Fußbereich widerrufen können.
    </p>
    <p>
      Dabei kann es zu einer Übermittlung an Server von Google in die USA kommen. Google
      hat sich dem EU-U.S. Data Privacy Framework unterstellt, das ein angemessenes
      Datenschutzniveau vorsieht. Wie lange Google die Daten speichert, legt Google in
      eigenen Einstellungen fest; Einzelheiten dazu finden Sie in der
      <a href="https://policies.google.com/privacy" rel="nofollow noopener" target="_blank">Datenschutzerklärung von Google</a>.
    </p>

    <h2>8. Karte auf der Teilnehmerseite</h2>
    <p>
      Auf der Seite „Wer ist dabei“ können Sie eine Karte laden, deren Kartenkacheln von
      openstreetmap.org stammen. Beim Laden übermittelt Ihr Browser Ihre IP-Adresse und
      technische Angaben an die Server der OpenStreetMap Foundation. Genau deshalb laden
      wir die Karte erst, wenn Sie das ausdrücklich anfordern. Rechtsgrundlage ist Ihre
      Einwilligung (Art. 6 Abs. 1 lit. a DSGVO, § 25 Abs. 1 TDDDG), die Sie jederzeit über
      „Cookie-Einstellungen“ im Fußbereich widerrufen können. Die Datenschutzerklärung der
      OpenStreetMap Foundation finden Sie unter
      <a href="https://osmfoundation.org/wiki/Privacy_Policy" rel="nofollow noopener" target="_blank">osmfoundation.org</a>.
      Ohne Einwilligung bleibt die vollständige Liste aller Teilnehmer nutzbar.
    </p>

    <h2>9. Bestellformular der Gastronomie</h2>
    <p>
      Wenn Sie Kartons bestellen, verarbeiten wir: Vor- und Nachname, Name des Betriebs,
      Anschrift, E-Mail-Adresse, Telefonnummer, optional die Website, die Betriebsart sowie
      Format und Menge. Diese Daten benötigen wir zur Durchführung der Bestellung
      (Art. 6 Abs. 1 lit. b DSGVO). Ihre Telefonnummer speichern wir verschlüsselt.
    </p>
    <p>
      Nutzen Sie den Ersparnisrechner und schließen die Bestellung ab, speichern wir
      zusätzlich Ihren angegebenen Einkaufspreis je Karton und Ihren monatlichen
      Kartonbedarf – ebenfalls zur Durchführung der Bestellung (Art. 6 Abs. 1 lit. b DSGVO)
      und um die auf der Startseite gezeigte Gesamtersparnis zu berechnen. Nutzen Sie den
      Rechner, ohne zu bestellen, verlässt keine dieser Angaben Ihren Browser.
    </p>
    <p>
      Die Anzeige Ihres Betriebs auf der Teilnehmerkarte erfolgt nur, wenn Sie das im
      Formular gesondert ankreuzen, und erst nach manueller Freigabe durch uns
      (Art. 6 Abs. 1 lit. a DSGVO). Diese Einwilligung können Sie jederzeit formlos
      widerrufen; wir entfernen den Eintrag dann umgehend. Wir dokumentieren zu jeder
      Einwilligung den Zeitpunkt und den Zweck.
    </p>

    <h2>10. Buchungsformular für Werbeflächen</h2>
    <p>
      Bei einer Werbebuchung verarbeiten wir Firmenname, Ansprechpartner, E-Mail-Adresse,
      Telefonnummer, Rechnungsanschrift, gegebenenfalls die Umsatzsteuer-Identifikationsnummer,
      die gewählten Formate sowie ein hochgeladenes Motiv. Rechtsgrundlage ist die Anbahnung
      und Durchführung des Vertrags (Art. 6 Abs. 1 lit. b DSGVO). Telefonnummer,
      Rechnungsanschrift und Umsatzsteuer-Identifikationsnummer speichern wir verschlüsselt.
      Hochgeladene Motive liegen außerhalb des öffentlich erreichbaren Bereichs und sind
      nicht über das Internet abrufbar.
    </p>

    <h2>11. Empfehlung einer Pizzeria</h2>
    <p>
      Über das Formular „Unterstütze Deine Lieblings-Pizzeria“ können Sie uns einen Betrieb
      nennen, den wir ansprechen sollen. Wir verarbeiten dann Name und Anschrift dieses
      Betriebs – also in der Regel öffentlich zugängliche Geschäftsdaten – sowie, falls Sie
      sie freiwillig angeben, Ihre E-Mail-Adresse für Rückfragen. Wir nennen dem Betrieb
      gegenüber nicht, wer ihn vorgeschlagen hat. Rechtsgrundlage ist unser berechtigtes
      Interesse an der Gewinnung teilnehmender Betriebe (Art. 6 Abs. 1 lit. f DSGVO), für
      Ihre E-Mail-Adresse Ihre Einwilligung.
    </p>

    <h2>12. Newsletter</h2>
    <p>
      Für den Newsletter verwenden wir das Double-Opt-in-Verfahren: Nach Ihrer Anmeldung
      erhalten Sie eine E-Mail mit einem Bestätigungslink. Erst nach dem Klick nehmen wir
      Sie auf. So verhindern wir, dass jemand fremde Adressen einträgt. Wir speichern Ihre
      E-Mail-Adresse, den Zeitpunkt der Anmeldung und den Zeitpunkt der Bestätigung.
      Rechtsgrundlage ist Ihre Einwilligung (Art. 6 Abs. 1 lit. a DSGVO). Jede Nachricht
      enthält einen Abmeldelink; nach der Abmeldung löschen wir Ihre Adresse. Nicht
      bestätigte Anmeldungen löschen wir nach <?= (int) $fr['newsletter_unbestaetigt'] ?> Tagen
      automatisch.
    </p>

    <h2>13. Kontaktformular</h2>
    <p>
      Ihre Angaben aus dem Kontaktformular verarbeiten wir zur Bearbeitung Ihrer Anfrage
      (Art. 6 Abs. 1 lit. b bzw. lit. f DSGVO). Wir bewahren die Anfragen auf, bis sie
      erledigt sind und keine Rückfragen mehr zu erwarten sind. Zwingende gesetzliche
      Aufbewahrungspflichten bleiben unberührt.
    </p>

    <h2>14. QR-Codes auf den Kartons</h2>
    <p>
      QR-Codes auf gedruckten Kartons führen technisch über eine Adresse auf pizzasupport.de
      und von dort auf die Seite des jeweiligen Inserenten. Beim Aufruf zählen wir den Klick
      mit denselben Mitteln wie unter Nummer 6 beschrieben: Datum, Stunde und die
      tagesrotierende Kennung. Eine IP-Adresse speichern wir nicht, und wir übermitteln dem
      Inserenten ausschließlich Summen, niemals Einzeldaten. Beim Weiterleiten geben wir die
      Herkunftsadresse nicht weiter.
    </p>

    <h2>15. Formularschutz</h2>
    <p>
      Zum Schutz vor automatisierten Einsendungen enthalten unsere Formulare ein für Menschen
      unsichtbares Feld und eine Zeitprüfung. Zusätzlich begrenzen wir die Zahl der
      Absendungen je Besucher; dafür verwenden wir dieselbe tagesrotierende Kennung wie bei
      der Reichweitenmessung und löschen diese Einträge nach
      <?= (int) $fr['rate_limit'] ?> Tagen. Ein externes Captcha setzen wir bewusst nicht ein.
      Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO.
    </p>

    <h2>16. Empfänger und Drittlandübermittlung</h2>
    <p>
      Ihre Daten erhalten außer unserem Hosting-Anbieter keine Dritten, sofern das nicht zur
      Vertragserfüllung notwendig ist – etwa die Druckerei für die Auslieferungsadresse oder
      unser Steuerberater für Rechnungsunterlagen. Eine Übermittlung in Länder außerhalb der
      EU und des EWR findet grundsätzlich nicht statt. Ausnahmen sind der Abruf der
      Kartenkacheln, den Sie selbst auslösen und der über die Server der OpenStreetMap
      Foundation läuft, sowie – nach Ihrer Einwilligung – Google Analytics, das Daten auch
      in die USA übermitteln kann (siehe Nummer 7).
    </p>

    <h2>17. Speicherdauer</h2>
    <ul class="liste-check">
      <li>Bestellungen und Buchungen: für die Dauer der Geschäftsbeziehung, danach nach den handels- und steuerrechtlichen Fristen von sechs bzw. zehn Jahren</li>
      <li>Abgelehnte oder zurückgezogene Einträge: <?= (int) $fr['abgelehnte_eintraege'] ?> Tage</li>
      <li>Einzelwerte der Reichweitenmessung: <?= (int) $fr['analytics_roh'] ?> Tage, danach nur anonyme Tagessummen</li>
      <li>Zähler des Formularschutzes: <?= (int) $fr['rate_limit'] ?> Tage</li>
      <li>Newsletter: bis zur Abmeldung</li>
      <li>Google Analytics: legt Google in eigenen Einstellungen fest, unabhängig von uns</li>
    </ul>

    <h2>18. Ihre Rechte</h2>
    <p>Sie haben jederzeit das Recht auf</p>
    <ul class="liste-check">
      <li>Auskunft über die zu Ihnen gespeicherten Daten (Art. 15 DSGVO)</li>
      <li>Berichtigung unrichtiger Daten (Art. 16 DSGVO)</li>
      <li>Löschung (Art. 17 DSGVO)</li>
      <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
      <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
      <li>Widerruf erteilter Einwilligungen mit Wirkung für die Zukunft (Art. 7 Abs. 3 DSGVO)</li>
      <li>Beschwerde bei einer Aufsichtsbehörde (Art. 77 DSGVO)</li>
    </ul>
    <p>
      Eine formlose E-Mail an
      <a href="mailto:<?= e(firma_email_link()) ?>"><?= e(config('firma.email')) ?></a>
      genügt. Zuständige Aufsichtsbehörde ist der Landesbeauftragte für den Datenschutz und
      die Informationsfreiheit Baden-Württemberg, Lautenschlagerstraße 20, 70173 Stuttgart.
    </p>

    <h2>19. Widerspruchsrecht</h2>
    <p>
      Soweit wir Daten auf Grundlage berechtigter Interessen verarbeiten, können Sie dieser
      Verarbeitung aus Gründen, die sich aus Ihrer besonderen Situation ergeben, jederzeit
      widersprechen (Art. 21 DSGVO). Wir verarbeiten die Daten dann nicht mehr, es sei denn,
      wir können zwingende schutzwürdige Gründe nachweisen, die Ihre Interessen überwiegen.
    </p>

    <h2>20. Verschlüsselung</h2>
    <p>
      Diese Website wird ausschließlich über HTTPS ausgeliefert. Besonders schutzwürdige
      Felder – Telefonnummern, Rechnungsanschriften und Umsatzsteuer-Identifikationsnummern –
      speichern wir zusätzlich verschlüsselt in der Datenbank. Die Datenbank selbst liegt
      außerhalb des über das Internet erreichbaren Verzeichnisses.
    </p>

    <p class="klein">
      Stand dieser Erklärung: 09/2026. Wir passen sie an, wenn sich die
      Verarbeitung ändert. <a href="/">Zurück zur Startseite</a>
    </p>
  </div>
</section>
