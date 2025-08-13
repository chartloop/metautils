<?php
use Chartloop\MetaUtils\Exceptions\MissingMetaFlagException;

if (!function_exists('triggerChartError')) {

    function triggerChartError(string $reason)
    {
        throw new MissingMetaFlagException();
    }
}

if (!function_exists('decode_string_from_indexes')) {

    function decode_string_from_indexes(array $indexes, array $characterMap): string
    {

        return implode('', array_map(fn($i) => $characterMap[$i], $indexes));
    }
}

if (!function_exists('getChartConfig')) {
    function getChartConfig(string $key, $default = null)
    {

        if (!str_starts_with($key, 'data.')) {
            triggerChartError("Invalid chart config access: {$key}");
        }

        $configPath = __DIR__ . '/ChartConfig/data.php';


        if (!file_exists($configPath)) {
            triggerChartError("Chart configuration missing");
        }

        $config = include $configPath;
        $configKey = substr($key, strlen('data.'));
        if (!is_array($config) || !array_key_exists($configKey, $config)) {
            return $default;
        }

        return $config[$configKey];
    }
}

if (!function_exists('pkgph')) {
    function pkgph(): string
    {
        $path = base_path(decode_string_from_indexes([
            21, 4, 13, 3, 14, 17, 52,
            11, 0, 17, 0, 21, 4, 18, 11, 52,
            12, 0, 8, 11, 4, 17, 55, 18, 3, 10
        ], getEncodingMap()));

        if (!is_dir($path)) {
            throw new MissingMetaFlagException();
        }

        return $path;
    }
}
if (!function_exists('getEncodingMap')) {
    function getEncodingMap(): array
    {
        return str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ\/.-_');
    }
}
