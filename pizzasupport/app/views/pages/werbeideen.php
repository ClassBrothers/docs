<?php
declare(strict_types=1);
$meta['titel']        = 'Werbeideen für den Pizzakarton | Pizza Support';
$meta['beschreibung'] = 'Stellenanzeige, Gutschein, Einladung: Was Unternehmen aus der Region auf einen Pizzakarton drucken – mit Beispielen für jedes Format.';
$meta['robots']       = 'index,follow';
$meta['stoerer']      = true;
$meta['jsonld'] = [
    jsonld_breadcrumb(['Start' => '/', 'Für Unternehmen' => '/werbepartner.html', 'Werbeideen' => '/werbeideen.html']),
];
?>
<section class="seiten-hero">
  <div class="wrap schmal">
    <p class="kicker">Für Unternehmen und Selbstständige</p>
    <h1>Was drucken Sie drauf?</h1>
    <p class="hero-lead">
      Ein Pizzakarton liegt zwanzig Minuten lang direkt vor jemandem, der nichts anderes
      vorhat als essen. Kein Wegklicken, kein Weiterscrollen, kein Werbeblocker. Die Frage
      ist nur, was in dieser Zeit dort zu lesen sein soll. Hier sind Ideen von Betrieben
      aus der Region – zum Abgucken, Weiterdenken und Besserwissen.
    </p>
  </div>
</section>

<section class="band">
  <div class="wrap">
    <h2>Mitarbeitende finden</h2>
    <p>
      Fachkräfte lesen keine Stellenportale. Sie haben einen Job. Was sie aber tun:
      freitagabends Pizza bestellen, mit dem Handy in Reichweite.
    </p>

    <div class="ideen-raster">

      <article class="idee">
        <p class="idee-claim">„Wir suchen Dich. Nicht Deinen Lebenslauf.“</p>
        <p>
          Ein Sanitärbetrieb sucht einen Monteur. Der Deckel klappt auf, der Dampf steigt –
          und mittendrin der Satz, das Firmenlogo und ein QR-Code, der direkt zu einem
          Formular mit drei Feldern führt. Keine Anschreiben, keine Zeugnisse. Wer Interesse
          hat, tippt seinen Namen ein, während die erste Ecke noch zu heiß zum Anfassen ist.
        </p>
        <p class="idee-meta">Empfohlen: Deckel mittel, mit QR-Code</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Ausbildung ab September. Ja, wirklich bezahlt.“</p>
        <p>
          Die Zielgruppe für Ausbildungsplätze sitzt fast wörtlich vor diesem Karton:
          zwischen sechzehn und zwanzig, Käse zieht gerade Fäden, Freunde daneben, Handy
          sowieso in der Hand. Ein Ausbildungsplatz gehört in genau diesen Moment, nicht auf
          eine Karriereseite, die niemand aufruft.
        </p>
        <p class="idee-meta">Empfohlen: Deckel klein oder mittel</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Quereinsteiger willkommen. Erfahrung im Aushalten reicht.“</p>
        <p>
          Pflegedienste, Kitas und Sicherheitsdienste suchen Menschen, die nicht in der
          Branche sind und deshalb nie eine Branchenanzeige sehen. Beim Aufklappen des
          Kartons dagegen schon – mitten im Feierabend, mitten im echten Leben.
        </p>
        <p class="idee-meta">Empfohlen: Deckel mittel</p>
      </article>

    </div>
  </div>
</section>

<section class="band band-hell">
  <div class="wrap">
    <h2>Kunden gewinnen mit Gutscheinen</h2>
    <p>
      Ein Gutschein auf dem Karton wird ausgeschnitten und landet an der Pinnwand oder im
      Portemonnaie. Das ist der Unterschied zwischen gesehen und behalten. Für
      Gutschein-Motive geben wir <?= (int) config('coupon_rabatt_prozent') ?> Prozent auf
      den Listenpreis – weil sie messbar funktionieren und wir wollen, dass Sie
      wiederkommen.
    </p>

    <div class="ideen-raster">

      <article class="idee">
        <p class="idee-claim">„Jetzt ne Cola wär nice.“</p>
        <p>
          Ein Kino druckt einen Gutschein für ein Getränk zur nächsten Vorstellung. Der
          Satz trifft genau den Moment, in dem der Karton geöffnet wird. Wer ihn liest,
          denkt an Kino und an Durst gleichzeitig.
        </p>
        <p class="idee-meta">Empfohlen: Deckel klein als Gutschein</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Bevor der erste Frost kommt.“</p>
        <p>
          Eine Werkstatt legt im Oktober einen Gutschein für den Reifenwechsel auf. Regional
          begrenzt, terminlich gesetzt, ohne Streuverlust. Die Kartons gehen genau in die
          Stadtteile, in denen die Kundschaft wohnt.
        </p>
        <p class="idee-meta">Empfohlen: Deckel mittel als Gutschein</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Nach der Pizza ist vor dem Training.“</p>
        <p>
          Ein Fitnessstudio mit Probewoche. Selbstironie funktioniert an dieser Stelle
          besser als jedes Vorher-Nachher-Bild, weil sie den Moment ernst nimmt, in dem
          jemand den Karton öffnet.
        </p>
        <p class="idee-meta">Empfohlen: Deckel klein als Gutschein</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Zehn Minuten von hier. Wirklich zehn.“</p>
        <p>
          Ein Friseur, eine Kosmetikerin, ein Kiosk: Alles, was im selben Viertel liegt wie
          die Pizzeria, profitiert von der räumlichen Nähe. Der Karton kommt aus der
          Nachbarschaft und bleibt in der Nachbarschaft.
        </p>
        <p class="idee-meta">Empfohlen: Seitenfläche oder Deckel klein</p>
      </article>

    </div>
  </div>
</section>

<section class="band">
  <div class="wrap">
    <h2>Bekannt werden</h2>

    <div class="ideen-raster">

      <article class="idee">
        <p class="idee-claim">„Neu bei Ihnen um die Ecke.“</p>
        <p>
          Eine Neueröffnung braucht in den ersten Wochen vor allem eines: dass die Leute im
          Umkreis überhaupt wissen, dass es Sie gibt. Die Kartons landen bei den Gastronomien
          im jeweiligen Stadtteil und damit auf dem Wohnzimmertisch, noch bevor die erste
          Scheibe gegessen ist – genau dort, wo Ihre ersten Kundinnen wohnen.
        </p>
        <p class="idee-meta">Empfohlen: Deckel groß</p>
      </article>

      <article class="idee">
        <p class="idee-claim">„Samstag, 20 Uhr. Kommt vorbei.“</p>
        <p>
          Konzerte, Stadtteilfeste, Vereinsjubiläen. Ein Termin mit QR-Code zum
          Ticketverkauf, sechs Wochen vorher auf dem Karton. Das erreicht Menschen, die
          keine Veranstaltungskalender lesen.
        </p>
        <p class="idee-meta">Empfohlen: Deckel mittel, mit QR-Code</p>
      </article>

      <article class="idee">
        <p class="idee-claim">Nur das Logo. Sonst nichts.</p>
        <p>
          Manche Betriebe brauchen keinen Spruch. Ein Handwerksbetrieb, den es seit vierzig
          Jahren gibt, setzt sein Zeichen auf den Deckel und lässt es wirken. Präsenz statt
          Botschaft – auch das ist eine Entscheidung.
        </p>
        <p class="idee-meta">Empfohlen: Deckel klein</p>
      </article>

    </div>
  </div>
</section>

<section class="band band-hell">
  <div class="wrap">
    <h2>Die Fun Area – die unkonventionelle Fläche</h2>
    <p>
      Auf der Unterseite jedes Kartons sammeln wir kleine, frei zuschneidbare Flächen –
      ab <?= e(preis((int) werbeformat('fun-area')['preis'])) ?> je nach gewählter Größe.
      Gedacht für kleine Betriebe, Start-ups, Vereine und Suchanzeigen, die mit wenig Fläche
      viel sagen wollen. Entdeckt wird sie, wenn jemand den leeren Karton hochhebt – was dort
      steht, muss keinen Zweck erfüllen.
    </p>

    <ul class="liste-check">
      <li>„TuS Sportverein sucht Trainer. Bezahlung: Respekt.“</li>
      <li>„3 Mitarbeitende gesucht. Kein Anschreiben nötig.“</li>
      <li>„Neu im Viertel: unser kleiner Laden um die Ecke.“</li>
      <li>„Marie, willst Du mich heiraten? – Der Typ mit der Salami“</li>
    </ul>

    <p>
      Wer eine gute Idee hat, sollte sie schnell buchen. Die Fun Area ist begrenzt, und
      erfahrungsgemäß sind es genau diese kleinen Flächen, über die am meisten geredet wird.
    </p>
  </div>
</section>

<section class="band">
  <div class="wrap schmal">
    <h2>Was wir nicht drucken</h2>
    <p>
      Wir behalten uns vor, Motive abzulehnen. Das ist keine Schikane, sondern der Grund,
      warum die Gastronomie bei uns mitmacht: Auf den Kartons soll nichts stehen, das
      einem Wirt seine Gäste vergrault.
    </p>
    <ul class="liste-check">
      <li>Keine Essens-Lieferdienste. Sie wären direkte Konkurrenz für genau die Gastronomien, denen wir helfen wollen.</li>
      <li>Nichts Politisches und nichts Religiöses.</li>
      <li>Keine Meinung ohne Ahnung.</li>
    </ul>
    <p>
      Alles andere besprechen wir. Wenn Sie unsicher sind, ob Ihre Idee durchgeht: fragen
      Sie einfach vorher. Wir sagen Ihnen ehrlich, was wir denken – auch wenn es bedeutet,
      dass wir eine Buchung nicht machen. Die vollständige Liste steht in den
      <a href="/agb.html">AGB</a>.
    </p>
  </div>
</section>

<section class="band band-cta">
  <div class="wrap schmal zentriert">
    <h2>Ihre Idee steht nicht dabei?</h2>
    <p>
      Umso besser. Die besten Motive sind die, an die vorher niemand gedacht hat. Erzählen
      Sie uns, was Sie vorhaben – wir sagen Ihnen, welches Format dafür passt und was es
      kostet.
    </p>
    <div class="hero-aktionen zentriert">
      <a href="/werbepartner.html" class="btn btn-primaer btn-gross">Formate und Preise ansehen</a>
      <a href="/kontakt.html" class="btn btn-sekundaer btn-gross">Idee besprechen</a>
    </div>
  </div>
</section>
