# Container

A minimal PSR-11 compliant container. Automatically resolves dependencies when possible.

## Install

```sh
composer require exan/container
```

## Usage

```php
$container = new Exan\Container();

$someClass = $container->get(SomeClass::class);
```

This will try to instantiate an instance of `SomeClass` for you and returns it. If you then request this class again further on, it will return the same instance.

Assuming no unresolvable dependencies, it will also resolve nested dependencies.

```php
class SomeClass
{
    public function __construct(public readonly Dependency $dependency)
    {
        // ...
    }
}

$container = new Exan\Container();

$someClass = $container->get(SomeClass::class);
```

`SomeClass` in this example will be instantiated with an instance of `Dependency`.

It can also handle interfaces, but will not be able to resolve an implementation of said interface automatically. You will need to register an entry manually.

```php
interface SomeInterface
{
    // ...
}

class SomeClass implements SomeInterface
{
    // ...
}

$container = new Exan\Container();

$container->register(SomeInterface::class, new SomeClass())

$someClass = $container->get(SomeInterface::class);
```

Note: it does not automatically resolve primitive types like `string` and `int`.
