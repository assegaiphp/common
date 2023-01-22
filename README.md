<div align="center" style="padding-bottom: 48px">
    <a href="https://assegaiphp.com/" target="blank"><img src="https://assegaiphp.com/images/logos/logo-cropped.png" width="200" alt="Assegai Logo"></a>
</div>

<p style="text-align: center">A progressive <a href="https://php.net">PHP</a> framework for building effecient and scalable server-side applications.</p>


# AssegaiPHP Common Library

Welcome to the AssegaiPHP common library! This package provides a set of common classes and utilities that can be used in any AssegaiPHP project.

The package includes the following features:

- HttpClient class: A simple and flexible class for performing HTTP requests. It supports `GET`, `POST`, `PUT`, `DELETE` and other request methods, and can also handle request headers and parameters.
- Utility classes: A collection of classes for common tasks such as logging, path manipulation, and more.

All classes are designed to be injectable and can be easily integrated into any AssegaiPHP application.

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