<?php

namespace TestWork\PhpSpecExtension;

use Behat\Testwork\ServiceContainer;
use Symfony\Component\DependencyInjection\Definition;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension implements ServiceContainer\Extension
{
    public function getConfigKey()
    {
        return 'phpspec';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function process(ContainerBuilder $container)
    {

    }

    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('TestWork\PhpSpecExtension\ExceptionStringer');
        $definition->addTag(ExceptionExtension::STRINGER_TAG, ['priority' => 50]);
        $container->setDefinition(ExceptionExtension::STRINGER_TAG . '.phpspec', $definition);
    }
}
