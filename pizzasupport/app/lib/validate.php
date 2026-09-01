<?php
/**
 * Serverseitige Validierung. Das Frontend darf helfen, entscheiden tut
 * ausschliesslich diese Datei.
 */

declare(strict_types=1);

final class Validator
{
    private array $daten  = [];
    private array $fehler = [];

    public function __construct(private array $input) {}

    private function raw(string $feld): string
    {
        $v = $this->input[$feld] ?? '';
        if (!is_string($v)) {
            return '';
        }
        // Steuerzeichen raus, Whitespace normalisieren.
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? '';
        return trim($v);
    }

    public function text(string $feld, string $label, bool $pflicht = true, int $max = 200, int $min = 1): self
    {
        $v = $this->raw($feld);
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (mb_strlen($v) < $min) {
            $this->fehler[$feld] = $label . ' ist zu kurz.';
        } elseif (mb_strlen($v) > $max) {
            $this->fehler[$feld] = $label . ' ist zu lang (max. ' . $max . " Zeichen).";
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    public function email(string $feld, string $label, bool $pflicht = true): self
    {
        $v = mb_strtolower($this->raw($feld));
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (!filter_var($v, FILTER_VALIDATE_EMAIL) || mb_strlen($v) > 254) {
            $this->fehler[$feld] = 'Diese E-Mail-Adresse sieht nicht richtig aus.';
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    public function plz(string $feld, string $label, bool $pflicht = true): self
    {
        $v = $this->raw($feld);
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (!preg_match('/^\d{5}$/', $v)) {
            $this->fehler[$feld] = 'Die Postleitzahl braucht fünf Ziffern.';
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    public function telefon(string $feld, string $label, bool $pflicht = true): self
    {
        $v = $this->raw($feld);
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (!preg_match('/^[+0-9 ()\/\.\-]{6,32}$/', $v)) {
            $this->fehler[$feld] = 'Diese Telefonnummer können wir nicht lesen.';
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    public function url(string $feld, string $label, bool $pflicht = false): self
    {
        $v = $this->raw($feld);
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (!preg_match('~^https?://~i', $v)) {
            $v = 'https://' . $v;
        }
        if (!filter_var($v, FILTER_VALIDATE_URL) || mb_strlen($v) > 300) {
            $this->fehler[$feld] = 'Diese Adresse ist keine gültige Web-Adresse.';
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    public function auswahl(string $feld, string $label, array $erlaubt, bool $pflicht = true): self
    {
        $v = $this->raw($feld);
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (!in_array($v, $erlaubt, true)) {
            $this->fehler[$feld] = $label . ' ist keine gültige Auswahl.';
            $v = null;
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    /** Mehrfachauswahl, etwa die gebuchten Werbeformate. */
    public function mehrfach(string $feld, string $label, array $erlaubt, bool $pflicht = true): self
    {
        $roh = $this->input[$feld] ?? [];
        if (!is_array($roh)) {
            $roh = [$roh];
        }
        $out = [];
        foreach ($roh as $v) {
            if (is_string($v) && in_array($v, $erlaubt, true) && !in_array($v, $out, true)) {
                $out[] = $v;
            }
        }
        if ($out === [] && $pflicht) {
            $this->fehler[$feld] = $label . ' fehlt.';
        }
        $this->daten[$feld] = $out;
        return $this;
    }

    public function zahl(string $feld, string $label, int $min, int $max, bool $pflicht = true): self
    {
        $v = str_replace(['.', ' ', "\u{00A0}"], '', $this->raw($feld));
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (!preg_match('/^\d+$/', $v)) {
            $this->fehler[$feld] = $label . ' muss eine Zahl sein.';
            $this->daten[$feld] = null;
            return $this;
        }
        $n = (int) $v;
        if ($n < $min) {
            $this->fehler[$feld] = 'Die kleinste Menge sind ' . number_format($min, 0, ',', '.') . ' Kartons.';
        } elseif ($n > $max) {
            $this->fehler[$feld] = 'Für mehr als ' . number_format($max, 0, ',', '.') . ' Kartons melde Dich bitte direkt bei uns.';
        }
        $this->daten[$feld] = $n;
        return $this;
    }

    public function checkbox(string $feld, string $meldung, bool $pflicht = true): self
    {
        $an = !empty($this->input[$feld]);
        if ($pflicht && !$an) {
            $this->fehler[$feld] = $meldung;
        }
        $this->daten[$feld] = $an ? 1 : 0;
        return $this;
    }

    public function langtext(string $feld, string $label, bool $pflicht = true, int $max = 4000): self
    {
        $v = $this->input[$feld] ?? '';
        $v = is_string($v) ? trim($v) : '';
        if ($v === '') {
            if ($pflicht) {
                $this->fehler[$feld] = $label . ' fehlt.';
            }
            $this->daten[$feld] = null;
            return $this;
        }
        if (mb_strlen($v) > $max) {
            $this->fehler[$feld] = $label . ' ist zu lang (max. ' . $max . ' Zeichen).';
        }
        $this->daten[$feld] = $v;
        return $this;
    }

    public function fehlerSetzen(string $feld, string $meldung): self
    {
        $this->fehler[$feld] = $meldung;
        return $this;
    }

    public function ok(): bool     { return $this->fehler === []; }
    public function fehler(): array { return $this->fehler; }
    public function daten(): array  { return $this->daten; }
    public function get(string $feld) { return $this->daten[$feld] ?? null; }
}
