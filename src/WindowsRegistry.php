<?php

namespace Kelunik\WindowsRegistry;

use Amp;

class WindowsRegistry {
    public function read($key) {
        return Amp\resolve(function () use ($key) {
            $process = new Amp\Process(["cmd", "/c", dirname(__DIR__) . "\\res\\read.bat"], [
                "env" => [
                    "KEY_NAME" => $key,
                ],
            ]);

            return yield $process->exec(Amp\Process::BUFFER_ALL);
        });
    }
}