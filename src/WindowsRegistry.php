<?php

namespace Kelunik\WindowsRegistry;

use Amp;

class WindowsRegistry {
    public function read($key) {
        return Amp\resolve(function () use ($key) {
            $script = <<<'SCRIPT'
@echo off
setlocal ENABLEEXTENSIONS
set VALUE_NAME=DefaultColor
for /F "usebackq tokens=3" %%A IN (`reg query "%KEY_NAME%" /v "%VALUE_NAME%" 2^>nul ^| find "%VALUE_NAME%"`) do (
echo %%A
)
SCRIPT;

            $process = new Amp\Process($script, [
                "env" => [
                    "KEY_NAME" => $key,
                ],
            ]);

            return yield $process->exec(Amp\Process::BUFFER_ALL);
        });
    }
}