<?php
/**
 * Class Brothers GmbH für PizzaSupport
 * Zentrale Konfiguration.
 *
 * Alles, was sich fachlich aendern kann wird zentral hier gesteuert -> Preise, Formate, Mengen,
 * Schwellenwerte, Kontaktdaten. Wer Preise anpasst, muss keine einzige Vorlage anfassen.
 */

declare(strict_types=1);

return [

    // -----------------------------------------------------------------
    // Betreiber / Kontakt
    // -----------------------------------------------------------------
    'firma' => [
        'name'      => 'Class Brothers GmbH',
        'strasse'   => 'Im Hubhof 5',
        'plz_ort'   => '79112 Freiburg im Breisgau',
        'telefon'   => '+49 151 616 052 29',
        // Unverschluesselt und ohne Leerzeichen - der Schutz vor
        // Sammelprogrammen laeuft ueber email_link_html() aus
        // app/bootstrap.php (JavaScript-Obfuskierung), nicht mehr ueber
        // Leerzeichen im Wert. Leerzeichen wuerden mailto:-Links brechen.
        'email'     => 'hallo@pizzasupport.de',
        'gf'        => 'Geschäftsführer Sebastian Class',
        'hrb'       => 'HRB 713509, Amtsgericht Freiburg i. Br.',
        'ustid'     => 'DE301531400',
    ],
    'partner_gastro' => 'Badische Entertainment GmbH',

    // Auflagenhoehe der aktuellen Kampagne. Zentral, damit eine spaetere,
    // groessere Auflage keine Textaenderung an vielen Stellen braucht -
    // Reichweitenrechnungen und Erwaehnungen im Fliesstext lesen immer
    // von hier. Stand: 21.000 Kartons (5 Paletten a 4.200 Stueck), vom
    // Kunden bestaetigt - die frueher genannten 42.000 sind ungueltig.
    'auflage' => 21000,

    // Wird im Header/Footer und in den OG-Tags verwendet.
    // Liegt hier, damit ein Austausch der Logodatei ein Einzeiler bleibt.
    'logo' => [
        'src'    => '/assets/img/logo-pizzasupport.png',
        'width'  => 260,
        'height' => 168,
        'alt'    => 'Pizza Support – gemeinschaftliches Projekt für Ess-Kultur',
    ],

    // -----------------------------------------------------------------
    // Kartonformate fuer das Gastro-Bestellformular
    //
    // Reale Produktion laeuft aktuell auf 32 x 32 x 3 cm. Die uebrigen
    // Groessen sind Vermarktungsoptionen: Wir produzieren sie, sobald
    // genug Nachfrage fuer eine eigene Auflage zusammenkommt. Wer die
    // Auswahl reduzieren will, loescht hier Zeilen - sonst nichts.
    // -----------------------------------------------------------------
    'karton_formate' => [
        ['id' => '30', 'label' => '30 × 30 cm', 'hinweis' => 'Standard',            'default' => false, 'sofort' => false],
        ['id' => '32', 'label' => '32 × 32 cm', 'hinweis' => 'unsere Erstauflage',  'default' => true,  'sofort' => true],
        ['id' => '33', 'label' => '33 × 33 cm', 'hinweis' => 'Familienformat',      'default' => false, 'sofort' => false],
    ],
    'karton_hoehe_cm' => 3,   // vom Kunden bestaetigt: 3 cm, nicht die 4 cm aus der Flaechenplan-Legende

    // -----------------------------------------------------------------
    // Ersparnisrechner auf der Startseite. Grenzwerte gegen Unsinnseingaben,
    // die die oeffentliche Gesamtsumme wertlos machen wuerden - serverseitig
    // in app/forms/gastro.php geprueft, nicht nur im Browser.
    // -----------------------------------------------------------------
    'ersparnisrechner' => [
        'einkaufspreis_min_cent' => 5,     // 0,05 EUR
        'einkaufspreis_max_cent' => 300,   // 3,00 EUR
        'kartons_monat_min'      => 50,
        'kartons_monat_max'      => 20000,
    ],

    // Mengenlogik: drei Schnellauswahlen plus freie Eingabe. Eine Bestellung
    // darf mehrere Formate mischen - format_min gilt je gewaehltem Format,
    // min/max/step gelten fuer die Summe aller Formate zusammen.
    'mengen' => [
        'presets'    => [300, 500, 1000],
        'format_min' => 100,
        'min'        => 300,
        'max'        => 10000,
        'step'       => 50,
    ],

    // -----------------------------------------------------------------
    // Versand fuer die Gastro-Bestellung. Innerhalb Freiburgs kostenlos,
    // ausserhalb eine Pauschale je angefangene 300 Kartons. Wird in den
    // AGB (§ 3) referenziert - hier aendern, nicht dort.
    // -----------------------------------------------------------------
    'porto' => [
        'frei_in'        => 'Freiburg im Breisgau',
        'plz_von'        => '79098',   // Freiburg im Breisgau, PLZ-Bereich
        'plz_bis'        => '79117',
        // Angrenzende Gemeinden ausserhalb des PLZ-Bereichs, die trotzdem noch
        // kostenfrei beliefert werden. Ortsname wird gegen das Feld "Ort" im
        // Formular geprueft, ohne Gross-/Kleinschreibung. Vom Kunden nachgetragen.
        'freie_orte'     => [],
        'pauschale_cent' => 1000,  // 10,00 EUR netto je Staffel
        'je_kartons'     => 300,
    ],

    // -----------------------------------------------------------------
    // Lieferung und Abruf (Nachtrag 4+5): die bestellte Menge kann auf
    // einmal, in monatlichen Teilmengen oder per Selbstabholung ankommen.
    // -----------------------------------------------------------------
    'lieferung' => [
        'abruf_min'            => 300,   // Mindestmenge je monatlichem Abruf
        'abruf_zeitraum_monate' => 12,   // Abrufzeitraum ab der ersten Lieferung
        'zusatz_pauschale_cent' => 1000,  // 10,00 EUR netto fuer eine zweite Lieferung im selben Monat
        'zusatz_mindestmenge'  => 500,   // ab dieser Menge lohnt sich die Sonderfahrt
        'zusatz_frist_werktage' => 7,    // ab Abrufwunsch, nicht ab Bestellung
        // Adressen traegt der Kunde nach - Feld bleibt bis dahin leer.
        'abholung_standorte'   => [
            ['ort' => 'Freiburg-Tiengen', 'adresse' => ''],
            ['ort' => 'Freiburg-Haid', 'adresse' => ''],
        ],
    ],

    // Nachlass auf den Listen-Mediapreis, wenn das Motiv ein Gutschein ist.
    'coupon_rabatt_prozent' => 10,
    'mwst_prozent'          => 19,

    // Partnernachlass fuer buchende Werbepartner (Nachtrag 1 Punkt 6 + 5
    // Punkt 2): Rabatt auf Leistungen der vier Haeuser aus "Was wir sonst
    // so koennen", nicht auf die Werbebuchung selbst. Bewusst nicht im
    // Kopfbereich oder Buchungsformular als Kaufargument platziert, siehe
    // Vorteilsliste, Bestaetigung und "Was wir sonst so koennen".
    'partnernachlass' => [
        'prozent' => 20,
        'monate'  => 12,
    ],

    // -----------------------------------------------------------------
    // Flaechenkatalog: einzige Wahrheit ueber die 42 einzeln buchbaren
    // Werbeflaechen auf dem Karton. Jede Flaeche hat einen eigenen,
    // festen Preis - keine Pakete mehr, ein Unternehmen kann aber beliebig
    // viele einzelne Flaechen in einer Buchung waehlen (Mehrfachauswahl).
    // Bezugsmass ist das Kartonformat 32 × 32 cm, siehe AGB
    // "Bezugsmaß und Skalierung". Preise netto in Cent.
    //
    // 'buchbar' => false heisst: taucht in der Flaechenauswahl im
    // Buchungsformular nicht auf (Markenfeld, Boden-Marke, Deckelblende -
    // Flaechen, die dem Projekt selbst gehoeren, nicht verkauft werden).
    //
    // Preise und Codes stammen aus der vom Kunden bestaetigten finalen
    // Preisliste. Eine Abweichung dort: Die Preisliste nennt fuer die
    // aussen liegenden Seitenflaechen (BF/BH/BL/BR) 93 × 23 mm - die
    // Originalgrafik (flaechenplan-gross.jpg) beschriftet dieselben
    // Flaechen jedoch durchgaengig mit 9,3 × 2,5 cm (93 × 25 mm), und das
    // wurde in einem frueheren Durchgang bereits vom Kunden anhand der
    // Grafik bestaetigt. Hier daher bewusst 93 × 25 mm uebernommen - bitte
    // beim Kunden gegenpruefen, falls 93 × 23 mm doch beabsichtigt war.
    // Bei den innen liegenden Flaechen (DIN/DSL/DSR) gilt weiterhin die
    // separat vom Kunden bestaetigte Angabe 93 × 23 mm, keine Abweichung.
    // -----------------------------------------------------------------
    'flaechenkatalog' => [
        'gruppen' => [
            'deckel'       => 'Deckel',
            'seiten-aussen'=> 'Seiten außen',
            'seiten-innen' => 'Seiten innen (bei geöffnetem Deckel sichtbar)',
            'boden'        => 'Boden – StartUps & Selbständige',
        ],
        'flaechen' => [
            // Nicht buchbar: gehoeren dem Projekt selbst.
            ['id' => 'B0', 'bezeichnung' => 'Boden außen · Marke', 'masse' => '32 × 32 cm', 'gruppe' => 'boden', 'preis' => null, 'buchbar' => false],
            ['id' => 'FA', 'bezeichnung' => 'Fun Area, 16 Felder, schwarzweiß', 'masse' => '28 × 19,3 cm', 'gruppe' => 'boden', 'preis' => null, 'buchbar' => false],
            ['id' => 'DF', 'bezeichnung' => 'Deckelblende', 'masse' => '32 × 4 cm, unbedruckt', 'gruppe' => 'boden', 'preis' => null, 'buchbar' => false],

            // Deckel Klein: 88 × 40 mm, 880,00 € netto.
            ['id' => 'D3', 'bezeichnung' => 'Deckel Klein', 'masse' => '88 × 40 mm', 'gruppe' => 'deckel', 'preis' => 88000, 'buchbar' => true],
            ['id' => 'D5', 'bezeichnung' => 'Deckel Klein', 'masse' => '88 × 40 mm', 'gruppe' => 'deckel', 'preis' => 88000, 'buchbar' => true],
            ['id' => 'D7', 'bezeichnung' => 'Deckel Klein', 'masse' => '88 × 40 mm', 'gruppe' => 'deckel', 'preis' => 88000, 'buchbar' => true],

            // Deckel Square: 88 × 88 mm, 1.703,68 € netto. D1 ist das
            // PizzaSupport-Markenfeld und wird normalerweise manuell vom
            // Betreiber vergeben - eine zahlende Kundenbuchung hat aber
            // Vorrang, deshalb ganz normal buchbar wie D6/D8.
            ['id' => 'D1', 'bezeichnung' => 'Deckel Square', 'masse' => '88 × 88 mm', 'gruppe' => 'deckel', 'preis' => 170368, 'buchbar' => true],
            ['id' => 'D6', 'bezeichnung' => 'Deckel Square', 'masse' => '88 × 88 mm', 'gruppe' => 'deckel', 'preis' => 170368, 'buchbar' => true],
            ['id' => 'D8', 'bezeichnung' => 'Deckel Square', 'masse' => '88 × 88 mm', 'gruppe' => 'deckel', 'preis' => 170368, 'buchbar' => true],

            // Deckel Groß: 88 × 136 mm, 2.273,92 € netto.
            ['id' => 'D2', 'bezeichnung' => 'Deckel Groß', 'masse' => '88 × 136 mm', 'gruppe' => 'deckel', 'preis' => 227392, 'buchbar' => true],
            ['id' => 'D4', 'bezeichnung' => 'Deckel Groß', 'masse' => '88 × 136 mm', 'gruppe' => 'deckel', 'preis' => 227392, 'buchbar' => true],
            ['id' => 'D9', 'bezeichnung' => 'Deckel Groß', 'masse' => '88 × 136 mm', 'gruppe' => 'deckel', 'preis' => 227392, 'buchbar' => true],

            // Front außen: 93 × 25 mm, 256,68 € netto (siehe Hinweis oben zum Mass).
            ['id' => 'BF1', 'bezeichnung' => 'Front außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 25668, 'buchbar' => true],
            ['id' => 'BF2', 'bezeichnung' => 'Front außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 25668, 'buchbar' => true],
            ['id' => 'BF3', 'bezeichnung' => 'Front außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 25668, 'buchbar' => true],

            // Seiten L/R außen: 93 × 25 mm, 213,90 € netto.
            ['id' => 'BL1', 'bezeichnung' => 'Seite links außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 21390, 'buchbar' => true],
            ['id' => 'BL2', 'bezeichnung' => 'Seite links außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 21390, 'buchbar' => true],
            ['id' => 'BL3', 'bezeichnung' => 'Seite links außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 21390, 'buchbar' => true],
            ['id' => 'BR1', 'bezeichnung' => 'Seite rechts außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 21390, 'buchbar' => true],
            ['id' => 'BR2', 'bezeichnung' => 'Seite rechts außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 21390, 'buchbar' => true],
            ['id' => 'BR3', 'bezeichnung' => 'Seite rechts außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 21390, 'buchbar' => true],

            // Hinten außen: 93 × 25 mm, 192,51 € netto.
            ['id' => 'BH1', 'bezeichnung' => 'Hinten außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 19251, 'buchbar' => true],
            ['id' => 'BH2', 'bezeichnung' => 'Hinten außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 19251, 'buchbar' => true],
            ['id' => 'BH3', 'bezeichnung' => 'Hinten außen', 'masse' => '93 × 25 mm', 'gruppe' => 'seiten-aussen', 'preis' => 19251, 'buchbar' => true],

            // Front innen: 93 × 23 mm, 235,29 € netto.
            ['id' => 'DIN1', 'bezeichnung' => 'Front innen', 'masse' => '93 × 23 mm', 'gruppe' => 'seiten-innen', 'preis' => 23529, 'buchbar' => true],
            ['id' => 'DIN2', 'bezeichnung' => 'Front innen', 'masse' => '93 × 23 mm', 'gruppe' => 'seiten-innen', 'preis' => 23529, 'buchbar' => true],

            // Seiten L/R innen: 93 × 23 mm, 149,73 € netto.
            ['id' => 'DSL1', 'bezeichnung' => 'Seite links innen', 'masse' => '93 × 23 mm', 'gruppe' => 'seiten-innen', 'preis' => 14973, 'buchbar' => true],
            ['id' => 'DSL2', 'bezeichnung' => 'Seite links innen', 'masse' => '93 × 23 mm', 'gruppe' => 'seiten-innen', 'preis' => 14973, 'buchbar' => true],
            ['id' => 'DSR1', 'bezeichnung' => 'Seite rechts innen', 'masse' => '93 × 23 mm', 'gruppe' => 'seiten-innen', 'preis' => 14973, 'buchbar' => true],
            ['id' => 'DSR2', 'bezeichnung' => 'Seite rechts innen', 'masse' => '93 × 23 mm', 'gruppe' => 'seiten-innen', 'preis' => 14973, 'buchbar' => true],

            // StartUp-Feld S: 6,4 × 2,7 cm, 89,00 € netto.
            ['id' => 'SU-S1', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S2', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S3', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S4', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S5', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S6', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S7', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S8', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S9', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],
            ['id' => 'SU-S10', 'bezeichnung' => 'StartUp-Feld S', 'masse' => '6,4 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 8900, 'buchbar' => true],

            // StartUp-Feld M: 13,2 × 2,7 cm, 149,00 € netto.
            ['id' => 'SU-M1', 'bezeichnung' => 'StartUp-Feld M', 'masse' => '13,2 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 14900, 'buchbar' => true],
            ['id' => 'SU-M2', 'bezeichnung' => 'StartUp-Feld M', 'masse' => '13,2 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 14900, 'buchbar' => true],
            ['id' => 'SU-M3', 'bezeichnung' => 'StartUp-Feld M', 'masse' => '13,2 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 14900, 'buchbar' => true],
            ['id' => 'SU-M4', 'bezeichnung' => 'StartUp-Feld M', 'masse' => '13,2 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 14900, 'buchbar' => true],
            ['id' => 'SU-M5', 'bezeichnung' => 'StartUp-Feld M', 'masse' => '13,2 × 2,7 cm', 'gruppe' => 'boden', 'preis' => 14900, 'buchbar' => true],
        ],
    ],

    // -----------------------------------------------------------------
    // Startschuss-Prinzip: Ab hier laeuft die Produktion an.
    // Beide Werte muessen erreicht sein.
    // -----------------------------------------------------------------
    'startschuss' => [
        'betriebe'     => 50,
        'budget_cent'  => 4000000,   // 40.000 EUR netto gebuchtes Werbevolumen
        'anzahlung'    => 50,        // Prozent
        'lieferwochen' => '10–12',
    ],

    // Fuer die Beispielrechnung auf der Steuer-Seite.
    'steuer' => [
        'karton_cent'  => 50,   // Verpackungssteuer je Einwegverpackung
        'geschirr_cent'=> 50,
        'besteck_cent' => 20,
        'deckel_cent'  => 20,
    ],

    // Aufbewahrungsfristen in Tagen (bin/cleanup.php raeumt danach auf).
    'aufbewahrung' => [
        'abgelehnte_eintraege' => 90,
        'analytics_roh'        => 30,
        'rate_limit'           => 2,
        'newsletter_unbestaetigt' => 14,
    ],

    // -----------------------------------------------------------------
    // Sitemap: wird in public/index.php automatisch aus der Routing-Liste
    // ($seiten) gebaut, damit eine neue Seite dort ohne weiteren Eingriff
    // auch in der Sitemap auftaucht. Hier stehen nur zwei Dinge, die sich
    // nicht automatisch ableiten lassen:
    // -----------------------------------------------------------------

    // Pfade, die trotz Route nicht in die Sitemap sollen - Rechtstexte
    // bringen keinen Suchverkehr und die Bestaetigungsseite ist rein
    // transaktional.
    'sitemap_ausschluss' => [
        '/impressum.html',
        '/datenschutz.html',
        '/agb.html',
        '/newsletter-bestaetigt.html',
        '/werbebuchung-bestaetigt.html',
        // Inaktiv bis zur ersten Auslieferung, siehe fuer-gaeste.php.
        '/fuer-gaeste.html',
    ],

    // Priority/Changefreq je Seite. Was hier fehlt, bekommt die
    // Voreinstellung weiter unten in public/index.php - eine neu
    // hinzugefuegte Seite braucht also keinen Eintrag, kann aber bei
    // Bedarf einen bekommen.
    'sitemap_prioritaeten' => [
        '/'                                => ['prio' => '1.0', 'freq' => 'weekly'],
        '/werbepartner.html'               => ['prio' => '0.9', 'freq' => 'weekly'],
        '/flaeche-buchen.html'             => ['prio' => '0.9', 'freq' => 'weekly'],
        '/werbeideen.html'                 => ['prio' => '0.7', 'freq' => 'monthly'],
        '/teilnehmer.html'                 => ['prio' => '0.8', 'freq' => 'daily'],
        '/verpackungssteuer-freiburg.html' => ['prio' => '0.8', 'freq' => 'monthly'],
        '/ueber-uns.html'                  => ['prio' => '0.6', 'freq' => 'monthly'],
        '/kontakt.html'                    => ['prio' => '0.5', 'freq' => 'monthly'],
    ],

    // Gaeste-Abstimmung auf /fuer-gaeste.html: wie viele Motive je Frage
    // gleichzeitig ausgewaehlt werden duerfen.
    'abstimmung' => [
        'max_auswahl' => 3,
    ],
];
