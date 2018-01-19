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

You can add entries to Bitty's container a few different ways.

### Via the Constructor

The most direct way is passing all of your entries in when you build the container.

The array key is the entry name and the value is an anonymous function that builds the thing you want to access. When the container calls the anonymous function, it passes in a reference to itself. This allows you to access anything from the container to inject into whatever thing you're building.

```php
<?php

use Acme\MyClass;
use Acme\MyOtherClass;
use Bitty\Container\Container;

$container = new Container(
    [
        'some_parameter' => function () {
            return 'i-need-this-value';
        },
        'my_service' => function ($container) {
            return new MyClass();
        },
        'my_other_service' => function ($container) {
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

### Using a Service Provider

WARNING: This is based on a [developing standard](https://github.com/container-interop/service-provider) and may change drastically.

The last option is to build a service provider using  `Interop\Container\ServiceProviderInterface` and pass it to the container.

```php
<?php

namespace Acme;

use Interop\Container\ServiceProviderInterface;

class MyServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            // Your factories
        ];
    }

    public function getExtensions()
    {
        return [
            // Your extensions
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
