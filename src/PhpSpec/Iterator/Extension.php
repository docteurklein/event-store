<?php

namespace PhpSpec\Iterator;

use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\ServiceContainer;
use PhpSpec\Iterator\Maintainer;

class Extension implements ExtensionInterface
{
    public function load(ServiceContainer $container)
    {
        $container->setShared('runner.maintainers.iterator', function ($c) {
            return new Maintainer($c->get('unwrapper'));
        });
    }
}
