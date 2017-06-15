<?php

use Amp\Loop;
use Amp\WindowsRegistry\KeyNotFoundException;

require __DIR__ . "/../vendor/autoload.php";

# Read Windows DNS configuration

Loop::run(function () {
    $keys = [
        "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\Nameserver",
        "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\DhcpNameServer",
    ];

    $reader = new Amp\WindowsRegistry\WindowsRegistry;
    $value = null;

    try {
        while ($key = array_shift($keys)) {
            try {
                $value = yield $reader->read($key);

                if ($value !== "") {
                    break;
                }
            } catch (KeyNotFoundException $e) { }
        }

        if ($value === null || $value === "") {
            print "Could not determine current DNS nameserver." . PHP_EOL;
            exit(1);
        }

        print "Current DNS Nameserver: {$value}" . PHP_EOL;
        exit(0);
    } catch (Exception $e) {
        print "Exception: " . $e->getMessage() . PHP_EOL;
        exit(2);
    } catch (Throwable $e) {
        print "Exception: " . $e->getMessage() . PHP_EOL;
        exit(2);
    }
});