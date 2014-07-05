<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/_base.php';

$product = $repository->find('Knp\Event\Example\Shop\Product', $argv[1])->get();
var_dump((string)$product);

