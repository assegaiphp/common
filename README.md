<div align="center" style="padding-bottom: 48px">
    <a href="https://assegaiphp.com/" target="blank"><img src="https://assegaiphp.com/images/logos/logo-cropped.png" width="200" alt="Assegai Logo"></a>
</div>

<p align="center">
  <a href="https://github.com/assegaiphp/common/releases"><img alt="Latest release" src="https://img.shields.io/github/v/release/assegaiphp/common?display_name=tag&sort=semver&style=flat-square"></a>
  <a href="https://github.com/assegaiphp/common/actions/workflows/php.yml"><img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/assegaiphp/common/php.yml?branch=main&label=tests&style=flat-square"></a>
  <img alt="PHP 8.4+" src="https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=flat-square&logo=php&logoColor=white">
  <a href="https://github.com/assegaiphp/common/blob/main/LICENSE"><img alt="License" src="https://img.shields.io/github/license/assegaiphp/common?style=flat-square"></a>
  <img alt="Status active" src="https://img.shields.io/badge/status-active-10b981?style=flat-square">
</p>

<p align="center">A progressive <a href="https://php.net">PHP</a> framework for building effecient and scalable server-side applications.</p>


# AssegaiPHP Common Library

Welcome to the AssegaiPHP common library! This package provides a set of common classes and utilities that can be used in any AssegaiPHP project.

The package includes the following features:

- HttpClient class: A simple and flexible class for performing HTTP requests. It supports `GET`, `POST`, `PUT`, `DELETE` and other request methods, and can also handle request headers and parameters.
- Utility classes: A collection of classes for common tasks such as logging, path manipulation, and more.

All classes are designed to be injectable and can be easily integrated into any AssegaiPHP application.

## Contribution workflow

For commit and pull request conventions in this repo, see:

- [docs/commit-and-pr-guidelines.md](./docs/commit-and-pr-guidelines.md)

To install the package, simply require it in your composer.json file:

```BASH
composer require assegaiphp/common
```

You can then use the classes in your application by importing the namespace:

```PHP
use Assegai\Common\Http\HttpClient;
```

You can also use the `#[Injectable]` attribute to easily inject the HttpClient class into your controllers and services.

We hope you find the AssegaiPHP common library useful in your projects! If you have any questions or feedback, please open an issue on the [GitHub] repository.

[GitHub]: https://github.com/assegaiphp/common
