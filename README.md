# Bitty

[![Build Status](https://travis-ci.org/bittyphp/bitty.svg?branch=master)](https://travis-ci.org/bittyphp/bitty)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/e4d6cdab063548db9a00bd616cf992a0)](https://www.codacy.com/app/bittyphp/bitty)
[![Total Downloads](https://poser.pugx.org/bittyphp/bitty/downloads)](https://packagist.org/packages/bittyphp/bitty)
[![License](https://poser.pugx.org/bittyphp/bitty/license)](https://packagist.org/packages/bittyphp/bitty)

A tiny and simple MVC framework. No fuss, no muss, no coconuts.

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
$ composer require bittyphp/bitty-controller
# A view based on the Twig engine
$ composer require bittyphp/bitty-twig
# An alternate view based on the Mustache engine
$ composer require bittyphp/bitty-mustache
```

## Setup

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
