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
    'karton_hoehe_cm' => 3,

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
        'pauschale_cent' => 1000,  // 10,00 EUR netto je Staffel
        'je_kartons'     => 300,
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
            'masse'   => '93 × 23 mm',
            'preis'   => 69900,    // 699,00 EUR netto
            'brutto'  => false,
            'text'    => 'Schmaler Streifen an der Kartonseite. Sichtbar im Stapel.',
        ],
        [
            'id'      => 'fun-area',
            'gruppe'  => 'Fun Area',
            'label'   => 'Fun Area (Boden)',
            'masse'   => 'Sammelfläche auf der Kartonunterseite',
            'preis'   => 790,      // 7,90 EUR inkl. 19 % MwSt.
            'brutto'  => true,
            'text'    => 'Für Privatleute: Gruß, Spruch, Heiratsantrag. Wird beim Aufmachen entdeckt.',
        ],
    ],

    // Nachlass auf den Listen-Mediapreis, wenn das Motiv ein Gutschein ist.
    'coupon_rabatt_prozent' => 10,
    'mwst_prozent'          => 19,

    // -----------------------------------------------------------------
    // Startschuss-Prinzip: Ab hier laeuft die Produktion an.
    // Beide Werte muessen erreicht sein.
    // -----------------------------------------------------------------
    'startschuss' => [
        'betriebe'     => 40,
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
