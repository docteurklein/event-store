<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/_base.php';

$cart = $repository->find('Knp\Event\Example\Shop\Cart', $argv[2])->getOrCall(function() {
    return new \Knp\Event\Example\Shop\Cart(\Rhumsaa\Uuid\Uuid::uuid4());
});

$product = $repository->find('Knp\Event\Example\Shop\Product', $argv[1])->get();
var_dump((string)$product);

$cart->addItem(new \Knp\Event\Example\Shop\Item(\Rhumsaa\Uuid\Uuid::uuid4(), $product->getId(), $argv[3]));
$repository->save($cart);

var_dump((string) $cart);
