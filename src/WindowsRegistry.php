<?php

namespace Amp\WindowsRegistry;

use Amp\ByteStream;
use Amp\Process\Process;

final class WindowsRegistry
{
    public function read(string $key): ?string
    {
        $key = \strtr($key, '/', "\\");
        $parts = \explode("\\", $key);

        $value = \array_pop($parts);
        $key = \implode("\\", $parts);

        $lines = $this->query($key);

        $lines = \array_filter($lines, static function ($line) {
            return '' !== $line && $line[0] === ' ';
        });

        $values = \array_map(static function ($line) {
            return \preg_split("(\\s+)", \ltrim($line), 3);
        }, $lines);

        $foundValue = null;

        foreach ($values as $v) {
            if ($v[0] === $value) {
                if (\count($v) >= 3) {
                    return $v[2];
                }

                $foundValue = $v;
            }
        }

        if ($foundValue) {
            throw new KeyNotFoundException("Windows registry key '{$key}\\{$value}' was found, but could not be read correctly, got " . \var_export(
                $foundValue,
                true
            ));
        }

        throw new KeyNotFoundException("Windows registry key '{$key}\\{$value}' not found.");
    }

    public function listKeys(string $key): array
    {
        $lines = $this->query($key);

        $lines = \array_filter($lines, static function ($line) {
            return '' !== $line && $line[0] !== ' ';
        });

        return $lines;
    }

    private function query(string $key): array
    {
        if (\PHP_OS_FAMILY !== 'Windows') {
            throw new \Error('Not running on Windows: ' . \PHP_OS_FAMILY);
        }

        $process = Process::start(['reg', 'query', \strtr($key, '/', "\\")]);
        $stdout = ByteStream\buffer($process->getStdout());
        $exitCode = $process->join();

        if ($exitCode !== 0) {
            throw new KeyNotFoundException("Windows registry key '{$key}' not found.");
        }

        return \explode("\n", \str_replace("\r", '', $stdout));
    }
}
