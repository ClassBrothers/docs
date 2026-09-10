<?php
declare(strict_types=1);

/**
 * Datenanreicherung: Website und Impressum suchen, Inhaber und E-Mail auslesen.
 *
 * Grundregel: nichts erfinden. Wird nichts Eindeutiges gefunden, bleibt das Feld leer
 * bzw. wird als "kein eigenes Impressum gefunden — telefonisch erfragen" markiert.
 */
final class Anreicherung
{
    private const AGENT = 'Mozilla/5.0 (compatible; PizzaSupportCRM/1.0; Recherche oeffentlicher Impressumsangaben)';
    private const PAUSE_SEKUNDEN = 2;

    /** Plattformen, die kein eigenes Impressum des Betriebs darstellen. */
    private const PLATTFORMEN = ['lieferando', 'wolt.com', 'ubereats', 'uber.com', 'takeaway.com', 'thefork', 'opentable', 'facebook.com', 'instagram.com', 'tripadvisor', 'yelp.', 'speisekarte.de', 'restaurant-kritik'];

    /**
     * @param array<int,array<string,mixed>> $betriebe
     * @param callable(string):void $melden Fortschrittsausgabe
     * @return array{gefunden:int,teilweise:int,kein_impressum:int,fehler:int}
     */
    public static function laufen(array $betriebe, callable $melden, bool $probelauf = false): array
    {
        $bericht = ['gefunden' => 0, 'teilweise' => 0, 'kein_impressum' => 0, 'fehler' => 0];

        foreach ($betriebe as $betrieb) {
            $melden(sprintf('· %s (%s)', $betrieb['name'], $betrieb['strasse']));
            try {
                $ergebnis = self::einenBetrieb($betrieb);
            } catch (Throwable $e) {
                $bericht['fehler']++;
                $melden('    Fehler: ' . $e->getMessage());
                continue;
            }

            $gefunden = array_filter([
                'Inhaber' => $ergebnis['inhaber'] ?? '',
                'E-Mail'  => $ergebnis['email'] ?? '',
                'Website' => $ergebnis['website'] ?? '',
            ]);
            $melden($gefunden
                ? '    ' . implode(' | ', array_map(static fn ($s, $w): string => "{$s}: {$w}", array_keys($gefunden), $gefunden))
                : '    nichts Eindeutiges gefunden');

            if (!$probelauf) {
                Repo::aktualisieren((int) $betrieb['id'], $ergebnis);
            }
            $status = $ergebnis['anreicherung_status'];
            if ($status === 'gefunden') {
                $bericht[($ergebnis['inhaber'] ?? '') !== '' && ($ergebnis['email'] ?? '') !== '' ? 'gefunden' : 'teilweise']++;
            } else {
                $bericht['kein_impressum']++;
            }
            sleep(self::PAUSE_SEKUNDEN);
        }
        return $bericht;
    }

    /** @return array<string,string> Felder fuer Repo::aktualisieren */
    public static function einenBetrieb(array $betrieb): array
    {
        $ergebnis = ['anreicherung_datum' => date('Y-m-d')];

        $website = trim((string) $betrieb['website']);
        if ($website === '' || Repo::istPlatzhalter($website)) {
            $website = self::websiteSuchen((string) $betrieb['name'], (string) $betrieb['strasse']);
        }

        if ($website === '' || self::istPlattform($website)) {
            $ergebnis['anreicherung_status'] = 'kein_impressum';
            $ergebnis['anreicherung_quelle'] = $website;
            if (Repo::istPlatzhalter((string) $betrieb['inhaber'])) {
                $ergebnis['inhaber'] = '';
            }
            $ergebnis['notiz_quelle'] = trim(((string) $betrieb['notiz_quelle'] . ' ') . 'kein eigenes Impressum gefunden — telefonisch erfragen');
            return $ergebnis;
        }

        $ergebnis['website'] = $website;
        $impressum = self::impressumFinden($website);
        $seite = $impressum !== '' ? self::holen($impressum) : '';
        if ($seite === '') {
            $seite = self::holen($website);
            $impressum = $website;
        }

        $inhaber = self::inhaberAuslesen($seite);
        $email = self::emailAuslesen($seite);

        if ($inhaber !== '') {
            $ergebnis['inhaber'] = $inhaber;
        }
        if ($email !== '') {
            $ergebnis['email'] = $email;
        }
        $ergebnis['anreicherung_status'] = ($inhaber !== '' || $email !== '') ? 'gefunden' : 'kein_impressum';
        $ergebnis['anreicherung_quelle'] = $impressum;
        return $ergebnis;
    }

    private static function istPlattform(string $url): bool
    {
        $klein = mb_strtolower($url);
        foreach (self::PLATTFORMEN as $plattform) {
            if (str_contains($klein, $plattform)) {
                return true;
            }
        }
        return false;
    }

    /** Website ueber die DuckDuckGo-HTML-Suche ermitteln (keine API noetig). */
    private static function websiteSuchen(string $name, string $strasse): string
    {
        $suche = trim("{$name} {$strasse} Freiburg Impressum");
        $html = self::holen('https://html.duckduckgo.com/html/?q=' . urlencode($suche));
        if ($html === '') {
            return '';
        }
        if (!preg_match_all('/<a[^>]+class="[^"]*result__a[^"]*"[^>]+href="([^"]+)"/i', $html, $treffer)) {
            return '';
        }
        foreach ($treffer[1] as $roh) {
            $url = html_entity_decode($roh);
            // DuckDuckGo verpackt Ziele teils in einen Weiterleitungs-Parameter.
            if (preg_match('/uddg=([^&]+)/', $url, $ziel)) {
                $url = urldecode($ziel[1]);
            }
            if (!str_starts_with($url, 'http') || self::istPlattform($url)) {
                continue;
            }
            return $url;
        }
        // Nur Plattformtreffer -> erste davon zurueckgeben, damit der Aufrufer das erkennt.
        $erste = html_entity_decode($treffer[1][0]);
        return preg_match('/uddg=([^&]+)/', $erste, $ziel) ? urldecode($ziel[1]) : $erste;
    }

    private static function impressumFinden(string $website): string
    {
        $html = self::holen($website);
        if ($html === '' || !preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>(.*?)<\/a>/is', $html, $treffer, PREG_SET_ORDER)) {
            return '';
        }
        foreach ($treffer as $link) {
            $text = mb_strtolower(strip_tags($link[2]));
            $ziel = html_entity_decode($link[1]);
            if (str_contains($text, 'impressum') || str_contains(mb_strtolower($ziel), 'impressum')) {
                return self::absolut($ziel, $website);
            }
        }
        return '';
    }

    private static function absolut(string $url, string $basis): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        $teile = parse_url($basis);
        $wurzel = ($teile['scheme'] ?? 'https') . '://' . ($teile['host'] ?? '');
        return $wurzel . '/' . ltrim($url, '/');
    }

    private static function inhaberAuslesen(string $html): string
    {
        $text = self::alsText($html);
        $muster = [
            '/(?:Inhaberin|Inhaber|Gesch(?:ä|ae)ftsf(?:ü|ue)hrerin|Gesch(?:ä|ae)ftsf(?:ü|ue)hrer|Vertretungsberechtigt(?:e[rn]?)?|Vertreten durch)\s*(?:\(in\))?\s*[:\-]?\s*(?:Herr|Frau)?\s*([A-ZÄÖÜ][\wÄÖÜäöüß.\'-]+(?:\s+[A-ZÄÖÜ][\wÄÖÜäöüß.\'-]+){1,3})/u',
        ];
        foreach ($muster as $regel) {
            if (preg_match($regel, $text, $treffer)) {
                $name = trim($treffer[1]);
                // Firmenbezeichnungen sind keine Personennamen.
                if (preg_match('/\b(GmbH|UG|OHG|KG|e\.K\.|GbR|AG|Betriebs|Gastronomie)\b/iu', $name)) {
                    continue;
                }
                return $name;
            }
        }
        return '';
    }

    private static function emailAuslesen(string $html): string
    {
        if (preg_match('/mailto:([^"\'?>\s]+@[^"\'?>\s]+)/i', $html, $treffer)) {
            $mail = html_entity_decode($treffer[1]);
            if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                return $mail;
            }
        }
        $text = self::alsText($html);
        // Auch verschleierte Schreibweisen beruecksichtigen.
        $text = preg_replace('/\s*\(at\)\s*|\s*\[at\]\s*|\s+at\s+/i', '@', $text) ?? $text;
        $text = preg_replace('/\s*\(punkt\)\s*|\s*\[dot\]\s*/i', '.', $text) ?? $text;
        if (preg_match_all('/[\w.+-]+@[\w-]+\.[\w.-]{2,}/u', $text, $treffer)) {
            foreach ($treffer[0] as $mail) {
                $mail = rtrim($mail, '.');
                if (filter_var($mail, FILTER_VALIDATE_EMAIL) && !preg_match('/\.(png|jpg|gif|webp)$/i', $mail)) {
                    return $mail;
                }
            }
        }
        return '';
    }

    private static function alsText(string $html): string
    {
        $ohneSkript = preg_replace('#<(script|style)[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $text = html_entity_decode(strip_tags($ohneSkript), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    private static function holen(string $url): string
    {
        $griff = curl_init($url);
        curl_setopt_array($griff, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 4,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_USERAGENT      => self::AGENT,
            CURLOPT_HTTPHEADER     => ['Accept-Language: de-DE,de;q=0.9'],
        ]);
        $inhalt = curl_exec($griff);
        $status = (int) curl_getinfo($griff, CURLINFO_RESPONSE_CODE);
        curl_close($griff);
        if (!is_string($inhalt) || $status >= 400) {
            return '';
        }
        // Auf UTF-8 vereinheitlichen, sonst zerlegen die Regexe die Umlaute.
        $kodierung = mb_detect_encoding($inhalt, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8';
        return $kodierung === 'UTF-8' ? $inhalt : mb_convert_encoding($inhalt, 'UTF-8', $kodierung);
    }
}
