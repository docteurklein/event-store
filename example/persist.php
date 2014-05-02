<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/_base.php';

$evm = new \Doctrine\Common\EventManager;
$evm->addEventSubscriber(new RDBMProjector);

$repository = new \Knp\Event\Repository(
    new \Knp\Event\Store\Dispatcher(
        //new \Knp\Event\Store\InMemory,
        new \Knp\Event\Store\Rdbm,
        $evm
    ),
    new \Knp\Event\Player
);

$start = microtime();
$replayedShoe = $repository->find('Knp\Event\Example\Shop\Product', $argv[1]);

var_dump((string)$replayedShoe);

var_dump((microtime() - $start)  * 1000);

