<div align="center" style="padding-bottom: 48px">
  <a href="https://assegaiphp.com/" target="blank"><img src="https://assegaiphp.com/images/logos/logo-cropped.png" width="200" alt="AssegaiPHP Logo"></a>
</div>

<p align="center">
  <a href="https://github.com/assegaiphp/common/releases"><img alt="Latest release" src="https://img.shields.io/github/v/release/assegaiphp/common?display_name=tag&sort=semver&style=flat-square"></a>
  <a href="https://github.com/assegaiphp/common/actions/workflows/php.yml"><img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/assegaiphp/common/php.yml?branch=main&label=tests&style=flat-square"></a>
  <img alt="PHP 8.4+" src="https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=flat-square&logo=php&logoColor=white">
  <a href="https://github.com/assegaiphp/common/blob/main/LICENSE"><img alt="License" src="https://img.shields.io/github/license/assegaiphp/common?style=flat-square"></a>
  <img alt="Status active" src="https://img.shields.io/badge/status-active-10b981?style=flat-square">
</p>

# AssegaiPHP Common

<p align="center">Shared HTTP, logging, path, exception, and queue primitives for AssegaiPHP packages.</p>

`assegaiphp/common` contains the small contracts and implementations that need to remain consistent across the framework ecosystem. It is infrastructure for first-party packages, not the AssegaiPHP application framework itself.

The package currently provides:

- an injectable HTTP client
- PSR-3-compatible logging support
- shared framework and HTTP exceptions
- path helpers and terminal color enumerations
- queue interfaces, typed job encoding, job-type discovery, and process results used by the official queue transports

## Requirements

- PHP 8.4 or newer

## Installation

```bash
composer require assegaiphp/common
```

## HTTP client

```php
use Assegai\Common\Http\HttpClient;
use GuzzleHttp\Client;

$client = new HttpClient(new Client());
$response = $client->get('https://example.com/api/status');
```

## Queue contracts

The queue contracts provide a transport-neutral boundary shared by packages such as `assegaiphp/beanstalkd` and `assegaiphp/rabbitmq`. `JsonQueueJobCodec` preserves a job's top-level class and JSON-safe state, while `QueueJobTypeResolver` discovers the domain type declared by a processor.

Applications normally consume these contracts through Core dependency injection and the official queue packages rather than constructing the transport boundary directly.

## Contributing

For contribution and pull request conventions, see [Commit and PR Guidelines](./docs/commit-and-pr-guidelines.md).

## License

This package is released under the [MIT license](./LICENSE).
