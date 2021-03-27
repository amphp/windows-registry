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
            throw new KeyNotFoundException("Windows registry key '{$key}\\{$value}' was found, but could not be read correctly, got " . \var_export($foundValue,
                    true));
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
        if (0 !== \stripos(\PHP_OS, 'WIN')) {
            throw new \Error('Not running on Windows.');
        }

        $key = \strtr($key, '/', "\\");

        $cmd = \sprintf('reg query %s', \escapeshellarg($key));
        $process = new Process($cmd);
        $process->start();

        $stdout = ByteStream\buffer($process->getStdout());
        $code = $process->join();

        if ($code !== 0) {
            throw new KeyNotFoundException("Windows registry key '{$key}' not found.");
        }

        return \explode("\n", \str_replace("\r", '', $stdout));
    }
}
