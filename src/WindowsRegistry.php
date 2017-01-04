<?php

namespace Kelunik\WindowsRegistry;

use Amp;

class WindowsRegistry {
    public function read($key) {
        return Amp\resolve(function () use ($key) {
            $parts = explode("\\", $key = strtr($key, "/", "\\"));

            $value = array_pop($parts);
            $key = implode("\\", $parts);

            $process = new Amp\Process(["reg", "query", $key]);

            $result = yield $process->exec(Amp\Process::BUFFER_ALL);

            var_dump($result);

            $lines = explode("\n", str_replace("\r", "", $result->stdout));
            $lines = array_filter($lines, function ($line) {
                return strlen($line) && $line[0] !== " ";
            });

            $values = array_map(function ($line) {
                return preg_split("(\\s+)", $line, 3);
            }, $lines);

            foreach ($values as $v) {
                if ($v[0] === $value) {
                    return $v[2];
                }
            }

            throw new KeyNotFoundException("Windows registry key '{$key}\\{$value}' not found.");
        });
    }
}