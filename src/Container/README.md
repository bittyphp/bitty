# Bitty Container

Bitty comes with a [PSR-11](http://www.php-fig.org/psr/psr-11/) compliant container. The container supports registering service providers that follow the (experimental) [service provider standard](https://github.com/container-interop/service-provider).

## Checking for an Entry

If needed, you can check if a container entry exists before requesting the container for it.

```php
<?php

use Bitty\Container\Container;

$container = new Container(...);

if ($container->has('some_thing')) {
    echo 'some_thing is available';
}
```

## Getting an Entry

Getting an entry from the container is also easy. However, if the entry doesn't exist, the container will throw a `NotFoundException`.

```php
<?php

use Bitty\Container\Container;

$container = new Container(...);

$someThing = $container->get('some_thing');
```

## Adding an Entry

You can add entries to Bitty's container a few different ways. All entries are adding using anonymous functions. When you first request an entry from the container, it calls the anonymous function and caches the result. The result can be anything - a string, an array, an object - you name it. On subsequent calls, you are given the same value each time.

The function will always be called with the container itself as the first argument. This allows you to reference other items from the container, if needed. If you do not need access to the container, you can omit the parameter from the function signature.

```php
<?php

use Acme\MyClass;
use Psr\Container\ContainerInterface;

// Access dependencies from the container
$service = function (ContainerInterface $container) {
    $dependency = $container->get('some_service');

    return new MyClass($dependency);
};

// Omit the container parameter if not needed
$parameter = function () {
    return 'some-value';
};
```

### Via the Constructor

The most direct way to add entries is by passing them all in when you build the container. However, this is probably not the most practical way to do things. The array key is the entry name and the value is an anonymous function that builds the thing you want to access.

```php
<?php

use Acme\MyClass;
use Acme\MyOtherClass;
use Bitty\Container\Container;
use Psr\Container\ContainerInterface;

$container = new Container(
    [
        // Adding a parameter
        'some_parameter' => function () {
            return 'i-need-this-value';
        },

        // Adding services
        'my_service' => function () {
            return new MyClass();
        },
        'my_other_service' => function (ContainerInterface $container) {
            $myParam   = $container->get('some_parameter');
            $myService = $container->get('my_service');

            return new MyOtherClass(myParam, $myService);
        },
    ]
);
```

### Via a Setter

Another method is to set services one at a time.

```php
<?php

use Acme\MyClass;
use Acme\MyOtherClass;
use Bitty\Container\Container;

$container = new Container();

$container->set('my_service', function ($container) {
    return new MyClass();
});

$container->set('my_other_service', function ($container) {
    $myService = $container->get('my_service');

    return new MyOtherClass($myService);
});
```

### Via a Service Provider

The last, and most extensible, option is to build a service provider using  `Interop\Container\ServiceProviderInterface` and pass it to the container or late register it using the `register()` method.

You can build a service provider to load service settings from anywhere - an XML file, YAML file, or maybe even JSON. More information is available in the Service Provider section.

## Extending an Entry

You can extend a container entry using the `extend()` method. Extensions can also be used to check for an existing entry and create one if not found. The function signature is similar to adding entries, except now there will be a second (nullable) parameter passed in.

You can also extend entries using service providers.

```php
<?php

use Acme\MyClass;
use Acme\MyOtherClass;
use Bitty\Container\Container;
use Psr\Container\ContainerInterface;

$container = new Container(
    [
        'my_service' => function () {
            return new MyClass();
        },
    ]
);

$container->extend('my_service', function (ContainerInterface $container, $previous = null) {
    if (null === $previous) {
        $previous = new MyClass();
    }

    return new MyOtherClass($previous);
});
```

## Service Providers

Service providers allow you to build or extend container services more easily and in a more portable way. It supports any implementation of `Interop\Container\ServiceProviderInterface` which means you can easily register the same service in different applications or projects.

WARNING: This is based on a [developing standard](https://github.com/container-interop/service-provider) and may change.

```php
<?php

namespace Acme;

use Interop\Container\ServiceProviderInterface;

class MyServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'my_service' => function () {
                return new MyClass();
            },
        ];
    }

    public function getExtensions()
    {
        return [
            'my_service' => function (ContainerInterface $container, $previous = null) {
                $myParam = $container->get('some_parameter');

                return new MyOtherClass($myParam, $previous);
            },
        ];
    }
}
```

Then pass your provider(s) into the container.

```php
<?php

use Acme\MyServiceProvider;
use Bitty\Container\Container;

$container = new Container(
    [
        // Services not built by the provider
    ],
    [
        new MyServiceProvider(),
        // ...
    ]
);

// Or registering it/them
$container->register(
    [
        new MyServiceProvider(),
        // ...
    ]
);
```

## Making Container Aware Services

You can make any service automatically aware of the container by making your service implement the `ContainerAwareInterface`. When the service is built, it will be passed a reference to the container. There's a `ContainerAwareTrait` you can add to classes to make implementing the interface easier.
