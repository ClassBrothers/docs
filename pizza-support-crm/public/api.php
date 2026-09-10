<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

Auth::start();
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

if (!Auth::angemeldet()) {
    http_response_code(401);
    echo json_encode(['fehler' => 'Nicht angemeldet.', 'login' => Auth::geschuetzt()], JSON_UNESCAPED_UNICODE);
    exit;
}

$aktion = (string) ($_GET['aktion'] ?? '');
$methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$eingabe = [];
if ($methode === 'POST') {
    $roh = file_get_contents('php://input') ?: '';
    $eingabe = json_decode($roh, true) ?: $_POST;
    if (!Auth::tokenPruefen($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($eingabe['csrf'] ?? null))) {
        http_response_code(419);
        echo json_encode(['fehler' => 'Sitzung abgelaufen — bitte Seite neu laden.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/** @param array<string,mixed> $daten */
function antwort(array $daten, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($daten, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function filterAusAnfrage(array $quelle): array
{
    $filter = ['kampagne' => (string) ($quelle['kampagne'] ?? 'pizzasupport')];
    foreach (['stadtteil', 'pizza_fokus', 'suche', 'tag', 'sort'] as $feld) {
        if (($quelle[$feld] ?? '') !== '') {
            $filter[$feld] = (string) $quelle[$feld];
        }
    }
    foreach (['prioritaet', 'stage'] as $feld) {
        $wert = $quelle[$feld] ?? '';
        if (is_string($wert) && $wert !== '') {
            $wert = explode(',', $wert);
        }
        if (is_array($wert) && $wert) {
            $filter[$feld] = array_values(array_filter($wert));
        }
    }
    foreach (['followup_faellig', 'ohne_kontakt'] as $feld) {
        if (!empty($quelle[$feld]) && $quelle[$feld] !== 'false' && $quelle[$feld] !== '0') {
            $filter[$feld] = true;
        }
    }
    if (!empty($quelle['ids'])) {
        $ids = is_array($quelle['ids']) ? $quelle['ids'] : explode(',', (string) $quelle['ids']);
        $filter['ids'] = array_values(array_filter(array_map('intval', $ids)));
    }
    return $filter;
}

try {
    switch ($aktion) {
        case 'start':
            antwort([
                'stages'      => PS_STAGES,
                'typen'       => PS_AKTIVITAETS_TYPEN,
                'filter'      => Repo::filterWerte(),
                'etiketten'   => array_map(static fn (array $e): string => $e['label'], PS_ETIKETTEN),
                'etikett_std' => (string) ps_cfg('etikettenformat', 'L7160'),
                'ziel'        => (int) ps_cfg('ziel_gewonnen', 50),
                'mail_aktiv'  => Mailer::konfiguriert(),
                'geschuetzt'  => Auth::geschuetzt(),
                'csrf'        => Auth::token(),
            ]);

        case 'betriebe':
            antwort(['betriebe' => Repo::liste(filterAusAnfrage($_GET))]);

        case 'betrieb':
            $betrieb = Repo::finde((int) ($_GET['id'] ?? 0));
            if (!$betrieb) {
                antwort(['fehler' => 'Betrieb nicht gefunden.'], 404);
            }
            antwort(['betrieb' => $betrieb, 'aktivitaeten' => Repo::aktivitaeten((int) $betrieb['id'])]);

        case 'dashboard':
            antwort(['dashboard' => Statistik::dashboard(), 'faellig' => Statistik::faellige('pizzasupport', (string) ($_GET['bis'] ?? 'woche'))]);

        case 'speichern':
            $id = (int) ($eingabe['id'] ?? 0);
            if ($id > 0) {
                Repo::aktualisieren($id, $eingabe);
            } else {
                if (trim((string) ($eingabe['name'] ?? '')) === '') {
                    antwort(['fehler' => 'Name ist erforderlich.'], 422);
                }
                $id = Repo::anlegen($eingabe);
            }
            antwort(['betrieb' => Repo::finde($id)]);

        case 'stage':
            antwort(['betrieb' => Repo::stageSetzen((int) ($eingabe['id'] ?? 0), (string) ($eingabe['stage'] ?? ''), (string) ($eingabe['notiz'] ?? ''))]);

        case 'aktivitaet':
            $betriebId = (int) ($eingabe['betrieb_id'] ?? 0);
            Repo::aktivitaetAnlegen($betriebId, $eingabe);
            antwort(['betrieb' => Repo::finde($betriebId), 'aktivitaeten' => Repo::aktivitaeten($betriebId)]);

        case 'aktivitaet_loeschen':
            Repo::aktivitaetLoeschen((int) ($eingabe['id'] ?? 0));
            antwort(['ok' => true]);

        case 'loeschen':
            Repo::loeschen((int) ($eingabe['id'] ?? 0));
            antwort(['ok' => true]);

        case 'vorlagen':
            antwort(['vorlagen' => Vorlagen::liste($_GET['art'] ?? null)]);

        case 'vorlage_speichern':
            $id = Vorlagen::speichern(((int) ($eingabe['id'] ?? 0)) ?: null, $eingabe);
            antwort(['vorlage' => Vorlagen::finde($id)]);

        case 'vorlage_loeschen':
            Vorlagen::loeschen((int) ($eingabe['id'] ?? 0));
            antwort(['ok' => true]);

        case 'vorschau':
            $betrieb = Repo::finde((int) ($_GET['id'] ?? 0));
            $vorlage = Vorlagen::finde((int) ($_GET['vorlage'] ?? 0));
            if (!$betrieb || !$vorlage) {
                antwort(['fehler' => 'Betrieb oder Vorlage nicht gefunden.'], 404);
            }
            antwort([
                'betreff' => Vorlagen::fuellen((string) $vorlage['betreff'], $betrieb),
                'koerper' => Vorlagen::fuellen((string) $vorlage['koerper'], $betrieb),
                'adresse' => Repo::adresse($betrieb),
            ]);

        case 'mail_senden':
            if (!Mailer::konfiguriert()) {
                antwort(['fehler' => 'SMTP ist nicht konfiguriert (config.local.php).'], 422);
            }
            $ergebnis = Mailer::anBetrieb((int) ($eingabe['id'] ?? 0), (string) ($eingabe['betreff'] ?? ''), (string) ($eingabe['koerper'] ?? ''));
            antwort(['gesendet' => $ergebnis, 'betrieb' => Repo::finde((int) $eingabe['id'])]);

        default:
            antwort(['fehler' => 'Unbekannte Aktion.'], 404);
    }
} catch (Throwable $e) {
    antwort(['fehler' => $e->getMessage()], 400);
}
