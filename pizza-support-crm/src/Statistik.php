<?php
declare(strict_types=1);

/** Kennzahlen fuer das Fortschritts-Dashboard. */
final class Statistik
{
    public static function dashboard(string $kampagne = 'pizzasupport'): array
    {
        $zaehle = static fn (string $bedingung, array $params = []): int => (int) Db::wert(
            "SELECT COUNT(*) FROM betriebe WHERE kampagne = ? AND {$bedingung}",
            array_merge([$kampagne], $params)
        );

        $gesamt = $zaehle('1=1');
        $gewonnen = $zaehle('pipeline_stage = ?', ['gewonnen']);
        $ziel = (int) ps_cfg('ziel_gewonnen', 50);

        $platzhalter = static fn (array $liste): string => implode(',', array_fill(0, count($liste), '?'));
        $kontaktiert = $zaehle('pipeline_stage IN (' . $platzhalter(PS_STAGES_KONTAKTIERT) . ')', PS_STAGES_KONTAKTIERT);
        $interessiert = $zaehle('pipeline_stage IN (' . $platzhalter(PS_STAGES_INTERESSIERT) . ')', PS_STAGES_INTERESSIERT);

        $proStage = [];
        foreach (PS_STAGES as $schluessel => $label) {
            $proStage[$schluessel] = ['label' => $label, 'anzahl' => $zaehle('pipeline_stage = ?', [$schluessel])];
        }

        $proPrio = [];
        foreach (['A', 'B', 'C'] as $prio) {
            $proPrio[$prio] = [
                'gesamt'   => $zaehle('prioritaet = ?', [$prio]),
                'gewonnen' => $zaehle('prioritaet = ? AND pipeline_stage = ?', [$prio, 'gewonnen']),
                'offen'    => $zaehle('prioritaet = ? AND kontaktversuche = 0', [$prio]),
            ];
        }

        $heute = date('Y-m-d');
        $wochenende = date('Y-m-d', strtotime('sunday this week'));

        return [
            'gesamt'          => $gesamt,
            'gewonnen'        => $gewonnen,
            'ziel'            => $ziel,
            'fortschritt'     => $ziel > 0 ? min(100, (int) round($gewonnen / $ziel * 100)) : 0,
            'fehlend'         => max(0, $ziel - $gewonnen),
            'funnel'          => [
                ['stufe' => 'Erfasst', 'anzahl' => $gesamt],
                ['stufe' => 'Kontaktiert', 'anzahl' => $kontaktiert],
                ['stufe' => 'Interessiert', 'anzahl' => $interessiert],
                ['stufe' => 'Gewonnen', 'anzahl' => $gewonnen],
            ],
            'pro_stage'       => $proStage,
            'pro_prioritaet'  => $proPrio,
            'ohne_kontakt'    => $zaehle('kontaktversuche = 0'),
            'faellig_heute'   => $zaehle("naechster_follow_up IS NOT NULL AND naechster_follow_up <> '' AND substr(naechster_follow_up,1,10) <= ?", [$heute]),
            'faellig_woche'   => $zaehle("naechster_follow_up IS NOT NULL AND naechster_follow_up <> '' AND substr(naechster_follow_up,1,10) <= ?", [$wochenende]),
            'kalt'            => $zaehle("kontaktversuche > 0 AND (naechster_follow_up IS NULL OR naechster_follow_up = '') AND letzter_kontakt IS NOT NULL AND julianday('now') - julianday(letzter_kontakt) > 7 AND pipeline_stage NOT IN ('gewonnen','abgelehnt','kein_interesse')"),
            'anreicherung'    => [
                'offen'         => $zaehle("anreicherung_status = 'offen'"),
                'gefunden'      => $zaehle("anreicherung_status = 'gefunden'"),
                'kein_impressum'=> $zaehle("anreicherung_status = 'kein_impressum'"),
                'mit_mail'      => $zaehle("email <> '' AND email LIKE '%@%'"),
                'mit_telefon'   => $zaehle("telefon <> ''"),
            ],
            'volumen_gewonnen' => (int) Db::wert('SELECT COALESCE(SUM(pizzenvolumen_monat),0) FROM betriebe WHERE kampagne = ? AND pipeline_stage = ?', [$kampagne, 'gewonnen']),
            'aktivitaeten_7t' => (int) Db::wert("SELECT COUNT(*) FROM aktivitaeten a JOIN betriebe b ON b.id = a.betrieb_id WHERE b.kampagne = ? AND julianday('now') - julianday(a.datum) <= 7", [$kampagne]),
        ];
    }

    /** @return array<int,array<string,mixed>> Faellige Wiedervorlagen. */
    public static function faellige(string $kampagne = 'pizzasupport', string $bis = 'heute'): array
    {
        $grenze = $bis === 'woche' ? date('Y-m-d', strtotime('sunday this week')) : date('Y-m-d');
        return array_values(array_filter(
            Repo::liste(['kampagne' => $kampagne, 'sort' => 'followup']),
            static fn (array $b): bool => !empty($b['naechster_follow_up']) && substr((string) $b['naechster_follow_up'], 0, 10) <= $grenze
        ));
    }
}
