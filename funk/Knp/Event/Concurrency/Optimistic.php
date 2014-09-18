<?php

namespace funk\Knp\Event\Concurrency;

use Knp\Event\Store;
use example\Shop\Model\Product;
use Knp\Event\Exception\Concurrency\Optimistic\Conflict;
use Knp\Event\Repository;

class Optimistic implements \Funk\Spec
{
    private $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    function it_allows_to_store_aggregates_at_same_verison()
    {
        $repository = (new Repository\Factory(new Store\Concurrency\Optimistic($this->store)))->create();

        $repository->save($product = (new Product(null, 'test')));
        $fetch1 = $repository->find(Product::class, (string)$product->getId())->get();
        $fetch2 = $repository->find(Product::class, (string)$product->getId())->get();
        $fetch2->rename('change 2');
        $repository->save($fetch2);
        $fetch2->rename('change 1');
        try {
            $repository->save($fetch2);
        } catch (Conflict $e) {
            throw new \LogicException('No Conflict should have been detected.', null, $e);
        }
    }

    function it_refuses_to_store_superseeded_versions()
    {
        $repository = (new Repository\Factory(new Store\Concurrency\Optimistic($this->store)))->create();

        $repository->save($product = (new Product(null, 'test')));
        $fetch1 = $repository->find(Product::class, (string)$product->getId())->get();
        $fetch2 = $repository->find(Product::class, (string)$product->getId())->get();
        $fetch1->rename('change 1');
        $fetch2->rename('change 2');
        $repository->save($fetch1);
        try {
            $repository->save($fetch2);
        } catch (Conflict $e) {
            return;
        }

        throw new \LogicException('Conflict should have been detected.');
    }
}
