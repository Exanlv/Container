<?php

namespace Tests\Exan\Container;

use Exan\Container\Container;
use Exan\Container\Exceptions\BuildItemException;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Exan\Container\Dummy\ClassWithDependency;
use Tests\Exan\Container\Dummy\Dependency;
use Tests\Exan\Container\Dummy\DummyInterface;

class ContainerTest extends TestCase
{
    public function testItBuildsAnItem()
    {
        $testClass = new class {};

        $container = new Container();

        $item = $container->get($testClass::class);

        $this->assertInstanceOf($testClass::class, $item);
    }

    public function testItCanTellWhetherItCanProvideAnItem()
    {
        $testClass = new class {};

        $container = new Container();

        $this->assertTrue($container->has($testClass::class));
    }

    /**
     * @dataProvider primitiveRequiringClassProvider
     */
    public function testItCanNotProvideItemsRequiringPrimitiveTypes($class)
    {
        $container = new Container();

        $this->assertFalse($container->has($class::class));
    }

    public static function primitiveRequiringClassProvider(): array
    {
        return [
            'string' => [
                'class' => new class ('string') {
                    public function __construct(string $someString)
                    {
                    }
                }
            ],
            'int' => [
                'class' => new class (123) {
                    public function __construct(int $someInt)
                    {
                    }
                }
            ],
            'array' => [
                'class' => new class ([]) {
                    public function __construct(array $someArray)
                    {
                    }
                }
            ],
            'bool' => [
                'class' => new class (true) {
                    public function __construct(bool $someBool)
                    {
                    }
                }
            ],
            'float' => [
                'class' => new class (1.23) {
                    public function __construct(float $someFloat)
                    {
                    }
                }
            ],
            'object' => [
                'class' => new class ((object) []) {
                    public function __construct(object $someFloat)
                    {
                    }
                }
            ],
        ];
    }

    public function testItResolvesDependenciesOfClasses()
    {
        $container = new Container();

        $class = $container->get(ClassWithDependency::class);

        $this->assertInstanceOf(ClassWithDependency::class, $class);
        $this->assertInstanceOf(Dependency::class, $class->dependency);
    }

    public function testItCanRegisterInterfaces()
    {
        $container = new Container();

        $class = new class implements DummyInterface {};

        $container->register(DummyInterface::class, $class);

        $this->assertEquals($class, $container->get(DummyInterface::class));
    }

    public function testItHandlesUnionTypes()
    {
        $testClass = new class ('string') {
            public function __construct(public readonly string|Dependency $dependency)
            {
            }
        };

        $container = new Container();

        $item = $container->get($testClass::class);

        $this->assertInstanceOf($testClass::class, $item);
        $this->assertInstanceOf(Dependency::class, $item->dependency);
    }

    public function testItThrowsAnErrorIfNoneOfUnionTypesCanBeInitialized()
    {
        $testClass = new class ('string') {
            public function __construct(public readonly string|int $dependency)
            {
            }
        };

        $container = new Container();

        $this->expectException(BuildItemException::class);
        $container->get($testClass::class);
    }

    public function testItThrowsAnErrorIfADependencyDoesNotHaveAType()
    {
        $testClass = new class ('string') {
            public function __construct($dependency)
            {
            }
        };

        $container = new Container();

        $this->expectException(BuildItemException::class);
        $container->get($testClass::class);
    }

    public function testItCatchesErrorsThrownInItemResolution()
    {
        $testClass = new class {
            private static $isInitializedOnce = false;
            public function __construct()
            {
                /**
                 * Constructor runs once when using anonymous classes, first time is not
                 * inside the container and should therefore not throw an error
                 */
                if (!self::$isInitializedOnce) {
                    self::$isInitializedOnce = true;
                    return;
                }

                throw new Exception();
            }
        };

        $container = new Container();

        $this->expectException(BuildItemException::class);
        $container->get($testClass::class);
    }
}
