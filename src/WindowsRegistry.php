<?php

namespace Kelunik\WindowsRegistry;

use Amp;

class WindowsRegistry {
    public function read($key) {
        return Amp\resolve(function () use ($key) {
            $parts = explode("\\", $key = strtr($key, "/", "\\"));

            $value = array_pop($parts);
            $key = implode("\\", $parts);

            $process = new Amp\Process(["cmd", "/c", dirname(__DIR__) . "\\res\\read.bat"], [
                "env" => [
                    "KEY_NAME" => $key,
                    "VALUE_NAME" => $value,
                ],
            ]);

            return yield $process->exec(Amp\Process::BUFFER_ALL);
        });
    }
}