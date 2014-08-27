<?php

use example\Shop\Model\Product;
use example\Shop\Model\Attribute;
use Rhumsaa\Uuid\Uuid;
use example\Shop\Model\Price;

require __DIR__.'/_base.php';

foreach (range(1, 1000) as $i) {

    $shoe = new Product($idShoe = Uuid::uuid4(), "a $i shoe", [
        new Attribute(Uuid::uuid4(), 'size', 4),
        new Attribute(Uuid::uuid4(), 'color', 'blue'),
        new Attribute(Uuid::uuid4(), 'price', new Price('EUR', 40 * $i)),
    ]);

    foreach (range(1, 10) as $j) {
        $shoe->rename("shoe $i $j");
        $shoe->addAttribute(new Attribute(Uuid::uuid4(), "length $i", 10 + $j));
    }
    $repository->save($shoe);

    var_dump((string)$shoe);
}
