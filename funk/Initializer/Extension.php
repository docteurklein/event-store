<?php

namespace funk\Initializer;

use Behat\Testwork\ServiceContainer\Extension as TestWorkExtension;
use Funk\Tester\ServiceContainer\TesterExtension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class Extension implements TestWorkExtension
{
    public function getConfigKey()
    {
        return 'initializer';
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
        $definition = new Definition('funk\Initializer\Store');
        $definition->addTag(TesterExtension::INITIALIZER_TAG);
        $container->setDefinition(TesterExtension::INITIALIZER_TAG . '.store', $definition);
    }
}
