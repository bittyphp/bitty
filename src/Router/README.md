# Router

Bitty's router supports [PSR-7](http://www.php-fig.org/psr/psr-7/) HTTP message interfaces. When a route is called, it is passed an instance of `Psr\Http\Message\ServerRequestInterface` and must return an instance of `Psr\Http\Message\ResponseInterface`.

## Adding a Route

Routes can be customized pretty well. Bitty supports the following options:

1. **Multiple HTTP request methods.** Have a single route match against one or multiple request methods.
2. **Resource pattern matching.** Specify pattern constraints for routes to match on resource IDs, blog slugs, specific file patterns, or anything else you can think of. The matching parameters are automatically passed into the route callback as an associative array.
3. **Multiple controller callback types.** Controllers can be defined using an anonymous function, invokable class, or an actual controller file with an action method to call.
4. **Named routes.** Routes can be named to make them more easy to reference. Of course, unnamed routes are supported, too.

### Basic Usage

For convenience, routes can be added directly from the application level. Or you can choose to access the router directly and add routes to it.

```php
<?php

use Bitty\Application;
use Bitty\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

$app = new Application();

// You can add routes directly from the application
$app->addRoute('GET', '/resource/path', function (ServerRequestInterface $request) {
    return new Response('Hello, world!');
});

// The above example has the same effect as:
$router = $app->getContainer()->get('router');
$router->add('GET', '/resource/path', function (ServerRequestInterface $request) {
    return new Response('Hello, world!');
});
```

### Multiple Request Methods

If you want, you can use the same route for multiple request methods. For the first parameter, simply pass in an array listing all the methods you want. Then you can use the request object to determine what method was used, if needed.

```php
<?php

use Bitty\Application;
use Bitty\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

$app = new Application();

$app->addRoute(['GET', 'POST'], '/resource/path', function (ServerRequestInterface $request) {
    if ($request->getMethod() === 'GET') {
        return new Response('You did a GET');
    }

    return new Response('You did a POST');
});
```

### Resource Pattern Matching

You can define patterns to create routes that automatically extract variables from the route path, e.g. getting a product's ID or the slug for a blog entry. Routes that contain patterns MUST specify the constraints to fulfill those patters. Constraints are specified by passing in an additional array.

```php
<?php

use Bitty\Application;
use Bitty\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

$app = new Application();

$app->addRoute(
    'GET',

    // Define the variables by placing curly brackets around a string.
    // You can define as many variables as needed.
    '/products/{id}',

    // Our callback can access the variable using the additional $params
    function (ServerRequestInterface $request, array $params) {
        return new Response('You requested product '.$params['id']);
    },

    // Define the constraints. In this case, only look for digits.
    ['id' => '\d+']
);
```

The above example essentially combines the path and the constraints to create a regex pattern of `/products/(\d+)`. It will match on requests for `/products/123`, but will not match on `/products/ABC123`.

### Multiple Callback Types

To be as flexible as possible, Bitty supports using a couple different styles of callbacks. You can use an anonymous function or pass in a string. If using a string, it should reference a class or container object to build. Optionally, the string can also include a method to call. If no method is given, it assumes `__invoke` should be used. The class and method should be separated with a colon (`:`), e.g. `Acme\\SomeClass:someMethod`.

Lets create a route that triggers an action in a controller class. First, we make the controller.

```php
<?php

use Acme\Controller;
use Bitty\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class ExampleController
{
    public function test(ServerRequestInterface $request)
    {
        return new Response('Hey, the controller worked!');
    }
}
```

Now we create a route and point it to the class we made and tell it what method to call. When someone visits the route, the `ExampleController` will be built and then the `test` method will be called. If `ExampleController` is defined in the container, it will be loaded from the container.

```php
<?php

use Acme\Controller\ExampleController;
use Bitty\Application;

$app = new Application();

$app->addRoute('GET', '/resource/path', ExampleController::class.':test');
```

### Named Routes

Named routes are handy if you know you'll be referencing them later, like by building a URL that points to it. You can specify the name for a route by passing in a fifth parameter (remember, the fourth parameter is an array of constraints).

```php
<?php

use Acme\Controller\ExampleController;
use Bitty\Application;

$app = new Application();

$app->addRoute('GET', '/foo', ExampleController::class, [], 'foo_route');
```
