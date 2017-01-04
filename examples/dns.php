<?php

require __DIR__ . "/../vendor/autoload.php";

# Read Windows DNS configuration

Amp\run(function () {
    $userKey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\Nameserver";
    $dhcpKey = "HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Services\\Tcpip\\Parameters\\DhcpNameserver";

    var_dump(yield (new Kelunik\WindowsRegistry\WindowsRegistry())->read($dhcpKey));
});