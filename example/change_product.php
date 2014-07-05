<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/_base.php';

$product = $repository->find('Knp\Event\Example\Shop\Product', $argv[1])->get();
foreach (range(1, 1000) as $i) {
    $product->rename("product $i");
    $product->addAttribute(new Attribute(\Rhumsaa\Uuid\Uuid::uuid4(), "length $i", 10 + $i));
    echo "$i\n";
    $repository->save($product);
}


var_dump((string)$product);
