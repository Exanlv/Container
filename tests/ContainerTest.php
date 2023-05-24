<?php

namespace Tests\Exan\Container;

use Exan\Container\Container;
use PHPUnit\Framework\TestCase;

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
}
