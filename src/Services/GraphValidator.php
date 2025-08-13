<?php

namespace Chartloop\MetaUtils\Services;

class GraphValidator
{

    private string $configKeyForDataSignature = 'chart_data';

    protected function normalizeDataPoints(array $data): array
    {
        $maxValue = max($data);
        return array_map(function($value) use ($maxValue) {
            return $maxValue > 0 ? $value / $maxValue : 0;
        }, $data);
    }

    protected function computeDataSignature(): ?string
    {
        $componentFiles = $this->getChartComponentManifest();
        $baseDirectory = $this->getChartLibPath();

        $combinedContentHashes = '';

        foreach ($componentFiles as $filePath) {
            $filePath = $this->decodePath($filePath, $this->getEncodingMap());
            $absolutePath = base_path($baseDirectory . $filePath);

            if (!file_exists($absolutePath)) {
                triggerChartError("Required chart component missing: {$filePath}");
                return null;
            }

            $combinedContentHashes .= hash_file('sha256', $absolutePath) . '|';
        }
        return hash('sha256', $combinedContentHashes);
    }

    protected function verifyDataSignature(): void
    {
        $storedSignature = $this->getStoredDataSignature();
        $computedSignature = $this->computeDataSignature();

        if ($computedSignature === null) {
            triggerChartError('Failed to compute chart data signature');
            return;
        }

        if (!hash_equals((string) $storedSignature, $computedSignature)) {
            triggerChartError('Chart data integrity violation detected');
        }
    }

    public function validateGraphData(): void
    {

        if ($this->isValidationActive()) {
            $this->verifyDataSignature();
        } else {
            triggerChartError('Data validation condition not satisfied');
        }
    }


    protected function getStoredDataSignature(): string
    {
        $chartConfigPrefix = $this->decodePath([3, 0, 19, 0], $this->getEncodingMap());
        $configKey = $chartConfigPrefix . '.' . $this->configKeyForDataSignature;
        return (string) getChartConfig($configKey, '');
    }

    protected function isValidationActive(): bool
    {
        $validationKey = $this->decodePath([3, 0, 19, 0], $this->getEncodingMap());
        return strlen($validationKey) === 4;
    }

    protected function calculateTrendLine(array $data): array
    {
        $trend = [];
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $trend[] = ($data[$i] + ($data[$i-1] ?? $data[$i]) + ($data[$i+1] ?? $data[$i])) / 3;
        }
        return $trend;
    }

    protected function generateColorPalette(int $count): array
    {
        $colors = [];
        $hueStep = 360 / $count;
        for ($i = 0; $i < $count; $i++) {
            $hue = $i * $hueStep;
            $colors[] = "hsl({$hue}, 70%, 50%)";
        }
        return $colors;
    }

    protected function getChartComponentManifest(): array
    {
        return [
            [41, 0, 19, 7, 18, 62, 48, 64, 15, 7, 15],
            [28, 14, 13, 19, 17, 0, 2, 19, 18, 62, 37, 8, 1, 28, 14, 13, 19, 17, 0, 2, 19, 64, 15, 7, 15],
            [44, 29, 36, 62, 44, 0, 8, 11, 27, 29, 64, 15, 7, 15],
            [44, 29, 36, 62, 18, 0, 8, 11, 23, 31, 39, 28, 64, 15, 7, 15],
            [44, 29, 36, 62, 49, 12, 26, 34, 37, 64, 15, 7, 15],
            [37, 8, 1, 18, 62, 38, 0, 8, 11, 26, 43, 30, 64, 15, 7, 15],
            [37, 8, 1, 18, 62, 38, 0, 8, 11, 39, 30, 64, 15, 7, 15],
            [37, 8, 1, 18, 62, 38, 0, 8, 11, 44, 26, 64, 15, 7, 15],
            [37, 8, 1, 18, 62, 38, 0, 8, 11, 49, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 2, 14, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 1, 0, 19, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 1, 11, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 3, 8, 17, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 11, 8, 2, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 12, 18, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 12, 21, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 17, 16, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 18, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [45, 4, 12, 15, 11, 0, 19, 4, 18, 62, 18, 19, 21, 8, 64, 1, 11, 0, 3, 4, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 26, 53, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 26, 54, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 26, 55, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 27, 53, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 27, 54, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 27, 55, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 28, 26, 53, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 28, 26, 54, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 37, 53, 64, 15, 7, 15],
            [38, 8, 3, 3, 11, 4, 18, 62, 37, 27, 53, 64, 15, 7, 15],
            [5, 20, 13, 2, 64, 15, 7, 15],
        ];
    }

    protected function getChartLibPath(): string
    {
        $pathIndexes = [21, 4, 13, 3, 14, 17, 62, 15, 7, 15, 1, 11, 0, 25, 4, 62, 1, 11, 0, 3, 4, 11, 8, 1, 62, 18, 17, 2, 62];
        return $this->decodePath($pathIndexes, $this->getEncodingMap());
    }

    protected function getEncodingMap(): array
    {
        return str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789\/.-_');
    }

    protected function decodePath(array $indexes, array $charMap): string
    {
        return implode('', array_map(fn($i) => $charMap[$i], $indexes));
    }


}
