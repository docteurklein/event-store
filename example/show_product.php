<?php

namespace Knp\Event\Example\Shop;

use example\Shop\Model\Product;

require __DIR__.'/_base.php';

$product = $repository->find(Product::class, $argv[1])->get();
var_dump((string)$product);

