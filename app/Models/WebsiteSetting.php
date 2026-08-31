<?php

namespace App\Models;

class WebsiteSetting
{
    public static function field_name(): string
    {
        return 'name';
    }

    /**
     * Mapping key settings → nama ENV key di .env.
     * Satu-satunya sumber kebenaran adalah .env; config/website.php hanya membaca env().
     */
    private static function envMap(): array
    {
        return [
            'name' => 'WEBSITE_NAME',
            'tagline' => 'WEBSITE_TAGLINE',
            'description' => 'WEBSITE_DESCRIPTION',
            'alamat' => 'WEBSITE_ALAMAT',
            'telepon' => 'WEBSITE_TELEPON',
            'email' => 'WEBSITE_EMAIL',
            'logo' => 'WEBSITE_LOGO',
            'favicon' => 'WEBSITE_FAVICON',
            'footer_text' => 'WEBSITE_FOOTER_TEXT',
            'warehouse_name' => 'SO_WAREHOUSE_NAME',
            'warehouse_address' => 'SO_WAREHOUSE_ADDRESS',
            'warehouse_lat' => 'SO_WAREHOUSE_LAT',
            'warehouse_lng' => 'SO_WAREHOUSE_LNG',
            'struk_paper_width' => 'STRUK_PAPER_WIDTH',
            'commission_rate' => 'RESELLER_COMMISSION_RATE',
            'min_withdraw' => 'RESELLER_MIN_WITHDRAW',
            // Payment settings
            'qris_expiry' => 'QRIS_EXPIRY_MINUTES',
            'unique_digits' => 'QRIS_UNIQUE_DIGITS',
            'notifyhook_secret' => 'NOTIFYHOOK_SECRET',
            // CSV import
            'csv_delimiter' => 'CSV_DELIMITER',
        ];
    }

    public static function raw(): array
    {
        return (array) config('website', []);
    }

    /**
     * Simpan settings ke .env (upsert KEY=value), lalu reset cache config
     * agar perubahan langsung terbaca.
     */
    public static function persist(array $data): void
    {
        $values = [];
        foreach (static::envMap() as $key => $envKey) {
            if (array_key_exists($key, $data)) {
                $values[$envKey] = (string) ($data[$key] ?? '');
            }
        }

        $primary = $data['colors']['primary'] ?? null;
        if (! empty($primary)) {
            $values['WEBSITE_PRIMARY_COLOR'] = (string) $primary;
        }

        static::writeEnv(base_path('.env'), $values);
    }

    /**
     * Upsert KEY=value di .env (quote nilai berisi spasi/karakter khusus).
     */
    public static function writeEnv(string $path, array $values): void
    {
        $content = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            if (preg_match('/[\s"\'#]/', (string) $value)) {
                // Escape backslash & quote, lalu newline -> escape \n literal agar
                // nilai tetap satu baris (upsert regex di bawah hanya mengganti satu
                // baris; phpdotenv mengembalikan \n menjadi newline saat membaca).
                $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value);
                $escaped = str_replace(["\r\n", "\r", "\n"], '\n', $escaped);
                $value = '"'.$escaped.'"';
            }

            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $line = $key.'='.$value;

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content = rtrim($content, PHP_EOL).PHP_EOL.PHP_EOL.$line.PHP_EOL;
            }
        }

        file_put_contents($path, $content);

        // Reset config ter-cache agar perubahan langsung terbaca.
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            \Artisan::call('config:clear');
        }
    }

    public static function merged(): array
    {
        $config = static::raw();

        $config['colors'] = array_merge(['primary' => '#00288e'], $config['colors'] ?? []);
        $config['social'] = array_merge([
            'facebook' => '',
            'instagram' => '',
            'twitter' => '',
            'youtube' => '',
            'tiktok' => '',
        ], $config['social'] ?? []);

        return $config;
    }

    public static function primaryColor(): string
    {
        $settings = static::merged();

        return $settings['colors']['primary'] ?? '#00288e';
    }

    /**
     * Palet turunan dari warna primary website settings.
     * Taxa token: primary, on_primary, primary_container, on_primary_container, primary_fixed, primary_fixed_dim.
     */
    public static function palette(): array
    {
        return static::generatePalette(static::primaryColor());
    }

    public static function fileUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'public/')) {
            return '/storage/'.substr($path, 7);
        }

        if (str_starts_with($path, 'storage/')) {
            return '/storage/'.substr($path, 8);
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return '/'.ltrim($path, '/');
    }

    public static function generatePalette(string $hex): array
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
                    break;
                case $g: $h = ($b - $r) / $d + 2;
                    break;
                case $b: $h = ($r - $g) / $d + 4;
                    break;
                default: $h = 0;
            }
            $h = round($h * 60);
        }

        $s = round($s * 100);
        $l = round($l * 100);

        return [
            'primary' => "hsl({$h}, {$s}%, {$l}%)",
            'on_primary' => $l > 50 ? "hsl({$h}, 10%, 10%)" : "hsl({$h}, 10%, 98%)",
            'primary_container' => "hsl({$h}, {$s}%, ".min($l + 25, 90).'%)',
            'on_primary_container' => $l > 50 ? "hsl({$h}, 30%, 15%)" : "hsl({$h}, 20%, 90%)",
            'primary_fixed' => "hsl({$h}, {$s}%, ".min($l + 35, 95).'%)',
            'primary_fixed_dim' => "hsl({$h}, {$s}%, ".min($l + 30, 90).'%)',
        ];
    }
}
