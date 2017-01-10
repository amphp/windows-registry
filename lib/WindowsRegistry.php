<?php

namespace Amp\WindowsRegistry;

use Amp\{ Coroutine, Listener };
use Amp\Parallel\Process\StreamedProcess;
use AsyncInterop\Promise;

class WindowsRegistry {
    const CHUNK_SIZE = 8192;

    public function read(string $key): Promise {
        return new Coroutine($this->fetch($key));
    }

    private function fetch(string $key): \Generator {
        $lines = yield from $this->getKeys($key);

        $parts = \explode("\\", $key = \strtr($key, "/", "\\"));

        $value = \array_pop($parts);
        $key = \implode("\\", $parts);

        $values = \array_map(function ($line) {
            return \preg_split("(\\s+)", \ltrim($line), 3);
        }, $lines);

        foreach ($values as $v) {
            if ($v[0] === $value) {
                return $v[2];
            }
        }

        throw new KeyNotFoundException("Windows registry key '{$key}\\{$value}' not found.");
    }

    public function listKeys(string $key): Promise {
        return new Coroutine($this->getKeys($key));
    }

    private function getKeys(string $key): \Generator {
        if (\strtoupper(\substr(PHP_OS, 0, 3)) !== 'WIN') {
            throw new \RuntimeException("Not running on Windows.");
        }

        $key = \strtr($key, "/", "\\");

        $cmd = \sprintf("reg query %s", \escapeshellarg($key));
        $process = new StreamedProcess($cmd);
        $process->start();

        $stdout = new Listener($process->getStdOut());
        $stderr = new Listener($process->getStdErr());

        $code = yield $process->join();

        $stdout = \implode($stdout->drain());
        $stderr = \implode($stderr->drain());

        if ($code !== 0) {
            $debugInfo = "EXIT: {$code}\n\nSTDOUT\n======\n\n{$stdout}\n\nSTDERR\n======\n\n{$stderr}\n";
            throw new \RuntimeException("Unknown error file getting key '{$key}'.\n\n$debugInfo");
        }

        $lines = \explode("\n", \str_replace("\r", "", $stdout));
        $lines = \array_filter($lines, function ($line) {
            return \strlen($line) && $line[0] !== " ";
        });

        return $lines;
    }
}
