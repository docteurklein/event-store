<?php

namespace funk\Initializer;

use Funk\Initializer\Spec as SpecInitializer;
use Funk\Spec;
use Behat\Testwork\Suite\Suite;

class Store implements SpecInitializer
{
    private $stores;
    private $serializer;

    public function __construct()
    {
        $this->serializer = (new \Knp\Event\Serializer\Jms\Builder)->build();
        $this->stores = [
            'memory' => function(Suite $suite) { return new \Knp\Event\Store\InMemory; },
            'pdo'    => function(Suite $suite) { return new \Knp\Event\Store\Pdo\Store(
                new \PDO("pgsql:dbname={$suite->getSetting('dbname')}", null, null, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_EMULATE_PREPARES => 0,
                ]),
                $this->serializer
            ); },
            'mongo'  => function(Suite $suite) {
                return new \Knp\Event\Store\Mongo((new \MongoClient)->selectDB($suite->getSetting('dbname')), $this->serializer);
            },
        ];
    }

    public function isSupported(Suite $suite, \ReflectionClass $reflect)
    {
        return true;
    }

    public function resolveArguments(Suite $suite, \ReflectionMethod $constructor)
    {
        $arguments = $constructor->getParameters();
        foreach ($arguments as &$argument) {
            if ($argument->getClass() && is_a($argument->getClass()->name, 'Knp\Event\Store', true)) {
                $argument = $this->getStore($suite);
            }
        }

        return $arguments;
    }

    public function initialize(Suite $suite, Spec $spec)
    {
    }

    private function getStore(Suite $suite)
    {
        return call_user_func($this->stores[$suite->getName()], $suite);
    }
}
