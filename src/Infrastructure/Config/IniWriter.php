<?php

declare(strict_types=1);

namespace Knowledgeroot\Infrastructure\Config;

/**
 * Lightweight replacement for Zend_Config_Writer_Ini.
 *
 * Renders a Config (or plain array) back to an ini string with sections.
 * Nested values below the section level are flattened to dotted keys
 * (params.host = "..."), matching the old Zend writer output.
 */
class IniWriter
{
    public function render(Config|array $config): string
    {
        $data = $config instanceof Config ? $config->toArray() : $config;

        $out = '';
        foreach ($data as $section => $values) {
            $out .= '[' . $section . "]\n";

            if (is_array($values)) {
                $out .= $this->renderValues($values);
            } else {
                $out .= $section . ' = ' . $this->formatValue($values) . "\n";
            }

            $out .= "\n";
        }

        return $out;
    }

    public function write(string $filename, Config|array $config): void
    {
        if (file_put_contents($filename, $this->render($config)) === false) {
            throw new \RuntimeException('Could not write config file: ' . $filename);
        }
    }

    /**
     * @param array<string, mixed> $values
     */
    private function renderValues(array $values, string $prefix = ''): string
    {
        $out = '';
        foreach ($values as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $out .= $this->renderValues($value, $fullKey);
            } else {
                $out .= $fullKey . ' = ' . $this->formatValue($value) . "\n";
            }
        }

        return $out;
    }

    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        if ($value === '' || preg_match('/^[0-9.]+$/', $value)) {
            return $value;
        }

        return '"' . str_replace('"', '\\"', $value) . '"';
    }
}
