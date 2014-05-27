<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/_base.php';

$start = microtime();
$replayedShoe = $repository->find('Knp\Event\Example\Shop\Product', $argv[1]);

var_dump((string)$replayedShoe);

