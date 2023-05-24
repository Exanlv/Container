<?php

namespace Tests\Exan\Container\Dummy;

class ClassWithDependency
{
    public function __construct(public readonly Dependency $dependency)
    {
    }
}
