<?php

use Exan\Container\Container;

require_once './vendor/autoload.php';

class Controller
{
    public function __construct(private readonly SomeInterface $sub)
    {
        echo 'hi';
    }
}

interface SomeInterface
{

}

class Sub implements SomeInterface
{
    public function __construct()
    {
    }
}

$container = new Container();

$container->register(SomeInterface::class, new Sub());

var_dump($container->get(Controller::class));
