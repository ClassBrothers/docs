<?php
declare(strict_types=1);
$meta['titel']        = 'Allgemeine Geschäftsbedingungen | Pizza Support';
$meta['beschreibung'] = 'AGB für Werbebuchungen und die Abgabe von Pizzakartons.';
$meta['robots']       = 'noindex,nofollow';
$meta['stoerer']      = false;
$st = config('startschuss');
?>
<section class="band band-recht">
  <div class="wrap schmal">
    <h1>Allgemeine Geschäftsbedingungen</h1>

    <div class="hinweis hinweis-todo" role="note">
      <strong>Vor dem Livegang zu prüfen:</strong> Dieser Text bildet die Abläufe ab, wie sie
      auf der Website beschrieben sind, und ist als Arbeitsgrundlage gedacht – nicht als
      geprüfte Rechtsdokumentation. Lassen Sie ihn vor Veröffentlichung anwaltlich abnehmen,
      insbesondere die Abschnitte zu Zahlung, Widerruf und Haftung.
    </div>

    <h2>§ 1 Geltungsbereich und Vertragspartner</h2>
    <p>
      Diese Bedingungen gelten für alle Verträge zwischen der <?= e(config('firma.name')) ?>
      (nachfolgend „wir“) und Werbepartnern über die Buchung von Werbeflächen auf
      Pizzakartons sowie für die unentgeltliche Abgabe dieser Kartons an gastronomische
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
      Ab diesem Zeitpunkt ist die Buchung verbindlich. Die Kartons werden voraussichtlich
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
      Teilnehmende Betriebe erhalten die bestellte Menge Pizzakartons unentgeltlich. Es fallen
      weder Kaufpreis noch Liefergebühren an. Ein Rechtsanspruch auf eine bestimmte Menge oder
      ein bestimmtes Format besteht nicht; wir teilen die verfügbare Auflage nach billigem
      Ermessen zu, wenn die Nachfrage die Auflage übersteigt.
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

    <h2>§ 4 Werbebuchungen, Preise und Zahlung</h2>
    <p>
      Es gelten die zum Zeitpunkt der Buchung auf pizzasupport.de veröffentlichten Preise.
      Preise für Unternehmen verstehen sich netto zuzüglich der gesetzlichen Umsatzsteuer;
      der Preis für die Fun Area versteht sich inklusive Umsatzsteuer.
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
      <li>politische Inhalte und Wahlwerbung</li>
      <li>religiöse und weltanschauliche Werbung</li>
      <li>Meinungsäußerungen ohne fachliche Grundlage sowie irreführende Gesundheits- oder Heilsversprechen</li>
      <li>Inhalte, die gegen geltendes Recht verstoßen, Rechte Dritter verletzen oder geeignet sind, dem Ansehen des Projekts oder der teilnehmenden Betriebe zu schaden</li>
    </ul>
    <p>
      Lehnen wir ein Motiv ab, erhält der Werbepartner Gelegenheit zur Nachbesserung. Wird
      auch das nachgebesserte Motiv abgelehnt, erstatten wir bereits geleistete Zahlungen
      vollständig; weitergehende Ansprüche bestehen nicht.
    </p>
    <p>
      Der Werbepartner sichert zu, über alle Rechte an den eingereichten Unterlagen zu
      verfügen, und stellt uns von Ansprüchen Dritter frei, die aus dem Inhalt seines Motivs
      hergeleitet werden.
    </p>

    <h2>§ 6 QR-Codes</h2>
    <p>
      Auf Wunsch drucken wir einen QR-Code, der technisch über pizzasupport.de auf eine vom
      Werbepartner benannte Adresse weiterleitet. Für den Inhalt der Zielseite ist
      ausschließlich der Werbepartner verantwortlich.
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
      Wir haften unbeschränkt bei Vorsatz und grober Fahrlässigkeit sowie bei der Verletzung
      von Leben, Körper und Gesundheit. Bei einfacher Fahrlässigkeit haften wir nur bei
      Verletzung einer wesentlichen Vertragspflicht und der Höhe nach begrenzt auf den
      vertragstypischen, vorhersehbaren Schaden. Eine weitergehende Haftung ist
      ausgeschlossen. Die Haftung nach dem Produkthaftungsgesetz bleibt unberührt.
    </p>
    <p>
      Für Werbewirkung, Reichweite oder wirtschaftlichen Erfolg einer Buchung übernehmen wir
      keine Gewähr. Druckbedingte Farbabweichungen im branchenüblichen Rahmen stellen keinen
      Mangel dar.
    </p>

    <h2>§ 10 Widerrufsrecht für Verbraucher</h2>
    <p>
      Verbrauchern steht bei im Fernabsatz geschlossenen Verträgen ein gesetzliches
      Widerrufsrecht zu. Das betrifft insbesondere Buchungen der Fun Area durch
      Privatpersonen.
      <em>PLATZHALTER: Hier die vollständige Widerrufsbelehrung nebst Muster-Widerrufsformular
      einsetzen und rechtlich prüfen lassen; das Widerrufsrecht kann bei nach Kundenwunsch
      angefertigten Waren eingeschränkt sein.</em>
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
