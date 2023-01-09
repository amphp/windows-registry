# amphp/windows-registry

AMPHP is a collection of event-driven libraries for PHP designed with fibers and concurrency in mind.
`amphp/windows-registry` provides asynchronous access to the Windows Registry.

[![Release](https://img.shields.io/github/release/amphp/windows-registry.svg?style=flat-square)](https://github.com/amphp/windows-registry/releases)
![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)

## Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require amphp/windows-registry
```

## Usage

`WindowsRegistry` has the two static methods `listKeys` and `read`:

 - `listKeys` fetches all sub-keys of one key.
 - `read` reads the value of the key.
   Note that `read` doesn't convert any values and returns them as they're printed by `reg query %s`.

## Versioning

`amphp/windows-registry` follows the [semver](http://semver.org/) semantic versioning specification like all other `amphp` packages.

## Security

If you discover any security related issues, please email [`me@kelunik.com`](mailto:me@kelunik.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
