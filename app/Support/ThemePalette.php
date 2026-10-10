<?php

namespace App\Support;

/**
 * Builds the --em-green-* colour scale (and the Bootstrap primary variables)
 * from a single brand colour, so a client can re-theme the app by choosing
 * one colour.
 *
 * Only values parsed from a validated #rrggbb string are ever written into
 * the generated CSS, never raw user input.
 */
class ThemePalette
{
    public const DEFAULT = '#047857';

    /** Minimum contrast against white text (WCAG AA for normal text). */
    public const MIN_CONTRAST = 4.5;

    public static function isHex(?string $hex): bool
    {
        return is_string($hex) && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1;
    }

    /** @return array{0:int,1:int,2:int} */
    public static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /** WCAG contrast ratio between the colour and white. */
    public static function contrastWithWhite(string $hex): float
    {
        $linear = array_map(function (int $channel): float {
            $c = $channel / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, self::rgb($hex));

        $luminance = 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];

        return 1.05 / ($luminance + 0.05);
    }

    /**
     * Colour scale keyed by shade (950 darkest … 50 lightest). Shade 700 is the
     * brand colour itself.
     *
     * @return array<string,string>
     */
    public static function scale(string $hex): array
    {
        [$h, $s, $l] = self::toHsl(...self::rgb($hex));
        $light = min($s, 0.9);

        return [
            '950' => self::fromHsl($h, $s, max(0.05, $l * 0.40)),
            '900' => self::fromHsl($h, $s, $l * 0.70),
            '800' => self::fromHsl($h, $s, $l * 0.85),
            '700' => strtolower($hex),
            '600' => self::fromHsl($h, $s, min($l + 0.06, 0.55)),
            '500' => self::fromHsl($h, $s * 0.88, min($l + 0.15, 0.62)),
            '200' => self::fromHsl($h, $light, 0.82),
            '100' => self::fromHsl($h, $light, 0.91),
            '50' => self::fromHsl($h, $light, 0.96),
        ];
    }

    /** CSS overriding the theme variables, or '' for the default colour. */
    public static function css(?string $hex): string
    {
        if (! self::isHex($hex) || strtolower($hex) === self::DEFAULT) {
            return '';
        }

        $scale = self::scale($hex);
        $rules = [];

        foreach ($scale as $shade => $value) {
            $rules[] = "--em-green-{$shade}:{$value}";
        }

        foreach (['700', '600', '500'] as $shade) {
            $rules[] = "--em-green-{$shade}-rgb:".implode(', ', self::rgb($scale[$shade]));
        }

        $rules[] = "--bs-primary:{$scale['700']}";
        $rules[] = '--bs-primary-rgb:'.implode(', ', self::rgb($scale['700']));

        return ':root{'.implode(';', $rules).'}';
    }

    /** @return array{0:float,1:float,2:float} hue 0-360, saturation and lightness 0-1 */
    private static function toHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        $h = match ($max) {
            $r => (($g - $b) / $d) + ($g < $b ? 6 : 0),
            $g => (($b - $r) / $d) + 2,
            default => (($r - $g) / $d) + 4,
        };

        return [$h * 60, $s, $l];
    }

    private static function fromHsl(float $h, float $s, float $l): string
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf('#%02x%02x%02x', round(($r + $m) * 255), round(($g + $m) * 255), round(($b + $m) * 255));
    }
}
