<?php

namespace App\Support;

use App\Models\Abbreviation;

class AbbreviationResolver
{
    /** @var array<string, string>|null */
    private static ?array $map = null;

    public function display(?string $code): string
    {
        $code = trim((string) $code);
        if ($code === '') {
            return '';
        }

        $map = $this->map();

        return $map[strtoupper($code)] ?? $code;
    }

    public function expandText(?string $text): string
    {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $map = $this->map();

        return (string) preg_replace_callback(
            '/\b([A-Za-z0-9]{2,20})\b/',
            static function (array $matches) use ($map): string {
                $key = strtoupper($matches[1]);

                return array_key_exists($key, $map) ? $map[$key] : $matches[1];
            },
            $text,
        );
    }

    public function flush(): void
    {
        self::$map = null;
    }

    /**
     * @return array<string, string>
     */
    private function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        self::$map = Abbreviation::query()
            ->orderBy('code')
            ->get(['code', 'full_form'])
            ->mapWithKeys(fn (Abbreviation $abbreviation): array => [
                strtoupper($abbreviation->code) => $abbreviation->full_form,
            ])
            ->all();

        return self::$map;
    }
}
