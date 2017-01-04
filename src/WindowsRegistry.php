<?php

namespace Amp\WindowsRegistry;

use Amp;

class WindowsRegistry {
    public function read($key) {
        return Amp\resolve(function () use ($key) {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                throw new \RuntimeException("Not running on Windows.");
            }

            $parts = explode("\\", $key = strtr($key, "/", "\\"));

            $value = array_pop($parts);
            $key = implode("\\", $parts);

            $process = new Amp\Process(["reg", "query", $key]);

            $result = (yield $process->exec(Amp\Process::BUFFER_ALL));

            if ($result->exit !== 0) {
                $debugInfo = "EXIT: {$result->exit}\n\nSTDOUT\n======\n\n{$result->stdout}\n\nSTDERR\n======\n\n{$result->stderr}\n";
                throw new \RuntimeException("Unknown error file getting key '{$key}\\{$value}'.\n\n$debugInfo");
            }

            $lines = explode("\n", str_replace("\r", "", $result->stdout));
            $lines = array_filter($lines, function ($line) {
                return strlen($line) && $line[0] === " ";
            });

            $values = array_map(function ($line) {
                return preg_split("(\\s+)", ltrim($line), 3);
            }, $lines);

            foreach ($values as $v) {
                if ($v[0] === $value) {
                    yield new Amp\CoroutineResult($v[2]);
                    return;
                }
            }

            throw new KeyNotFoundException("Windows registry key '{$key}\\{$value}' not found.");
        });
    }

    public function listKeys($key) {
        return Amp\resolve(function () use ($key) {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                throw new \RuntimeException("Not running on Windows.");
            }

            $key = strtr($key, "/", "\\");

            $process = new Amp\Process(["reg", "query", $key]);

            $result = (yield $process->exec(Amp\Process::BUFFER_ALL));

            if ($result->exit !== 0) {
                $debugInfo = "EXIT: {$result->exit}\n\nSTDOUT\n======\n\n{$result->stdout}\n\nSTDERR\n======\n\n{$result->stderr}\n";
                throw new \RuntimeException("Unknown error file getting key '{$key}'.\n\n$debugInfo");
            }

            $lines = explode("\n", str_replace("\r", "", $result->stdout));
            $lines = array_filter($lines, function ($line) {
                return strlen($line) && $line[0] !== " ";
            });

            yield new Amp\CoroutineResult($lines);
        });
    }
}