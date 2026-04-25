<?php

namespace App\Support;

class UploadLimits
{
    public static function maxUploadKilobytes(): int
    {
        $uploadLimit = self::iniBytes('upload_max_filesize');
        $postLimit = self::iniBytes('post_max_size');

        $limits = array_filter([$uploadLimit, $postLimit], fn (int $bytes) => $bytes > 0);
        $bytes = $limits === [] ? 2 * 1024 * 1024 : min($limits);

        return max(1, (int) floor($bytes / 1024));
    }

    public static function maxUploadLabel(): string
    {
        $kilobytes = self::maxUploadKilobytes();

        if ($kilobytes >= 1024) {
            return rtrim(rtrim(number_format($kilobytes / 1024, 1), '0'), '.').' MB';
        }

        return $kilobytes.' KB';
    }

    protected static function iniBytes(string $key): int
    {
        $value = ini_get($key);

        if ($value === false) {
            return 0;
        }

        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
