<?php
/**
 * Rendert eine Seite in das gemeinsame Layout.
 *
 * Jede Seitendatei in app/views/pages/ liefert ueber $meta ihre SEO-Daten
 * und gibt ihren Inhalt aus. Das Layout kuemmert sich um alles Uebrige.
 */

declare(strict_types=1);

function render_seite(string $seite, string $pfad): void
{
    $datei = APP_ROOT . '/app/views/pages/' . $seite . '.php';
    if (!is_file($datei)) {
        $datei = APP_ROOT . '/app/views/pages/404.php';
        $seite = '404';
    }

    // Voreinstellungen, die jede Seite ueberschreiben kann.
    $meta = [
        'titel'       => 'Pizza Support',
        'beschreibung'=> '',
        'canonical'   => url($pfad),
        'robots'      => 'index,follow',
        'og_bild'     => url('/assets/img/og-pizzasupport.png'),
        'jsonld'      => [],
        'body_klasse' => 'seite-' . $seite,
        'stoerer'     => true,
    ];

    ob_start();
    include $datei;                 // setzt $meta und gibt den Inhalt aus
    $inhalt = ob_get_clean();

    include APP_ROOT . '/app/views/layout.php';
}

/**
 * Strukturierte Daten der Organisation. Steht auf jeder Seite und macht
 * Suchmaschinen und Antwortmaschinen klar, wer hier spricht.
 */
function jsonld_organisation(): array
{
    return [
        '@type'       => 'Organization',
        '@id'         => url('/#organisation'),
        'name'        => 'Pizza Support',
        'url'         => url('/'),
        'logo'        => url(config('logo.src')),
        'description' => 'Pizza Support vermittelt kostenlose, werbefinanzierte Pizzakartons an die Freiburger Gastronomie.',
        'parentOrganization' => [
            '@type' => 'Organization',
            'name'  => config('firma.name'),
        ],
        'areaServed'  => [
            '@type' => 'City',
            'name'  => 'Freiburg im Breisgau',
        ],
        'email'       => config('firma.email'),
    ];
}

/** FAQ-Block als JSON-LD. Erwartet [['frage' => ..., 'antwort' => ...], ...] */
function jsonld_faq(array $faq): array
{
    $items = [];
    foreach ($faq as $f) {
        $items[] = [
            '@type'          => 'Question',
            'name'           => $f['frage'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => strip_tags($f['antwort']),
            ],
        ];
    }
    return ['@type' => 'FAQPage', 'mainEntity' => $items];
}

function jsonld_breadcrumb(array $stufen): array
{
    $items = [];
    $i = 1;
    foreach ($stufen as $name => $pfad) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i++,
            'name'     => $name,
            'item'     => url($pfad),
        ];
    }
    return ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/**
 * Einheitlicher FAQ-Ausgabeblock. Fragen als Ueberschriften, damit sowohl
 * Leser als auch Antwortmaschinen die Struktur sofort erfassen.
 */
function faq_block(array $faq, string $ueberschrift = 'Häufige Fragen'): string
{
    $html = '<section class="faq" aria-labelledby="faq-titel">'
          . '<h2 id="faq-titel">' . e($ueberschrift) . '</h2>'
          . '<div class="faq-liste">';
    foreach ($faq as $i => $f) {
        $id = 'faq-' . $i;
        $html .= '<details class="faq-item"' . ($i === 0 ? ' open' : '') . '>'
               . '<summary id="' . $id . '"><h3>' . e($f['frage']) . '</h3></summary>'
               . '<div class="faq-antwort">' . $f['antwort'] . '</div>'
               . '</details>';
    }
    return $html . '</div></section>';
}
