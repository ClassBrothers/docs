<?php
declare(strict_types=1);
$meta['titel']        = 'Allgemeine Geschäftsbedingungen | Pizza Support';
$meta['beschreibung'] = 'AGB für Werbebuchungen und die Abgabe von Pizzakartons.';
$meta['robots']       = 'noindex,nofollow';
$meta['stoerer']      = false;
$st = config('startschuss');
$porto = config('porto');
$lf = config('lieferung');
$mailAnzeige = (string) config('firma.email');
$mailLink    = firma_email_link();
?>
<section class="band band-recht">
  <div class="wrap schmal">
    <h1>Allgemeine Geschäftsbedingungen</h1>

    <h2>§ 1 Geltungsbereich und Vertragspartner</h2>
    <p>
      Diese Bedingungen gelten für alle Verträge zwischen der <?= e(config('firma.name')) ?>
      (nachfolgend „wir“) und Werbepartnern über die Buchung von Werbeflächen auf
      Pizzakartons sowie für die Abgabe dieser Kartons an gastronomische
      Betriebe. Abweichende Bedingungen des Vertragspartners werden nicht Vertragsbestandteil,
      es sei denn, wir stimmen ihnen schriftlich zu.
    </p>

    <h2>§ 2 Startschuss-Prinzip</h2>
    <p>
      Die Produktion beginnt erst, wenn zwei Bedingungen gleichzeitig erfüllt sind: eine
      ausreichende Zahl teilnehmender Betriebe und ein ausreichendes Volumen gebuchter
      Werbeflächen. Bis dahin sind alle Eintragungen und Buchungen unverbindlich und
      können formlos zurückgezogen werden. Es entstehen keine Kosten.
    </p>
    <p>
      Mit Erreichen beider Schwellen versenden wir Auftragsbestätigungen und Teilrechnungen.
      Ab diesem Zeitpunkt ist die Buchung verbindlich. Die Kartons werden aus
      produktionstechnischen Gründen voraussichtlich
      <?= e($st['lieferwochen']) ?> Wochen nach Produktionsstart ausgeliefert. Feste
      Kalendertermine sagen wir nicht zu; Verzögerungen bei Druckerei oder Logistik
      berechtigen nicht zum Rücktritt, solange die Lieferung in angemessener Frist erfolgt.
    </p>
    <p>
      Kommen die Schwellen nicht zustande, teilen wir das mit. Bereits geleistete Zahlungen
      erstatten wir in diesem Fall vollständig. Ein Anspruch auf Durchführung des Projekts
      besteht nicht.
    </p>

    <h2>§ 3 Leistungen für gastronomische Betriebe</h2>
    <p>
      Teilnehmende Betriebe erhalten die bestellte Menge Pizzakartons unentgeltlich. Im
      Stadtgebiet <?= e($porto['frei_in']) ?> fallen weder Kaufpreis noch Liefergebühren an.
      Außerhalb <?= e($porto['frei_in']) ?> wird eine Portopauschale i. H. v.
      <?= e(preis($porto['pauschale_cent'], false)) ?> Euro netto zzgl.
      <?= (int) config('mwst_prozent') ?> % MwSt. je angefangene <?= zahl((int) $porto['je_kartons']) ?>
      Kartons berechnet. Ein Rechtsanspruch auf eine bestimmte Menge oder ein bestimmtes
      Format besteht nicht; wir teilen die verfügbare Auflage nach billigem Ermessen zu, wenn
      die Nachfrage die Auflage übersteigt.
    </p>
    <p>
      Der Betrieb verpflichtet sich, die Kartons ausschließlich im eigenen Geschäftsbetrieb zu
      verwenden und nicht weiterzuverkaufen. Die auf den Kartons aufgebrachte Werbung darf
      nicht überklebt, entfernt oder unkenntlich gemacht werden.
    </p>
    <p>
      Steuerliche Pflichten des Betriebs – insbesondere im Zusammenhang mit kommunalen
      Verpackungssteuern – bleiben unberührt und sind nicht Gegenstand dieses Vertrags.
    </p>

    <h2>§ 3a Lieferung und Abruf</h2>
    <p>
      Die bestellte Menge wird auf Wunsch in Teilmengen abgerufen. Je Betrieb und Monat
      liefern wir eine Teilmenge innerhalb des in § 3 genannten Liefergebiets kostenfrei aus,
      sofern sie mindestens <?= zahl((int) $lf['abruf_min']) ?> Kartons umfasst. Eine weitere
      Lieferung im selben Monat ist gegen eine Pauschale von
      <?= e(preis((int) $lf['zusatz_pauschale_cent'], false)) ?> Euro netto ab
      <?= zahl((int) $lf['zusatz_mindestmenge']) ?> Kartons möglich; wir liefern spätestens
      <?= (int) $lf['zusatz_frist_werktage'] ?> Werktage nach dem Abrufwunsch, da wir Fahrten
      bündeln. Außerhalb des Liefergebiets gilt für eine solche Zusatzlieferung die
      Portopauschale nach § 3 oder, bei größeren Mengen, eine vorher abgestimmte
      Versandabrechnung nach Aufwand.
    </p>
    <p>
      Die Abholung durch den Betrieb ist an unseren Standorten in
      <?= e(implode(' und ', array_column($lf['abholung_standorte'], 'ort'))) ?> nach
      telefonischer Terminvereinbarung jederzeit möglich, kostenlos und ohne
      Mengenbeschränkung; sie zählt nicht als Lieferung im Sinne dieses Paragrafen.
    </p>
    <p>
      Der Abruf ist auf <?= (int) $lf['abruf_zeitraum_monate'] ?> Monate ab der ersten
      Lieferung begrenzt. Nicht abgerufene Mengen liefern wir nach Ablauf dieser Frist aus.
      Feste Liefertermine sagen wir nicht zu.
    </p>

    <h2>§ 4 Werbebuchungen, Preise und Zahlung</h2>
    <p>
      Es gelten die zum Zeitpunkt der Buchung auf pizzasupport.de veröffentlichten Preise.
      Preise für Unternehmen verstehen sich netto zuzüglich der gesetzlichen Umsatzsteuer.
      Die Fun Area hingegen richtet sich an Endverbraucher, weshalb sich ihr Preis inklusive
      Umsatzsteuer versteht.
    </p>
    <p>
      Nach Erreichen der Schwellen stellen wir <?= (int) $st['anzahlung'] ?> % des
      Auftragswerts als Anzahlung in Rechnung. Der Restbetrag wird mit Auslieferung fällig.
      Beide Rechnungen sind ohne Abzug innerhalb von 14 Tagen zahlbar. Die Abwicklung erfolgt
      ausschließlich per Rechnung; eine Online-Zahlung bieten wir nicht an.
    </p>
    <p>
      Für Motive, die einen einlösbaren Gutschein enthalten, gewähren wir
      <?= (int) config('coupon_rabatt_prozent') ?> % Nachlass auf den Listen-Mediapreis. Der
      Nachlass entfällt rückwirkend, wenn das eingereichte Motiv entgegen der Angabe im
      Buchungsformular keinen Gutschein enthält.
    </p>

    <h2>§ 5 Motive, Druckunterlagen und Vorbehalt</h2>
    <p>
      Druckfähige Unterlagen sind bis zu dem von uns mitgeteilten Termin einzureichen. Gehen
      sie nicht rechtzeitig ein, können wir die Fläche anderweitig vergeben; der Anspruch auf
      Rückerstattung geleisteter Zahlungen bleibt bestehen.
    </p>
    <p>
      <strong>Wir behalten uns vor, Motive ohne Angabe von Gründen abzulehnen.</strong>
      Ausgeschlossen sind insbesondere:
    </p>
    <ul class="liste-check">
      <li>Angebote von Essens-Lieferdiensten, da sie in direkter Konkurrenz zu den ausgebenden Betrieben stehen</li>
      <li>politische Inhalte, Wahlwerbung, hetzerische oder diskriminierende Inhalte</li>
      <li>religiöse und weltanschauliche Werbung</li>
      <li>Meinungsäußerungen ohne fachliche Grundlage (viel Meinung, wenig Ahnung)</li>
      <li>Inhalte, die gegen geltendes Recht verstoßen, Rechte Dritter verletzen oder geeignet sind, dem Ansehen des Projekts oder der teilnehmenden Betriebe zu schaden</li>
      <li>Inhalte der Gender-Diskussion</li>
      <li>Inhalte, die als Provokation empfunden werden können</li>
    </ul>
    <p>
      Lehnen wir ein Motiv ab, erhält der Werbepartner Gelegenheit zur Nachbesserung. Wird
      auch das nachgebesserte Motiv abgelehnt, kommt kein Vertrag zustande und wir erstatten
      bereits geleistete Zahlungen vollständig; weitergehende Ansprüche bestehen nicht.
    </p>
    <p>
      Der Werbepartner sichert zu, über alle Rechte an den eingereichten Unterlagen zu
      verfügen, und stellt uns vollumfänglich von Ansprüchen Dritter frei, die aus dem Inhalt
      seines Motivs hergeleitet werden.
    </p>

    <h2>§ 6 QR-Codes</h2>
    <p>
      Auf Wunsch drucken wir einen QR-Code, der technisch über pizzasupport.de auf eine vom
      Werbepartner benannte Adresse weiterleitet. Für den Inhalt der Zielseite ist
      ausschließlich der Werbepartner verantwortlich. Weil der Code über uns zum Ziel führt,
      sichern wir zu, dass ein von uns vor Drucklegung geprüfter Link zu diesem Zeitpunkt
      nicht zu erkennbar illegalen Inhalten verlinkt.
    </p>
    <p>
      Die Ziel-Adresse kann bis zur Druckfreigabe geändert werden. <strong>Nach der
      Druckfreigabe ist eine Änderung ausgeschlossen.</strong> Wir sind berechtigt, die
      Weiterleitung abzuschalten, wenn die Zielseite rechtswidrige Inhalte enthält, dauerhaft
      nicht erreichbar ist oder sich ihr Inhalt so verändert, dass er unter § 5 fallen würde.
      Ein Anspruch auf Erstattung besteht in diesen Fällen nicht.
    </p>

    <h2>§ 7 Verpackungsrechtliche Pflichten</h2>
    <p>
      Die Systembeteiligung nach dem Verpackungsgesetz sowie die Registrierung im
      Verpackungsregister übernehmen wir als Inverkehrbringer der Kartons. Die
      erforderlichen Kennzeichnungen bringen wir auf dem Karton an.
      <em>PLATZHALTER: Vor Drucklegung mit dem Systembeteiligungsdienstleister abstimmen,
      welche Zeichen und Registrierungsnummern konkret auf das Layout müssen, und diesen
      Absatz entsprechend fassen.</em>
    </p>

    <h2>§ 8 Teilnehmerkarte</h2>
    <p>
      Die Nennung auf der öffentlichen Teilnehmerkarte erfolgt ausschließlich nach
      ausdrücklicher Einwilligung und nach manueller Freigabe durch uns. Die Einwilligung
      kann jederzeit formlos widerrufen werden. Ein Anspruch auf Aufnahme in die Karte
      besteht nicht.
    </p>

    <h2>§ 9 Haftung</h2>
    <p>
      Wir haften unbeschränkt bei Vorsatz und grober Fahrlässigkeit.
      Bei einfacher Fahrlässigkeit haften wir nur bei Verletzung einer wesentlichen
      Vertragspflicht und der Höhe nach begrenzt auf den vertragstypischen, vorhersehbaren
      Schaden. Eine weitergehende Haftung ist ausgeschlossen. Die Haftung nach dem
      Produkthaftungsgesetz bleibt unberührt.
    </p>
    <p>
      Für Werbewirkung, Reichweite oder wirtschaftlichen Erfolg einer Buchung übernehmen wir
      keine Gewähr. Druckbedingte Farbabweichungen im branchenüblichen Rahmen stellen keinen
      Mangel dar. Der Druck auf Pizzakartons kann keine kleinen Schriften darstellen, was bei
      der Erstellung der Druckdaten durch Werbepartner zu beachten ist. Für unscharfen Druck
      oder schlechte Lesbarkeit als Folge zu kleiner oder zu feiner Druckdaten übernehmen wir
      keine Haftung.
    </p>

    <h2 id="widerruf">§ 10 Widerrufsrecht für Verbraucher</h2>
    <p>
      Die folgende Belehrung gilt ausschließlich für Verbraucherinnen und Verbraucher,
      also für natürliche Personen, die eine Fläche zu Zwecken buchen, die überwiegend
      weder ihrer gewerblichen noch ihrer selbständigen beruflichen Tätigkeit zugerechnet
      werden können (§ 13 BGB). In der Praxis betrifft das die Fun Area auf der
      Kartonunterseite. Buchungen von Unternehmen auf Deckel- und Seitenflächen sind
      Geschäfte unter Unternehmern; für sie besteht kein gesetzliches Widerrufsrecht.
    </p>

    <h3>Widerrufsbelehrung</h3>

    <?php /* Text steht einmal in app/lib/widerruf.php und wird identisch in
             der Buchungsbestätigung privater Fun-Area-Buchungen verwendet -
             hier nicht mehr von Hand pflegen. */ ?>
    <?= widerrufsbelehrung_html() ?>

    <h3>Muster-Widerrufsformular</h3>
    <p>
      Wenn Sie den Vertrag widerrufen wollen, können Sie dieses Formular ausfüllen und an
      uns zurücksenden. Sie müssen es nicht verwenden.
    </p>
    <div class="muster-widerruf">
      <p>
        An<br>
        <?= e(config('firma.name')) ?><br>
        <?= e(config('firma.strasse')) ?><br>
        <?= e(config('firma.plz_ort')) ?><br>
        E-Mail: <?= e($mailAnzeige) ?>
      </p>
      <p>
        Hiermit widerrufe(n) ich/wir (*) den von mir/uns (*) abgeschlossenen Vertrag über
        die Erbringung der folgenden Dienstleistung:
      </p>
      <p>
        ______________________________________________
      </p>
      <p>
        Bestellt am (*)/erhalten am (*): ____________________<br>
        Name des/der Verbraucher(s): ____________________<br>
        Anschrift des/der Verbraucher(s): ____________________<br><br>
        Unterschrift des/der Verbraucher(s) (nur bei Mitteilung auf Papier):<br><br>
        ____________________<br><br>
        Datum: ____________________
      </p>
      <p class="klein">(*) Unzutreffendes streichen.</p>
    </div>

    <h3>Keine Anwendung auf Buchungen von Unternehmen</h3>
    <p>
      Buchungen von Deckel- und Seitenflächen richten sich an Unternehmen im Sinne des
      § 14 BGB. Für diese Verträge besteht kein gesetzliches Widerrufsrecht. Es gelten
      ausschließlich die Regelungen dieser AGB, insbesondere zur Freigabe der Motive, zur
      Anzahlung nach Erreichen des Startschusses und zum Rücktritt vor Produktionsfreigabe.
    </p>

    <h2>§ 11 Schlussbestimmungen</h2>
    <p>
      Es gilt das Recht der Bundesrepublik Deutschland unter Ausschluss des UN-Kaufrechts.
      Ist der Vertragspartner Kaufmann, juristische Person des öffentlichen Rechts oder
      öffentlich-rechtliches Sondervermögen, ist Gerichtsstand Freiburg im Breisgau.
      Sollte eine Bestimmung unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen
      unberührt.
    </p>

    <p class="klein">
      Stand: <?= date('m/Y') ?>. <a href="/">Zurück zur Startseite</a>
    </p>
  </div>
</section>
