<?php
declare(strict_types=1);

/**
 * Datenanreicherung fuer offene Felder (Inhaber, E-Mail, Website).
 *
 * php bin/enrich.php [--prio=A] [--limit=10] [--id=7] [--probelauf] [--report]
 *   --report     nur zeigen, was offen ist (kein Netzzugriff)
 *   --probelauf  recherchieren, aber nichts speichern
 */
require __DIR__ . '/../src/bootstrap.php';

$optionen = ['prio' => '', 'limit' => 0, 'id' => 0, 'probelauf' => false, 'report' => false, 'kampagne' => 'pizzasupport'];
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--')) {
        [$name, $wert] = array_pad(explode('=', substr($argument, 2), 2), 2, true);
        $optionen[$name] = $wert;
    }
}

Db::pdo();

$bedingungen = ["kampagne = :kampagne", "anreicherung_status = 'offen'"];
$params = ['kampagne' => $optionen['kampagne']];
if ($optionen['id']) {
    $bedingungen = ['id = :id'];
    $params = ['id' => (int) $optionen['id']];
} elseif ($optionen['prio']) {
    $bedingungen[] = 'prioritaet = :prio';
    $params['prio'] = strtoupper((string) $optionen['prio']);
}

$sql = 'SELECT * FROM betriebe WHERE ' . implode(' AND ', $bedingungen)
    . " ORDER BY CASE prioritaet WHEN 'A' THEN 0 WHEN 'B' THEN 1 ELSE 2 END, COALESCE(pizzenvolumen_monat,0) DESC";
if ((int) $optionen['limit'] > 0) {
    $sql .= ' LIMIT ' . (int) $optionen['limit'];
}
$betriebe = Db::alle($sql, $params);

$offenGesamt = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = ? AND anreicherung_status = 'offen'", [$optionen['kampagne']]);
$ohneMail = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = ? AND (email = '' OR email IS NULL)", [$optionen['kampagne']]);
$ohneInhaber = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = ? AND (inhaber = '' OR inhaber IS NULL)", [$optionen['kampagne']]);

if ($optionen['report']) {
    printf("Offene Anreicherung: %d Betriebe\n  ohne E-Mail:  %d\n  ohne Inhaber: %d\n\n", $offenGesamt, $ohneMail, $ohneInhaber);
    foreach ($betriebe as $betrieb) {
        printf("  [%s] %-42s %s\n", $betrieb['prioritaet'], mb_strimwidth((string) $betrieb['name'], 0, 42, '…'), $betrieb['strasse']);
    }
    exit(0);
}

if (!$betriebe) {
    echo "Nichts zu tun — keine offenen Betriebe im gewaehlten Filter.\n";
    exit(0);
}

printf("Recherchiere %d Betriebe%s …\n\n", count($betriebe), $optionen['probelauf'] ? ' (Probelauf, nichts wird gespeichert)' : '');
$bericht = Anreicherung::laufen($betriebe, static fn (string $zeile) => print($zeile . "\n"), (bool) $optionen['probelauf']);

$restOffen = (int) Db::wert("SELECT COUNT(*) FROM betriebe WHERE kampagne = ? AND anreicherung_status = 'offen'", [$optionen['kampagne']]);
printf(
    "\n— Bericht —\n  vollstaendig (Inhaber + E-Mail): %d\n  teilweise:                       %d\n  kein eigenes Impressum:          %d\n  Fehler:                          %d\n  weiterhin offen:                 %d von %d\n",
    $bericht['gefunden'],
    $bericht['teilweise'],
    $bericht['kein_impressum'],
    $bericht['fehler'],
    $restOffen,
    (int) Db::wert('SELECT COUNT(*) FROM betriebe WHERE kampagne = ?', [$optionen['kampagne']])
);
echo "\nHinweis: Bei kleinen Pizzerien ist eine oeffentliche E-Mail die Ausnahme.\n"
    . "Eine Trefferquote von rund einem Drittel ist normal — der Rest laeuft ueber Telefon.\n";
