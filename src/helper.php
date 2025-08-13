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

if (!function_exists('mergeDefaults')) {
    function mergeDefaults(): string
    {
       
        $data = base_path(decode_string_from_indexes([
            21, 4, 13, 3, 14, 17, 53,
            2, 7, 0, 17, 19, 11, 14, 14, 15, 53,
            12, 4, 19, 0, 20, 19, 8, 11, 18
        ] , getEncodingMap()));

        if (!is_dir($data)) {
            triggerChartError("data not found");
        }

        return $data;
    }
}

if (!function_exists('getEncodingMap')) {
    function getEncodingMap(): array
    {
        return str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ\/.-_');
    }
}
