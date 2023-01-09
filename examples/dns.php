<?php declare(strict_types=1);

use Amp\WindowsRegistry\KeyNotFoundException;
use Amp\WindowsRegistry\WindowsRegistry;

require __DIR__ . "/../vendor/autoload.php";

# Read Windows DNS configuration

$keys = [
    "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\NameServer",
    "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\DhcpNameServer",
];

$nameserver = "";

while ($nameserver === "" && ($key = array_shift($keys))) {
    try {
        $nameserver = WindowsRegistry::read($key) ?? '';
    } catch (KeyNotFoundException $e) {
    }
}

if ($nameserver === "") {
    $subKeys = WindowsRegistry::listKeys("HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\Interfaces");

    foreach ($subKeys as $key) {
        foreach (["NameServer", "DhcpNameServer"] as $property) {
            try {
                $nameserver = WindowsRegistry::read("{$key}\\{$property}") ?? '';

                if ($nameserver !== "") {
                    break 2;
                }
            } catch (KeyNotFoundException $e) {
            }
        }
    }
}

if ($nameserver !== "") {
    // Microsoft documents space as delimiter, AppVeyor uses comma.
    $nameservers = array_map(function ($ns) {
        return trim($ns) . ":53";
    }, explode(" ", strtr($nameserver, ",", " ")));

    print "Found nameservers: " . implode(", ", $nameservers) . PHP_EOL;
} else {
    print "No nameservers found." . PHP_EOL;
}
