<?php

namespace Knp\Event\Example\Shop;

require __DIR__.'/_base.php';

foreach (range(1, 1000) as $i) {

    $shoe = new Product($idShoe = \Rhumsaa\Uuid\Uuid::uuid4(), "a $i shoe", [
        new Attribute(\Rhumsaa\Uuid\Uuid::uuid4(), 'size', 4),
        new Attribute(\Rhumsaa\Uuid\Uuid::uuid4(), 'color', 'blue'),
        new Attribute(\Rhumsaa\Uuid\Uuid::uuid4(), 'price', new Price('EUR', 40 * $i)),
    ]);

    foreach (range(1, 10) as $j) {
        $shoe->rename("shoe $i $j");
        $shoe->addAttribute(new Attribute(\Rhumsaa\Uuid\Uuid::uuid4(), "length $i", 10 + $j));
    }
    $repository->save($shoe);

    var_dump((string)$shoe);
}
