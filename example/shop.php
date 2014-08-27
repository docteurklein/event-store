<?php

namespace Knp\Event\Example\Shop;

use example\Shop\Model\Cart;
use Rhumsaa\Uuid\Uuid;
use example\Shop\Model\Item;
use example\Shop\Model\Product;

require __DIR__.'/_base.php';

$cart = $repository->find(Cart::class, $argv[2])->getOrCall(function() {
    return new Cart(Uuid::uuid4());
});

$product = $repository->find(Product::class, $argv[1])->get();
var_dump((string)$product);

$cart->addItem(new Item(Uuid::uuid4(), $product->getId(), $argv[3]));
$repository->save($cart);

var_dump((string) $cart);
