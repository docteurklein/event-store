<?php

use example\Shop\Model\Product;
use example\Shop\Model\Attribute;
use Rhumsaa\Uuid\Uuid;

require __DIR__.'/_base.php';

$product = $repository->find(Product::class, $argv[1])->getOrCall(function() {
    return new Product;
});

foreach (range(1, 1000) as $i) {
    $product->rename("product $i");
    $product->addAttribute(new Attribute(Uuid::uuid4(), "length $i", 10 + $i));
    echo "$i\n";
    $repository->save($product);
}


var_dump((string)$product);
