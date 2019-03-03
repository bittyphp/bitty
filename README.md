# Bitty

[![Build Status](https://travis-ci.org/bittyphp/bitty.svg?branch=master)](https://travis-ci.org/bittyphp/bitty)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/e4d6cdab063548db9a00bd616cf992a0)](https://www.codacy.com/app/bittyphp/bitty)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Mutation Score](https://badge.stryker-mutator.io/github.com/bittyphp/bitty/master)](https://infection.github.io)
[![Total Downloads](https://poser.pugx.org/bittyphp/bitty/downloads)](https://packagist.org/packages/bittyphp/bitty)
[![License](https://poser.pugx.org/bittyphp/bitty/license)](https://packagist.org/packages/bittyphp/bitty)

A tiny and simple PHP framework. No fuss, no muss, no coconuts.

## Work in Progress

As a warning, this is a work in progress. Things may break randomly. Use at your own risk.

## Purpose

Bitty began as a learning experiment, but evolved into the desire to build something that followed [PSRs](https://www.php-fig.org/psr/) without adding too many non-standard additions. Several libraries have PSR-compliant implementations, but they add extra methods and unwanted dependencies. I wanted something without the bloat - something that was "itty bitty" (hence the name Bitty).

## Installation

It's best to install Bitty using [Composer](https://getcomposer.org/).

```sh
$ composer require bittyphp/bitty
```

Since Bitty doesn't force what view to use or what kind of controller you use, you may or may not want to also require the following:

```sh
# An abstract controller with common methods added for convenience
$ composer require bittyphp/controller

# A view based on the Twig engine
$ composer require bittyphp/view-twig
```

There's view layers for [Twig](https://github.com/bittyphp/view-twig), [Mustache](https://github.com/bittyphp/view-mustache), [Latte](https://github.com/bittyphp/view-latte), and [Plates](https://github.com/bittyphp/view-plates). If those aren't enough, you can also [make your own](https://github.com/bittyphp/view).

## Setup

Starting with Bitty is easy. The main application has shortcuts for adding routes, managing middleware, accessing the container, and registering services.

### Adding Routes

There are helper methods for adding routes for `get`, `post`, `put`, `patch`, `delete`, `options`, and a generic `map` for supporting multiple methods on the same route.

See Bitty's [Router docs](https://github.com/bittyphp/router) for more details.

```php
<?php

require(dirname(__DIR__).'/vendor/autoload.php');

use Bitty\Application;
use Bitty\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

$app = new Application();

$app->get('/', function (ServerRequestInterface $request) {
    return new Response('Hello, world!');
});

$app->patch('/foo', function (ServerRequestInterface $request) {
    return new Response('PATCHed /foo');
});

$app->map(['GET', 'POST'], '/foo', function (ServerRequestInterface $request) {
    return new Response('I support GET and POST');
});

$app->run();

```

### Managing Middleware

Bitty supports any [PSR-15](http://www.php-fig.org/psr/psr-15/) middleware. See the [Middleware docs](https://github.com/bittyphp/middleware) for more info.

```php
<?php

require(dirname(__DIR__).'/vendor/autoload.php');

use Bitty\Application;
use Psr\Http\Server\MiddlewareInterface;

$app = new Application();

/** @var MiddlewareInterface */
$myMiddleware = ...;

$app->add($myMiddleware);

$app->run();

```

### Accessing the Container

Bitty comes with a [PSR-11](http://www.php-fig.org/psr/psr-11/) container. See the [Container docs](https://github.com/bittyphp/container) for how you can manage it.

```php
<?php

require(dirname(__DIR__).'/vendor/autoload.php');

use Bitty\Application;

$app = new Application();

$container = $app->getContainer();

$app->run();

```

### Registering Services

You can easily register services with the container using any [service provider](https://github.com/container-interop/service-provider) implementation.

```php
<?php

require(dirname(__DIR__).'/vendor/autoload.php');

use Bitty\Application;
use Interop\Container\ServiceProviderInterface;

$app = new Application();

/** @var ServiceProviderInterface */
$myProvider = ...;

$app->register($myProvider);

$app->run();

```

## Standards

Bitty adheres the following framework standards:

- [PSR-1: Basic Coding Standard](http://www.php-fig.org/psr/psr-1/)
- [PSR-2: Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
- [PSR-4: Autoloading Standard](http://www.php-fig.org/psr/psr-4/)
- [PSR-7: HTTP Message Interface](http://www.php-fig.org/psr/psr-7/)
- [PSR-11: Container Interface](http://www.php-fig.org/psr/psr-11/)
- [PSR-15: HTTP Middleware](http://www.php-fig.org/psr/psr-15/)
- [PSR-17: HTTP Factories](http://www.php-fig.org/psr/psr-17/)

It also follows (or closely follows) some proposed standards:

- [PSR-14: Event Manager](https://github.com/php-fig/fig-standards/blob/master/proposed/event-manager.md)

## Lacking

Bitty does not have built-in support for the following. At least not yet.

- [PSR-3: Logger Interface](http://www.php-fig.org/psr/psr-3/)
- [PSR-6: Caching Interface](http://www.php-fig.org/psr/psr-6/)
  - Probably won't support ever, but maybe PSR-16 instead.
- [PSR-13: Hypermedia Links](http://www.php-fig.org/psr/psr-13/)
- [PSR-16: Simple Cache](http://www.php-fig.org/psr/psr-16/)

## Credits

Bitty follows some design ideas from [Symfony](https://symfony.com/), specifically in the realm of security. The main application follows a similar design as [Slim](https://www.slimframework.com/) and [Silex](https://silex.symfony.com/) (and possibly others).
