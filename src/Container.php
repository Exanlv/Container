<?php

namespace Exan\Container;

use Exan\Container\Exceptions\BuildItemException;
use Exan\Container\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

class Container implements ContainerInterface
{
    private array $items = [];

    /**
     * @template T
     * @param class-string<T> $id
     * @return T
     *
     * @throws BuildItemException
     * @throws NotFoundException
     */
    public function get(string $id)
    {
        if (!$this->hasActive($id)) {
            try {
                $this->build($id);
            } catch (BuildItemException $e) {
                if ($this->canBuild($id)) {
                    throw $e;
                }

                throw new NotFoundException(previous: $e);
            }
        }

        return $this->items[$id];
    }

    public function has(string $id): bool
    {
        return $this->hasActive($id) || $this->canBuild($id);
    }

    public function register(string $id, mixed $item)
    {
        $this->items[$id] = $item;
    }

    private function hasActive(string $id): bool
    {
        return isset($this->items[$id]);
    }

    private function canBuild(string $id): bool
    {
        try {
            $args = $this->getArgRequirements($id);
        } catch (BuildItemException) {
            return false;
        }

        foreach ($args as $arg) {
            if (!$this->has($arg)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return class-string[]
     * @throws BuildItemException
     */
    private function getArgRequirements(string $id): array
    {
        try {
            $reflectionClass = new ReflectionClass($id);
        } catch (ReflectionException $e) {
            throw new BuildItemException(previous: $e);
        }

        $reflectionConstructor = $reflectionClass->getConstructor();

        if (is_null($reflectionConstructor)) {
            return [];
        }

        return array_map(function (ReflectionParameter $param) {
            $type = $param->getType();

            if (is_null($type)) {
                throw new BuildItemException('No valid type set');
            }

            if ($type instanceof ReflectionNamedType) {
                return $type->getName();
            }

            foreach ($type->getTypes() as $typeOfUnion) {
                if ($this->canBuild($typeOfUnion->getName())) {
                    return $typeOfUnion->getName();
                }
            }

            throw new BuildItemException('No types for parameter can be built: ' . $param->getName());
        }, $reflectionConstructor->getParameters());
    }

    /**
     * @throws BuildItemException
     */
    private function build(string $id): void
    {
        try {
            $argsRequired = $this->getArgRequirements($id);

            $args = array_map($this->get(...), $argsRequired);

            $this->items[$id] = new $id(...$args);
        } catch (BuildItemException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new BuildItemException(previous: $e);
        }
    }
}
