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
        // Mit Leerzeichen geschrieben, um Sammelprogramme abzuwehren. Fuer
        // mailto:-Links immer firma_email_link() aus app/bootstrap.php nutzen,
        // die die Leerzeichen entfernt - config('firma.email') bleibt fuer
        // die Anzeige unveraendert.
        'email'     => 'hallo @ pizzasupport . de',
        'gf'        => 'Geschäftsführer Sebastian Class',
        'hrb'       => 'HRB 713509, Amtsgericht Freiburg i. Br.',
        'ustid'     => 'DE301531400',
    ],
    'partner_gastro' => 'Badische Entertainment GmbH',

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

    // -----------------------------------------------------------------
    // Werbeformate. Preise in Cent, damit nichts durch Rundung verrutscht.
    // -----------------------------------------------------------------
    'werbeformate' => [
        [
            'id'      => 'deckel-klein',
            'gruppe'  => 'Deckel',
            'label'   => 'Deckel klein',
            'masse'   => '88 × 40 mm',
            'preis'   => 172480,   // 1.724,80 EUR netto
            'brutto'  => false,
            'text'    => 'Der Einstieg. Logo, Claim, QR-Code – mehr braucht es oft nicht.',
        ],
        [
            'id'      => 'deckel-mittel',
            'gruppe'  => 'Deckel',
            'label'   => 'Deckel mittel',
            'masse'   => '88 × 88 mm',
            'preis'   => 340736,   // 3.407,36 EUR netto
            'brutto'  => false,
            'text'    => 'Quadratisch, gut sichtbar, genug Platz für ein Angebot mit Bild.',
        ],
        [
            'id'      => 'deckel-gross',
            'gruppe'  => 'Deckel',
            'label'   => 'Deckel groß',
            'masse'   => '88 × 136 mm',
            'preis'   => 490688,   // 4.906,88 EUR netto
            'brutto'  => false,
            'text'    => 'Die Fläche, die man aus zwei Metern Entfernung noch liest.',
        ],
        [
            'id'      => 'seite',
            'gruppe'  => 'Seite',
            'label'   => 'Seitenfläche',
            'masse'   => '93 × 25 mm',
            'preis'   => 69900,    // 699,00 EUR netto
            'brutto'  => false,
            'text'    => 'Schmaler Streifen an der Kartonseite. Sichtbar im Stapel.',
        ],
        [
            'id'      => 'fun-area',
            'gruppe'  => 'Fun Area',
            'label'   => 'Fun Area (Boden)',
            'masse'   => 'Sammelfläche auf der Kartonunterseite, frei wählbare Größe ab 12 cm²',
            // Vorlaeufig der Mindestpreis (12 cm² × 7,99 EUR/cm², inkl. 19 % MwSt.), bis die
            // flaechenbasierte Buchung im Formular steht - siehe Nachtrag 5, Punkt 1.
            'preis'   => 9588,     // ab 95,88 EUR inkl. 19 % MwSt., je nach gewählter Fläche
            'brutto'  => true,
            'text'    => 'Kleine Fläche, große Wirkung: für kleine Betriebe, Start-ups, Vereine und Suchanzeigen. Wird beim Aufmachen entdeckt.',
        ],
    ],

    // Nachlass auf den Listen-Mediapreis, wenn das Motiv ein Gutschein ist.
    'coupon_rabatt_prozent' => 10,
    'mwst_prozent'          => 19,

    // -----------------------------------------------------------------
    // Fun Area: Preis nach tatsaechlicher Flaeche statt Festpreis
    // (Nachtrag 2+5). Flaeche wird auf eine Nachkommastelle gerundet,
    // danach mit dem Preis je cm² multipliziert.
    // -----------------------------------------------------------------
    'fun_area' => [
        'preis_je_cm2_cent'  => 799,   // 7,99 EUR je cm², inkl. MwSt.
        'mindestflaeche_cm2' => 12,
        'schnellauswahl' => [
            ['label' => 'Feld S', 'breite_cm' => 6.4, 'hoehe_cm' => 2.7],
            ['label' => 'Feld M', 'breite_cm' => 13.2, 'hoehe_cm' => 2.7],
        ],
    ],

    // -----------------------------------------------------------------
    // Flaechenkatalog: die Kennungen aus dem Flaechenplan (Nachtrag 2).
    // Einzige Wahrheit ueber einzelne, benannte Flaechen auf dem Karton -
    // die Wunschflaeche-Auswahl im Formular und die Bestaetigungsmails
    // greifen darauf zu. Bezugsmass ist das Kartonformat 32 × 32 cm, siehe
    // AGB "Bezugsmaß und Skalierung".
    //
    // 'paket' verweist auf eine 'id' aus 'werbeformate' oben - die
    // Preisbindung laeuft weiterhin ueber die dortigen Paket-Checkboxen,
    // hier geht es nur um die gewuenschte Position innerhalb des gebuchten
    // Pakets. 'buchbar' => false heisst: taucht in der Wunschflaeche-Auswahl
    // im Formular nicht auf (siehe Nachtrag 2, Punkt 2: "Nicht buchbare
    // Flaechen erscheinen nicht").
    //
    // Nach Erhalt der Original-Grafik (FINAL_Pizzakarton_Layout_Flaechenplan_020926.jpg)
    // mit lesbarer Legende korrigiert:
    // - Seitenflaeche BH/BL/BR: 9,3 × 2,5 cm laut Legende (vorher versehentlich
    //   an die alte Website-Angabe "93 × 23 mm" angeglichen). Das bestehende
    //   werbeformate-Paket "seite" oben ist entsprechend mitgezogen (93 × 25 mm).
    // - DF (Deckelblende): Legende nennt 32 × 4 cm, das Feld selbst im Layout
    //   ist mit "32 x 3 cm" beschriftet - ein Widerspruch im Kundendokument,
    //   der sich aber nicht auswirkt (nicht buchbar).
    //
    // Weiterhin offen aus Nachtrag 2, Punkt 0 - der Kunde muss noch bestaetigen:
    // - BF1-3: im Plan ohne Preis, deshalb vorerst nicht buchbar.
    // - DSL/DSR: 12 × 1 cm aus der Legende uebernommen, das Layout selbst
    //   beschriftet dieselben Felder mit 9,3 × 2,5 cm - unbedruckt und nicht
    //   buchbar, die genaue Zahl wirkt sich deshalb auf nichts aus.
    // -----------------------------------------------------------------
    'flaechenkatalog' => [
        'gruppen' => [
            'deckel' => 'Deckel',
            'boden'  => 'Boden',
            'seiten' => 'Seiten',
        ],
        'flaechen' => [
            ['id' => 'D1',  'bezeichnung' => 'Markenfeld + Claim',            'masse' => '8,8 × 8,8 cm',  'gruppe' => 'deckel', 'paket' => null,           'buchbar' => false],
            ['id' => 'D2',  'bezeichnung' => 'Motiv A',                      'masse' => '8,8 × 13,6 cm', 'gruppe' => 'deckel', 'paket' => 'deckel-gross', 'buchbar' => true],
            ['id' => 'D3',  'bezeichnung' => 'Slot',                         'masse' => '8,8 × 4,0 cm',  'gruppe' => 'deckel', 'paket' => 'deckel-klein', 'buchbar' => true],
            ['id' => 'D4',  'bezeichnung' => 'Motiv B',                      'masse' => '8,8 × 13,6 cm', 'gruppe' => 'deckel', 'paket' => 'deckel-gross', 'buchbar' => true],
            ['id' => 'D5',  'bezeichnung' => 'Zeile',                        'masse' => '8,8 × 4,0 cm',  'gruppe' => 'deckel', 'paket' => 'deckel-klein', 'buchbar' => true],
            ['id' => 'D6',  'bezeichnung' => 'Square',                       'masse' => '8,8 × 8,8 cm',  'gruppe' => 'deckel', 'paket' => 'deckel-mittel','buchbar' => true],
            ['id' => 'D7',  'bezeichnung' => 'Absender',                     'masse' => '8,8 × 4,0 cm',  'gruppe' => 'deckel', 'paket' => 'deckel-klein', 'buchbar' => true],
            ['id' => 'D8',  'bezeichnung' => 'Square',                       'masse' => '8,8 × 8,8 cm',  'gruppe' => 'deckel', 'paket' => 'deckel-mittel','buchbar' => true],
            ['id' => 'D9',  'bezeichnung' => 'Motiv C',                      'masse' => '8,8 × 13,6 cm', 'gruppe' => 'deckel', 'paket' => 'deckel-gross', 'buchbar' => true],

            ['id' => 'BF1', 'bezeichnung' => 'Bodenfront · Partnerslot',     'masse' => '7,6 × 2,6 cm',  'gruppe' => 'boden',  'paket' => null,           'buchbar' => false],
            ['id' => 'BF2', 'bezeichnung' => 'Bodenfront · Partnerslot',     'masse' => '7,6 × 2,6 cm',  'gruppe' => 'boden',  'paket' => null,           'buchbar' => false],
            ['id' => 'BF3', 'bezeichnung' => 'Bodenfront · Partnerslot',     'masse' => '7,6 × 2,6 cm',  'gruppe' => 'boden',  'paket' => null,           'buchbar' => false],
            ['id' => 'B0',  'bezeichnung' => 'Boden außen · Marke',          'masse' => '32 × 32 cm',    'gruppe' => 'boden',  'paket' => null,           'buchbar' => false],
            ['id' => 'FA',  'bezeichnung' => 'Fun Area, 16 Felder, schwarzweiß', 'masse' => '28 × 19,3 cm', 'gruppe' => 'boden', 'paket' => 'fun-area',  'buchbar' => false],
            ['id' => 'DF',  'bezeichnung' => 'Deckelblende',                 'masse' => '32 × 4 cm, unbedruckt',    'gruppe' => 'boden',  'paket' => null,           'buchbar' => false],

            ['id' => 'BH1', 'bezeichnung' => 'Boden Rückwand',               'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BH2', 'bezeichnung' => 'Boden Rückwand',               'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BH3', 'bezeichnung' => 'Boden Rückwand',               'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BL1', 'bezeichnung' => 'Boden Seite links',            'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BL2', 'bezeichnung' => 'Boden Seite links',            'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BL3', 'bezeichnung' => 'Boden Seite links',            'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BR1', 'bezeichnung' => 'Boden Seite rechts',           'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BR2', 'bezeichnung' => 'Boden Seite rechts',           'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'BR3', 'bezeichnung' => 'Boden Seite rechts',           'masse' => '9,3 × 2,5 cm',  'gruppe' => 'seiten', 'paket' => 'seite',        'buchbar' => true],
            ['id' => 'DSL', 'bezeichnung' => 'Deckelseite links',            'masse' => '12 × 1 cm',     'gruppe' => 'seiten', 'paket' => null,           'buchbar' => false],
            ['id' => 'DSR', 'bezeichnung' => 'Deckelseite rechts',           'masse' => '12 × 1 cm',     'gruppe' => 'seiten', 'paket' => null,           'buchbar' => false],
        ],
    ],

    // -----------------------------------------------------------------
    // Startschuss-Prinzip: Ab hier laeuft die Produktion an.
    // Beide Werte muessen erreicht sein.
    // -----------------------------------------------------------------
    'startschuss' => [
        'betriebe'     => 50,
        'budget_cent'  => 6000000,   // 60.000 EUR netto gebuchtes Werbevolumen
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
    ],

    // Priority/Changefreq je Seite. Was hier fehlt, bekommt die
    // Voreinstellung weiter unten in public/index.php - eine neu
    // hinzugefuegte Seite braucht also keinen Eintrag, kann aber bei
    // Bedarf einen bekommen.
    'sitemap_prioritaeten' => [
        '/'                                => ['prio' => '1.0', 'freq' => 'weekly'],
        '/werbepartner.html'               => ['prio' => '0.9', 'freq' => 'weekly'],
        '/werbeideen.html'                 => ['prio' => '0.7', 'freq' => 'monthly'],
        '/teilnehmer.html'                 => ['prio' => '0.8', 'freq' => 'daily'],
        '/verpackungssteuer-freiburg.html' => ['prio' => '0.8', 'freq' => 'monthly'],
        '/ueber-uns.html'                  => ['prio' => '0.6', 'freq' => 'monthly'],
        '/kontakt.html'                    => ['prio' => '0.5', 'freq' => 'monthly'],
    ],

    // Mindestanzeige fuer die Fortschrittszahlen auf der Startseite, damit
    // die Seite zum Start nicht mit Nullen dasteht. Sobald die echten
    // Zahlen darueber liegen, zeigen wir ausschliesslich die echten Zahlen -
    // die Schwellen fuer den Startschuss selbst (oben unter 'startschuss')
    // rechnen immer mit den echten Werten, unabhaengig von dieser Anzeige.
    'fortschritt_mindestanzeige' => [
        'betriebe'    => 16,
        'unternehmen' => 5,
        'kartons'     => 5500,
    ],
];
