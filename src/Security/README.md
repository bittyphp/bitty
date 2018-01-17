# Bitty Security

Bitty supports multiple security layers, covering multiple secured areas, with different authentication methods, using multiple user providers, with multiple password encoders, and supports different authorization strategies for each area. That's a whole lot of security!

The best part? It does all this in a fairly tiny package.

For those interested, Bitty uses a [role-based access control (RBAC)](https://en.wikipedia.org/wiki/Role-based_access_control) security model.

## Setup

Security is added as a middleware component. This middleware is designed to be applied **before** all other middleware. Security should always be the first priority. However, it's up to you to ensure it's set up that way.

### Basic Usage

A basic application will likely only have one security layer with one secured area to shield.

```php
<?php

use Bitty\Application;
use Bitty\Security\SecurityMiddleware;
use Bitty\Security\Shield\FormShield;

$app = new Application();

// Add security first!
$app->add(
    new SecurityMiddleware(
        new FormShield(...)
    )
);

// Then add any other middleware components.
$app->add(...);
```

### Accessing the Security Context

At some point, you'll probably need access to the security context to determine who is logged in. Bitty does not force this context to be shared in a specific way, so it's up to you to share it. Luckily, that's fairly easy.

```php
<?php

use Bitty\Application;
use Bitty\Security\Context\ContextMap;
use Bitty\Security\SecurityMiddleware;
use Bitty\Security\Shield\FormShield;

$app = new Application();

// Define your context.
$myContext = new ContextMap();
$app->getContainer()->set('my_security_context', $myContext);

$app->add(
    new SecurityMiddleware(
        new FormShield(...),
        // Pass your context in.
        $myContext
    )
);

$request = $app->getContainer()->get('request');

// Access your context and see who is logged in.
$user = $app->getContainer()->get('my_security_context')->getUser($request);

```

## Shields

Bitty uses "shields" to protect secure areas from unauthorized access. One or multiple shields can be in place to protect the areas you want to secure. For example, you can have one shield to grant basic access and a completely separate shield to restrict access to an administration area. Multiple users can be logged into the separate areas at the same time. Or you can use one shield to secure both areas, but require different authorization for each area. It's all up to you.

Bitty comes with two built-in shields for granting access: an HTTP Basic shield and a form-based login shield. Not enough? No worries, you can use the `ShieldInterface` or extend the `AbstractShield` to grant access using any method you want. For example, you could build an `AuthTokenShield` to grant access using an API token or a `NetworkShield` to only allow certain IP ranges.

### Basic Usage

Each shield is designed to have its own security context, authentication method, authorization strategy, and configuration options. However, you can share any part of that with another shield simply by passing in the same object to both shields.

```php
<?php

use Bitty\Security\Authentication\Authenticator;
use Bitty\Security\Authorization\Authorizer;
use Bitty\Security\Context\Context;
use Bitty\Security\Shield\FormShield;

$myShield = new FormShield(
    new Context(...),
    new Authenticator(...),
    new Authorizer(...),
    $options
);
```

### Advanced Usage

For more advanced setups, you might need multiple shields to protect different areas based on different rules. Not a problem! You can build a collection of shields to do exactly that!

```php
<?php

use Bitty\Security\Shield\FormShield;
use Bitty\Security\Shield\HttpBasicShield;
use Bitty\Security\Shield\ShieldCollection;

$myShield = new ShieldCollection(
    [
        // Protect area 1
        new FormShield(...),

        // Protect area 2
        new HttpBasicShield(...),

        // Protect area 3
        new FormShield(...),
    ]
);
```

You can get even more advanced by stacking a `ShieldCollection` inside another `ShieldCollection`. Or if you set up the shields inside a collection to share the same context, they can become really strong layers of security. For example, you could build a `NetworkShield` to block access based on IP address and then have a `FormShield` show up only for users with a valid IP. As long as both shields have the same context, they will both protect the same area.

## Context

Each shield has its own security context to define which area(s) to secure and to keep track of who is logged in. The context is automatically added to the `ContextMap` of the `SecurityMiddleware`. This allows the security layer to determine who is logged in even if you have multiple shields configured.

Bitty only comes with a session-based security context. Don't want to track users that way? No problemo! You can create your own security context by using the `ContextInterface`. For example, if you were to create an API token shield, you'd probably want to make an `InMemoryContext` so that authentication doesn't persist on subsequent requests.

### Basic Usage

At a minimum, you need to give a name to the context and a list of paths to protect. The name is used to store authentication data. Different contexts might require different authentication, so it's important to keep it all separate.

The list of paths should be an array indexed by a regex pattern with an array of roles required to access the path as the value. In case that didn't make sense, it's probably easier to see it as code:

```php
<?php

$paths = [
    'some_regex' => ['list', 'of', 'roles'],
    // ...
];
```

Since the pattern is a regex, you can get very specific - just make sure you escape any special characters! To allow anyone to access a path, use an empty array for the roles.

Just remember, the first pattern that matches is the one used. So always put your "allow" statements at the top, then your "deny" statements. Ordering matters. If you do it wrong, you might block all access.

```php
<?php

use Bitty\Security\Context\Context;

// Do this!
$context = new Context(
    'my_secure_area',
    [
        // Allow anyone to access /admin/login
        '^/admin/login$' => [],

        // Restrict all other /admin/ access to user's with ROLE_ADMIN
        '^/admin/' => ['ROLE_ADMIN'],
    ]
);

// DON'T do this.
$context = new Context(
    'my_secure_area',
    [
        // Restrict all /admin/ access to user's with ROLE_ADMIN
        '^/admin/' => ['ROLE_ADMIN'],

        // Now no one can log in.
        '^/admin/login$' => [],
    ]
);
```

### Advanced Usage

You can also control additional aspects of the security context by overriding some of the default parameters.

```php
<?php

use Bitty\Security\Context\Context;

$context = new Context(
    'my_secure_area',
    [
        // Your paths
        ...
    ],
    [
        // Whether or not this is the default context.
        'default' => true,

        // How long (in seconds) sessions are good for.
        // Defaults to 24 hours.
        'ttl' => 86400,

        // Timeout (in seconds) to invalidate a session after no activity.
        // Defaults to zero (disabled).
        'timeout' => 0,

        // Delay (in seconds) to wait before destroying an old session.
        // Sessions are flagged as "destroyed" during re-authentication.
        // Allows for a network lag in asynchronous applications.
        'destroy.delay' => 300
    ]
);
```

Another option is to create a custom context by overwriting `Context::getDefaultConfig()`. You could then use your custom context in different shields or different applications and always have your desired defaults.

## Authentication

The built-in authentication supports any number of user providers which can all use the same password encoder or different classes of users can use different encoders.

Bitty only comes with an `InMemoryUserProvider`. You'll most likely want to load users from a database, so you'll have to build a custom user provider using the `UserProviderInterface`. The User Provider section goes into more detail on how to create custom providers.

### Basic Usage

A simple application will probably only have one source of users that all use the same password encoding method.

```php
<?php

use Bitty\Security\Authentication\Authenticator;
use Bitty\Security\Encoder\BcryptEncoder;
use Bitty\Security\User\Provider\InMemoryUserProvider;

$authenticator = new Authenticator(
    new InMemoryUserProvider(
        [
            'user' => [
                // Password is "user"
                'password' => '$2y$10$99Ru4p3RYylJObg919g1iOCvbI0hPl/glCjRwITNQ7cHO6jxdumrC',
                'roles' => ['ROLE_USER'],
            ],
            'admin' => [
                // Password is "admin"
                'password' => '$2y$10$mcjBnwIm90iz6OH0HXEyGO3QWaCdO29RX60uiBzMqrenBsEHgIARK',
                'roles' => ['ROLE_ADMIN'],
            ],
        ]
    ),
    new BcryptEncoder()
);
```

### Advanced Usage

You may also want to load users from different sources and each source might need to use a different password encoder. No worries, there's a class for that. We'll simply create a `UserProviderCollection` and the authentication layer will look for a user from each user provider until it finds one.

Once it does find a user, it will look at the list of encoders to determine how to encode the password for the specific type of user that was returned.

This is very similar to (and inspired by) [the way Symfony does it](https://symfony.com/doc/current/security/multiple_user_providers.html).

```php
<?php

use Bitty\Security\Authentication\Authenticator;
use Bitty\Security\Encoder\PlainTextEncoder;
use Bitty\Security\User\Provider\InMemoryUserProvider;
use Bitty\Security\User\Provider\UserProviderCollection;
use Bitty\Security\User\User;

$authenticator = new Authenticator(
    new UserProviderCollection(
        [
            // Returns instance of Bitty\Security\User\User
            new InMemoryUserProvider(
                [
                    'user' => [
                        'password' => 'user',
                        'roles' => ['ROLE_USER'],
                    ],
                    'admin' => [
                        'password' => 'admin',
                        'roles' => ['ROLE_ADMIN'],
                    ],
                ]
            ),
            // ...
        ]
    ),
    [
        // Define which user classes use which encoders.
        User::class => new PlainTextEncoder(),
    ]
);
```

### User Providers

All users are loaded using a user provider. However, the only user provider that comes with Bitty is the `InMemoryUserProvider`. Luckily, we can build any sort of custom user provider using the `UserProviderInterface`.

#### Creating a Custom User

Each user provider is expected to return an instance of `UserInterface`. If we want to make our own user provider, we'll first have to make a user it can return.

The user object is stored in the session, so the less data there is to store, the better. Other than the interface methods, you may want to define a `__sleep` or `__wakeup` method to define what properties are safe to store in the session.

```php
<?php

use Bitty\Security\User\UserInterface;

class MyUser implements UserInterface
{
    // ...

    /**
     * Only serialize non-sensitive data.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return ['id', 'username', 'roles'];
    }
}
```

#### Creating a Custom User Provider

Now that we have a user, we'll need to make a way of loading it. That's where the `UserProviderInterface` comes in. Alternatively, you can extend the `AbstractUserProvider`, but it is not required.

In this example, we're going to build a very basic database user provider.

```php
<?php

use Bitty\Security\Exception\AuthenticationException;
use Bitty\Security\User\Provider\UserProviderInterface;
use Bitty\Security\User\UserInterface;

class MyDatabaseUserProvider implements UserProviderInterface
{
    protected $db = null;

    public function __construct($user, $pass, $db, $host = 'localhost')
    {
        $this->db = new \PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
    }

    public function getUser($username)
    {
        // Protect against absurdly long usernames.
        if (strlen($username) > UserProviderInterface::MAX_USERNAME_LEN) {
            throw new AuthenticationException('Invalid username.');
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);

        $user = $stmt->fetch();
        if (!$user) {
            return;
        }

        return new MyUser($user);
    }
}
```

### Encoders

Encoders both encode and verify passwords. There are three encoders that come with Bitty that should handle most needs: `PlainTextEncoder`, `MessageDigestEncoder`, and the `BcryptEncoder` (recommended default).

#### PlainTextEncoder

The `PlainTextEncoder`, as you may have guessed, doesn't actually encode a password; it simply returns password as it was received. It comes in handy when testing the authentication system, but is definitely not recommended for real world use.

```php
<?php

use Bitty\Security\Encoder\PlainTextEncoder;

$encoder = new PlainTextEncoder();

$encoder->encode('password');
```

#### MessageDigestEncoder

The `MessageDigestEncoder` wraps PHP's built-in `hash` function and supports a wide variety of hashing algorithms. This includes md5, sha1, sha256, sha512, and an entire list of others.

```php
<?php

use Bitty\Security\Encoder\MessageDigestEncoder;

$algorithm = 'sha256';
$encoder   = new MessageDigestEncoder($algorithm);

$encoder->encode('password');
```

#### BrcyptEncoder

The recommended default encoder is the `BcryptEncoder`. It wraps PHP's `password_hash` and `password_verify` functions and is likely to be the most secure and reliable method of encoding user passwords.

```php
<?php

use Bitty\Security\Encoder\BrcyptEncoder;

$cost    = 10;
$encoder = new BrcyptEncoder($cost);

$encoder->encode('password');
```

#### Custom Encoders

If the default encoders aren't enough, you can also build your own using the `EncoderInterface` or by extending the `AbstractEncoder`. For example, if you're using PHP 7.2+, you could make an Argon2 encoder. This is the hashing function recommended by the [Open Web Application Security Project (OWASP)](https://www.owasp.org/index.php/Password_Storage_Cheat_Sheet).

## Authorization

TODO: Write this.

### Strategies

TODO: Write this.

### Voters

TODO: Write this.
