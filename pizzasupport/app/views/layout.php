<?php
/**
 * Gemeinsames Seitengeruest.
 *
 * Erwartet $meta und $inhalt aus render_seite().
 * Critical CSS steht inline (mit CSP-Nonce), der Rest wird ausgelagert
 * geladen. Skripte stehen am Ende und laufen mit defer.
 */

declare(strict_types=1);

$nonce   = $GLOBALS['csp_nonce'] ?? '';
$logo    = config('logo');
$fortsch = fortschritt();

$jsonld = array_merge([jsonld_organisation()], $meta['jsonld'] ?? []);
$graph  = ['@context' => 'https://schema.org', '@graph' => $jsonld];

$navigation = [
    '/'                                => 'Start',
    '/werbepartner.html'               => 'Für Unternehmen',
    '/teilnehmer.html'                 => 'Wer ist dabei',
    '/verpackungssteuer-freiburg.html' => 'Verpackungssteuer',
    '/ueber-uns.html'                  => 'Über uns',
    '/kontakt.html'                    => 'Kontakt',
];
$aktuell = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['titel']) ?></title>
<meta name="description" content="<?= e($meta['beschreibung']) ?>">
<meta name="robots" content="<?= e($meta['robots']) ?>">
<link rel="canonical" href="<?= e($meta['canonical']) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Pizza Support">
<meta property="og:locale" content="de_DE">
<meta property="og:title" content="<?= e($meta['titel']) ?>">
<meta property="og:description" content="<?= e($meta['beschreibung']) ?>">
<meta property="og:url" content="<?= e($meta['canonical']) ?>">
<meta property="og:image" content="<?= e($meta['og_bild']) ?>">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<meta name="theme-color" content="#241C18">

<?php /* Critical CSS: nur was ueber der Falz gebraucht wird. */ ?>
<style nonce="<?= e($nonce) ?>"><?php readfile(APP_ROOT . '/public/assets/css/critical.css'); ?></style>

<?php /* Der Rest blockiert das Rendern nicht. Der Lader traegt denselben
         Nonce wie das Critical CSS - Inline-Attribute wie onload waeren
         unter unserer Content-Security-Policy wirkungslos. */ ?>
<link rel="preload" id="css-haupt" href="<?= e(asset('/assets/css/main.css')) ?>" as="style">
<script nonce="<?= e($nonce) ?>">
(function(){var l=document.getElementById('css-haupt');
 if(!l)return;
 // Sobald die Datei da ist, wird aus dem Preload ein echtes Stylesheet.
 l.addEventListener('load',function(){l.rel='stylesheet';});
 l.rel='stylesheet';})();
</script>
<noscript><link rel="stylesheet" href="<?= e(asset('/assets/css/main.css')) ?>"></noscript>

<script type="application/ld+json"><?= json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</head>
<body class="<?= e($meta['body_klasse']) ?>">

<a class="skip-link" href="#inhalt">Zum Inhalt springen</a>

<header class="kopf" id="kopf">
  <div class="wrap kopf-innen">
    <a class="kopf-logo" href="/" aria-label="Pizza Support, zur Startseite">
      <img src="<?= e(asset($logo['src'])) ?>" width="<?= (int) $logo['width'] ?>" height="<?= (int) $logo['height'] ?>" alt="<?= e($logo['alt']) ?>" fetchpriority="high" decoding="async">
    </a>

    <button class="kopf-toggle" type="button" aria-expanded="false" aria-controls="hauptnav" aria-label="Menü öffnen">
      <span class="burger" aria-hidden="true"></span>
    </button>

    <nav class="kopf-nav" id="hauptnav" aria-label="Hauptnavigation">
      <ul>
        <?php foreach ($navigation as $ziel => $label): ?>
          <li><a href="<?= e($ziel) ?>"<?= $ziel === $aktuell ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn-primaer kopf-cta" href="/#bestellen">Jetzt bestellen</a>
    </nav>
  </div>
</header>

<?php if ($meldung = flash_get('fehler_global')): ?>
  <div class="wrap"><p class="hinweis hinweis-fehler" role="alert"><?= e((string) $meldung) ?></p></div>
<?php endif; ?>

<main id="inhalt">
<?= $inhalt ?>
</main>

<?php include APP_ROOT . '/app/views/partials/footer.php'; ?>

<?php if (!empty($meta['stoerer'])): ?>
  <?php include APP_ROOT . '/app/views/partials/stoerer.php'; ?>
<?php endif; ?>

<?php include APP_ROOT . '/app/views/partials/consent.php'; ?>

<script src="<?= e(asset('/assets/js/main.js')) ?>" defer></script>
</body>
</html>
